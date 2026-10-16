<!DOCTYPE html>
<html lang="en" style="background: #0f172a;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion Congratulations Wish Card (16:9 1080p) - {{ $generatedDate->format('d M Y') }}</title>
    <x-favicon />

    {{-- Google Fonts: Plus Jakarta Sans & JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700;800&family=Playfair+Display:ital,wght@0,700;0,900;1,700&display=swap"
        rel="stylesheet">

    {{-- Client-side rasterization engine for 1:1 1080p export --}}
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

        .font-serif-title {
            font-family: 'Playfair Display', Georgia, serif;
        }

        /* Top Toolbar */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(15, 23, 42, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toolbar-title {
            color: #ffffff;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-badge {
            background: rgba(249, 115, 22, 0.2);
            color: #fb923c;
            border: 1px solid rgba(249, 115, 22, 0.4);
            font-size: 11px;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-switcher-tabs {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.06);
            padding: 4px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .tab-btn {
            background: transparent;
            color: #94a3b8;
            border: none;
            padding: 6px 14px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .tab-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }

        .tab-btn.active {
            background: #f97316;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(249, 115, 22, 0.4);
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .zoom-group {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 2px;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .zoom-btn {
            background: transparent;
            color: #cbd5e1;
            border: none;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.15s;
        }

        .zoom-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
        }

        .btn-brand {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 20px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #ffffff;
            font-size: 12.5px;
            font-weight: 800;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(234, 88, 12, 0.35);
            transition: all 0.2s ease;
        }

        .btn-brand:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(234, 88, 12, 0.45);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.15);
            font-size: 12.5px;
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
            padding: 30px 16px 60px;
            box-sizing: border-box;
            min-height: calc(100vh - 70px);
        }

        .preview-scale-wrapper {
            transform-origin: top center;
            transition: transform 0.15s ease-out;
            flex-shrink: 0;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            overflow: hidden;
            background: #ffffff;
        }

        /* ------------------------------------------------------------- */
        /* EXACT 16:9 CARD CANVAS (1920 x 1080 PIXELS)                   */
        /* Clean, Bright, Simple, Cheerful with NO dark glares          */
        /* ------------------------------------------------------------- */
        .wish-card-canvas {
            width: 1920px;
            height: 1080px;
            min-width: 1920px;
            min-height: 1080px;
            max-width: 1920px;
            max-height: 1080px;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px 56px 36px;
            overflow: hidden;
            color: #0f172a;

            /* Crisp, Bright Ivory & White Palette with subtle warm ambient glow */
            background-color: #ffffff;
            background-image: 
                radial-gradient(at 0% 0%, rgba(254, 243, 199, 0.5) 0px, transparent 40%),
                radial-gradient(at 100% 0%, rgba(255, 237, 213, 0.5) 0px, transparent 40%),
                radial-gradient(at 50% 100%, rgba(240, 253, 244, 0.4) 0px, transparent 45%);
            border: 1px solid #e2e8f0;
        }

        /* Inner Decorative Frame Border */
        .card-inner-frame {
            position: absolute;
            inset: 16px;
            border: 2px solid #fed7aa;
            border-radius: 16px;
            pointer-events: none;
            z-index: 1;
        }

        .card-inner-frame::after {
            content: '';
            position: absolute;
            inset: 4px;
            border: 1px dashed rgba(249, 115, 22, 0.35);
            border-radius: 12px;
        }

        .canvas-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* ------------------------------------------------------------- */
        /* SINGLE ENTRY SPOTLIGHT LAYOUT                                 */
        /* ------------------------------------------------------------- */
        .single-layout {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .single-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #ffedd5;
            padding-bottom: 22px;
        }

        .brand-block {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-badge {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 26px;
            font-weight: 900;
            box-shadow: 0 8px 16px rgba(234, 88, 12, 0.25);
        }

        .brand-text h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: 2px;
            color: #0f172a;
            text-transform: uppercase;
        }

        .brand-text p {
            margin: 3px 0 0 0;
            font-size: 13px;
            font-weight: 700;
            color: #ea580c;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .celebration-pill {
            background: #fff7ed;
            border: 1.5px solid #fed7aa;
            color: #c2410c;
            padding: 8px 22px;
            border-radius: 9999px;
            font-size: 13.5px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Single Spotlight Body */
        .single-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin: auto 0;
            padding: 10px 40px;
        }

        .spotlight-salutation {
            font-size: 16px;
            font-weight: 800;
            color: #ea580c;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .spotlight-headline {
            font-size: 52px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.15;
            margin: 0 0 24px 0;
            letter-spacing: -0.5px;
        }

        .spotlight-card {
            background: #ffffff;
            border: 2px solid #fed7aa;
            border-radius: 24px;
            padding: 36px 60px;
            box-shadow: 0 16px 40px -10px rgba(234, 88, 12, 0.12), 0 4px 12px rgba(0, 0, 0, 0.04);
            max-width: 1400px;
            width: 100%;
        }

        .emp-name-tag {
            font-size: 44px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin: 0 0 8px 0;
        }

        .emp-meta-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 28px;
        }

        .code-pill {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #334155;
            font-family: 'JetBrains Mono', monospace;
            font-size: 15px;
            font-weight: 800;
            padding: 4px 14px;
            border-radius: 8px;
        }

        .dept-pill {
            background: #fef3c7;
            border: 1px solid #fde68a;
            color: #92400e;
            font-size: 15px;
            font-weight: 800;
            padding: 4px 14px;
            border-radius: 8px;
        }

        .date-pill {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            font-size: 15px;
            font-weight: 800;
            padding: 4px 14px;
            border-radius: 8px;
        }

        /* Role Progression Boxes */
        .progression-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 32px;
            margin: 24px 0 32px;
        }

        .role-box {
            flex: 1;
            max-width: 480px;
            padding: 22px 28px;
            border-radius: 18px;
            text-align: center;
        }

        .role-box-previous {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
        }

        .role-box-promoted {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            border: 2px solid #f97316;
            box-shadow: 0 8px 24px rgba(249, 115, 22, 0.15);
        }

        .role-caption {
            font-size: 12.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }

        .role-box-previous .role-caption {
            color: #64748b;
        }

        .role-box-promoted .role-caption {
            color: #ea580c;
        }

        .role-title {
            font-size: 26px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.25;
            margin: 0;
        }

        .arrow-badge {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #f97316;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 6px 16px rgba(249, 115, 22, 0.35);
            flex-shrink: 0;
        }

        .wish-message-box {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 16px;
            padding: 20px 36px;
            margin-top: 10px;
        }

        .wish-message-text {
            font-size: 17px;
            line-height: 1.6;
            font-weight: 600;
            color: #78350f;
            margin: 0;
        }

        .single-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 2px solid #ffedd5;
            padding-top: 20px;
        }

        .footer-note {
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
        }

        .footer-signature {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ------------------------------------------------------------- */
        /* MULTI-ENTRY GRID LAYOUT (MAX 25 PER CARD)                     */
        /* ------------------------------------------------------------- */
        .multi-layout {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .multi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #ffedd5;
            padding-bottom: 18px;
            margin-bottom: 16px;
        }

        .multi-title-area h1 {
            margin: 0;
            font-size: 34px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .multi-title-area p {
            margin: 4px 0 0 0;
            font-size: 14.5px;
            font-weight: 600;
            color: #c2410c;
        }

        .multi-header-meta {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-page-badge {
            background: #fff7ed;
            border: 1.5px solid #fed7aa;
            color: #ea580c;
            padding: 8px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        /* Grid of Promotion Records (5 cols x 5 rows max = 25 items) */
        .promotions-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            flex: 1;
            align-content: start;
            margin-bottom: 16px;
        }

        /* Balanced layout when chunk has <= 10 items */
        .promotions-grid.few-records-5 {
            grid-template-columns: repeat(5, 1fr);
        }

        .promotions-grid.few-records-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        .promotions-grid.few-records-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .promotions-grid.few-records-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .promotions-grid.few-records-1 {
            grid-template-columns: 1fr;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .employee-promo-item {
            background: #ffffff;
            border: 1.5px solid #fed7aa;
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            position: relative;
            min-height: 136px;
        }

        .item-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 6px;
        }

        .item-name {
            font-size: 15.5px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 250px;
        }

        .item-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 800;
            color: #ea580c;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            padding: 2px 7px;
            border-radius: 6px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .item-dept {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-progression {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 10px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .prog-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
        }

        .prog-from {
            color: #64748b;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 130px;
        }

        .prog-arrow {
            color: #f97316;
            font-weight: 900;
            font-size: 12px;
            flex-shrink: 0;
        }

        .prog-to {
            color: #0f172a;
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-badge-highlight {
            color: #ea580c;
            font-weight: 900;
        }

        .multi-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 2px solid #ffedd5;
            padding-top: 14px;
        }

        .multi-wish-banner {
            font-size: 13.5px;
            font-weight: 700;
            color: #78350f;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .multi-footer-right {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
        }
    </style>
</head>

<body>
    {{-- Top Screen Toolbar --}}
    <div class="toolbar no-print">
        <div class="toolbar-left">
            <div class="toolbar-title">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#f97316" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
                <span>Promotion Congratulation Wish Card (16:9 • 1920×1080)</span>
            </div>
            <span class="toolbar-badge">{{ $totalCount }} {{ Str::plural('Record', $totalCount) }}</span>

            @if($totalCards > 1)
                <div class="card-switcher-tabs" id="cardSwitcherTabs">
                    @foreach($cardChunks as $index => $chunk)
                        <button type="button" class="tab-btn {{ $index === 0 ? 'active' : '' }}" onclick="switchCard({{ $index }})">
                            Card {{ $index + 1 }} ({{ $chunk->count() }})
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="toolbar-right">
            <div class="zoom-group">
                <button type="button" class="zoom-btn" onclick="setZoomMode('fit')" id="zoomFitBtn">Fit</button>
                <button type="button" class="zoom-btn" onclick="setZoomMode('50')" id="zoom50Btn">50%</button>
                <button type="button" class="zoom-btn" onclick="setZoomMode('100')" id="zoom100Btn">100%</button>
            </div>

            <button type="button" class="btn-brand" id="downloadJpgBtn" onclick="downloadCurrentCard('jpg')">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span id="downloadBtnText">Download Card (JPG)</span>
            </button>

            <button type="button" class="btn-secondary" onclick="downloadCurrentCard('png')">
                PNG
            </button>

            @if($totalCards > 1)
                <button type="button" class="btn-secondary" onclick="downloadAllCards()">
                    Download All ({{ $totalCards }})
                </button>
            @endif

            <button type="button" class="btn-secondary" onclick="window.close()">
                Close
            </button>
        </div>
    </div>

    @if($promotions->isEmpty())
        <div style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
            <div style="background: #ffffff; border: 1.5px solid #fed7aa; border-radius: 20px; padding: 48px; max-width: 520px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                <div style="font-size: 40px; margin-bottom: 12px;">🌟</div>
                <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 10px 0;">No Promotions Selected</h2>
                <p style="font-size: 14px; color: #64748b; line-height: 1.5; margin: 0 0 24px 0;">Please select one or more promotion records from the promotions table to generate congratulation wish cards.</p>
                <button type="button" class="btn-brand" onclick="window.close()" style="margin: 0 auto;">Close</button>
            </div>
        </div>
    @else
        {{-- Main Preview Container --}}
        <div class="preview-container">
            <div class="preview-scale-wrapper" id="previewScaleWrapper">
                @foreach($cardChunks as $cardIndex => $chunk)
                <div class="wish-card-canvas" id="wish-card-canvas-{{ $cardIndex }}" style="{{ $cardIndex !== 0 ? 'display: none;' : '' }}">
                    <div class="card-inner-frame"></div>
                    <div class="canvas-content">
                        @if($isSingle)
                            {{-- ============================================================== --}}
                            {{-- SINGLE PROMOTION SPOTLIGHT DESIGN                             --}}
                            {{-- ============================================================== --}}
                            @php
                                $promo = $chunk->first();
                                $emp = $promo->employee;
                            @endphp
                            <div class="single-layout">
                                {{-- Header --}}
                                <div class="single-header">
                                    <div class="brand-block">
                                        <div class="brand-badge">W</div>
                                        <div class="brand-text">
                                            <h2>Waldo Dynasty</h2>
                                            <p>Celebrating Excellence & Leadership</p>
                                        </div>
                                    </div>
                                    <div class="celebration-pill">
                                        <span>🎉</span>
                                        <span>Promotion Milestone</span>
                                    </div>
                                </div>

                                {{-- Body Spotlight --}}
                                <div class="single-body">
                                    <div class="spotlight-salutation">Heartiest Congratulations</div>
                                    <h1 class="spotlight-headline">Celebrating Your Well-Deserved Promotion!</h1>

                                    <div class="spotlight-card">
                                        <div class="emp-name-tag">{{ $emp?->name ?? 'Team Member' }}</div>
                                        <div class="emp-meta-row">
                                            <span class="code-pill">ID: {{ $promo->employee_id }}</span>
                                            <span class="dept-pill">{{ $promo->toDepartment?->name ?? $promo->fromDepartment?->name ?? 'Department' }}</span>
                                            @if($promo->promotion_date)
                                                <span class="date-pill">Effective: {{ $promo->promotion_date->format('F d, Y') }}</span>
                                            @endif
                                        </div>

                                        {{-- Advancement Transition --}}
                                        <div class="progression-container">
                                            <div class="role-box role-box-previous">
                                                <div class="role-caption">Previous Designation</div>
                                                <h3 class="role-title">{{ $promo->fromDesignation?->name ?? 'Previous Role' }}</h3>
                                            </div>

                                            <div class="arrow-badge">&rarr;</div>

                                            <div class="role-box role-box-promoted">
                                                <div class="role-caption">Promoted Designation</div>
                                                <h3 class="role-title">{{ $promo->toDesignation?->name ?? 'New Designation' }}</h3>
                                            </div>
                                        </div>

                                        {{-- Cheerful Personal Wish --}}
                                        <div class="wish-message-box">
                                            <p class="wish-message-text">
                                                "Warmest congratulations on reaching this remarkable professional milestone! Your hard work, passion, and steadfast commitment continue to inspire everyone around you. We celebrate your dedication and wish you boundless success, joy, and fulfilling achievements in your new elevated role!"
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Footer --}}
                                <div class="single-footer">
                                    <div class="footer-note">
                                        Official Organization Recognition • Issued on {{ $generatedDate->format('F d, Y') }}
                                    </div>
                                    <div class="footer-signature">
                                        <span>With Pride & Best Wishes,</span>
                                        <strong>Waldo Dynasty Family</strong>
                                    </div>
                                </div>
                            </div>

                        @else
                            {{-- ============================================================== --}}
                            {{-- MULTI-PROMOTION GROUP CARD DESIGN (MAX 25 PER CARD)            --}}
                            {{-- ============================================================== --}}
                            @php
                                $chunkCount = $chunk->count();
                                $gridModifier = '';
                                if ($chunkCount <= 5) {
                                    $gridModifier = 'few-records-' . $chunkCount;
                                }
                            @endphp
                            <div class="multi-layout">
                                {{-- Header --}}
                                <div class="multi-header">
                                    <div class="brand-block">
                                        <div class="brand-badge">W</div>
                                        <div class="multi-title-area">
                                            <h1>
                                                <span>Heartiest Congratulations on Your Promotions!</span>
                                                <span style="font-size: 26px;">🎉</span>
                                            </h1>
                                            <p>Celebrating growth, dedication, and leadership across our teams</p>
                                        </div>
                                    </div>

                                    <div class="multi-header-meta">
                                        @if($totalCards > 1)
                                            <div class="card-page-badge">
                                                Card {{ $cardIndex + 1 }} of {{ $totalCards }} • {{ $chunkCount }} Honored Team Members
                                            </div>
                                        @else
                                            <div class="card-page-badge">
                                                {{ $chunkCount }} Honored Team Members
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Multi-Grid (Max 25 items: 5 cols x 5 rows) --}}
                                <div class="promotions-grid {{ $gridModifier }}">
                                    @foreach($chunk as $promo)
                                        @php
                                            $isDesignationChanged = $promo->from_designation_id !== $promo->to_designation_id;
                                            $isDeptChanged = $promo->from_department_id !== $promo->to_department_id;
                                        @endphp
                                        <div class="employee-promo-item">
                                            <div>
                                                <div class="item-top">
                                                    <span class="item-name" title="{{ $promo->employee?->name ?? 'N/A' }}">
                                                        {{ $promo->employee?->name ?? 'N/A' }}
                                                    </span>
                                                    <span class="item-code">{{ $promo->employee_id }}</span>
                                                </div>
                                                <div class="item-dept" title="{{ $promo->toDepartment?->name ?? $promo->fromDepartment?->name ?? '-' }}">
                                                    {{ $promo->toDepartment?->name ?? $promo->fromDepartment?->name ?? '-' }}
                                                </div>
                                            </div>

                                            <div class="item-progression">
                                                <div class="prog-row">
                                                    <span class="prog-from" title="{{ $promo->fromDesignation?->name ?? '-' }}">
                                                        {{ $promo->fromDesignation?->name ?? '-' }}
                                                    </span>
                                                    <span class="prog-arrow">&rarr;</span>
                                                    <span class="prog-to item-badge-highlight" title="{{ $promo->toDesignation?->name ?? '-' }}">
                                                        {{ $promo->toDesignation?->name ?? '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Multi-Footer with warm cheerful message --}}
                                <div class="multi-footer">
                                    <div class="multi-wish-banner">
                                        <span>🌟</span>
                                        <span>"Wishing all our promoted team members continued inspiration, excellence, and grand achievements in their new roles!"</span>
                                    </div>
                                    <div class="multi-footer-right">
                                        <span>Waldo Dynasty</span>
                                        <span>•</span>
                                        <span>{{ $generatedDate->format('F d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Script for Zoom Scaling & High-Resolution Export --}}
    <script>
        let currentZoomMode = 'fit';
        let activeCardIndex = 0;
        const totalCards = {{ $totalCards }};

        function switchCard(index) {
            activeCardIndex = index;

            // Hide all cards, show active card
            for (let i = 0; i < totalCards; i++) {
                const el = document.getElementById(`wish-card-canvas-${i}`);
                if (el) {
                    el.style.display = (i === index) ? 'flex' : 'none';
                }
            }

            // Update tab button styles
            const tabBtns = document.querySelectorAll('.card-switcher-tabs .tab-btn');
            tabBtns.forEach((btn, idx) => {
                if (idx === index) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        function updatePreviewScale() {
            const wrapper = document.getElementById('previewScaleWrapper');
            if (!wrapper) return;

            let scale = 0.5;
            if (currentZoomMode === 'fit') {
                const availableWidth = window.innerWidth - 64;
                // Target width is 1920px
                scale = Math.min(Math.max(availableWidth / 1920, 0.25), 0.90);
            } else if (currentZoomMode === '50') {
                scale = 0.50;
            } else if (currentZoomMode === '100') {
                scale = 1.0;
            }

            wrapper.style.transform = `scale(${scale})`;
        }

        function setZoomMode(mode) {
            currentZoomMode = mode;
            updatePreviewScale();
        }

        window.addEventListener('resize', updatePreviewScale);
        window.addEventListener('DOMContentLoaded', updatePreviewScale);
        setTimeout(updatePreviewScale, 100);

        /**
         * Render and download a specific card canvas (1920x1080)
         */
        async function captureCard(cardIdx, format = 'jpg') {
            const wrapper = document.getElementById('previewScaleWrapper');
            const targetEl = document.getElementById(`wish-card-canvas-${cardIdx}`);

            if (!targetEl || !wrapper) {
                throw new Error('Canvas element not found');
            }

            // Ensure card is visible for rendering
            const prevDisplay = targetEl.style.display;
            targetEl.style.display = 'flex';

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
                document.body.style.width = '1920px';

                await new Promise(r => setTimeout(r, 120));

                const canvas = await window.html2canvas(targetEl, {
                    width: 1920,
                    height: 1080,
                    scale: 1,
                    windowWidth: 1920,
                    windowHeight: 1080,
                    x: 0,
                    y: 0,
                    scrollX: 0,
                    scrollY: 0,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    logging: false,
                });

                const mimeType = format === 'png' ? 'image/png' : 'image/jpeg';
                const quality = format === 'png' ? 1.0 : 0.95;
                const dataUrl = canvas.toDataURL(mimeType, quality);

                if (!dataUrl || dataUrl === 'data:,') {
                    throw new Error('Canvas snapshot produced an empty result');
                }

                @if($isSingle)
                    const filename = `Promotion-Wish-Card-{{ \Illuminate\Support\Str::slug($promotions->first()?->employee?->name ?? 'single') }}-1920x1080.${format}`;
                @else
                    const filename = `Promotion-Wish-Card-Page-${cardIdx + 1}-of-${totalCards}-1920x1080.${format}`;
                @endif

                const link = document.createElement('a');
                link.download = filename;
                link.href = dataUrl;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

            } finally {
                wrapper.style.transform = origWrapperTransform;
                wrapper.style.boxShadow = origWrapperBoxShadow;
                document.body.style.overflow = origBodyOverflow;
                document.body.style.width = origBodyWidth;
                targetEl.style.display = prevDisplay;
            }
        }

        async function downloadCurrentCard(format = 'jpg') {
            const btn = document.getElementById('downloadJpgBtn');
            const btnText = document.getElementById('downloadBtnText');

            btn.disabled = true;
            btnText.textContent = `Rendering 1080p ${format.toUpperCase()}...`;

            try {
                await captureCard(activeCardIndex, format);
            } catch (err) {
                console.error('Error generating card image:', err);
                alert('Could not render image automatically. Please check your browser permissions.');
            } finally {
                btn.disabled = false;
                btnText.textContent = 'Download Card (JPG)';
            }
        }

        async function downloadAllCards() {
            const btn = document.getElementById('downloadJpgBtn');
            const btnText = document.getElementById('downloadBtnText');

            btn.disabled = true;

            try {
                for (let i = 0; i < totalCards; i++) {
                    btnText.textContent = `Rendering Card ${i + 1} of ${totalCards}...`;
                    await captureCard(i, 'jpg');
                    await new Promise(r => setTimeout(r, 600)); // Stagger downloads
                }
            } catch (err) {
                console.error('Error in batch card download:', err);
                alert('Could not complete all card downloads.');
            } finally {
                btn.disabled = false;
                btnText.textContent = 'Download Card (JPG)';
            }
        }
    </script>
</body>

</html>
