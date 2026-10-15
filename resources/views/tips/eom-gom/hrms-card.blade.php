<!DOCTYPE html>
<html lang="en" style="background: #0f172a;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee & Grooming of the Month - {{ $monthName }} {{ $evaluatedYear ?? $report->year }}</title>
    <x-favicon />

    {{-- Google Fonts: Plus Jakarta Sans & JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@700;800&display=swap"
        rel="stylesheet">

    {{-- Client-side rasterization engine for 1:1 export --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #0f172a;
            color: #0f172a;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Top Toolbar */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .btn-brand {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 12px;
            background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%);
            color: #ffffff;
            font-size: 13px;
            font-weight: 800;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(234, 88, 12, 0.35);
            transition: all 0.2s ease;
        }

        .btn-brand:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(234, 88, 12, 0.45);
        }

        .btn-brand:active {
            transform: translateY(0);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.15);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
        }

        /* Responsive preview viewport */
        .preview-container {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 24px 16px 48px;
            box-sizing: border-box;
            min-height: calc(100vh - 70px);
        }

        .preview-scale-wrapper {
            transform-origin: top center;
            transition: transform 0.15s ease-out;
            flex-shrink: 0;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            overflow: hidden;
        }

        /* ------------------------------------------------------------- */
        /* MASTER 4K CANVAS (3840 x 1920 - 2:1 RATIO)                    */
        /* Modern Glassmorphism with Warm Orange Tint & Minimal Styling  */
        /* ------------------------------------------------------------- */
        #master-wish-card-canvas {
            width: 3840px;
            height: 1920px;
            min-width: 3840px;
            min-height: 1920px;
            max-width: 3840px;
            max-height: 1920px;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 52px 64px;
            overflow: hidden;
            color: #0f172a;

            /* Luxury Orange-Tinted Ambient Glass Canvas */
            background-color: #fff7ed;
            background-image: 
                radial-gradient(at 0% 0%, rgba(249, 115, 22, 0.22) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(234, 88, 12, 0.18) 0px, transparent 45%),
                radial-gradient(at 100% 100%, rgba(220, 38, 38, 0.16) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(251, 146, 60, 0.15) 0px, transparent 45%),
                radial-gradient(circle, rgba(234, 88, 12, 0.08) 2px, transparent 2px);
            background-size: 100% 100%, 100% 100%, 100% 100%, 100% 100%, 48px 48px;
        }

        /* Glassmorphism Header Card */
        .glass-header-card {
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 2px solid rgba(255, 255, 255, 0.95);
            border-radius: 26px;
            box-shadow: 0 16px 40px rgba(234, 88, 12, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.9);
            padding: 24px 44px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 190px;
            box-sizing: border-box;
            width: 100%;
        }

        /* Glassmorphism Table Cards */
        .glass-table-card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.92);
            border-radius: 26px;
            box-shadow: 0 18px 45px rgba(234, 88, 12, 0.07), inset 0 1px 0 rgba(255, 255, 255, 0.9);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        /* Table Rows */
        .table-row {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 0 40px;
            border-bottom: 2px solid rgba(251, 146, 60, 0.16);
            background: rgba(255, 255, 255, 0.65);
            width: 100%;
        }

        .table-row:last-child {
            border-bottom: none;
        }

        .table-row-eom {
            border-left: 12px solid #ea580c;
            height: 202px;
        }

        .table-row-gom {
            border-left: 12px solid #dc2626;
            height: 206px;
        }

        .table-header-row {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 0 40px 0 52px;
            height: 60px;
            background: rgba(255, 247, 237, 0.85);
            border-bottom: 2px solid rgba(251, 146, 60, 0.22);
            font-size: 22px;
            font-weight: 800;
            color: #9a3412;
            text-transform: uppercase;
            letter-spacing: 2px;
            width: 100%;
        }

        /* Column width allocations across 3712px available width */
        .col-honoree {
            width: 35%;
            display: flex;
            align-items: center;
            gap: 28px;
            box-sizing: border-box;
            padding-right: 24px;
        }

        .col-code {
            width: 12%;
            display: flex;
            align-items: center;
            box-sizing: border-box;
        }

        .col-dept {
            width: 22%;
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding-right: 24px;
        }

        .col-desig {
            width: 18%;
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding-right: 24px;
        }

        .col-badge {
            width: 13%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            box-sizing: border-box;
        }

        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .preview-container {
                padding: 0 !important;
            }

            .preview-scale-wrapper {
                box-shadow: none !important;
                border-radius: 0 !important;
                transform: scale(0.25) !important;
            }
        }
    </style>
