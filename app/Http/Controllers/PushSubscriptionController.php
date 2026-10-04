<?php

namespace App\Http\Controllers;

use App\Notifications\WebPushGenericNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Store or update a push subscription for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['nullable', 'string'],
            'keys.auth' => ['nullable', 'string'],
            'public_key' => ['nullable', 'string'],
            'auth_token' => ['nullable', 'string'],
            'content_encoding' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $endpoint = $request->input('endpoint');
        $key = $request->input('keys.p256dh') ?? $request->input('public_key');
        $token = $request->input('keys.auth') ?? $request->input('auth_token');
        $encoding = $request->input('content_encoding', 'aes128gcm');

        $user->updatePushSubscription($endpoint, $key, $token, $encoding);

        return response()->json([
            'success' => true,
            'message' => 'Push subscription saved successfully.',
        ]);
    }

    /**
     * Delete a push subscription.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        $user = $request->user();
        if ($user) {
            $user->deletePushSubscription($request->input('endpoint'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Push subscription removed successfully.',
        ]);
    }

    /**
     * Send a test push notification to the authenticated user.
     */
    public function test(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $user->pushSubscriptions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No active push subscriptions found on your account. Please enable notifications first.',
            ], 422);
        }

        try {
            $user->notify(new WebPushGenericNotification(
                title: '🎉 Push Notifications Active!',
                body: 'You will now receive instant alerts for chat messages and system notifications even when Kamkaj is closed.',
                actionUrl: url('/kamkaj'),
                icon: asset('pwa-icons/icon-192x192.png'),
                tag: 'test-push-notification-'.time(),
                data: ['test' => true]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Test push notification dispatched!',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test push notification: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return the VAPID public key.
     */
    public function vapidPublicKey(): JsonResponse
    {
        $key = config('webpush.vapid.public_key') ?: env('VAPID_PUBLIC_KEY', '');

        return response()->json([
            'publicKey' => (string) $key,
        ]);
    }
}
