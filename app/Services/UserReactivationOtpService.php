<?php

namespace App\Services;

use App\Mail\UserReactivationOtpMail;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class UserReactivationOtpService
{
    public const OTP_EXPIRY_MINUTES = 10;

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
     * Generate a new 6-digit OTP, store in cache for 10 minutes, and email it.
     *
     * @return array{email: string, masked_email: string}
     */
    public function generateAndSendOtp(User $targetUser, User $requester): array
    {
        $otp = sprintf('%06d', random_int(100000, 999999));
        $cacheKey = $this->getCacheKey($targetUser);

        Cache::put($cacheKey, [
            'code' => $otp,
            'requester_id' => $requester->id,
            'target_user_id' => $targetUser->id,
            'created_at' => now()->timestamp,
        ], now()->addMinutes(self::OTP_EXPIRY_MINUTES));

        $recipientEmail = $this->getConfiguredEmail();

        Mail::to($recipientEmail)->send(
            new UserReactivationOtpMail($targetUser, $requester, $otp, self::OTP_EXPIRY_MINUTES)
        );

        return [
            'email' => $recipientEmail,
            'masked_email' => $this->getMaskedEmail($recipientEmail),
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
     * Verify the entered OTP code.
     */
    public function verifyOtp(User $targetUser, string $enteredOtp): bool
    {
        $cacheKey = $this->getCacheKey($targetUser);
        $data = Cache::get($cacheKey);

        if (! is_array($data) || empty($data['code'])) {
            return false;
        }

        if (hash_equals((string) $data['code'], trim($enteredOtp))) {
            Cache::forget($cacheKey);

            return true;
        }

        return false;
    }

    /**
     * Cache key for user OTP.
     */
    protected function getCacheKey(User $targetUser): string
    {
        return "user_reactivation_otp:{$targetUser->id}";
    }
}
