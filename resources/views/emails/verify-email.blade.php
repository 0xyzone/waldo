<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f4f7; font-family: 'Segoe UI', Arial, sans-serif; color: #333; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); padding: 40px 40px 32px; text-align: center; }
        .header-logo { font-size: 28px; font-weight: 800; color: #fff; letter-spacing: -0.5px; }
        .header-tagline { color: rgba(255,255,255,0.85); font-size: 13px; margin-top: 6px; font-weight: 500; }
        .header-emoji { font-size: 52px; margin-bottom: 12px; display: block; }
        .body { padding: 40px; }
        .greeting { font-size: 22px; font-weight: 700; color: #111; margin-bottom: 12px; }
        .intro { color: #555; font-size: 15px; line-height: 1.8; margin-bottom: 28px; }
        .verify-btn {
            display: block;
            text-align: center;
            background: linear-gradient(135deg, #f97316, #fb923c);
            color: #ffffff !important;
            text-decoration: none !important;
            font-size: 16px;
            font-weight: 700;
            padding: 18px 32px;
            border-radius: 10px;
            margin-bottom: 28px;
            letter-spacing: 0.3px;
        }
        .url-fallback { background: #f8f8f8; border: 1px dashed #ddd; border-radius: 8px; padding: 14px 18px; margin-bottom: 28px; word-break: break-all; }
        .url-fallback p { font-size: 12px; color: #888; margin-bottom: 6px; }
        .url-fallback a { font-size: 12px; color: #f97316 !important; text-decoration: none !important; }
        .expiry-notice { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 14px 18px; font-size: 13px; color: #c2410c; margin-bottom: 28px; line-height: 1.6; }
        .fun-note { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; font-size: 13px; color: #166534; margin-bottom: 28px; line-height: 1.6; }
        .footer { border-top: 1px solid #f0f0f0; padding: 24px 40px; text-align: center; font-size: 12px; color: #aaa; background: #fafafa; }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <span class="header-emoji">🔐</span>
            <div class="header-logo">{{ config('app.name') }}</div>
            <div class="header-tagline">Waldo Kamkaj</div>
        </div>

        <!-- Body -->
        <div class="body">
            <p class="greeting">Hey {{ $user->name }}, you're almost in! 🎉</p>

            <p class="intro">
                So you've been trusted with a shiny new account on <strong>{{ config('app.name') }}</strong>. Good for you. 🎊<br><br>
                Before we hand over the keys to the kingdom, we need to confirm that this email address actually belongs to you —
                and not to a raccoon who somehow got access to a keyboard.
                (It happens more than you'd think.)
            </p>

            <!-- Verify Button -->
            <a href="{{ $url }}" class="verify-btn">
                ✅ Yes, this is me — Verify my email!
            </a>

            <!-- Fun note -->
            <div class="fun-note">
                🚀 <strong>This link expires in 60 minutes.</strong> So don't go making a cup of tea and forgetting about it.
                Click it now while you still can!
            </div>

            <!-- Expiry notice -->
            <div class="expiry-notice">
                🤔 <strong>Didn't request this?</strong> Then someone either made a typo or they're jealous of your email address.
                Either way, you can safely ignore this email — nothing will happen, we promise.
            </div>

            <!-- URL Fallback -->
            <div class="url-fallback">
                <p>If the button above is playing hard to get, copy-paste this link into your browser:</p>
                <a href="{{ $url }}">{{ $url }}</a>
            </div>

            <p style="color:#777; font-size:13px; line-height:1.7; text-align:center;">
                With love (and mild urgency),<br>
                <strong style="color:#f97316;">The {{ config('app.name') }} Team</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p style="margin-top:6px;">This is an automated message. Please do not reply — our inbox is just as busy as you are.</p>
        </div>
    </div>
</body>
</html>
