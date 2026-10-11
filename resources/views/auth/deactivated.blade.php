<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Deactivated — Kamkaj</title>
    <x-favicon />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --rose: #f43f5e;
            --rose-glow: rgba(244, 63, 94, 0.28);
            --amber: #f59e0b;
            --amber-glow: rgba(245, 158, 11, 0.2);
            --bg-dark: #0a0b10;
            --card-bg: rgba(18, 20, 29, 0.82);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        body {
            min-height: 100vh;
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient glowing orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
            animation: float 14s ease-in-out infinite alternate;
        }
        .orb-1 {
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, var(--rose-glow), transparent 70%);
            top: -120px;
            left: -100px;
            opacity: 0.75;
        }
        .orb-2 {
            width: 520px;
            height: 520px;
            background: radial-gradient(circle, var(--amber-glow), transparent 70%);
            bottom: -140px;
            right: -100px;
            opacity: 0.65;
            animation-delay: -6s;
        }
        .orb-3 {
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.18), transparent 70%);
            top: 40%;
            left: 55%;
            transform: translate(-50%, -50%);
            opacity: 0.4;
            animation-delay: -10s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(45px, 30px) scale(1.08); }
        }

        /* Container & Glass Card */
        .deactivated-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 560px;
            background: var(--card-bg);
            backdrop-filter: blur(32px);
            -webkit-backdrop-filter: blur(32px);
            border: 1px solid var(--card-border);
            border-radius: 1.75rem;
            padding: 2.75rem 2.25rem;
            box-shadow: 0 0 80px rgba(244, 63, 94, 0.12), 0 35px 70px -15px rgba(0, 0, 0, 0.7);
            text-align: center;
            animation: card-appear 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes card-appear {
            0% { opacity: 0; transform: translateY(24px) scale(0.96); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Top status pill */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.95rem;
            border-radius: 9999px;
            background: rgba(244, 63, 94, 0.12);
            border: 1px solid rgba(244, 63, 94, 0.28);
            color: #fb7185;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }
        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: var(--rose);
            box-shadow: 0 0 10px var(--rose);
            animation: pulse-dot 2s infinite ease-in-out;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.8); }
        }

        /* Icon Badge */
        .icon-wrapper {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-glow-ring {
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.5), rgba(245, 158, 11, 0.3));
            filter: blur(12px);
            opacity: 0.8;
            animation: ring-pulse 3s infinite alternate;
        }
        @keyframes ring-pulse {
            from { transform: scale(0.95); opacity: 0.6; }
            to { transform: scale(1.05); opacity: 0.9; }
        }
        .icon-circle {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(145deg, #1f1b26, #120e17);
            border: 1px solid rgba(244, 63, 94, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f43f5e;
            box-shadow: inset 0 2px 6px rgba(255, 255, 255, 0.1);
        }
        .icon-circle svg {
            width: 38px;
            height: 38px;
        }

        /* Typography */
        h1 {
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.75rem;
            color: #ffffff;
        }
        .lead-text {
            font-size: 0.98rem;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 1.75rem;
        }
        .highlight-notice {
            color: #f1f5f9;
            font-weight: 500;
        }

        /* Administrator Card */
        .admin-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 1.15rem;
            padding: 1.25rem 1.4rem;
            margin-bottom: 1.75rem;
            text-align: left;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 1.1rem;
            transition: all 0.25s ease;
        }
        .admin-card:hover {
            background: rgba(255, 255, 255, 0.045);
            border-color: rgba(245, 158, 11, 0.25);
            transform: translateY(-2px);
        }
        .admin-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, #f59e0b, #f43f5e);
            border-top-left-radius: 1.15rem;
            border-bottom-left-radius: 1.15rem;
        }
        .admin-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b, #ea580c);
            color: #ffffff;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
        }
        .admin-info {
            flex-grow: 1;
            min-width: 0;
        }
        .admin-role {
            font-size: 0.73rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #f59e0b;
            font-weight: 600;
            margin-bottom: 0.15rem;
        }
        .admin-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.2rem;
        }
        .admin-email {
            font-size: 0.82rem;
            color: #94a3b8;
            font-family: 'JetBrains Mono', monospace;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: color 0.2s ease;
        }
        .admin-email:hover {
            color: #e2e8f0;
        }
        .email-btn {
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.25);
            color: #fbbf24;
            padding: 0.45rem 0.85rem;
            border-radius: 0.65rem;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        .email-btn:hover {
            background: rgba(245, 158, 11, 0.22);
            border-color: rgba(245, 158, 11, 0.4);
            color: #ffffff;
        }

        /* User Chip */
        .user-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 9999px;
            padding: 0.4rem 1rem;
            font-size: 0.82rem;
            color: #94a3b8;
            margin-bottom: 1.75rem;
        }
        .user-chip strong {
            color: #e2e8f0;
        }

        /* Alerts */
        .toast-still-inactive {
            background: rgba(244, 63, 94, 0.12);
            border: 1px solid rgba(244, 63, 94, 0.3);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            color: #fda4af;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            animation: fadeIn 0.3s ease;
        }

        /* Buttons & Actions */
        .actions-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        @media (min-width: 480px) {
            .actions-group {
                flex-direction: row;
            }
        }
        .btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            padding: 0.85rem 1.4rem;
            border-radius: 0.85rem;
            font-size: 0.92rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            border: none;
            font-family: inherit;
        }
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #0b0d13;
            box-shadow: 0 4px 18px rgba(245, 158, 11, 0.28);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(245, 158, 11, 0.42);
            color: #000000;
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.09);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Footer */
        .footer-note {
            margin-top: 1.75rem;
            font-size: 0.75rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <!-- Ambient glowing backdrops -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <main class="deactivated-card">
        <!-- Top Status Pill -->
        <div class="status-pill">
            <span class="status-dot"></span>
            Access Revoked
        </div>

        <!-- Animated Shield/Lock Icon -->
        <div class="icon-wrapper">
            <div class="icon-glow-ring"></div>
            <div class="icon-circle">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>
        </div>

        <h1>Account Deactivated</h1>

        <p class="lead-text">
            Your account has been deactivated and your access to the <strong class="highlight-notice">Kamkaj</strong> panel has been revoked. If you want to continue having access, please contact the system administration personnel <strong class="highlight-notice">Mr. Sumin Shrestha</strong>.
        </p>

        @if(session('still_inactive'))
            <div class="toast-still-inactive">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>Your account is still currently inactive. Please coordinate with Mr. Sumin Shrestha.</span>
            </div>
        @endif

        @if($currentUser)
            <div class="user-chip">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7 0 3.75 3.75 0 017 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                <span>Signed in as <strong>{{ $currentUser->name }}</strong> ({{ $currentUser->email ?? $currentUser->username }})</span>
            </div>
        @endif

        <!-- Administrator Contact Card -->
        <section class="admin-card">
            <div class="admin-avatar">
                SS
            </div>
            <div class="admin-info">
                <div class="admin-role">System Administration</div>
                <div class="admin-name">Mr. Sumin Shrestha</div>
                @php
                    $adminEmail = $adminUser?->email ?? 'sumnsth@gmail.com';
                @endphp
                <a href="mailto:{{ $adminEmail }}" class="admin-email">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <span>{{ $adminEmail }}</span>
                </a>
            </div>
            <a href="mailto:{{ $adminEmail }}?subject=Kamkaj%20Account%20Reactivation%20Request%20-%20{{ urlencode($currentUser?->name ?? 'User') }}" class="email-btn">
                Contact
            </a>
        </section>

        <!-- Actions -->
        <div class="actions-group">
            <a href="{{ route('account.deactivated.check') }}" class="btn btn-primary">
                <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span>Check Status</span>
            </a>

            @if($currentUser)
                <form action="{{ route('filament.kamkaj.auth.logout') }}" method="POST" style="flex: 1; display: flex;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="width: 100%;">
                        <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        <span>Sign Out</span>
                    </button>
                </form>
            @else
                <a href="{{ route('filament.kamkaj.auth.login') }}" class="btn btn-secondary">
                    <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    <span>Back to Login</span>
                </a>
            @endif
        </div>

        <p class="footer-note">
            Kamkaj Operations Management System &bull; Waldo
        </p>
    </main>
</body>
</html>
