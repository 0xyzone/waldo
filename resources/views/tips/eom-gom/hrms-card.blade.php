<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $monthName }} {{ $evaluatedYear ?? $report->year }} - EOM & GOM Recognition Card</title>
    <x-favicon />

    <!-- Google Fonts for Luxury Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- htmlToImage & html2canvas for instant 100% faithful JPG generation -->
    <script src="https://cdn.jsdelivr.net/npm/html-to-image@1.11.11/dist/html-to-image.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #080c16;
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 16px;
        }

        /* Top Action / Control Bar */
        .control-bar {
            width: 100%;
            max-width: 1200px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(14px);
            padding: 12px 20px;
            border-radius: 14px;
            margin-bottom: 24px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
            z-index: 50;
        }

        .control-title {
            font-size: 15px;
            font-weight: 700;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .control-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-gold {
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            color: #0f172a;
            box-shadow: 0 4px 14px 0 rgba(217, 119, 6, 0.39);
        }

        .btn-gold:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(217, 119, 6, 0.5);
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.16);
        }

        /* Scaled Preview Wrapper */
        .preview-wrapper {
            width: 100%;
            max-width: 1354px;
            overflow-x: auto;
            display: flex;
            justify-content: center;
            padding-bottom: 40px;
        }

        .preview-container {
            transform-origin: top center;
            transform: scale(0.48);
            width: 2708px;
            height: 1492px;
            margin-bottom: -770px;
        }

        @media (max-width: 1200px) {
            .preview-container {
                transform: scale(0.35);
                margin-bottom: -960px;
            }
        }

        @media (max-width: 800px) {
            .preview-container {
                transform: scale(0.24);
                margin-bottom: -1130px;
            }
        }

        /* -------------------------------------------------------------------------- */
        /* MASTER 2708 x 1492 CANVAS CARD (FRESH EXECUTIVE WALL OF FAME)             */
        /* -------------------------------------------------------------------------- */
        #hrms-wish-card-canvas {
            width: 2708px;
            height: 1492px;
            min-width: 2708px;
            min-height: 1492px;
            background: linear-gradient(135deg, #070c17 0%, #0c152a 50%, #060a14 100%);
            color: #ffffff;
            border: 14px solid #d4af37;
            border-radius: 8px;
            padding: 38px 50px 32px 50px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 30px 90px rgba(0, 0, 0, 0.98);
            box-sizing: border-box;
        }

        /* Fine Inset Framing */
        .inner-frame {
            position: absolute;
            inset: 14px;
            border: 1.5px solid rgba(212, 175, 55, 0.45);
            border-radius: 4px;
            pointer-events: none;
        }

        /* -------------------------------------------------------------------------- */
        /* HEADER SECTION (ELEGANT & COMPACT)                                        */
        /* -------------------------------------------------------------------------- */
        .card-header {
            text-align: center;
            position: relative;
            z-index: 5;
            padding-bottom: 14px;
            border-bottom: 1.5px solid rgba(212, 175, 55, 0.3);
        }

        .header-kicker {
            font-family: 'Cinzel', serif;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 8px;
            text-transform: uppercase;
            color: #d4af37;
            margin-bottom: 4px;
        }

        .header-title {
            font-family: 'Cinzel', serif;
            font-size: 52px;
            font-weight: 900;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #ffffff;
            line-height: 1.15;
            margin-bottom: 10px;
            text-shadow: 0 4px 16px rgba(0, 0, 0, 0.8);
        }

        .header-badge {
            display: inline-block;
            background: rgba(212, 175, 55, 0.15);
            border: 1.5px solid #d4af37;
            border-radius: 30px;
            padding: 6px 42px;
            font-family: 'Cinzel', serif;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 5px;
            color: #fef08a;
            text-transform: uppercase;
        }

        /* -------------------------------------------------------------------------- */
        /* MAIN GALLERY STAGE (5 UNIFIED FULL-HEIGHT SHOWCASE CARDS)                 */
        /* -------------------------------------------------------------------------- */
        .gallery-stage {
            display: flex;
            align-items: stretch;
            gap: 28px;
            position: relative;
            z-index: 5;
            margin-top: 18px;
            margin-bottom: 18px;
            flex: 1;
        }

        /* ZONE 1: EOM (Left 3 Cards) */
        .zone-eom {
            flex: 3;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* ZONE 2: GOM (Right 2 Cards) */
        .zone-gom {
            flex: 2;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Zone Header Bar */
        .zone-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 18px;
            border-radius: 12px;
            font-family: 'Cinzel', serif;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .zone-header-eom {
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.22) 0%, rgba(245, 158, 11, 0.05) 100%);
            border-left: 5px solid #f59e0b;
            color: #fbbf24;
            font-size: 19px;
            font-weight: 800;
        }

        .zone-header-gom {
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.22) 0%, rgba(14, 165, 233, 0.05) 100%);
            border-left: 5px solid #0ea5e9;
            color: #38bdf8;
            font-size: 19px;
            font-weight: 800;
        }

        .zone-cards-grid {
            display: flex;
            gap: 20px;
            flex: 1;
            align-items: stretch;
        }

        /* -------------------------------------------------------------------------- */
        /* INDIVIDUAL EXECUTIVE SPOTLIGHT CARD (EQUAL FULL HEIGHT)                   */
        /* -------------------------------------------------------------------------- */
        .spotlight-card {
            flex: 1;
            background: linear-gradient(180deg, rgba(22, 34, 58, 0.85) 0%, rgba(10, 16, 30, 0.95) 100%);
            border-radius: 18px;
            padding: 28px 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            position: relative;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.6);
            box-sizing: border-box;
        }

        .card-eom {
            border: 2px solid rgba(212, 175, 55, 0.5);
        }

        .card-gom {
            border: 2px solid rgba(56, 189, 248, 0.5);
        }

        /* Card Top Header */
        .card-top {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .pill-dept {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 4px 12px;
            border-radius: 20px;
            max-width: 65%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pill-dept-eom {
            color: #fde047;
            background: rgba(253, 224, 71, 0.12);
            border: 1px solid rgba(253, 224, 71, 0.3);
        }

        .pill-dept-gom {
            color: #7dd3fc;
            background: rgba(125, 211, 252, 0.12);
            border: 1px solid rgba(125, 211, 252, 0.3);
        }

        .badge-winner {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .badge-winner-eom {
            background: linear-gradient(135deg, #d97706, #b45309);
            color: #ffffff;
            border: 1px solid #fde68a;
        }

        .badge-winner-gom {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: #ffffff;
            border: 1px solid #bae6fd;
        }

        /* Center Monogram Medallion */
        .card-body {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            margin: auto 0;
            padding: 20px 0;
        }

        .medallion {
            width: 124px;
            height: 124px;
            min-width: 124px;
            min-height: 124px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cinzel', serif;
            font-size: 46px;
            font-weight: 900;
            color: #0f172a;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.6);
        }

        .medallion-eom {
            background: linear-gradient(135deg, #fef08a 0%, #eab308 50%, #ca8a04 100%);
            border: 4px solid #ffffff;
        }

        .medallion-gom {
            background: linear-gradient(135deg, #e0f2fe 0%, #38bdf8 50%, #0284c7 100%);
            border: 4px solid #ffffff;
        }

        .winner-title-wrap {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .winner-fullname {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 34px;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.25;
            letter-spacing: -0.3px;
            max-width: 100%;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.8);
        }

        .winner-designation {
            font-size: 19px;
            font-weight: 600;
            color: #cbd5e1;
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .winner-code-tag {
            display: inline-block;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 3px 12px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 15px;
            font-weight: 700;
            color: #e2e8f0;
            margin-top: 4px;
        }

        /* Bottom Section of Card */
        .card-bottom {
            width: 100%;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 14px;
        }

        .remark-quote {
            font-size: 14.5px;
            font-style: italic;
            color: #94a3b8;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ribbon-honor {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 5px 12px;
            border-radius: 6px;
            display: inline-block;
        }

        .ribbon-honor-eom {
            color: #fde68a;
            background: rgba(245, 158, 11, 0.12);
        }

        .ribbon-honor-gom {
            color: #bae6fd;
            background: rgba(14, 165, 233, 0.12);
        }

        /* Unassigned Card State */
        .card-unassigned {
            border: 2px dashed rgba(255, 255, 255, 0.2);
            background: rgba(15, 23, 42, 0.4);
            justify-content: center;
        }

        .unassigned-label {
            font-family: 'Cinzel', serif;
            font-size: 22px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 6px;
        }

        .unassigned-sub {
            font-size: 14px;
            color: #475569;
            font-weight: 500;
        }

        /* -------------------------------------------------------------------------- */
        /* FOOTER (CLEAN LUXURY BASELINE)                                            */
        /* -------------------------------------------------------------------------- */
        .card-footer {
            border-top: 1.5px solid rgba(212, 175, 55, 0.3);
            padding-top: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #94a3b8;
            font-size: 15px;
            position: relative;
            z-index: 5;
        }

        .footer-left {
            font-family: 'Cinzel', serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #d4af37;
            text-transform: uppercase;
        }

        .footer-center {
            font-size: 15px;
            color: #cbd5e1;
            letter-spacing: 1px;
        }

        .footer-right {
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
        }

        @media print {
            body {
                background: #000;
                padding: 0;
            }
            .control-bar {
                display: none !important;
            }
            .preview-wrapper {
                max-width: none;
                padding: 0;
            }
            .preview-container {
                transform: scale(0.38) !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action / Control Bar -->
    <div class="control-bar">
        <div class="control-title">
            <span style="font-size: 20px;">🏆</span>
            <span><strong>{{ $monthName }} {{ $evaluatedYear ?? $report->year }}</strong> Recognition Wish Card Preview (2708 × 1492 HD)</span>
        </div>

        <div class="control-actions">
            <button id="downloadBtn" onclick="downloadWishCard()" class="btn btn-gold">
                <span>⬇️ Download Card (JPG)</span>
            </button>

            <button onclick="window.print()" class="btn btn-outline">
                <span>🖨️ Print Card</span>
            </button>

            <button onclick="window.close()" class="btn btn-outline">
                <span>Close</span>
            </button>
        </div>
    </div>

    <!-- Scaled Preview Wrapper for Screen -->
    <div class="preview-wrapper">
        <div class="preview-container">

            <!-- MASTER 2708 x 1492 CANVAS ELEMENT -->
            <div id="hrms-wish-card-canvas">

                <!-- Inner Framing Border -->
                <div class="inner-frame"></div>

                <!-- Header Banner -->
                <div class="card-header">
                    <div class="header-kicker">★ Waldo Dynasty Resort & Casino • Monthly Recognition Honors ★</div>
                    <h1 class="header-title">Employee & Grooming of the Month</h1>
                    <div class="header-badge">
                        ✦ Honorees of {{ strtoupper($monthName) }} {{ $evaluatedYear ?? $report->year }} ✦
                    </div>
                </div>

                <!-- Main Gallery Stage: Unified 5 Full-Height Spotlight Cards -->
                <div class="gallery-stage">

                    <!-- ZONE 1: EMPLOYEES OF THE MONTH (3 Cards) -->
                    <div class="zone-eom">
                        <div class="zone-header zone-header-eom">
                            <span>🏆 Employees of the Month</span>
                            <span style="font-size: 13px; font-weight: 700; letter-spacing: 0;">3 Honorees</span>
                        </div>

                        <div class="zone-cards-grid">
                            @foreach($eomWinners as $winner)
                                @if($winner['employee'])
                                    <div class="spotlight-card card-eom">
                                        <div class="card-top">
                                            <span class="pill-dept pill-dept-eom" title="{{ $winner['department'] }}">{{ $winner['department'] }}</span>
                                            <span class="badge-winner badge-winner-eom">🏆 {{ $winner['badge'] }}</span>
                                        </div>

                                        <div class="card-body">
                                            <div class="medallion medallion-eom">
                                                {{ strtoupper(substr($winner['employee']->name, 0, 2)) }}
                                            </div>

                                            <div class="winner-title-wrap">
                                                <div class="winner-fullname" title="{{ $winner['employee']->name }}">
                                                    {{ $winner['employee']->name }}
                                                </div>

                                                <div class="winner-designation" title="{{ $winner['employee']->designation?->name ?: 'Staff' }}">
                                                    {{ $winner['employee']->designation?->name ?: 'Staff' }}
                                                </div>

                                                <div>
                                                    <span class="winner-code-tag">{{ $winner['employee']->employee_code }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-bottom">
                                            @if(!empty($winner['remarks']))
                                                <div class="remark-quote">
                                                    “{{ $winner['remarks'] }}”
                                                </div>
                                            @else
                                                <div class="ribbon-honor ribbon-honor-eom">
                                                    ✦ Outstanding Performance ✦
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="spotlight-card card-unassigned">
                                        <div class="unassigned-label">Slot Unassigned</div>
                                        <div class="unassigned-sub">{{ $winner['entry_label'] }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- ZONE 2: GROOMING OF THE MONTH (2 Cards) -->
                    <div class="zone-gom">
                        <div class="zone-header zone-header-gom">
                            <span>✨ Grooming of the Month</span>
                            <span style="font-size: 13px; font-weight: 700; letter-spacing: 0;">2 Honorees</span>
                        </div>

                        <div class="zone-cards-grid">
                            @foreach($gomWinners as $winner)
                                @if($winner['employee'])
                                    <div class="spotlight-card card-gom">
                                        <div class="card-top">
                                            <span class="pill-dept pill-dept-gom" title="{{ $winner['department'] }}">{{ $winner['department'] }}</span>
                                            <span class="badge-winner badge-winner-gom">✨ {{ $winner['badge'] }}</span>
                                        </div>

                                        <div class="card-body">
                                            <div class="medallion medallion-gom">
                                                {{ strtoupper(substr($winner['employee']->name, 0, 2)) }}
                                            </div>

                                            <div class="winner-title-wrap">
                                                <div class="winner-fullname" title="{{ $winner['employee']->name }}">
                                                    {{ $winner['employee']->name }}
                                                </div>

                                                <div class="winner-designation" title="{{ $winner['employee']->designation?->name ?: 'Staff' }}">
                                                    {{ $winner['employee']->designation?->name ?: 'Staff' }}
                                                </div>

                                                <div>
                                                    <span class="winner-code-tag">{{ $winner['employee']->employee_code }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-bottom">
                                            @if(!empty($winner['remarks']))
                                                <div class="remark-quote">
                                                    “{{ $winner['remarks'] }}”
                                                </div>
                                            @else
                                                <div class="ribbon-honor ribbon-honor-gom">
                                                    ✦ Immaculate Grooming ✦
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="spotlight-card card-unassigned">
                                        <div class="unassigned-label">Slot Unassigned</div>
                                        <div class="unassigned-sub">{{ $winner['entry_label'] }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                </div>

                <!-- Clean Luxury Footer -->
                <div class="card-footer">
                    <div class="footer-left">
                        ★ Human Resources Department ★
                    </div>

                    <div class="footer-center">
                        Waldo Dynasty Resort & Casino • The Gold Standard of Hospitality
                    </div>

                    <div class="footer-right">
                        Annual Cycle {{ $report->year }}
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- JPG Export Logic (Guarantees 100% Identical Render) -->
    <script>
        async function downloadWishCard() {
            const btn = document.getElementById('downloadBtn');
            const origText = btn.innerHTML;
            btn.innerHTML = '<span>⏳ Generating JPG (2708 × 1492)...</span>';
            btn.disabled = true;

            const card = document.getElementById('hrms-wish-card-canvas');
            if (!card) {
                alert('Card element missing');
                btn.innerHTML = origText;
                btn.disabled = false;
                return;
            }

            // Ensure fonts are loaded before rasterization
            if (document.fonts && document.fonts.ready) {
                try { await document.fonts.ready; } catch(e) {}
            }
            await new Promise(r => setTimeout(r, 100));

            // CRITICAL FIX: The preview container has CSS transform: scale(...).
            // If captured while scaled, html2canvas/htmlToImage gets scaled down and cropped!
            // We temporarily reset scale to 1 during the capture!
            const container = card.parentElement;
            const prevTransform = container.style.transform;
            const prevMargin = container.style.marginBottom;
            const prevScrollY = window.scrollY;

            container.style.transform = 'none';
            container.style.marginBottom = '0';
            window.scrollTo(0, 0);

            await new Promise(r => setTimeout(r, 80));

            try {
                let dataUrl = null;

                // 1. Try htmlToImage (Exact SVG foreignObject rendering with 100% browser CSS match)
                if (window.htmlToImage && typeof window.htmlToImage.toJpeg === 'function') {
                    try {
                        dataUrl = await window.htmlToImage.toJpeg(card, {
                            width: 2708,
                            height: 1492,
                            canvasWidth: 2708,
                            canvasHeight: 1492,
                            quality: 0.96,
                            pixelRatio: 1,
                            cacheBust: true,
                            style: {
                                transform: 'none',
                                margin: '0',
                                width: '2708px',
                                height: '1492px',
                                minWidth: '2708px',
                                minHeight: '1492px'
                            }
                        });
                    } catch (e) {
                        console.warn('htmlToImage failed, trying html2canvas:', e);
                    }
                }

                // 2. Fallback to html2canvas if htmlToImage was not available or errored
                if (!dataUrl && window.html2canvas) {
                    const canvas = await window.html2canvas(card, {
                        width: 2708,
                        height: 1492,
                        scale: 1,
                        useCORS: true,
                        allowTaint: true,
                        backgroundColor: '#070c17',
                        logging: false
                    });
                    dataUrl = canvas.toDataURL('image/jpeg', 0.95);
                }

                if (dataUrl) {
                    const fileName = `EOM_GOM_Wish_Card_{{ $monthName }}_{{ $evaluatedYear ?? $report->year }}.jpg`;
                    const link = document.createElement('a');
                    link.download = fileName;
                    link.href = dataUrl;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert('Could not render image. Please try again.');
                }
            } catch (err) {
                console.error('Export error:', err);
                alert('Failed to generate JPG: ' + (err.message || err));
            } finally {
                // Restore preview scaling and scroll position
                container.style.transform = prevTransform;
                container.style.marginBottom = prevMargin;
                window.scrollTo(0, prevScrollY);
                btn.innerHTML = origText;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
