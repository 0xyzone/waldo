<!DOCTYPE html>
<html lang="en" style="background: #090d16;">

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
            background-color: #070a12;
            color: #f1f5f9;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }

        /* ------------------------------------------------------------- */
        /* TOP TOOLBAR (Sleek Glassmorphism Control Bar)                 */
        /* ------------------------------------------------------------- */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(9, 13, 22, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
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
            letter-spacing: 0.2px;
        }

        .toolbar-badge {
            background: rgba(255, 255, 255, 0.06);
            color: #94a3b8;
            border: 1px solid rgba(255, 255, 255, 0.1);
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
            background: rgba(255, 255, 255, 0.04);
            padding: 3px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .tab-btn {
            background: transparent;
            color: #94a3b8;
            border: none;
            padding: 5px 12px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .tab-btn:hover {
            color: #f8fafc;
            background: rgba(255, 255, 255, 0.06);
        }

        .tab-btn.active {
            background: rgba(245, 158, 11, 0.18);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.35);
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .zoom-group {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 2px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .zoom-btn {
            background: transparent;
            color: #94a3b8;
            border: none;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.15s;
        }

        .zoom-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }

        .btn-glass-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.25) 0%, rgba(217, 119, 6, 0.25) 100%);
            color: #fef3c7;
            border: 1px solid rgba(245, 158, 11, 0.45);
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        .btn-glass-primary:hover {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.35) 0%, rgba(217, 119, 6, 0.35) 100%);
            border-color: rgba(245, 158, 11, 0.6);
            color: #ffffff;
        }

        .btn-glass-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-glass-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
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
            padding: 32px 16px 64px;
            box-sizing: border-box;
            min-height: calc(100vh - 65px);
        }

        .preview-scale-wrapper {
            transform-origin: top center;
            transition: transform 0.15s ease-out;
            flex-shrink: 0;
            box-shadow: 0 30px 80px -20px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            overflow: hidden;
            background: #090d16;
        }

        /* ------------------------------------------------------------- */
        /* EXACT 16:9 1080p CANVAS (1920 x 1080 PIXELS)                  */
        /* Clean, Sophisticated, Non-Flashy Glassmorphism               */
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
            color: #f1f5f9;

            /* Deep Obsidian Canvas with Refined Ambient Mesh Orbs */
            background-color: #090d16;
            background-image: 
                radial-gradient(circle at 18% 15%, rgba(59, 130, 246, 0.09) 0%, transparent 45%),
                radial-gradient(circle at 82% 18%, rgba(245, 158, 11, 0.08) 0%, transparent 45%),
                radial-gradient(circle at 50% 80%, rgba(16, 185, 129, 0.07) 0%, transparent 45%),
                radial-gradient(circle at 85% 85%, rgba(99, 102, 241, 0.06) 0%, transparent 40%);
        }

        /* Universal Glass Container Token */
        .glass-panel {
            background: rgba(255, 255, 255, 0.035);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        /* ------------------------------------------------------------- */
        /* SINGLE PROMOTION SPOTLIGHT (A4 Equivalent 16:9 Focus)         */
        /* ------------------------------------------------------------- */
        .single-wrapper {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px 64px 38px;
            position: relative;
            z-index: 2;
        }

        .single-header-panel {
            padding: 18px 36px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-cluster {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-monogram {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fbbf24;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 800;
        }

        .brand-titles h2 {
            margin: 0;
            font-size: 19px;
            font-weight: 800;
            color: #f8fafc;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .brand-titles p {
            margin: 2px 0 0 0;
            font-size: 11.5px;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header-meta-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .glass-pill {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            color: #cbd5e1;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .glass-pill-amber {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.25);
            color: #fbbf24;
            font-weight: 700;
        }

        /* Central Spotlight Card */
        .single-spotlight-card {
            border-radius: 24px;
            padding: 42px 64px 38px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin: 18px 0;
            position: relative;
        }

        .spotlight-eyebrow {
            font-size: 13px;
            font-weight: 700;
            color: #fbbf24;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .spotlight-title {
            font-size: 38px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            margin: 0 0 24px 0;
        }

        .emp-name-headline {
            font-size: 46px;
            font-weight: 800;
            color: #f8fafc;
            letter-spacing: -0.5px;
            margin: 0 0 14px 0;
            line-height: 1.15;
        }

        .emp-tags-deck {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        /* Role Progression Tiles */
        .progression-deck {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            width: 100%;
            max-width: 1100px;
            margin-bottom: 32px;
        }

        .prog-card {
            flex: 1;
            padding: 22px 28px;
            border-radius: 16px;
            text-align: center;
        }

        .prog-card-from {
            background: rgba(255, 255, 255, 0.025);
            border: 1px solid rgba(255, 255, 255, 0.07);
        }

        .prog-card-to {
            background: rgba(245, 158, 11, 0.06);
            border: 1px solid rgba(245, 158, 11, 0.28);
            box-shadow: 0 12px 30px -10px rgba(245, 158, 11, 0.15);
        }

        .prog-label {
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .prog-card-from .prog-label {
            color: #94a3b8;
        }

        .prog-card-to .prog-label {
            color: #fbbf24;
        }

        .prog-val {
            font-size: 24px;
            font-weight: 800;
            margin: 0;
            line-height: 1.25;
        }

        .prog-card-from .prog-val {
            color: #cbd5e1;
        }

        .prog-card-to .prog-val {
            color: #ffffff;
        }

        .prog-separator {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fbbf24;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        /* Management Memo Line */
        .management-quote-panel {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-left: 3px solid #fbbf24;
            border-radius: 12px;
            padding: 16px 28px;
            max-width: 1040px;
            text-align: left;
        }

        .management-quote-text {
            font-size: 14.5px;
            line-height: 1.6;
            color: #cbd5e1;
            margin: 0;
            font-weight: 500;
        }

        .management-quote-text strong {
            color: #f8fafc;
            font-weight: 700;
        }

        .single-footer-panel {
            padding: 16px 36px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer-org {
            font-size: 12.5px;
            color: #94a3b8;
            font-weight: 500;
        }

        .footer-signoff {
            font-size: 13px;
            font-weight: 700;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ------------------------------------------------------------- */
        /* MULTI-RECORD GRID LAYOUT (MAX 25 PER CARD)                     */
        /* ------------------------------------------------------------- */
        .multi-wrapper {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 34px 48px 28px;
            position: relative;
            z-index: 2;
        }

        .multi-header-panel {
            padding: 18px 32px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 14px;
        }

        .multi-header-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .multi-super-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .multi-brand-text {
            font-size: 13px;
            font-weight: 800;
            color: #fbbf24;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .multi-title {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.3px;
        }

        .multi-management-line {
            margin: 2px 0 0 0;
            font-size: 13px;
            color: #94a3b8;
            line-height: 1.45;
            max-width: 1200px;
        }

        .multi-management-line strong {
            color: #e2e8f0;
        }

        /* 5-Column Grid for up to 25 records */
        .records-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            flex: 1;
            align-content: start;
            margin-bottom: 14px;
        }

        /* Responsive centered variations for chunks with <= 10 records */
        .records-grid.grid-count-5 {
            grid-template-columns: repeat(5, 1fr);
        }

        .records-grid.grid-count-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        .records-grid.grid-count-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .records-grid.grid-count-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .records-grid.grid-count-1 {
            grid-template-columns: 1fr;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Employee Record Glass Card */
        .record-card {
            border-radius: 12px;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 126px;
            transition: all 0.2s ease;
        }

        .record-card:hover {
            border-color: rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.05);
        }

        .card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 4px;
        }

        .rec-name {
            font-size: 14.5px;
            font-weight: 700;
            color: #f8fafc;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 230px;
        }

        .rec-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10.5px;
            font-weight: 700;
            color: #fbbf24;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.22);
            padding: 2px 6px;
            border-radius: 5px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .rec-dept {
            font-size: 10.5px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rec-transition {
            background: rgba(255, 255, 255, 0.025);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 5px 8px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .rec-flow-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
        }

        .flow-from {
            color: #94a3b8;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
        }

        .flow-arrow {
            color: #fbbf24;
            font-weight: 700;
            font-size: 11px;
            flex-shrink: 0;
        }

        .flow-to {
            color: #ffffff;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .flow-to-accent {
            color: #fef08a;
            font-weight: 700;
        }

        .multi-footer-panel {
            padding: 14px 30px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .multi-footer-note {
            font-size: 12.5px;
            color: #cbd5e1;
            font-weight: 500;
        }

        .multi-footer-note strong {
            color: #fbbf24;
        }

        .multi-footer-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 12px;
            color: #94a3b8;
            font-weight: 500;
        }
    </style>
</head>

<body>
    {{-- Top Screen Toolbar --}}
    <div class="toolbar no-print">
        <div class="toolbar-left">
            <div class="toolbar-title">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#fbbf24" stroke-width="2.2">
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

            <button type="button" class="btn-glass-primary" id="downloadJpgBtn" onclick="downloadCurrentCard('jpg')">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span id="downloadBtnText">Download Card (JPG)</span>
            </button>

            <button type="button" class="btn-glass-secondary" onclick="downloadCurrentCard('png')">
                PNG
            </button>

            @if($totalCards > 1)
                <button type="button" class="btn-glass-secondary" onclick="downloadAllCards()">
                    Download All ({{ $totalCards }})
                </button>
            @endif

            <button type="button" class="btn-glass-secondary" onclick="window.close()">
                Close
            </button>
        </div>
    </div>

    @if($promotions->isEmpty())
        {{-- Empty State Fallback --}}
        <div style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
            <div class="glass-panel" style="border-radius: 20px; padding: 48px; max-width: 520px; text-align: center;">
                <div style="font-size: 36px; margin-bottom: 12px; color: #fbbf24;">✦</div>
                <h2 style="font-size: 20px; font-weight: 800; color: #ffffff; margin: 0 0 10px 0;">No Promotions Selected</h2>
                <p style="font-size: 13.5px; color: #94a3b8; line-height: 1.5; margin: 0 0 24px 0;">Please select one or more promotion records from the promotions table to generate congratulation wish cards.</p>
                <button type="button" class="btn-glass-primary" onclick="window.close()" style="margin: 0 auto;">Close</button>
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
                            {{-- SINGLE PROMOTION SPOTLIGHT (CLEAN GLASSMORPHISM)               --}}
                            {{-- ============================================================== --}}
                            @php
                                $promo = $chunk->first();
                                $emp = $promo->employee;
                            @endphp
                            <div class="single-wrapper">
                                {{-- Header --}}
                                <div class="single-header-panel glass-panel">
                                    <div class="brand-cluster">
                                        <div class="brand-monogram">W</div>
                                        <div class="brand-titles">
                                            <h2>Waldo Dynasty</h2>
                                            <p>Career Advancement & Leadership</p>
                                        </div>
                                    </div>
                                    <div class="header-meta-group">
                                        <div class="glass-pill glass-pill-amber">
                                            <span>Promotion Recognition</span>
                                        </div>
                                        <div class="glass-pill">
                                            <span>{{ $generatedDate->format('F d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Main Spotlight Card --}}
                                <div class="single-spotlight-card glass-panel">
                                    <div class="spotlight-eyebrow">A Celebration of Professional Growth</div>
                                    <h1 class="spotlight-title">Congratulations on Your Promotion</h1>

                                    <div class="emp-name-headline">{{ $emp?->name ?? 'Team Member' }}</div>
                                    <div class="emp-tags-deck">
                                        <span class="glass-pill font-mono-code">ID: {{ $promo->employee_id }}</span>
                                        <span class="glass-pill">{{ $promo->toDepartment?->name ?? $promo->fromDepartment?->name ?? 'Department' }}</span>
                                        @if($promo->promotion_date)
                                            <span class="glass-pill glass-pill-amber">Effective: {{ $promo->promotion_date->format('F d, Y') }}</span>
                                        @endif
                                    </div>

                                    {{-- Role Progression Deck --}}
                                    <div class="progression-deck">
                                        <div class="prog-card prog-card-from">
                                            <div class="prog-label">Previous Designation</div>
                                            <h3 class="prog-val">{{ $promo->fromDesignation?->name ?? 'Previous Role' }}</h3>
                                        </div>

                                        <div class="prog-separator">&rarr;</div>

                                        <div class="prog-card prog-card-to">
                                            <div class="prog-label">Promoted Designation</div>
                                            <h3 class="prog-val">{{ $promo->toDesignation?->name ?? 'New Designation' }}</h3>
                                        </div>
                                    </div>

                                    {{-- Exact Management Note from Print Memo --}}
                                    <div class="management-quote-panel">
                                        <p class="management-quote-text">
                                            "<strong>The Management is pleased to congratulate you on your well-deserved promotion.</strong> We appreciate your dedication and valuable contributions to the organization, and wish you continued success in your new role."
                                        </p>
                                    </div>
                                </div>

                                {{-- Footer --}}
                                <div class="single-footer-panel glass-panel">
                                    <div class="footer-org">
                                        Waldo Dynasty Entertainment Pvt. Ltd. • Official Career Recognition
                                    </div>
                                    <div class="footer-signoff">
                                        <span>Authorized by</span>
                                        <span style="color: #fbbf24;">The Management</span>
                                    </div>
                                </div>
                            </div>

                        @else
                            {{-- ============================================================== --}}
                            {{-- MULTI-RECORD GRID DESIGN (CLEAN GLASSMORPHISM • MAX 25)        --}}
                            {{-- ============================================================== --}}
                            @php
                                $chunkCount = $chunk->count();
                                $gridClass = '';
                                if ($chunkCount <= 5) {
                                    $gridClass = 'grid-count-' . $chunkCount;
                                }
                            @endphp
                            <div class="multi-wrapper">
                                {{-- Header --}}
                                <div class="multi-header-panel glass-panel">
                                    <div class="multi-header-left">
                                        <div class="multi-super-title">
                                            <span class="multi-brand-text">Waldo Dynasty</span>
                                            <span style="color: rgba(255, 255, 255, 0.25);">•</span>
                                            <span style="font-size: 11.5px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Official Promotion Announcement</span>
                                        </div>
                                        <h1 class="multi-title">Congratulations on Your Promotion</h1>
                                        <p class="multi-management-line">
                                            <strong>The Management is pleased to congratulate the following employees on their well-deserved promotions.</strong> We appreciate your dedication and valuable contributions to the organization.
                                        </p>
                                    </div>

                                    <div>
                                        @if($totalCards > 1)
                                            <div class="glass-pill glass-pill-amber">
                                                Card {{ $cardIndex + 1 }} of {{ $totalCards }} • {{ $chunkCount }} Members
                                            </div>
                                        @else
                                            <div class="glass-pill glass-pill-amber">
                                                {{ $chunkCount }} Promoted Team Members
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- 5-Column Grid (Max 25 Items) --}}
                                <div class="records-grid {{ $gridClass }}">
                                    @foreach($chunk as $promo)
                                        <div class="record-card glass-panel">
                                            <div>
                                                <div class="card-top">
                                                    <span class="rec-name" title="{{ $promo->employee?->name ?? 'N/A' }}">
                                                        {{ $promo->employee?->name ?? 'N/A' }}
                                                    </span>
                                                    <span class="rec-code">{{ $promo->employee_id }}</span>
                                                </div>
                                                <div class="rec-dept" title="{{ $promo->toDepartment?->name ?? $promo->fromDepartment?->name ?? '-' }}">
                                                    {{ $promo->toDepartment?->name ?? $promo->fromDepartment?->name ?? '-' }}
                                                </div>
                                            </div>

                                            <div class="rec-transition">
                                                <div class="rec-flow-row">
                                                    <span class="flow-from" title="{{ $promo->fromDesignation?->name ?? '-' }}">
                                                        {{ $promo->fromDesignation?->name ?? '-' }}
                                                    </span>
                                                    <span class="flow-arrow">&rarr;</span>
                                                    <span class="flow-to flow-to-accent" title="{{ $promo->toDesignation?->name ?? '-' }}">
                                                        {{ $promo->toDesignation?->name ?? '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Footer --}}
                                <div class="multi-footer-panel glass-panel">
                                    <div class="multi-footer-note">
                                        "We wish you continued success in your new {{ Str::plural('role', $chunkCount) }}."
                                    </div>
                                    <div class="multi-footer-meta">
                                        <span>Waldo Dynasty Entertainment Pvt. Ltd.</span>
                                        <span>•</span>
                                        <span>{{ $generatedDate->format('F d, Y') }}</span>
                                    </div>
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
                    backgroundColor: '#090d16',
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
