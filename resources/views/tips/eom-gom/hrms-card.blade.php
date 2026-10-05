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
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- html2canvas for instant JPG generation -->
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
            background: rgba(15, 23, 42, 0.85);
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
            /* Scale 0.49 to fit desktop screens nicely */
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

        /* The 2708 x 1492 Master Canvas Card */
        #hrms-wish-card-canvas {
            width: 2708px;
            height: 1492px;
            min-width: 2708px;
            min-height: 1492px;
            background: radial-gradient(circle at 50% 10%, #152245 0%, #0a1024 45%, #050814 100%);
            color: #ffffff;
            border: 20px solid #d4af37;
            padding: 50px 70px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 25px 60px rgba(0,0,0,0.9);
            box-sizing: border-box;
        }

        /* Decorative Inner Borders & Accents */
        .inner-frame {
            position: absolute;
            inset: 16px;
            border: 3px solid rgba(212, 175, 55, 0.45);
            pointer-events: none;
            border-radius: 8px;
        }

        .inner-frame-thin {
            position: absolute;
            inset: 24px;
            border: 1px dashed rgba(212, 175, 55, 0.3);
            pointer-events: none;
        }

        /* Corner Gold Ornaments */
        .ornament-tl { position: absolute; top: 32px; left: 32px; width: 90px; height: 90px; border-top: 5px solid #d4af37; border-left: 5px solid #d4af37; }
        .ornament-tr { position: absolute; top: 32px; right: 32px; width: 90px; height: 90px; border-top: 5px solid #d4af37; border-right: 5px solid #d4af37; }
        .ornament-bl { position: absolute; bottom: 32px; left: 32px; width: 90px; height: 90px; border-bottom: 5px solid #d4af37; border-left: 5px solid #d4af37; }
        .ornament-br { position: absolute; bottom: 32px; right: 32px; width: 90px; height: 90px; border-bottom: 5px solid #d4af37; border-right: 5px solid #d4af37; }

        /* Card Header */
        .card-header {
            text-align: center;
            position: relative;
            z-index: 5;
            padding-top: 10px;
        }

        .super-title {
            font-family: 'Cinzel', serif;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 8px;
            text-transform: uppercase;
            color: #d4af37;
            text-shadow: 0 2px 8px rgba(212, 175, 55, 0.4);
            margin-bottom: 6px;
        }

        .main-card-title {
            font-family: 'Cinzel', serif;
            font-size: 64px;
            font-weight: 900;
            letter-spacing: 4px;
            text-transform: uppercase;
            background: linear-gradient(180deg, #ffffff 20%, #fef3c7 60%, #fbbf24 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 8px 30px rgba(0, 0, 0, 0.7);
            line-height: 1.15;
            margin-bottom: 12px;
        }

        .month-ribbon {
            display: inline-block;
            background: linear-gradient(90deg, transparent 0%, rgba(212, 175, 55, 0.25) 20%, rgba(212, 175, 55, 0.3) 50%, rgba(212, 175, 55, 0.25) 80%, transparent 100%);
            border-top: 2px solid #d4af37;
            border-bottom: 2px solid #d4af37;
            padding: 8px 60px;
            font-family: 'Cinzel', serif;
            font-size: 30px;
            font-weight: 800;
            letter-spacing: 6px;
            color: #fef08a;
            text-transform: uppercase;
        }

        /* Winners Grid */
        .showcase-area {
            position: relative;
            z-index: 5;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 40px;
            margin-top: 30px;
            margin-bottom: 25px;
            flex: 1;
        }

        .category-box {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .category-heading {
            font-family: 'Cinzel', serif;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 3px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 8px;
            border-bottom: 2px solid rgba(212, 175, 55, 0.4);
        }

        .heading-eom {
            color: #fbbf24;
        }

        .heading-gom {
            color: #38bdf8;
            border-bottom-color: rgba(56, 189, 248, 0.4);
        }

        .cards-row-eom {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            flex: 1;
        }

        .cards-row-gom {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 22px;
            flex: 1;
        }

        /* Single Winner Card */
        .winner-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.08) 0%, rgba(15, 23, 42, 0.7) 100%);
            border: 2px solid rgba(212, 175, 55, 0.4);
            border-radius: 20px;
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
        }

        .winner-card-gom {
            border-color: rgba(56, 189, 248, 0.4);
        }

        .badge-tag {
            position: absolute;
            top: -14px;
            padding: 5px 18px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }

        .tag-eom {
            background: linear-gradient(135deg, #f59e0b, #b45309);
            color: #ffffff;
            border: 1px solid #fde68a;
        }

        .tag-gom {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: #ffffff;
            border: 1px solid #bae6fd;
        }

        /* Avatar Monogram */
        .avatar-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-top: 14px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cinzel', serif;
            font-size: 38px;
            font-weight: 900;
            color: #0f172a;
            position: relative;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
        }

        .avatar-eom {
            background: linear-gradient(135deg, #fef08a 0%, #eab308 50%, #ca8a04 100%);
            border: 4px solid #fff;
        }

        .avatar-gom {
            background: linear-gradient(135deg, #e0f2fe 0%, #38bdf8 50%, #0284c7 100%);
            border: 4px solid #fff;
        }

        .winner-name {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 25px;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.25;
            margin-bottom: 6px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.8);
            min-height: 62px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .code-pill {
            display: inline-block;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 3px 12px;
            border-radius: 12px;
            font-family: monospace;
            font-size: 15px;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 10px;
        }

        .department-label {
            font-size: 16px;
            font-weight: 800;
            color: #fde047;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .designation-label {
            font-size: 15px;
            font-weight: 500;
            color: #cbd5e1;
            margin-bottom: 12px;
        }

        .remarks-quote {
            margin-top: auto;
            padding: 8px 12px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.25);
            border-left: 3px solid #d4af37;
            font-size: 13.5px;
            font-style: italic;
            color: #e2e8f0;
            line-height: 1.35;
            width: 100%;
        }

        .remarks-quote-gom {
            border-left-color: #38bdf8;
        }

        /* Unassigned placeholder */
        .empty-slot {
            margin-top: 40px;
            font-size: 18px;
            color: rgba(255, 255, 255, 0.4);
            font-style: italic;
        }

        /* Card Footer */
        .card-footer {
            position: relative;
            z-index: 5;
            text-align: center;
            padding-top: 15px;
            border-top: 1px solid rgba(212, 175, 55, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer-note {
            font-size: 18px;
            font-weight: 600;
            color: #e2e8f0;
            letter-spacing: 0.5px;
        }

        .footer-gold {
            color: #fbbf24;
            font-weight: 800;
        }

        .footer-seal {
            font-family: 'Cinzel', serif;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 3px;
            color: #d4af37;
            text-transform: uppercase;
        }

        @media print {
            .control-bar {
                display: none !important;
            }
            body {
                background: none;
                padding: 0;
            }
            .preview-wrapper {
                padding: 0;
            }
            .preview-container {
                transform: scale(0.38);
                margin: 0;
            }
            @page {
                size: A4 landscape;
                margin: 0;
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

                <!-- Inner Decorative Gold Frames -->
                <div class="inner-frame"></div>
                <div class="inner-frame-thin"></div>

                <!-- Corner Art-Deco Accents -->
                <div class="ornament-tl"></div>
                <div class="ornament-tr"></div>
                <div class="ornament-bl"></div>
                <div class="ornament-br"></div>

                <!-- Header Banner -->
                <div class="card-header">
                    <div class="super-title">★ Waldo Luxury Resort & Casino • Monthly Recognition Awards ★</div>
                    <h1 class="main-card-title">Employee & Grooming of the Month</h1>
                    <div class="month-ribbon">
                        ✦ Honoring Excellence in {{ strtoupper($monthName) }} {{ $evaluatedYear ?? $report->year }} ✦
                    </div>
                </div>

                <!-- Showcase Grid: EOM (Left) & GOM (Right) -->
                <div class="showcase-area">

                    <!-- SECTION 1: EMPLOYEES OF THE MONTH (3 Winners) -->
                    <div class="category-box">
                        <div class="category-heading heading-eom">
                            <span>🏆 Employees of the Month</span>
                            <span style="font-size: 15px; color: #fde68a; font-weight: 600;">(3 Honorees)</span>
                        </div>

                        <div class="cards-row-eom">
                            @foreach($eomWinners as $winner)
                                <div class="winner-card">
                                    <div class="badge-tag tag-eom">🏆 {{ $winner['badge'] }}</div>

                                    @if($winner['employee'])
                                        <div class="avatar-circle avatar-eom">
                                            {{ strtoupper(substr($winner['employee']->name, 0, 2)) }}
                                        </div>

                                        <div class="winner-name">
                                            {{ $winner['employee']->name }}
                                        </div>

                                        <div class="code-pill">
                                            {{ $winner['employee']->employee_code }}
                                        </div>

                                        <div class="department-label">
                                            {{ $winner['department'] }}
                                        </div>

                                        <div class="designation-label">
                                            {{ $winner['employee']->designation?->name ?: 'Staff' }}
                                        </div>

                                        @if($winner['remarks'])
                                            <div class="remarks-quote">
                                                "{{ $winner['remarks'] }}"
                                            </div>
                                        @else
                                            <div class="remarks-quote" style="opacity: 0.7;">
                                                "Recognized for outstanding dedication, excellence, and exceptional team commitment."
                                            </div>
                                        @endif
                                    @else
                                        <div class="empty-slot">
                                            Slot Unassigned<br>
                                            <span style="font-size: 14px; opacity: 0.6;">{{ $winner['entry_label'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- SECTION 2: GROOMING OF THE MONTH (2 Winners) -->
                    <div class="category-box">
                        <div class="category-heading heading-gom">
                            <span>✨ Grooming of the Month</span>
                            <span style="font-size: 15px; color: #bae6fd; font-weight: 600;">(2 Honorees)</span>
                        </div>

                        <div class="cards-row-gom">
                            @foreach($gomWinners as $winner)
                                <div class="winner-card winner-card-gom">
                                    <div class="badge-tag tag-gom">✨ {{ $winner['badge'] }}</div>

                                    @if($winner['employee'])
                                        <div class="avatar-circle avatar-gom">
                                            {{ strtoupper(substr($winner['employee']->name, 0, 2)) }}
                                        </div>

                                        <div class="winner-name">
                                            {{ $winner['employee']->name }}
                                        </div>

                                        <div class="code-pill">
                                            {{ $winner['employee']->employee_code }}
                                        </div>

                                        <div class="department-label" style="color: #7dd3fc;">
                                            {{ $winner['department'] }}
                                        </div>

                                        <div class="designation-label">
                                            {{ $winner['employee']->designation?->name ?: 'Staff' }}
                                        </div>

                                        @if($winner['remarks'])
                                            <div class="remarks-quote remarks-quote-gom">
                                                "{{ $winner['remarks'] }}"
                                            </div>
                                        @else
                                            <div class="remarks-quote remarks-quote-gom" style="opacity: 0.7;">
                                                "Commended for immaculate grooming, professional etiquette, and pristine presentation."
                                            </div>
                                        @endif
                                    @else
                                        <div class="empty-slot">
                                            Slot Unassigned<br>
                                            <span style="font-size: 14px; opacity: 0.6;">{{ $winner['entry_label'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="card-footer">
                    <div class="footer-note">
                        Heartiest Congratulations to our achievers! Your exceptional spirit embodies <span class="footer-gold">The Gold Standard of Waldo</span>.
                    </div>

                    <div class="footer-seal">
                        ★ Human Resources Department • Official Recognition ★
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- JPG Export Logic -->
    <script>
        async function downloadWishCard() {
            const btn = document.getElementById('downloadBtn');
            const origText = btn.innerHTML;
            btn.innerHTML = '<span>⏳ Generating JPG...</span>';
            btn.disabled = true;

            const card = document.getElementById('hrms-wish-card-canvas');
            if (!card) {
                alert('Card element missing');
                btn.innerHTML = origText;
                btn.disabled = false;
                return;
            }

            // Ensure fonts are loaded
            if (document.fonts && document.fonts.ready) {
                try { await document.fonts.ready; } catch(e) {}
            }
            await new Promise(r => setTimeout(r, 200));

            try {
                // Temporarily clone or style card at true full scale 2708x1492 for clean rasterization
                const canvas = await window.html2canvas(card, {
                    width: 2708,
                    height: 1492,
                    scale: 1,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#050814',
                    logging: false
                });

                const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
                const fileName = `EOM_GOM_Wish_Card_{{ $monthName }}_{{ $evaluatedYear ?? $report->year }}.jpg`;

                const link = document.createElement('a');
                link.download = fileName;
                link.href = dataUrl;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (err) {
                console.error(err);
                alert('Could not generate JPG export: ' + err.message);
            } finally {
                btn.innerHTML = origText;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
