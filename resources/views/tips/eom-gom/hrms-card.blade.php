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
            background-color: #070b14;
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
        /* MASTER 2708 x 1492 CANVAS CARD (CLEAN HORIZONTAL TIERS)                   */
        /* -------------------------------------------------------------------------- */
        #hrms-wish-card-canvas {
            width: 2708px;
            height: 1492px;
            min-width: 2708px;
            min-height: 1492px;
            background: radial-gradient(circle at 50% 20%, #131e36 0%, #090e1c 55%, #04070f 100%);
            color: #ffffff;
            border: 14px solid #d4af37;
            border-radius: 12px;
            padding: 44px 56px 36px 56px;
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
            border-radius: 6px;
            pointer-events: none;
        }

        /* -------------------------------------------------------------------------- */
        /* HEADER SECTION                                                             */
        /* -------------------------------------------------------------------------- */
        .card-header {
            text-align: center;
            position: relative;
            z-index: 5;
            padding-bottom: 12px;
            border-bottom: 1.5px solid rgba(212, 175, 55, 0.25);
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
            font-size: 54px;
            font-weight: 900;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #ffffff;
            line-height: 1.15;
            margin-bottom: 8px;
            text-shadow: 0 4px 16px rgba(0, 0, 0, 0.8);
        }

        .header-badge {
            display: inline-block;
            background: rgba(212, 175, 55, 0.15);
            border: 1.5px solid #d4af37;
            border-radius: 30px;
            padding: 6px 44px;
            font-family: 'Cinzel', serif;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 5px;
            color: #fef08a;
            text-transform: uppercase;
        }

        /* -------------------------------------------------------------------------- */
        /* MAIN BODY: 2 SPACIOUS HORIZONTAL TIERS                                    */
        /* -------------------------------------------------------------------------- */
        .main-showcase {
            display: flex;
            flex-direction: column;
            gap: 36px;
            position: relative;
            z-index: 5;
            margin-top: 16px;
            margin-bottom: 16px;
            flex: 1;
            justify-content: center;
        }

        /* Tier 1: EOM Row (3 Cards across) */
        .tier-section {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .tier-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 24px;
            border-radius: 12px;
            font-family: 'Cinzel', serif;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 2.5px;
            text-transform: uppercase;
        }

        .tier-header-eom {
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.22) 0%, rgba(245, 158, 11, 0.04) 100%);
            border-left: 6px solid #f59e0b;
            color: #fbbf24;
        }

        .tier-header-gom {
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.22) 0%, rgba(14, 165, 233, 0.04) 100%);
            border-left: 6px solid #0ea5e9;
            color: #38bdf8;
        }

        .tier-grid-eom {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .tier-grid-gom {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 36px;
            max-width: 1800px;
            margin: 0 auto;
            width: 100%;
        }

        /* -------------------------------------------------------------------------- */
        /* LUXURY HORIZONTAL CARD (4 EOM ACROSS + 2 GOM BALANCED)                    */
        /* -------------------------------------------------------------------------- */
        .award-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.85) 0%, rgba(15, 23, 42, 0.98) 100%);
            border-radius: 24px;
            padding: 30px 24px;
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 22px;
            position: relative;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6);
            min-height: 350px;
            box-sizing: border-box;
        }

        .card-eom {
            border: 2.5px solid rgba(212, 175, 55, 0.5);
        }

        .card-gom {
            border: 2.5px solid rgba(56, 189, 248, 0.5);
        }

        /* Avatar Monogram Column */
        .avatar-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .avatar-medallion {
            width: 140px;
            height: 140px;
            min-width: 140px;
            min-height: 140px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cinzel', serif;
            font-size: 52px;
            font-weight: 900;
            color: #0f172a;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.65);
        }

        .avatar-eom {
            background: linear-gradient(135deg, #fef08a 0%, #eab308 50%, #ca8a04 100%);
            border: 5px solid #ffffff;
        }

        .avatar-gom {
            background: linear-gradient(135deg, #e0f2fe 0%, #38bdf8 50%, #0284c7 100%);
            border: 5px solid #ffffff;
        }

        /* Card Content Column */
        .card-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }

        /* Top Meta Row (Badges) */
        .card-meta-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 4px;
        }

        .dept-tag {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 6px 14px;
            border-radius: 20px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 60%;
        }

        .dept-tag-eom {
            color: #fde047;
            background: rgba(253, 224, 71, 0.16);
            border: 2px solid rgba(253, 224, 71, 0.4);
        }

        .dept-tag-gom {
            color: #7dd3fc;
            background: rgba(125, 211, 252, 0.16);
            border: 2px solid rgba(125, 211, 252, 0.4);
        }

        .award-badge-pill {
            font-size: 17px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 7px 16px;
            border-radius: 10px;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
        }

        .award-badge-eom {
            background: linear-gradient(135deg, #f59e0b, #b45309);
            color: #ffffff;
            border: 2px solid #fde68a;
        }

        .award-badge-gom {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: #ffffff;
            border: 2px solid #bae6fd;
        }

        /* Honoree Name */
        .honoree-name {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 38px;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.15;
            letter-spacing: -0.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-shadow: 0 3px 12px rgba(0, 0, 0, 0.85);
        }

        /* Subtitle: Code + Designation */
        .honoree-sub {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 19px;
            color: #cbd5e1;
            font-weight: 600;
        }

        .code-pill {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.28);
            color: #f8fafc;
            font-family: monospace;
            font-size: 15px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .designation-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #e2e8f0;
        }

        /* Remarks Note (if provided) */
        .remarks-tag {
            font-size: 15px;
            font-style: italic;
            color: #94a3b8;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 8px;
            margin-top: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Unassigned Card Placeholder */
        .card-unassigned {
            border: 2px dashed rgba(255, 255, 255, 0.2);
            background: rgba(15, 23, 42, 0.4);
            justify-content: center;
            text-align: center;
        }

        .unassigned-title {
            font-family: 'Cinzel', serif;
            font-size: 20px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .unassigned-desc {
            font-size: 14px;
            color: #475569;
            margin-top: 4px;
        }

        /* -------------------------------------------------------------------------- */
        /* FOOTER (CLEAN LUXURY BASELINE)                                            */
        /* -------------------------------------------------------------------------- */
        .card-footer {
            border-top: 1.5px solid rgba(212, 175, 55, 0.25);
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

                <!-- Main Showcase: 2 Balanced Horizontal Tiers -->
                <div class="main-showcase">

                    <!-- TIER 1: EMPLOYEES OF THE MONTH (4 Winners Across) -->
                    <div class="tier-section">
                        <div class="tier-header tier-header-eom">
                            <span>🏆 Employees of the Month</span>
                            <span style="font-size: 13px; font-weight: 700; letter-spacing: 0;">4 Honorees</span>
                        </div>

                        <div class="tier-grid-eom">
                            @foreach($eomWinners as $winner)
                                @if($winner['employee'])
                                    <div class="award-card card-eom">
                                        <div class="avatar-wrap">
                                            <div class="avatar-medallion avatar-eom">
                                                {{ strtoupper(substr($winner['employee']->name, 0, 2)) }}
                                            </div>
                                        </div>

                                        <div class="card-info">
                                            <div class="card-meta-top">
                                                <span class="dept-tag dept-tag-eom" title="{{ $winner['department'] }}">{{ $winner['department'] }}</span>
                                                <span class="award-badge-pill award-badge-eom">🏆 {{ $winner['badge'] }}</span>
                                            </div>

                                            <div class="honoree-name" title="{{ $winner['employee']->name }}">
                                                {{ $winner['employee']->name }}
                                            </div>

                                            <div class="honoree-sub">
                                                <span class="code-pill">{{ $winner['employee']->employee_code }}</span>
                                                <span class="designation-text">{{ $winner['employee']->designation?->name ?: 'Staff' }}</span>
                                            </div>

                                            @if(!empty($winner['remarks']))
                                                <div class="remarks-tag">
                                                    “{{ $winner['remarks'] }}”
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="award-card card-unassigned">
                                        <div>
                                            <div class="unassigned-title">Slot Unassigned</div>
                                            <div class="unassigned-desc">{{ $winner['entry_label'] }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- TIER 2: GROOMING OF THE MONTH (2 Winners Centered) -->
                    <div class="tier-section">
                        <div class="tier-header tier-header-gom" style="max-width: 2200px; margin: 0 auto; width: 100%;">
                            <span>✨ Grooming of the Month</span>
                            <span style="font-size: 13px; font-weight: 700; letter-spacing: 0;">2 Honorees</span>
                        </div>

                        <div class="tier-grid-gom">
                            @foreach($gomWinners as $winner)
                                @if($winner['employee'])
                                    <div class="award-card card-gom">
                                        <div class="avatar-wrap">
                                            <div class="avatar-medallion avatar-gom">
                                                {{ strtoupper(substr($winner['employee']->name, 0, 2)) }}
                                            </div>
                                        </div>

                                        <div class="card-info">
                                            <div class="card-meta-top">
                                                <span class="dept-tag dept-tag-gom" title="{{ $winner['department'] }}">{{ $winner['department'] }}</span>
                                                <span class="award-badge-pill award-badge-gom">✨ {{ $winner['badge'] }}</span>
                                            </div>

                                            <div class="honoree-name" title="{{ $winner['employee']->name }}">
                                                {{ $winner['employee']->name }}
                                            </div>

                                            <div class="honoree-sub">
                                                <span class="code-pill">{{ $winner['employee']->employee_code }}</span>
                                                <span class="designation-text">{{ $winner['employee']->designation?->name ?: 'Staff' }}</span>
                                            </div>

                                            @if(!empty($winner['remarks']))
                                                <div class="remarks-tag">
                                                    “{{ $winner['remarks'] }}”
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="award-card card-unassigned">
                                        <div>
                                            <div class="unassigned-title">Slot Unassigned</div>
                                            <div class="unassigned-desc">{{ $winner['entry_label'] }}</div>
                                        </div>
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
                        backgroundColor: '#04070f',
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
