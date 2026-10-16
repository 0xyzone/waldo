<!DOCTYPE html>
<html lang="en" style="background: #0f172a;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion Congratulations Wish Card (16:9 • 1080p) - {{ $generatedDate->format('d M Y') }}</title>
    <x-favicon />

    {{-- Google Fonts: Plus Jakarta Sans & JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap"
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
            background-color: #0b1120;
            color: #0f172a;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }

        /* ------------------------------------------------------------- */
        /* TOP SCREEN TOOLBAR                                            */
        /* ------------------------------------------------------------- */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(11, 17, 32, 0.94);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .toolbar-title {
            color: #f8fafc;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toolbar-badge {
            background: rgba(255, 255, 255, 0.08);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.14);
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 9999px;
            letter-spacing: 0.3px;
        }

        .card-switcher-tabs {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.05);
            padding: 3px;
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
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .tab-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }

        .tab-btn.active {
            background: #d97706;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(217, 119, 6, 0.35);
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .zoom-group {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 8px;
            padding: 2px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .zoom-btn {
            background: transparent;
            color: #cbd5e1;
            border: none;
            padding: 5px 11px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.15s;
        }

        .zoom-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
        }

        .btn-action-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 10px;
            background: #d97706;
            color: #ffffff;
            border: none;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(217, 119, 6, 0.3);
            transition: all 0.2s ease;
        }

        .btn-action-primary:hover {
            background: #b45309;
            box-shadow: 0 6px 18px rgba(217, 119, 6, 0.4);
        }

        .btn-action-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.14);
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-action-secondary:hover {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
        }

        /* ------------------------------------------------------------- */
        /* PREVIEW VIEWPORT                                              */
        /* ------------------------------------------------------------- */
        .preview-container {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 30px 16px 60px;
            box-sizing: border-box;
            min-height: calc(100vh - 65px);
        }

        .preview-scale-wrapper {
            transform-origin: top center;
            transition: transform 0.15s ease-out;
            flex-shrink: 0;
            box-shadow: 0 30px 80px -15px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
            background: #f1f5f9;
        }

        /* ------------------------------------------------------------- */
        /* EXACT 16:9 1080p CANVAS (1920 x 1080 PIXELS)                  */
        /* Modern Minimal Executive Light Glass Aesthetic               */
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
            overflow: hidden;
            color: #0f172a;

            /* Soft, Calm Light Canvas (cool slate & warm ivory subtle ambience) */
            background-color: #f1f5f9;
            background-image: 
                radial-gradient(circle at 10% 10%, rgba(255, 255, 255, 0.95) 0%, transparent 45%),
                radial-gradient(circle at 90% 12%, rgba(254, 243, 199, 0.45) 0%, transparent 40%),
                radial-gradient(circle at 50% 90%, rgba(241, 245, 249, 0.9) 0%, transparent 50%),
                radial-gradient(circle at 85% 85%, rgba(226, 232, 240, 0.6) 0%, transparent 45%);
            border: 1px solid #cbd5e1;
        }

        /* Frosted Glass Container Token */
        .light-glass-panel {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        /* ------------------------------------------------------------- */
        /* SINGLE PROMOTION SPOTLIGHT (MINIMAL EXECUTIVE FOCUS)          */
        /* ------------------------------------------------------------- */
        .single-layout-container {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px 64px 36px;
            position: relative;
            z-index: 2;
        }

        .single-header-card {
            padding: 16px 36px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-inline-cluster {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-monogram-circle {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #0f172a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .brand-identity h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .brand-identity p {
            margin: 2px 0 0 0;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .header-status-badge {
            background: #fef3c7;
            border: 1px solid #fde68a;
            color: #92400e;
            padding: 5px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Center Hero Spotlight */
        .single-hero-card {
            border-radius: 24px;
            padding: 44px 64px 38px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin: auto 0;
            max-width: 1300px;
            width: 100%;
            align-self: center;
        }

        .single-caption-eyebrow {
            font-size: 12px;
            font-weight: 800;
            color: #d97706;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .single-main-headline {
            font-size: 36px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin: 0 0 20px 0;
        }

        .single-emp-name {
            font-size: 48px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin: 0 0 14px 0;
            line-height: 1.15;
        }

        .single-meta-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .meta-pill {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 5px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
        }

        .meta-pill-amber {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
            font-weight: 700;
        }

        .meta-pill-green {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
            font-weight: 700;
        }

        /* Progression Deck */
        .single-progression-deck {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            width: 100%;
            max-width: 980px;
            margin-bottom: 28px;
        }

        .single-prog-tile {
            flex: 1;
            padding: 20px 24px;
            border-radius: 16px;
            text-align: center;
        }

        .tile-from {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .tile-to {
            background: #fffbeb;
            border: 1.5px solid #f59e0b;
            box-shadow: 0 8px 24px -4px rgba(245, 158, 11, 0.12);
        }

        .tile-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .tile-from .tile-label {
            color: #64748b;
        }

        .tile-to .tile-label {
            color: #b45309;
        }

        .tile-title {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            line-height: 1.25;
        }

        .prog-arrow-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #d97706;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
            flex-shrink: 0;
        }

        /* Management Memo Block */
        .single-management-memo {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3.5px solid #d97706;
            border-radius: 12px;
            padding: 16px 28px;
            max-width: 980px;
            width: 100%;
            text-align: left;
        }

        .single-management-text {
            font-size: 14.5px;
            line-height: 1.6;
            color: #334155;
            margin: 0;
            font-weight: 500;
        }

        .single-management-text strong {
            color: #0f172a;
            font-weight: 700;
        }

        .single-bottom-credit {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            padding: 0 10px;
        }

        /* ------------------------------------------------------------- */
        /* MULTI-RECORD GRID LAYOUT (MAX 25 PER CARD)                     */
        /* ------------------------------------------------------------- */
        .multi-layout-container {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 30px 44px 24px;
            position: relative;
            z-index: 2;
        }

        .multi-header-bar {
            padding: 16px 28px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 14px;
            flex-shrink: 0;
        }

        .header-content-left {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .header-brand-line {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #d97706;
        }

        .header-brand-line .dot {
            color: #cbd5e1;
        }

        .header-brand-line .sub {
            color: #64748b;
            font-weight: 600;
        }

        .multi-heading-title {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.4px;
        }

        .multi-management-sentence {
            margin: 2px 0 0 0;
            font-size: 13px;
            line-height: 1.45;
            color: #475569;
            max-width: 1200px;
        }

        .multi-management-sentence strong {
            color: #0f172a;
        }

        .header-counter-badge {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #0f172a;
            padding: 7px 16px;
            border-radius: 10px;
            font-size: 12.5px;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* Full Height 5-Column Grid */
        .multi-records-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            grid-template-rows: repeat(5, 1fr);
            gap: 10px;
            flex: 1;
            margin-bottom: 10px;
        }

        /* Adaptive row sizing when chunk has fewer than 25 records */
        .multi-records-grid.fewer-rows {
            grid-template-rows: auto;
            align-content: start;
            gap: 12px;
        }

        .multi-records-grid.few-records-1 {
            grid-template-columns: 1fr;
            max-width: 600px;
            margin: auto;
        }

        .multi-records-grid.few-records-2 {
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: auto;
        }

        .multi-records-grid.few-records-3 {
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: auto;
        }

        .multi-records-grid.few-records-4 {
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: auto;
        }

        .multi-records-grid.few-records-5 {
            grid-template-columns: repeat(5, 1fr);
            grid-template-rows: auto;
        }

        /* Individual Employee Card */
        .emp-glass-card {
            border-radius: 14px;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 8px;
            transition: all 0.15s ease;
            box-shadow: 0 2px 10px -2px rgba(15, 23, 42, 0.04);
            background: rgba(255, 255, 255, 0.95);
        }

        .emp-glass-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.08);
        }

        .card-profile-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-avatar-monogram {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            color: #0f172a;
            font-size: 13px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .card-name-group {
            display: flex;
            flex-direction: column;
            min-width: 0;
            flex: 1;
        }

        .card-emp-name {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-meta-line {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 1px;
            font-size: 10.5px;
        }

        .card-emp-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9.5px;
            font-weight: 700;
            color: #475569;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 1px 5px;
            border-radius: 4px;
            white-space: nowrap;
        }

        .card-dept-name {
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Center Elevation Deck */
        .card-elevation-deck {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 10px;
        }

        .elevation-col {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .elevation-caption {
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .elevation-col-from .elevation-caption {
            color: #64748b;
        }

        .elevation-col-from .elevation-text {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .elevation-arrow-disc {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #d97706;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 12px;
            font-weight: 800;
        }

        .elevation-col-to .elevation-caption {
            color: #b45309;
        }

        .elevation-col-to .elevation-text {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Bottom Row */
        .card-bottom-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 10.5px;
            padding: 0 2px;
        }

        .card-promo-date {
            color: #b45309;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .card-tag-pill {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            white-space: nowrap;
        }

        /* Subtle Bottom Watermark Line */
        .canvas-footer-minimal {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11.5px;
            color: #64748b;
            font-weight: 500;
            padding: 0 6px;
            flex-shrink: 0;
        }
    </style>
</head>

<body>
    {{-- Top Screen Toolbar --}}
    <div class="toolbar no-print">
        <div class="toolbar-left">
            <div class="toolbar-title">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
                <span>Promotion Congratulation Wish Card</span>
            </div>
            <span class="toolbar-badge">{{ $totalCount }} {{ Str::plural('Record', $totalCount) }} • 16:9 1080p</span>

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

            <button type="button" class="btn-action-primary" id="downloadJpgBtn" onclick="downloadCurrentCard('jpg')">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span id="downloadBtnText">Download Card (JPG)</span>
            </button>

            <button type="button" class="btn-action-secondary" onclick="downloadCurrentCard('png')">
                PNG
            </button>

            @if($totalCards > 1)
                <button type="button" class="btn-action-secondary" onclick="downloadAllCards()">
                    Download All ({{ $totalCards }})
                </button>
            @endif

            <button type="button" class="btn-action-secondary" onclick="window.close()">
                Close
            </button>
        </div>
    </div>

    @if($promotions->isEmpty())
        {{-- Empty State Fallback --}}
        <div style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
            <div class="light-glass-panel" style="border-radius: 20px; padding: 48px; max-width: 520px; text-align: center;">
                <div style="font-size: 36px; margin-bottom: 12px; color: #d97706;">✦</div>
                <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 10px 0;">No Promotions Selected</h2>
                <p style="font-size: 13.5px; color: #64748b; line-height: 1.5; margin: 0 0 24px 0;">Please select one or more promotion records from the promotions table to generate congratulation wish cards.</p>
                <button type="button" class="btn-action-primary" onclick="window.close()" style="margin: 0 auto;">Close</button>
            </div>
        </div>
    @else
        {{-- Main Preview Container --}}
        <div class="preview-container">
            <div class="preview-scale-wrapper" id="previewScaleWrapper">
                @foreach($cardChunks as $cardIndex => $chunk)
                    <div class="wish-card-canvas" id="wish-card-canvas-{{ $cardIndex }}" style="{{ $cardIndex !== 0 ? 'display: none;' : '' }}">
                        @if($isSingle)
                            {{-- ============================================================== --}}
                            {{-- SINGLE PROMOTION SPOTLIGHT (MINIMAL EXECUTIVE LIGHT GLASS)     --}}
                            {{-- ============================================================== --}}
                            @php
                                $promo = $chunk->first();
                                $emp = $promo->employee;
                            @endphp
                            <div class="single-layout-container">
                                {{-- Header --}}
                                <div class="single-header-card light-glass-panel">
                                    <div class="brand-inline-cluster">
                                        <div class="brand-monogram-circle">W</div>
                                        <div class="brand-identity">
                                            <h2>Waldo Dynasty</h2>
                                            <p>Career Advancement & Leadership</p>
                                        </div>
                                    </div>
                                    <div class="header-status-badge">
                                        <span>Official Promotion Announcement</span>
                                    </div>
                                </div>

                                {{-- Main Spotlight Card --}}
                                <div class="single-hero-card light-glass-panel">
                                    <div class="single-caption-eyebrow">Professional Milestone Recognition</div>
                                    <h1 class="single-main-headline">Congratulations on Your Promotion</h1>

                                    <div class="single-emp-name">{{ $emp?->name ?? 'Team Member' }}</div>
                                    <div class="single-meta-row">
                                        <span class="meta-pill font-mono-code">ID: {{ $promo->employee_id }}</span>
                                        <span class="meta-pill">{{ $promo->toDepartment?->name ?? $promo->fromDepartment?->name ?? 'Department' }}</span>
                                        @if($promo->promotion_date)
                                            <span class="meta-pill meta-pill-green">
                                                Effective: {{ $promo->promotion_date->format('F d, Y') }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Role Progression Deck --}}
                                    <div class="single-progression-deck">
                                        <div class="single-prog-tile tile-from">
                                            <div class="tile-label">Previous Designation</div>
                                            <h3 class="tile-title">{{ $promo->fromDesignation?->name ?? 'Previous Role' }}</h3>
                                        </div>

                                        <div class="prog-arrow-circle">&rarr;</div>

                                        <div class="single-prog-tile tile-to">
                                            <div class="tile-label">Promoted Designation</div>
                                            <h3 class="tile-title">{{ $promo->toDesignation?->name ?? 'New Designation' }}</h3>
                                        </div>
                                    </div>

                                    {{-- Management Note from Memo --}}
                                    <div class="single-management-memo">
                                        <p class="single-management-text">
                                            "<strong>The Management is pleased to congratulate you on your well-deserved promotion.</strong> We appreciate your dedication and valuable contributions to the organization, and wish you continued success in your new role."
                                        </p>
                                    </div>
                                </div>

                                {{-- Bottom Footnote Line --}}
                                <div class="single-bottom-credit">
                                    <span>Waldo Dynasty Entertainment Pvt. Ltd. • Human Resources Management</span>
                                    <span>Issued: {{ $generatedDate->format('F d, Y') }}</span>
                                </div>
                            </div>

                        @else
                            {{-- ============================================================== --}}
                            {{-- MULTI-RECORD GRID DESIGN (MINIMAL EXECUTIVE LIGHT GLASS • 25) --}}
                            {{-- ============================================================== --}}
                            @php
                                $chunkCount = $chunk->count();
                                $gridModifier = '';
                                if ($chunkCount < 25) {
                                    $gridModifier = 'fewer-rows';
                                    if ($chunkCount <= 5) {
                                        $gridModifier .= ' few-records-' . $chunkCount;
                                    }
                                }
                            @endphp
                            <div class="multi-layout-container">
                                {{-- Header Bar with Integrated Management Note --}}
                                <div class="multi-header-bar light-glass-panel">
                                    <div class="header-content-left">
                                        <div class="header-brand-line">
                                            <span>Waldo Dynasty</span>
                                            <span class="dot">•</span>
                                            <span class="sub">Official Promotion Announcement</span>
                                        </div>
                                        <h1 class="multi-heading-title">Congratulations on Your Promotion</h1>
                                        <p class="multi-management-sentence">
                                            <strong>The Management is pleased to congratulate the following employees on their well-deserved promotions.</strong> We appreciate your dedication and valuable contributions to the organization, and wish you continued success in your new roles.
                                        </p>
                                    </div>

                                    <div class="header-counter-badge">
                                        @if($totalCards > 1)
                                            Card {{ $cardIndex + 1 }} of {{ $totalCards }} • {{ $chunkCount }} Members
                                        @else
                                            {{ $chunkCount }} Promoted Team Members
                                        @endif
                                    </div>
                                </div>

                                {{-- Full-Height 5-Column Grid (Max 25 Items) --}}
                                <div class="multi-records-grid {{ $gridModifier }}">
                                    @foreach($chunk as $promo)
                                        @php
                                            $empName = trim($promo->employee?->name ?? 'Team Member');
                                            $nameParts = preg_split('/\s+/', $empName);
                                            $initials = count($nameParts) >= 2 
                                                ? mb_substr($nameParts[0], 0, 1) . mb_substr(end($nameParts), 0, 1)
                                                : mb_substr($empName, 0, 2);
                                            $initials = strtoupper($initials);
                                        @endphp
                                        <div class="emp-glass-card light-glass-panel">
                                            {{-- Profile Header with Avatar & Details --}}
                                            <div class="card-profile-header">
                                                <div class="card-avatar-monogram">{{ $initials }}</div>
                                                <div class="card-name-group">
                                                    <span class="card-emp-name" title="{{ $empName }}">
                                                        {{ $empName }}
                                                    </span>
                                                    <div class="card-meta-line">
                                                        <span class="card-emp-code">{{ $promo->employee_id }}</span>
                                                        <span style="color: #cbd5e1;">•</span>
                                                        <span class="card-dept-name" title="{{ $promo->toDepartment?->name ?? $promo->fromDepartment?->name ?? '-' }}">
                                                            {{ $promo->toDepartment?->name ?? $promo->fromDepartment?->name ?? '-' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Center Elevation Deck (Fills Card Center) --}}
                                            <div class="card-elevation-deck">
                                                <div class="elevation-col elevation-col-from">
                                                    <span class="elevation-caption">Previous</span>
                                                    <span class="elevation-text" title="{{ $promo->fromDesignation?->name ?? '-' }}">{{ $promo->fromDesignation?->name ?? '-' }}</span>
                                                </div>
                                                <div class="elevation-arrow-disc">&rarr;</div>
                                                <div class="elevation-col elevation-col-to">
                                                    <span class="elevation-caption">Promoted To</span>
                                                    <span class="elevation-text" title="{{ $promo->toDesignation?->name ?? '-' }}">{{ $promo->toDesignation?->name ?? '-' }}</span>
                                                </div>
                                            </div>

                                            {{-- Bottom Status & Date Row --}}
                                            <div class="card-bottom-row">
                                                @if($promo->promotion_date)
                                                    <span class="card-promo-date" title="Effective Promotion Date">
                                                        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                        {{ $promo->promotion_date->format('d M, Y') }}
                                                    </span>
                                                @else
                                                    <span class="card-promo-date">Promotion Verified</span>
                                                @endif
                                                <span class="card-tag-pill">Advancement</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Minimal Footer Watermark --}}
                                <div class="canvas-footer-minimal">
                                    <span>Waldo Dynasty Entertainment Pvt. Ltd.</span>
                                    <span>Date of Issue: {{ $generatedDate->format('F d, Y') }}</span>
                                </div>
                            </div>
                        @endif
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

            for (let i = 0; i < totalCards; i++) {
                const el = document.getElementById(`wish-card-canvas-${i}`);
                if (el) {
                    el.style.display = (i === index) ? 'flex' : 'none';
                }
            }

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
                    backgroundColor: '#f1f5f9',
                    logging: false,
                });

                const mimeType = format === 'png' ? 'image/png' : 'image/jpeg';
                const quality = format === 'png' ? 1.0 : 0.96;
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
                    await new Promise(r => setTimeout(r, 600));
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
