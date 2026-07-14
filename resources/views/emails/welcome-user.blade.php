<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f4f7; font-family: 'Segoe UI', Arial, sans-serif; color: #333; }
        a { color: inherit !important; text-decoration: none !important; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); padding: 40px 40px 32px; text-align: center; }
        .header-emoji { font-size: 52px; margin-bottom: 12px; display: block; }
        .header-logo { font-size: 28px; font-weight: 800; color: #fff; letter-spacing: -0.5px; }
        .header-tagline { color: rgba(255,255,255,0.85); font-size: 13px; margin-top: 6px; font-weight: 500; }
        .body { padding: 40px; }
        .greeting { font-size: 22px; font-weight: 700; color: #111; margin-bottom: 12px; }
        .intro { color: #555; font-size: 15px; line-height: 1.8; margin-bottom: 32px; }
        .credentials-box { background: #fafafa; border: 1px solid #e8e8e8; border-radius: 10px; overflow: hidden; margin-bottom: 32px; }
        .credentials-title { background: #f97316; color: #fff; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; padding: 10px 20px; }
        .credential-row { display: flex; align-items: flex-start; padding: 14px 20px; border-bottom: 1px solid #eee; }
        .credential-row:last-child { border-bottom: none; }
        .credential-label { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; width: 100px; flex-shrink: 0; padding-top: 2px; }
        .credential-value { font-size: 15px; color: #111; font-weight: 600; font-family: 'Courier New', monospace; word-break: break-all; }
        .login-btn {
            display: block;
            text-align: center;
            background: linear-gradient(135deg, #f97316, #fb923c);
            color: #ffffff !important;
            text-decoration: none !important;
            font-size: 15px;
            font-weight: 700;
            padding: 18px 32px;
            border-radius: 10px;
            margin-bottom: 28px;
            letter-spacing: 0.3px;
        }
        .notice { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px; padding: 14px 18px; font-size: 13px; color: #92400e; line-height: 1.6; margin-bottom: 24px; }
        .fun-note { background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; padding: 14px 18px; font-size: 13px; color: #78350f; line-height: 1.6; margin-bottom: 28px; }
        .footer { border-top: 1px solid #f0f0f0; padding: 24px 40px; text-align: center; font-size: 12px; color: #aaa; background: #fafafa; }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <span class="header-emoji">🎉</span>
            <div class="header-logo">{{ config('app.name') }}</div>
            <div class="header-tagline">Waldo Kamkaj</div>
        </div>

        <!-- Body -->
        <div class="body">
            <p class="greeting">Welcome aboard, {{ $user->name }}! 🚀</p>

            <p class="intro">
                Someone at {{ config('app.name') }} thought you were cool enough to have an account.
                Clearly, they have excellent taste. 😎<br><br>
                Your account is all set up and ready to roll. Below are your login credentials —
                guard them like they're the last slice of pizza at an office party.
            </p>

            <!-- Credentials Box -->
            <div class="credentials-box">
                <div class="credentials-title">🔑 Your Login Credentials</div>
                <div class="credential-row">
                    <span class="credential-label">Portal</span>
                    <span class="credential-value">{{ config('app.url') }}/kamkaj</span>
                </div>
                <div class="credential-row">
                    <span class="credential-label">Email</span>
                    <span class="credential-value">{{ $user->email }}</span>
                </div>
                <div class="credential-row">
                    <span class="credential-label">Password</span>
                    <span class="credential-value">{{ $plainPassword }}</span>
                </div>
            </div>

            <!-- CTA Button -->
            <a href="{{ config('app.url') }}/kamkaj" class="login-btn">
                🚪 Step Through the Door →
            </a>

            <!-- Fun note -->
            <div class="fun-note">
                💡 <strong>Pro tip:</strong> Change your password right after you log in for the first time.
                Using <em>"{{ $user->name }}123"</em> is not what we'd call creative. We believe in you. 🙏
            </div>

            <!-- Security Notice -->
            <div class="notice">
                ⚠️ <strong>Heads up:</strong> This email contains your credentials in plain text.
                Once you've logged in and changed your password, please delete this email.
                Or eat it. Whatever works for you.
            </div>

            <p style="color:#777; font-size:13px; line-height:1.7; text-align:center;">
                If you didn't expect this account or someone's pranking you,<br>
                contact your admin before things get weird.<br><br>
                Cheers,<br>
                <strong style="color:#f97316;">The {{ config('app.name') }} Team 🧡</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p style="margin-top:6px;">This is an automated message — our robot minions wrote this, not a human. Mostly.</p>
        </div>
    </div>
</body>
</html>
