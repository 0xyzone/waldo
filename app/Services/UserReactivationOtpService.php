<?php

namespace App\Services;

use App\Mail\UserReactivationOtpMail;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class UserReactivationOtpService
{
    /**
     * OTP validity in minutes.
     */
    public const OTP_EXPIRY_MINUTES = 10;

    /**
     * Cooldown in seconds before a new OTP can be requested (1 minute).
     */
    public const COOLDOWN_SECONDS = 60;

    /**
     * Maximum OTP send requests allowed per target user within the decay window.
     */
    public const MAX_SEND_ATTEMPTS = 5;

    /**
     * Send rate limit decay in seconds (15 minutes).
     */
    public const SEND_RATE_LIMIT_DECAY = 900;

    /**
     * Maximum incorrect verification attempts allowed per OTP before it is invalidated.
     */
    public const MAX_VERIFY_ATTEMPTS = 5;

    /**
     * Lockout window in seconds after exhausting verification attempts (15 minutes).
     */
    public const VERIFY_LOCKOUT_DECAY = 900;

    /**
     * Get the configured email address that receives the reactivation OTP.
     */
    public function getConfiguredEmail(): string
    {
        $setting = AppSetting::get('user_reactivation_otp_email');

        if (! empty($setting) && filter_var($setting, FILTER_VALIDATE_EMAIL)) {
            return trim($setting);
        }

        $envEmail = env('USER_REACTIVATION_OTP_EMAIL');
        if (! empty($envEmail) && filter_var($envEmail, FILTER_VALIDATE_EMAIL)) {
            return trim($envEmail);
        }

        /** @var User|null $superAdmin */
        $superAdmin = User::role('super_admin')->first();
        if ($superAdmin && ! empty($superAdmin->email)) {
            return $superAdmin->email;
        }

        return (string) config('mail.from.address', 'admin@example.com');
    }

    /**
     * Get masked version of the configured email for display (e.g. su***@example.com).
     */
    public function getMaskedEmail(?string $email = null): string
    {
        $email = $email ?: $this->getConfiguredEmail();

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];

        if (strlen($name) <= 2) {
            $maskedName = substr($name, 0, 1).'*';
        } else {
            $maskedName = substr($name, 0, 2).str_repeat('*', max(1, strlen($name) - 3)).substr($name, -1);
        }

        return $maskedName.'@'.$domain;
    }

    /**
     * Get remaining cooldown seconds before a new OTP can be dispatched (0 if cooled down).
     */
    public function getCooldownRemaining(User $targetUser): int
    {
        $expiresAt = Cache::get($this->getCooldownKey($targetUser));

        if (! $expiresAt) {
            return 0;
        }

        $remaining = (int) ($expiresAt - now()->timestamp);

        return max(0, $remaining);
    }

    /**
     * Generate and dispatch a 6-digit OTP with strict cooldown and rate limiting.
     *
     * @return array{status: string, email: string, masked_email: string, cooldown: int}
     *
     * @throws ValidationException
     */
    public function generateAndSendOtp(User $targetUser, User $requester, bool $isResend = false): array
    {
        $sendLimitKey = $this->getSendRateLimitKey($targetUser);

        // Check overall send rate limit
        if (RateLimiter::tooManyAttempts($sendLimitKey, self::MAX_SEND_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($sendLimitKey);
            $minutes = max(1, (int) ceil($seconds / 60));

            throw ValidationException::withMessages([
                'otp' => "Too many OTP requests for this account. Please wait {$minutes} minute(s) before requesting again.",
            ]);
        }

        $cooldown = $this->getCooldownRemaining($targetUser);

        if ($cooldown > 0) {
            if ($isResend) {
                throw ValidationException::withMessages([
                    'otp' => "Please wait {$cooldown} second(s) before requesting another OTP.",
                ]);
            }

            // If modal is re-opened and active OTP already exists within cooldown, don't re-send duplicate email
            if ($this->hasActiveOtp($targetUser)) {
                return [
                    'status' => 'existing',
                    'email' => $this->getConfiguredEmail(),
                    'masked_email' => $this->getMaskedEmail(),
                    'cooldown' => $cooldown,
                ];
            }
        }

        // Record send attempt
        RateLimiter::hit($sendLimitKey, self::SEND_RATE_LIMIT_DECAY);

        $otp = sprintf('%06d', random_int(100000, 999999));
        $cacheKey = $this->getCacheKey($targetUser);

        Cache::put($cacheKey, [
            'code' => $otp,
            'requester_id' => $requester->id,
            'target_user_id' => $targetUser->id,
            'created_at' => now()->timestamp,
        ], now()->addMinutes(self::OTP_EXPIRY_MINUTES));

        // Set cooldown timer
        Cache::put(
            $this->getCooldownKey($targetUser),
            now()->timestamp + self::COOLDOWN_SECONDS,
            self::COOLDOWN_SECONDS
        );

        // Clear verify attempt counter for the fresh OTP
        RateLimiter::clear($this->getVerifyRateLimitKey($targetUser));

        $recipientEmail = $this->getConfiguredEmail();

        Mail::to($recipientEmail)->send(
            new UserReactivationOtpMail($targetUser, $requester, $otp, self::OTP_EXPIRY_MINUTES)
        );

        return [
            'status' => 'sent',
            'email' => $recipientEmail,
            'masked_email' => $this->getMaskedEmail($recipientEmail),
            'cooldown' => self::COOLDOWN_SECONDS,
        ];
    }

    /**
     * Check if an active OTP is waiting for verification.
     */
    public function hasActiveOtp(User $targetUser): bool
    {
        return Cache::has($this->getCacheKey($targetUser));
    }

    /**
     * Verify the entered OTP code with brute-force lockout protection.
     *
     * @return array{status: 'success'|'locked'|'expired'|'invalid', message?: string}
     */
    public function verifyOtp(User $targetUser, string $enteredOtp): array
    {
        $verifyLimitKey = $this->getVerifyRateLimitKey($targetUser);

        // Check if brute-force lockout is triggered
        if (RateLimiter::tooManyAttempts($verifyLimitKey, self::MAX_VERIFY_ATTEMPTS)) {
            Cache::forget($this->getCacheKey($targetUser));
            $seconds = RateLimiter::availableIn($verifyLimitKey);
            $minutes = max(1, (int) ceil($seconds / 60));

            return [
                'status' => 'locked',
                'message' => "Too many failed attempts. This OTP was invalidated for security. Please wait {$minutes} minute(s) before requesting a new code.",
            ];
        }

        $cacheKey = $this->getCacheKey($targetUser);
        $data = Cache::get($cacheKey);

        if (! is_array($data) || empty($data['code'])) {
            return [
                'status' => 'expired',
                'message' => 'The OTP code has expired or was not found. Please click "Resend Code" to receive a new code.',
            ];
        }

        if (hash_equals((string) $data['code'], trim($enteredOtp))) {
            // Success: clear OTP, cooldown, and verification rate limits
            Cache::forget($cacheKey);
            Cache::forget($this->getCooldownKey($targetUser));
            RateLimiter::clear($verifyLimitKey);

            return [
                'status' => 'success',
            ];
        }

        // Increment failed attempt counter
        RateLimiter::hit($verifyLimitKey, self::VERIFY_LOCKOUT_DECAY);
        $attemptsLeft = self::MAX_VERIFY_ATTEMPTS - RateLimiter::attempts($verifyLimitKey);

        if ($attemptsLeft <= 0) {
            Cache::forget($cacheKey);

            return [
                'status' => 'locked',
                'message' => 'Too many incorrect attempts. This OTP has been invalidated for security. Please request a new code.',
            ];
        }

        return [
            'status' => 'invalid',
            'message' => "Incorrect OTP code. {$attemptsLeft} attempt(s) remaining before this code is invalidated.",
        ];
    }

    /**
     * Cache key for user OTP payload.
     */
    protected function getCacheKey(User $targetUser): string
    {
        return "user_reactivation_otp:{$targetUser->id}";
    }

    /**
     * Cache key for resend cooldown timestamp.
     */
    protected function getCooldownKey(User $targetUser): string
    {
        return "user_reactivation_otp_cooldown:{$targetUser->id}";
    }

    /**
     * Rate limiter key for OTP send frequency.
     */
    protected function getSendRateLimitKey(User $targetUser): string
    {
        return "user_reactivation_otp_send:{$targetUser->id}";
    }

    /**
     * Rate limiter key for failed verification attempts.
     */
    protected function getVerifyRateLimitKey(User $targetUser): string
    {
        return "user_reactivation_otp_verify:{$targetUser->id}";
    }
}