</head>

<body>

    {{-- TOP TOOLBAR --}}
    <header class="toolbar no-print">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #ffffff; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);">
                🏆
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h1 style="margin: 0; font-size: 15px; font-weight: 800; color: #ffffff; letter-spacing: -0.2px;">
                        HRMS Wish Card
                    </h1>
                    <span style="font-size: 11px; font-weight: 800; padding: 3px 9px; border-radius: 6px; background: rgba(234, 88, 12, 0.2); color: #fb923c; border: 1px solid rgba(234, 88, 12, 0.4); font-family: monospace;">
                        4K UHD • 3840 × 1920 (2:1)
                    </span>
                    <span style="font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; background: rgba(255, 255, 255, 0.1); color: #cbd5e1;">
                        Minimalist Glassmorphism (Orange Tint)
                    </span>
                </div>
                <p style="margin: 3px 0 0; font-size: 12px; color: #94a3b8;">
                    {{ $monthName }} {{ $evaluatedYear ?? $report->year }}
                </p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 6px; background: rgba(255, 255, 255, 0.08); padding: 4px 10px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.12);">
                <span style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Scale:</span>
                <span id="scaleIndicator" style="font-size: 12px; font-weight: 800; color: #f8fafc; font-family: monospace;">35%</span>
                <button type="button" onclick="setZoomMode('fit')" class="btn-secondary" style="padding: 3px 8px; font-size: 11px; border-radius: 6px;">Fit</button>
                <button type="button" onclick="setZoomMode('50')" class="btn-secondary" style="padding: 3px 8px; font-size: 11px; border-radius: 6px;">50%</button>
            </div>

            <button type="button" id="downloadBtn" onclick="downloadCard('jpg')" class="btn-brand">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span id="downloadBtnText">Download 4K Card (JPG)</span>
            </button>

            <button type="button" id="downloadPngBtn" onclick="downloadCard('png')" class="btn-secondary" title="Lossless 4K PNG">
                <span>PNG</span>
            </button>

            <button type="button" onclick="window.print()" class="btn-secondary">
                <svg style="width: 15px; height: 15px; color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print</span>
            </button>

            <button type="button" onclick="window.close()" class="btn-secondary" style="padding: 10px 14px;">
                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </header>

    {{-- PREVIEW VIEWPORT --}}
    <main class="preview-container">
        <div class="preview-scale-wrapper">

            {{-- ============================================================= --}}
            {{-- MASTER 3840 x 1920 CANVAS (2:1 RATIO IN 4K RESOLUTION)        --}}
            {{-- Modern Glassmorphism with Warm Orange Tint & Clean Typography --}}
            {{-- ============================================================= --}}
            <div id="master-wish-card-canvas">

                {{-- ========================================================= --}}
                {{-- 1. HEADER SECTION (MINIMALIST GLASS BANNER)               --}}
                {{-- ========================================================= --}}
                <div class="glass-header-card">
                    <div style="display: flex; align-items: center; gap: 32px;">
                        {{-- Logo --}}
                        <div style="width: 130px; height: 130px; border-radius: 22px; background: #ffffff; border: 3px solid rgba(251, 146, 60, 0.6); box-shadow: 0 10px 24px rgba(234, 88, 12, 0.15); display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 10px; box-sizing: border-box; flex-shrink: 0;">
                            <img src="{{ $logoBase64 ?? asset('img/logo.jpg') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>

                        <div>
                            <span style="font-size: 26px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: #ea580c;">
                                Waldo Dynasty Entertainment
                            </span>
                            <h2 style="margin: 6px 0 0; font-size: 64px; font-weight: 900; color: #0f172a; letter-spacing: -1px; line-height: 1.1;">
                                Employee & Grooming of the Month
                            </h2>
                        </div>
                    </div>

                    {{-- Month Badge --}}
                    <div style="display: inline-flex; align-items: center; gap: 16px; padding: 22px 46px; border-radius: 22px; background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%); box-shadow: 0 12px 30px rgba(234, 88, 12, 0.35); flex-shrink: 0;">
                        <span style="width: 16px; height: 16px; border-radius: 50%; background: #ffffff;"></span>
                        <span style="font-size: 46px; font-weight: 900; color: #ffffff; letter-spacing: 1.5px; text-transform: uppercase; white-space: nowrap;">
                            {{ $monthName }} {{ $evaluatedYear ?? $report->year }}
                        </span>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 2. MAIN TABLES (FULLY EXPANDED TO FILL CANVAS VERTICALLY) --}}
                {{-- ========================================================= --}}
                <div style="display: flex; flex-direction: column; justify-content: space-between; flex: 1; padding: 24px 0 8px; gap: 28px; box-sizing: border-box;">

                    {{-- ----------------------------------------------------- --}}
                    {{-- SECTION 1: EMPLOYEE OF THE MONTH (4 TABLE ROWS)       --}}
                    {{-- ----------------------------------------------------- --}}
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        
                        {{-- Section Title Pill --}}
                        <div style="display: flex; align-items: center;">
                            <div style="display: inline-flex; align-items: center; gap: 14px; padding: 12px 28px; border-radius: 18px; background: rgba(255, 237, 213, 0.9); border: 2px solid rgba(251, 146, 60, 0.5); backdrop-filter: blur(12px);">
                                <span style="font-size: 30px;">🏆</span>
                                <span style="font-size: 30px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; color: #c2410c; white-space: nowrap;">
                                    Employee of the Month
                                </span>
                            </div>
                        </div>

                        {{-- Glassmorphism Table --}}
                        <div class="glass-table-card">
                            <div class="table-header-row">
                                <div class="col-honoree">Employee</div>
                                <div class="col-code">ID</div>
                                <div class="col-dept">Department</div>
                                <div class="col-desig">Designation</div>
                                <div class="col-badge">Award</div>
                            </div>

                            @foreach ($eomWinners as $winner)
                                @php $emp = $winner['employee']; @endphp
                                @if ($emp)
                                    <div class="table-row table-row-eom">
                                        {{-- Employee Name + Avatar --}}
                                        <div class="col-honoree">
                                            <div style="width: 110px; height: 110px; border-radius: 26px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 44px; font-weight: 900; box-shadow: 0 8px 22px rgba(234, 88, 12, 0.32); flex-shrink: 0;">
                                                {{ strtoupper(substr($emp->name, 0, 2)) }}
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <h3 style="margin: 0; font-size: 48px; font-weight: 900; color: #0f172a; line-height: 1.15; word-break: break-word;">
                                                    {{ $emp->name }}
                                                </h3>
                                            </div>
                                        </div>

                                        {{-- Employee ID --}}
                                        <div class="col-code">
                                            <span class="font-mono-code" style="font-size: 32px; font-weight: 800; color: #0f172a; background: rgba(255, 255, 255, 0.9); border: 2px solid rgba(251, 146, 60, 0.35); padding: 14px 26px; border-radius: 14px; letter-spacing: 1px; white-space: nowrap;">
                                                {{ $emp->employee_code }}
                                            </span>
                                        </div>

                                        {{-- Department --}}
                                        <div class="col-dept">
                                            <span style="font-size: 36px; font-weight: 800; color: #1e293b; line-height: 1.25; word-break: break-word;">
                                                {{ $winner['department'] }}
                                            </span>
                                        </div>

                                        {{-- Designation --}}
                                        <div class="col-desig">
                                            <span style="font-size: 32px; font-weight: 700; color: #475569; line-height: 1.25; word-break: break-word;">
                                                {{ $emp->designation?->name ?: 'Staff' }}
                                            </span>
                                        </div>

                                        {{-- Award Badge --}}
                                        <div class="col-badge">
                                            <span style="display: inline-flex; align-items: center; gap: 10px; background: rgba(255, 237, 213, 0.95); border: 2px solid #ea580c; color: #c2410c; font-size: 26px; font-weight: 900; padding: 14px 30px; border-radius: 16px; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.15); white-space: nowrap;">
                                                <span>🏆</span>
                                                <span>EOM</span>
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    {{-- Unassigned Slot Row --}}
                                    <div class="table-row table-row-eom" style="background: rgba(255, 255, 255, 0.45);">
                                        <div class="col-honoree">
                                            <div style="width: 110px; height: 110px; border-radius: 26px; background: rgba(203, 213, 225, 0.6); display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 38px; font-weight: 900; flex-shrink: 0;">
                                                ✦
                                            </div>
                                            <div>
                                                <h3 style="margin: 0; font-size: 40px; font-weight: 700; color: #94a3b8; font-style: italic;">
                                                    Slot Unassigned
                                                </h3>
                                            </div>
                                        </div>
                                        <div class="col-code">
                                            <span style="font-size: 30px; color: #94a3b8;">—</span>
                                        </div>
                                        <div class="col-dept">
                                            <span style="font-size: 32px; color: #64748b;">
                                                {{ $winner['department'] }}
                                            </span>
                                        </div>
                                        <div class="col-desig">
                                            <span style="font-size: 30px; color: #94a3b8;">—</span>
                                        </div>
                                        <div class="col-badge">
                                            <span style="background: rgba(241, 245, 249, 0.8); border: 1.5px solid #cbd5e1; color: #64748b; font-size: 22px; font-weight: 800; padding: 12px 24px; border-radius: 14px; text-transform: uppercase;">
                                                Pending
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- ----------------------------------------------------- --}}
                    {{-- SECTION 2: GROOMING OF THE MONTH (2 TABLE ROWS)       --}}
                    {{-- ----------------------------------------------------- --}}
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        
                        {{-- Section Title Pill --}}
                        <div style="display: flex; align-items: center;">
                            <div style="display: inline-flex; align-items: center; gap: 14px; padding: 12px 28px; border-radius: 18px; background: rgba(254, 226, 226, 0.9); border: 2px solid rgba(248, 113, 113, 0.5); backdrop-filter: blur(12px);">
                                <span style="font-size: 30px;">✨</span>
                                <span style="font-size: 30px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; color: #b91c1c; white-space: nowrap;">
                                    Grooming of the Month
                                </span>
                            </div>
                        </div>

                        {{-- Glassmorphism Table --}}
                        <div class="glass-table-card">
                            <div class="table-header-row">
                                <div class="col-honoree">Employee</div>
                                <div class="col-code">ID</div>
                                <div class="col-dept">Department</div>
                                <div class="col-desig">Designation</div>
                                <div class="col-badge">Award</div>
                            </div>

                            @foreach ($gomWinners as $winner)
                                @php $emp = $winner['employee']; @endphp
                                @if ($emp)
                                    <div class="table-row table-row-gom">
                                        {{-- Employee Name + Avatar --}}
                                        <div class="col-honoree">
                                            <div style="width: 110px; height: 110px; border-radius: 26px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 44px; font-weight: 900; box-shadow: 0 8px 22px rgba(220, 38, 38, 0.32); flex-shrink: 0;">
                                                {{ strtoupper(substr($emp->name, 0, 2)) }}
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <h3 style="margin: 0; font-size: 48px; font-weight: 900; color: #0f172a; line-height: 1.15; word-break: break-word;">
                                                    {{ $emp->name }}
                                                </h3>
                                            </div>
                                        </div>

                                        {{-- Employee ID --}}
                                        <div class="col-code">
                                            <span class="font-mono-code" style="font-size: 32px; font-weight: 800; color: #0f172a; background: rgba(255, 255, 255, 0.9); border: 2px solid rgba(248, 113, 113, 0.35); padding: 14px 26px; border-radius: 14px; letter-spacing: 1px; white-space: nowrap;">
                                                {{ $emp->employee_code }}
                                            </span>
                                        </div>

                                        {{-- Department --}}
                                        <div class="col-dept">
                                            <span style="font-size: 36px; font-weight: 800; color: #1e293b; line-height: 1.25; word-break: break-word;">
                                                {{ $winner['department'] }}
                                            </span>
                                        </div>

                                        {{-- Designation --}}
                                        <div class="col-desig">
                                            <span style="font-size: 32px; font-weight: 700; color: #475569; line-height: 1.25; word-break: break-word;">
                                                {{ $emp->designation?->name ?: 'Staff' }}
                                            </span>
                                        </div>

                                        {{-- Award Badge --}}
                                        <div class="col-badge">
                                            <span style="display: inline-flex; align-items: center; gap: 10px; background: rgba(254, 226, 226, 0.95); border: 2px solid #dc2626; color: #b91c1c; font-size: 26px; font-weight: 900; padding: 14px 30px; border-radius: 16px; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15); white-space: nowrap;">
                                                <span>✨</span>
                                                <span>GOM</span>
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    {{-- Unassigned Slot Row --}}
                                    <div class="table-row table-row-gom" style="background: rgba(255, 255, 255, 0.45);">
                                        <div class="col-honoree">
                                            <div style="width: 110px; height: 110px; border-radius: 26px; background: rgba(203, 213, 225, 0.6); display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 38px; font-weight: 900; flex-shrink: 0;">
                                                ✦
                                            </div>
                                            <div>
                                                <h3 style="margin: 0; font-size: 40px; font-weight: 700; color: #94a3b8; font-style: italic;">
                                                    Slot Unassigned
                                                </h3>
                                            </div>
                                        </div>
                                        <div class="col-code">
                                            <span style="font-size: 30px; color: #94a3b8;">—</span>
                                        </div>
                                        <div class="col-dept">
                                            <span style="font-size: 32px; color: #64748b;">
                                                {{ $winner['department'] }}
                                            </span>
                                        </div>
                                        <div class="col-desig">
                                            <span style="font-size: 30px; color: #94a3b8;">—</span>
                                        </div>
                                        <div class="col-badge">
                                            <span style="background: rgba(241, 245, 249, 0.8); border: 1.5px solid #cbd5e1; color: #64748b; font-size: 22px; font-weight: 800; padding: 12px 24px; border-radius: 14px; text-transform: uppercase;">
                                                Pending
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </main>

    {{-- CLIENT-SIDE HIGH-RESOLUTION EXPORT ENGINE (1:1 PIXEL IDENTICAL) --}}
    <script>
        let currentZoomMode = 'fit';

        function updatePreviewScale() {
            const wrapper = document.querySelector('.preview-scale-wrapper');
            const indicator = document.getElementById('scaleIndicator');
            if (!wrapper) return;

            let scale = 0.35;
            if (currentZoomMode === 'fit') {
                const availableWidth = window.innerWidth - 64;
                scale = Math.min(Math.max(availableWidth / 3840, 0.18), 0.50);
            } else if (currentZoomMode === '50') {
                scale = 0.50;
            }

            wrapper.style.transform = `scale(${scale})`;
            if (indicator) {
                indicator.textContent = `${Math.round(scale * 100)}%`;
            }
        }

        function setZoomMode(mode) {
            currentZoomMode = mode;
            updatePreviewScale();
        }

        window.addEventListener('resize', updatePreviewScale);
        window.addEventListener('DOMContentLoaded', updatePreviewScale);
        setTimeout(updatePreviewScale, 100);

        /**
         * Capture the 3840x1920 card canvas with 100% exact design fidelity.
         * Un-scales the live element directly so that html2canvas captures
         * the exact computed layout, fonts, and colors displayed in the preview.
         */
        async function downloadCard(format = 'jpg') {
            const btn = document.getElementById('downloadBtn');
            const btnText = document.getElementById('downloadBtnText');
            const wrapper = document.querySelector('.preview-scale-wrapper');
            const targetEl = document.getElementById('master-wish-card-canvas');

            if (!targetEl || !wrapper) {
                alert('Card canvas element not found.');
                return;
            }

            btn.disabled = true;
            btnText.textContent = `Rendering 4K ${format.toUpperCase()}...`;

            if (document.fonts && document.fonts.ready) {
                try {
                    await document.fonts.ready;
                } catch (e) {}
            }

            const origWrapperTransform = wrapper.style.transform;
            const origWrapperBoxShadow = wrapper.style.boxShadow;
            const origBodyOverflow = document.body.style.overflow;
            const origBodyWidth = document.body.style.width;

            try {
                wrapper.style.transform = 'none';
                wrapper.style.boxShadow = 'none';
                document.body.style.overflow = 'visible';
                document.body.style.width = '3840px';

                await new Promise(r => setTimeout(r, 150));

                const canvas = await window.html2canvas(targetEl, {
                    width: 3840,
                    height: 1920,
                    scale: 1,
                    windowWidth: 3840,
                    windowHeight: 1920,
                    x: 0,
                    y: 0,
                    scrollX: 0,
                    scrollY: 0,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#fff7ed',
                    logging: false,
                });

                const mimeType = format === 'png' ? 'image/png' : 'image/jpeg';
                const quality = format === 'png' ? 1.0 : 0.98;
                const dataUrl = canvas.toDataURL(mimeType, quality);

                if (!dataUrl || dataUrl === 'data:,') {
                    throw new Error('Canvas render produced empty result.');
                }

                const filename = `EOM-GOM-WishCard-{{ \Illuminate\Support\Str::slug($monthName) }}-{{ $evaluatedYear ?? $report->year }}-4K.${format}`;
                const link = document.createElement('a');
                link.download = filename;
                link.href = dataUrl;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

            } catch (err) {
                console.error('Error generating card image:', err);
                alert('Could not generate 4K image automatically. You can use the "Print" button to save as PDF or image.');
            } finally {
                wrapper.style.transform = origWrapperTransform;
                wrapper.style.boxShadow = origWrapperBoxShadow;
                document.body.style.overflow = origBodyOverflow;
                document.body.style.width = origBodyWidth;

                btn.disabled = false;
                btnText.textContent = 'Download 4K Card (JPG)';
            }
        }
    </script>
</body>

</html>
