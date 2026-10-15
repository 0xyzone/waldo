<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMS Recognition Card (4K) - {{ $monthName }} {{ $evaluatedYear ?? $report->year }}</title>
    <x-favicon />

    {{-- Google Fonts: Plus Jakarta Sans, Outfit & JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@700;800&display=swap"
        rel="stylesheet">

    {{-- Client-side rasterization engines for 1:1 pixel-perfect export --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        /* Base typography */
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #0f172a;
            color: #0f172a;
            overflow-x: hidden;
        }

        .font-display {
            font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif;
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
            padding: 10px 20px;
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
            padding: 32px 16px 64px;
            box-sizing: border-box;
            min-height: calc(100vh - 70px);
        }

        .preview-scale-wrapper {
            transform-origin: top center;
            transition: transform 0.15s ease-out;
            flex-shrink: 0;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            overflow: hidden;
        }

        /* ------------------------------------------------------------- */
        /* MASTER 4K CANVAS (3840 x 1920 - EXACT 2:1 ASPECT RATIO)       */
        /* Uses standard, explicit CSS so preview and download match 1:1  */
        /* ------------------------------------------------------------- */
        #master-wish-card-canvas {
            width: 3840px;
            height: 1920px;
            min-width: 3840px;
            min-height: 1920px;
            background: #f8fafc;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 56px 72px 52px;
            overflow: hidden;
            color: #0f172a;
        }

        /* Top decorative brand stripe */
        .brand-top-stripe {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 14px;
            background: linear-gradient(90deg, #ea580c 0%, #f97316 35%, #dc2626 70%, #b91c1c 100%);
        }

        /* Table modern container styles */
        .recognition-table-card {
            background: #ffffff;
            border-radius: 22px;
            border: 2px solid #e2e8f0;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .table-row {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 0 36px;
            border-bottom: 2px solid #f1f5f9;
            background: #ffffff;
            transition: background 0.15s ease;
        }

        .table-row:last-child {
            border-bottom: none;
        }

        .table-row-eom {
            border-left: 10px solid #ea580c;
            height: 138px;
        }

        .table-row-gom {
            border-left: 10px solid #dc2626;
            height: 138px;
        }

        .table-header-row {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 0 36px 0 46px;
            height: 58px;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            font-size: 20px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Column width allocations */
        .col-honoree {
            width: 32%;
            display: flex;
            align-items: center;
            gap: 24px;
            min-width: 0;
        }

        .col-code {
            width: 14%;
            display: flex;
            align-items: center;
        }

        .col-dept {
            width: 22%;
            display: flex;
            align-items: center;
            padding-right: 20px;
            min-width: 0;
        }

        .col-desig {
            width: 18%;
            display: flex;
            align-items: center;
            padding-right: 20px;
            min-width: 0;
        }

        .col-badge {
            width: 14%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
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

    {{-- TOP MATERIAL TOOLBAR --}}
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
                        Light Modern Table Theme
                    </span>
                </div>
                <p style="margin: 3px 0 0; font-size: 12px; color: #94a3b8;">
                    Honoring winners for <strong style="color: #f1f5f9;">{{ $monthName }} {{ $evaluatedYear ?? $report->year }}</strong> ({{ $releaseLabel }} Cycle)
                </p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            {{-- Zoom indicator & controls --}}
            <div style="display: flex; align-items: center; gap: 6px; background: rgba(255, 255, 255, 0.08); padding: 4px 10px; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.12);">
                <span style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Scale:</span>
                <span id="scaleIndicator" style="font-size: 12px; font-weight: 800; color: #f8fafc; font-family: monospace;">35%</span>
                <button type="button" onclick="setZoomMode('fit')" class="btn-secondary" style="padding: 3px 8px; font-size: 11px; border-radius: 6px;">Fit</button>
                <button type="button" onclick="setZoomMode('50')" class="btn-secondary" style="padding: 3px 8px; font-size: 11px; border-radius: 6px;">50%</button>
            </div>

            {{-- Download High-Res JPG button --}}
            <button type="button" id="downloadBtn" onclick="downloadCard('jpg')" class="btn-brand">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span id="downloadBtnText">Download 4K Card (JPG)</span>
            </button>

            {{-- Download High-Res PNG button --}}
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
            {{-- MASTER 3840 x 1920 CANVAS (EXACT 2:1 RATIO IN 4K RESOLUTION)  --}}
            {{-- Modern Table Layout with Clean Light Brand Theme             --}}
            {{-- ============================================================= --}}
            <div id="master-wish-card-canvas">

                {{-- Top Brand Accent Stripe (Orange to Red) --}}
                <div class="brand-top-stripe"></div>

                {{-- ========================================================= --}}
                {{-- 1. HEADER SECTION (EXECUTIVE BRANDING & PERIOD BADGE)     --}}
                {{-- ========================================================= --}}
                <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 24px; border-bottom: 3px solid #e2e8f0;">
                    
                    {{-- Left Brand Block --}}
                    <div style="display: flex; align-items: center; gap: 32px;">
                        {{-- Casino Logo Frame --}}
                        <div style="width: 136px; height: 136px; border-radius: 24px; background: #ffffff; border: 3px solid #fdba74; box-shadow: 0 10px 25px -5px rgba(234, 88, 12, 0.18); display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 10px; box-sizing: border-box; flex-shrink: 0;">
                            <img src="{{ $logoBase64 ?? asset('img/logo.jpg') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>

                        <div>
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <span class="font-display" style="font-size: 26px; font-weight: 900; letter-spacing: 3px; text-transform: uppercase; color: #ea580c;">
                                    Waldo Dynasty Entertainment Pvt. Ltd.
                                </span>
                                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #cbd5e1;"></span>
                                <span style="font-size: 20px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #64748b;">
                                    Human Resources & Talent Recognition
                                </span>
                            </div>
                            <h2 class="font-display" style="margin: 8px 0 0; font-size: 58px; font-weight: 900; color: #0f172a; letter-spacing: -1px; line-height: 1.1;">
                                Employee & Grooming of the Month
                            </h2>
                            <p style="margin: 6px 0 0; font-size: 22px; font-weight: 600; color: #64748b;">
                                Celebrating Outstanding Workplace Dedication, Integrity, and Exemplary Presentation Standards
                            </p>
                        </div>
                    </div>

                    {{-- Right Month & Cycle Pill Badge --}}
                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                        <div style="display: inline-flex; align-items: center; gap: 18px; padding: 22px 42px; border-radius: 24px; background: linear-gradient(135deg, #ea580c 0%, #f97316 45%, #dc2626 100%); box-shadow: 0 12px 30px rgba(234, 88, 12, 0.35);">
                            <span style="width: 16px; height: 16px; border-radius: 50%; background: #ffffff;"></span>
                            <span class="font-display" style="font-size: 44px; font-weight: 900; color: #ffffff; letter-spacing: 1px; text-transform: uppercase;">
                                {{ $monthName }} {{ $evaluatedYear ?? $report->year }}
                            </span>
                        </div>
                        <span style="font-size: 22px; font-weight: 800; color: #ea580c; text-transform: uppercase; letter-spacing: 1.5px; padding-right: 6px;">
                            {{ $releaseLabel }} Cycle Award Release
                        </span>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 2. MAIN TABLES AREA (EOM: 4 HONOREES + GOM: 2 HONOREES)   --}}
                {{-- ========================================================= --}}
                <div style="display: flex; flex-direction: column; justify-content: space-between; flex: 1; padding: 28px 0 20px; gap: 32px;">

                    {{-- ----------------------------------------------------- --}}
                    {{-- SECTION 1: EMPLOYEES OF THE MONTH (4 TABLE ROWS)      --}}
                    {{-- ----------------------------------------------------- --}}
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        
                        {{-- Section Title Bar --}}
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: inline-flex; align-items: center; gap: 14px; padding: 10px 24px; border-radius: 16px; background: #fff7ed; border: 2px solid #fdba74;">
                                <span style="font-size: 28px;">🏆</span>
                                <span class="font-display" style="font-size: 28px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; color: #c2410c;">
                                    Employees of the Month (EOM)
                                </span>
                            </div>
                            <span style="font-size: 24px; font-weight: 800; color: #ea580c; text-transform: uppercase; letter-spacing: 1.5px;">
                                4 Honorees Awarded • General Operational Excellence
                            </span>
                        </div>

                        {{-- Modern Table --}}
                        <div class="recognition-table-card">
                            {{-- Header --}}
                            <div class="table-header-row">
                                <div class="col-honoree">Honoree</div>
                                <div class="col-code">Employee ID</div>
                                <div class="col-dept">Department</div>
                                <div class="col-desig">Designation</div>
                                <div class="col-badge">Award Status</div>
                            </div>

                            {{-- 4 Honoree Rows --}}
                            @foreach ($eomWinners as $winner)
                                @php $emp = $winner['employee']; @endphp
                                @if ($emp)
                                    <div class="table-row table-row-eom">
                                        {{-- Honoree Info --}}
                                        <div class="col-honoree">
                                            <div style="width: 82px; height: 82px; border-radius: 20px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 34px; font-weight: 900; box-shadow: 0 6px 16px rgba(234, 88, 12, 0.28); flex-shrink: 0;">
                                                {{ strtoupper(substr($emp->name, 0, 2)) }}
                                            </div>
                                            <div style="min-width: 0;">
                                                <h3 class="font-display" style="margin: 0; font-size: 40px; font-weight: 900; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.15;" title="{{ $emp->name }}">
                                                    {{ $emp->name }}
                                                </h3>
                                                <span style="display: inline-block; margin-top: 4px; font-size: 19px; font-weight: 700; color: #ea580c; text-transform: uppercase; letter-spacing: 1px;">
                                                    {{ $winner['entry_label'] }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Employee ID --}}
                                        <div class="col-code">
                                            <span class="font-mono-code" style="font-size: 28px; font-weight: 800; color: #0f172a; background: #f1f5f9; border: 1.5px solid #cbd5e1; padding: 10px 22px; border-radius: 12px; letter-spacing: 1px;">
                                                {{ $emp->employee_code }}
                                            </span>
                                        </div>

                                        {{-- Department --}}
                                        <div class="col-dept">
                                            <span style="font-size: 30px; font-weight: 800; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $winner['department'] }}">
                                                {{ $winner['department'] }}
                                            </span>
                                        </div>

                                        {{-- Designation --}}
                                        <div class="col-desig">
                                            <span style="font-size: 28px; font-weight: 700; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $emp->designation?->name ?: 'Staff' }}">
                                                {{ $emp->designation?->name ?: 'Staff' }}
                                            </span>
                                        </div>

                                        {{-- Award Badge --}}
                                        <div class="col-badge">
                                            <span style="display: inline-flex; align-items: center; gap: 10px; background: #fff7ed; border: 2px solid #ea580c; color: #c2410c; font-size: 22px; font-weight: 900; padding: 12px 26px; border-radius: 14px; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 10px rgba(234, 88, 12, 0.12); white-space: nowrap;">
                                                <span>🏆</span>
                                                <span>EOM Winner</span>
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    {{-- Unassigned Slot Row --}}
                                    <div class="table-row table-row-eom" style="opacity: 0.6; background: #fafafa;">
                                        <div class="col-honoree">
                                            <div style="width: 82px; height: 82px; border-radius: 20px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 32px; font-weight: 900; flex-shrink: 0;">
                                                ✦
                                            </div>
                                            <div>
                                                <h3 class="font-display" style="margin: 0; font-size: 36px; font-weight: 700; color: #94a3b8; font-style: italic;">
                                                    Slot Unassigned
                                                </h3>
                                                <span style="font-size: 19px; font-weight: 600; color: #94a3b8;">
                                                    {{ $winner['entry_label'] }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-code">
                                            <span style="font-size: 26px; color: #94a3b8;">—</span>
                                        </div>
                                        <div class="col-dept">
                                            <span style="font-size: 28px; color: #64748b;">
                                                {{ $winner['department'] }}
                                            </span>
                                        </div>
                                        <div class="col-desig">
                                            <span style="font-size: 26px; color: #94a3b8;">—</span>
                                        </div>
                                        <div class="col-badge">
                                            <span style="background: #f1f5f9; border: 1.5px solid #cbd5e1; color: #64748b; font-size: 20px; font-weight: 800; padding: 10px 22px; border-radius: 12px; text-transform: uppercase;">
                                                Pending Nomination
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
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        
                        {{-- Section Title Bar --}}
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: inline-flex; align-items: center; gap: 14px; padding: 10px 24px; border-radius: 16px; background: #fef2f2; border: 2px solid #fca5a5;">
                                <span style="font-size: 28px;">✨</span>
                                <span class="font-display" style="font-size: 28px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; color: #b91c1c;">
                                    Grooming of the Month (GOM)
                                </span>
                            </div>
                            <span style="font-size: 24px; font-weight: 800; color: #dc2626; text-transform: uppercase; letter-spacing: 1.5px;">
                                2 Honorees Awarded • Corporate Etiquette & Flawless Presentation
                            </span>
                        </div>

                        {{-- Modern Table --}}
                        <div class="recognition-table-card">
                            {{-- Header --}}
                            <div class="table-header-row">
                                <div class="col-honoree">Honoree</div>
                                <div class="col-code">Employee ID</div>
                                <div class="col-dept">Department</div>
                                <div class="col-desig">Designation</div>
                                <div class="col-badge">Award Status</div>
                            </div>

                            {{-- 2 Honoree Rows --}}
                            @foreach ($gomWinners as $winner)
                                @php $emp = $winner['employee']; @endphp
                                @if ($emp)
                                    <div class="table-row table-row-gom">
                                        {{-- Honoree Info --}}
                                        <div class="col-honoree">
                                            <div style="width: 82px; height: 82px; border-radius: 20px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 34px; font-weight: 900; box-shadow: 0 6px 16px rgba(220, 38, 38, 0.28); flex-shrink: 0;">
                                                {{ strtoupper(substr($emp->name, 0, 2)) }}
                                            </div>
                                            <div style="min-width: 0;">
                                                <h3 class="font-display" style="margin: 0; font-size: 40px; font-weight: 900; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.15;" title="{{ $emp->name }}">
                                                    {{ $emp->name }}
                                                </h3>
                                                <span style="display: inline-block; margin-top: 4px; font-size: 19px; font-weight: 700; color: #dc2626; text-transform: uppercase; letter-spacing: 1px;">
                                                    {{ $winner['entry_label'] }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Employee ID --}}
                                        <div class="col-code">
                                            <span class="font-mono-code" style="font-size: 28px; font-weight: 800; color: #0f172a; background: #f1f5f9; border: 1.5px solid #cbd5e1; padding: 10px 22px; border-radius: 12px; letter-spacing: 1px;">
                                                {{ $emp->employee_code }}
                                            </span>
                                        </div>

                                        {{-- Department --}}
                                        <div class="col-dept">
                                            <span style="font-size: 30px; font-weight: 800; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $winner['department'] }}">
                                                {{ $winner['department'] }}
                                            </span>
                                        </div>

                                        {{-- Designation --}}
                                        <div class="col-desig">
                                            <span style="font-size: 28px; font-weight: 700; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $emp->designation?->name ?: 'Staff' }}">
                                                {{ $emp->designation?->name ?: 'Staff' }}
                                            </span>
                                        </div>

                                        {{-- Award Badge --}}
                                        <div class="col-badge">
                                            <span style="display: inline-flex; align-items: center; gap: 10px; background: #fef2f2; border: 2px solid #dc2626; color: #b91c1c; font-size: 22px; font-weight: 900; padding: 12px 26px; border-radius: 14px; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 10px rgba(220, 38, 38, 0.12); white-space: nowrap;">
                                                <span>✨</span>
                                                <span>GOM Winner</span>
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    {{-- Unassigned Slot Row --}}
                                    <div class="table-row table-row-gom" style="opacity: 0.6; background: #fafafa;">
                                        <div class="col-honoree">
                                            <div style="width: 82px; height: 82px; border-radius: 20px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 32px; font-weight: 900; flex-shrink: 0;">
                                                ✦
                                            </div>
                                            <div>
                                                <h3 class="font-display" style="margin: 0; font-size: 36px; font-weight: 700; color: #94a3b8; font-style: italic;">
                                                    Slot Unassigned
                                                </h3>
                                                <span style="font-size: 19px; font-weight: 600; color: #94a3b8;">
                                                    {{ $winner['entry_label'] }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-code">
                                            <span style="font-size: 26px; color: #94a3b8;">—</span>
                                        </div>
                                        <div class="col-dept">
                                            <span style="font-size: 28px; color: #64748b;">
                                                {{ $winner['department'] }}
                                            </span>
                                        </div>
                                        <div class="col-desig">
                                            <span style="font-size: 26px; color: #94a3b8;">—</span>
                                        </div>
                                        <div class="col-badge">
                                            <span style="background: #f1f5f9; border: 1.5px solid #cbd5e1; color: #64748b; font-size: 20px; font-weight: 800; padding: 10px 22px; border-radius: 12px; text-transform: uppercase;">
                                                Pending Nomination
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- ========================================================= --}}
                {{-- 3. FOOTER SECTION (HRMS CERTIFICATION & CYCLE META)       --}}
                {{-- ========================================================= --}}
                <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 22px; border-top: 3px solid #e2e8f0;">
                    
                    {{-- Left Verification Seal --}}
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <span style="display: inline-flex; align-items: center; gap: 10px; padding: 10px 22px; border-radius: 12px; background: #fff7ed; border: 2px solid #fdba74; font-size: 22px; font-weight: 900; color: #c2410c; text-transform: uppercase; letter-spacing: 1px;">
                            <span>🛡️</span>
                            <span>Official HRMS Certified</span>
                        </span>
                        <span style="font-size: 22px; font-weight: 800; color: #0f172a;">
                            Waldo Dynasty Entertainment Pvt. Ltd.
                        </span>
                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #94a3b8;"></span>
                        <span style="font-size: 20px; font-weight: 600; color: #64748b;">
                            Casino Pride Kathmandu • Human Resources Department
                        </span>
                    </div>

                    {{-- Right Cycle & Spec Tag --}}
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <span class="font-mono-code" style="font-size: 22px; font-weight: 800; color: #475569; background: #ffffff; border: 2px solid #e2e8f0; padding: 10px 22px; border-radius: 12px;">
                            Evaluation Cycle: {{ $periodLabel }}
                        </span>
                        <span class="font-mono-code" style="font-size: 22px; font-weight: 800; color: #ea580c; background: #fff7ed; border: 2px solid #fed7aa; padding: 10px 22px; border-radius: 12px;">
                            4K Ultra HD • 3840 × 1920 (2:1 Ratio)
                        </span>
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
         * Creates an isolated offscreen clone at 1:1 scale so browser viewport size,
         * zoom levels, or transform scaling never distort or clip the output image.
         */
        async function downloadCard(format = 'jpg') {
            const btn = document.getElementById('downloadBtn');
            const btnText = document.getElementById('downloadBtnText');
            const targetEl = document.getElementById('master-wish-card-canvas');

            if (!targetEl) {
                alert('Card canvas element not found.');
                return;
            }

            btn.disabled = true;
            btnText.textContent = `Rendering 4K ${format.toUpperCase()}...`;

            // Wait for all fonts to be 100% loaded
            if (document.fonts && document.fonts.ready) {
                try {
                    await document.fonts.ready;
                } catch (e) {}
            }

            // Create an isolated offscreen sandbox container
            const offscreenContainer = document.createElement('div');
            offscreenContainer.style.position = 'fixed';
            offscreenContainer.style.left = '0';
            offscreenContainer.style.top = '0';
            offscreenContainer.style.width = '3840px';
            offscreenContainer.style.height = '1920px';
            offscreenContainer.style.zIndex = '-999999';
            offscreenContainer.style.pointerEvents = 'none';
            offscreenContainer.style.opacity = '1';
            offscreenContainer.style.overflow = 'hidden';
            offscreenContainer.style.transform = 'none';

            // Clone target directly
            const clone = targetEl.cloneNode(true);
            clone.id = 'master-wish-card-canvas-export-clone';
            clone.style.transform = 'none';
            clone.style.margin = '0';
            clone.style.width = '3840px';
            clone.style.height = '1920px';
            clone.style.position = 'relative';

            offscreenContainer.appendChild(clone);
            document.body.appendChild(offscreenContainer);

            // Small tick for DOM attachment
            await new Promise(r => setTimeout(r, 200));

            let dataUrl = null;

            try {
                // 1. Try htmlToImage first (produces flawless SVG foreignObject rasterization identical to browser DOM)
                if (typeof window.htmlToImage !== 'undefined') {
                    try {
                        const options = {
                            quality: 0.98,
                            width: 3840,
                            height: 1920,
                            canvasWidth: 3840,
                            canvasHeight: 1920,
                            pixelRatio: 1,
                            backgroundColor: '#f8fafc',
                        };

                        if (format === 'png') {
                            dataUrl = await window.htmlToImage.toPng(clone, options);
                        } else {
                            dataUrl = await window.htmlToImage.toJpeg(clone, options);
                        }
                    } catch (imageErr) {
                        console.warn('htmlToImage capture failed, falling back to html2canvas:', imageErr);
                    }
                }

                // 2. Fallback to html2canvas if htmlToImage failed
                if (!dataUrl && typeof window.html2canvas !== 'undefined') {
                    const canvas = await window.html2canvas(clone, {
                        width: 3840,
                        height: 1920,
                        windowWidth: 3840,
                        windowHeight: 1920,
                        scale: 1,
                        useCORS: true,
                        allowTaint: true,
                        backgroundColor: '#f8fafc',
                        logging: false,
                        x: 0,
                        y: 0,
                    });

                    dataUrl = canvas.toDataURL(format === 'png' ? 'image/png' : 'image/jpeg', 0.98);
                }

                if (!dataUrl) {
                    throw new Error('Canvas render produced empty result.');
                }

                // Trigger download
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
                // Clean up offscreen sandbox
                if (offscreenContainer.parentNode) {
                    offscreenContainer.parentNode.removeChild(offscreenContainer);
                }

                btn.disabled = false;
                btnText.textContent = 'Download 4K Card (JPG)';
            }
        }
    </script>
</body>

</html>
