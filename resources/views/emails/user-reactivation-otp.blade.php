<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Reactivation Security Code</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f4f7; font-family: 'Segoe UI', Arial, sans-serif; color: #1e293b; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 36px 40px 30px; text-align: center; color: #fff; }
        .header-badge { display: inline-block; padding: 4px 12px; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 9999px; font-size: 11px; font-weight: 700; color: #fca5a5; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 12px; }
        .header-title { font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px; }
        .body { padding: 36px 40px; }
        .intro { font-size: 15px; color: #475569; line-height: 1.7; margin-bottom: 24px; }
        .otp-container { background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 24px; text-align: center; margin: 24px 0; }
        .otp-label { font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 1px; margin-bottom: 8px; }
        .otp-code { font-family: 'Courier New', Courier, monospace; font-size: 38px; font-weight: 900; color: #0f172a; letter-spacing: 8px; margin: 4px 0; }
        .otp-expiry { font-size: 12px; color: #ef4444; font-weight: 600; margin-top: 8px; }
        .account-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; margin-bottom: 24px; }
        .account-title { font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; }
        .account-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px; }
        .account-row:last-child { margin-bottom: 0; }
        .account-label { color: #64748b; font-weight: 600; }
        .account-value { color: #0f172a; font-weight: 700; font-family: monospace; }
        .security-note { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px 16px; border-radius: 6px; font-size: 12px; color: #92400e; line-height: 1.6; margin-bottom: 24px; }
        .footer { border-top: 1px solid #f1f5f9; padding: 20px 40px; text-align: center; font-size: 12px; color: #94a3b8; background: #fafafa; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <div class="header-badge">Security Verification</div>
            <div class="header-title">User Account Reactivation OTP</div>
        </div>

        <div class="body">
            <p class="intro">
                A request has been initiated to <strong>reactivate</strong> a user account on <strong>{{ config('app.name') }}</strong> by Super Admin <strong>{{ $requester->name }}</strong>.
            </p>

            <div class="account-box">
                <div class="account-title">Target Account Details</div>
                <div class="account-row">
                    <span class="account-label">Name</span>
                    <span class="account-value">{{ $targetUser->name }}</span>
                </div>
                <div class="account-row">
                    <span class="account-label">Email</span>
                    <span class="account-value">{{ $targetUser->email }}</span>
                </div>
                @if ($targetUser->username)
                    <div class="account-row">
                        <span class="account-label">Username</span>
                        <span class="account-value">{{ $targetUser->username }}</span>
                    </div>
                @endif
            </div>

            <div class="otp-container">
                <div class="otp-label">Reactivation One-Time Password</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-expiry">⏱️ Valid for {{ $expiresMinutes }} minutes only</div>
            </div>

            <div class="security-note">
                <strong>Important:</strong> Enter this OTP in the reactivation modal to complete the reactivation. Without this code, the user cannot be activated. If you did not request this, please review your admin audit logs immediately.
            </div>

            <p style="color:#64748b; font-size:12px; line-height:1.6; text-align:center;">
                Initiated from: <strong>{{ config('app.url') }}</strong><br>
                Timestamp: <strong>{{ now()->format('Y-m-d H:i:s') }}</strong>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Automated security notification.</p>
        </div>
    </div>
</body>
</html>
