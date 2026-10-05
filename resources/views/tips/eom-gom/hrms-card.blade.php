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
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

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
            background-color: #0b1120;
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 16px;
        }

        /* Top Control Bar */
        .control-bar {
            width: 100%;
            max-width: 1200px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
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
        /* MASTER 2708 x 1492 CANVAS CARD (CLEAN LUXURY DESIGN)                      */
        /* -------------------------------------------------------------------------- */
        #hrms-wish-card-canvas {
            width: 2708px;
            height: 1492px;
            min-width: 2708px;
            min-height: 1492px;
            background: radial-gradient(ellipse at 50% 20%, #162447 0%, #0d152a 55%, #060913 100%);
            color: #ffffff;
            border: 14px solid #d4af37;
            border-radius: 8px;
            padding: 44px 64px 36px 64px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 30px 80px rgba(0,0,0,0.95);
            box-sizing: border-box;
        }

        /* Fine Interior Framing Line */
        .inner-frame {
            position: absolute;
            inset: 14px;
            border: 1.5px solid rgba(212, 175, 55, 0.4);
            border-radius: 4px;
            pointer-events: none;
        }

        /* Card Header */
        .card-header {
            text-align: center;
            position: relative;
            z-index: 5;
            padding-top: 6px;
        }

        .super-title {
            font-family: 'Cinzel', serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 8px;
            text-transform: uppercase;
            color: #d4af37;
            margin-bottom: 6px;
        }

        .main-card-title {
            font-family: 'Cinzel', serif;
            font-size: 56px;
            font-weight: 900;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #ffffff;
            line-height: 1.15;
            margin-bottom: 12px;
        }

        .month-ribbon {
            display: inline-block;
            background: rgba(212, 175, 55, 0.12);
            border: 1.5px solid #d4af37;
            border-radius: 30px;
            padding: 7px 44px;
            font-family: 'Cinzel', serif;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 5px;
            color: #fef08a;
            text-transform: uppercase;
        }

        /* Showcase Area: 2 Clean Rows (Row 1: 3 EOM Cards; Row 2: 2 GOM Cards) */
        .showcase-area {
            position: relative;
            z-index: 5;
            display: flex;
            flex-direction: column;
            gap: 24px;
            margin-top: 24px;
            margin-bottom: 20px;
            flex: 1;
        }

        .section-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .section-heading {
            font-family: 'Cinzel', serif;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 3px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .heading-eom {
            color: #fbbf24;
        }

        .heading-gom {
            color: #38bdf8;
        }

        .cards-row-eom {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .cards-row-gom {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        /* Clean Luxury Winner Card */
        .winner-card {
            background: rgba(15, 23, 42, 0.75);
            border: 2px solid rgba(212, 175, 55, 0.4);
            border-radius: 18px;
            padding: 22px 28px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            min-height: 290px;
        }

        .winner-card-gom {
            border-color: rgba(56, 189, 248, 0.4);
        }

        /* Top Bar inside Card */
        .card-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .dept-pill {
            font-size: 14px;
            font-weight: 700;
            color: #fde047;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: rgba(253, 224, 71, 0.1);
            padding: 4px 14px;
            border-radius: 20px;
            border: 1px solid rgba(253, 224, 71, 0.25);
        }

        .dept-pill-gom {
            color: #7dd3fc;
            background: rgba(125, 211, 252, 0.1);
            border-color: rgba(125, 211, 252, 0.25);
        }

        .award-badge {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 8px;
        }

        .badge-eom {
            background: linear-gradient(135deg, #d97706, #b45309);
            color: #ffffff;
            border: 1px solid #fde68a;
        }

        .badge-gom {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: #ffffff;
            border: 1px solid #bae6fd;
        }

        /* Middle Content: Avatar + Name + Designation */
        .card-content {
            display: flex;
            align-items: center;
            gap: 22px;
            flex: 1;
        }

        .avatar-circle {
            width: 84px;
            height: 84px;
            min-width: 84px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cinzel', serif;
            font-size: 32px;
            font-weight: 900;
            color: #0f172a;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.5);
        }

        .avatar-eom {
            background: linear-gradient(135deg, #fef08a 0%, #eab308 50%, #ca8a04 100%);
            border: 3px solid #ffffff;
        }

        .avatar-gom {
            background: linear-gradient(135deg, #e0f2fe 0%, #38bdf8 50%, #0284c7 100%);
            border: 3px solid #ffffff;
        }

        .employee-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1;
            min-width: 0;
        }

        .winner-name {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
            letter-spacing: -0.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .winner-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
            color: #94a3b8;
            font-weight: 600;
        }

        .emp-code-pill {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #e2e8f0;
            font-family: monospace;
            font-size: 14px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 6px;
            flex-shrink: 0;
        }

        .designation-text {
            color: #cbd5e1;
            font-size: 17px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Clean remarks note (if provided) */
        .remarks-line {
            font-size: 14px;
            font-style: italic;
            color: #94a3b8;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 8px;
            margin-top: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Empty Slot Presentation */
        .empty-slot {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 150px;
            color: #64748b;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .empty-slot-sub {
            font-size: 13px;
            color: #475569;
            margin-top: 4px;
            text-transform: none;
            letter-spacing: normal;
        }

        /* Card Footer */
        .card-footer {
            border-top: 1.5px solid rgba(212, 175, 55, 0.3);
            padding-top: 14px;
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
                    <div class="super-title">★ Waldo Dynasty Resort & Casino • Monthly Recognition ★</div>
                    <h1 class="main-card-title">Employee & Grooming of the Month</h1>
                    <div class="month-ribbon">
                        ✦ Honorees of {{ strtoupper($monthName) }} {{ $evaluatedYear ?? $report->year }} ✦
                    </div>
                </div>

                <!-- Showcase Grid: EOM (Row 1) & GOM (Row 2) -->
                <div class="showcase-area">

                    <!-- SECTION 1: EMPLOYEES OF THE MONTH (3 Winners) -->
                    <div class="section-group">
                        <div class="section-heading heading-eom">
                            <span>🏆 Employees of the Month</span>
                            <span style="font-size: 14px; color: #fde68a; font-weight: 600; text-transform: none; letter-spacing: 0;">(3 Honorees)</span>
                        </div>

                        <div class="cards-row-eom">
                            @foreach($eomWinners as $winner)
                                <div class="winner-card">
                                    <div class="card-top-bar">
                                        <span class="dept-pill">{{ $winner['department'] }}</span>
                                        <span class="award-badge badge-eom">🏆 {{ $winner['badge'] }}</span>
                                    </div>

                                    @if($winner['employee'])
                                        <div class="card-content">
                                            <div class="avatar-circle avatar-eom">
                                                {{ strtoupper(substr($winner['employee']->name, 0, 2)) }}
                                            </div>

                                            <div class="employee-info">
                                                <div class="winner-name" title="{{ $winner['employee']->name }}">
                                                    {{ $winner['employee']->name }}
                                                </div>

                                                <div class="winner-meta">
                                                    <span class="emp-code-pill">{{ $winner['employee']->employee_code }}</span>
                                                    <span class="designation-text">{{ $winner['employee']->designation?->name ?: 'Staff' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        @if(!empty($winner['remarks']))
                                            <div class="remarks-line">
                                                “{{ $winner['remarks'] }}”
                                            </div>
                                        @endif
                                    @else
                                        <div class="empty-slot">
                                            <span>Slot Unassigned</span>
                                            <span class="empty-slot-sub">{{ $winner['entry_label'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- SECTION 2: GROOMING OF THE MONTH (2 Winners) -->
                    <div class="section-group">
                        <div class="section-heading heading-gom">
                            <span>✨ Grooming of the Month</span>
                            <span style="font-size: 14px; color: #bae6fd; font-weight: 600; text-transform: none; letter-spacing: 0;">(2 Honorees)</span>
                        </div>

                        <div class="cards-row-gom">
                            @foreach($gomWinners as $winner)
                                <div class="winner-card winner-card-gom">
                                    <div class="card-top-bar">
                                        <span class="dept-pill dept-pill-gom">{{ $winner['department'] }}</span>
                                        <span class="award-badge badge-gom">✨ {{ $winner['badge'] }}</span>
                                    </div>

                                    @if($winner['employee'])
                                        <div class="card-content">
                                            <div class="avatar-circle avatar-gom">
                                                {{ strtoupper(substr($winner['employee']->name, 0, 2)) }}
                                            </div>

                                            <div class="employee-info">
                                                <div class="winner-name" title="{{ $winner['employee']->name }}">
                                                    {{ $winner['employee']->name }}
                                                </div>

                                                <div class="winner-meta">
                                                    <span class="emp-code-pill">{{ $winner['employee']->employee_code }}</span>
                                                    <span class="designation-text">{{ $winner['employee']->designation?->name ?: 'Staff' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        @if(!empty($winner['remarks']))
                                            <div class="remarks-line">
                                                “{{ $winner['remarks'] }}”
                                            </div>
                                        @endif
                                    @else
                                        <div class="empty-slot">
                                            <span>Slot Unassigned</span>
                                            <span class="empty-slot-sub">{{ $winner['entry_label'] }}</span>
                                        </div>
                                    @endif
                                </div>
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
                        backgroundColor: '#060913',
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
