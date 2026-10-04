<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Promotions Congratulatory Announcement - A4 Letterhead Print</title>
    <style>
        :root {
            --primary: #d97706;        /* Amber 600 */
            --primary-hover: #b45309;  /* Amber 700 */
            --primary-light: #fef3c7;  /* Amber 100 */
            --primary-text: #92400e;   /* Amber 800 */
            --primary-border: #fde68a; /* Amber 200 */
            --letterhead-margin-top: 100px; /* Default top margin for letterhead */
        }

        /* A4 Page & Print Media Setup */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
            -webkit-font-smoothing: antialiased;
        }

        /* Screen-only Toolbar */
        .screen-toolbar {
            max-width: 210mm;
            margin: 16px auto 14px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
            flex-wrap: wrap;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toolbar-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-badge {
            background-color: var(--primary-light);
            color: var(--primary-text);
            font-size: 11.5px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 9999px;
            border: 1px solid var(--primary-border);
        }

        /* Margin Control in Toolbar */
        .margin-controller {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 4px 10px;
        }

        .margin-controller label {
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .margin-slider {
            width: 90px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .margin-input {
            width: 52px;
            padding: 3px 6px;
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            text-align: center;
            background: #ffffff;
        }

        .margin-unit {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            white-space: nowrap;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-secondary:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .btn-guide {
            background-color: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }
        .btn-guide:hover {
            background-color: #f1f5f9;
            color: #334155;
        }

        /* Printable A4 Document Container */
        .page-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 30px auto;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.06);
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* First Page Letterhead Spacer (Controlled dynamically by slider/input) */
        .first-page-letterhead-spacer {
            height: var(--letterhead-margin-top);
            min-height: var(--letterhead-margin-top);
            width: 100%;
            position: relative;
            box-sizing: border-box;
            transition: height 0.1s ease;
        }

        /* Visual guide for screen preview only */
        .letterhead-screen-guide {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-bottom: 2px dashed var(--primary);
            background-color: rgba(254, 243, 199, 0.28);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-text);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            user-select: none;
            z-index: 10;
        }

        /* Document Inner Body Padding */
        .document-inner {
            padding: 0 20mm 20mm 20mm;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Header / Reference Block */
        .memo-meta {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
            margin-bottom: 18px;
            font-size: 11.5px;
            color: #64748b;
        }

        .memo-ref {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 600;
            color: #334155;
        }

        .memo-date {
            font-weight: 600;
            color: #334155;
        }

        /* Title Block */
        .memo-heading-container {
            text-align: center;
            margin-bottom: 18px;
        }

        .memo-classification {
            display: inline-block;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--primary-text);
            background-color: var(--primary-light);
            border: 1px solid var(--primary-border);
            padding: 2.5px 10px;
            border-radius: 4px;
            margin-bottom: 6px;
        }

        .memo-main-title {
            font-size: 19px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 3px 0;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }

        .memo-sub-title {
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
            margin: 0;
        }

        .title-accent-bar {
            width: 60px;
            height: 2.5px;
            background: linear-gradient(90deg, #d97706, #f59e0b);
            margin: 8px auto 0 auto;
            border-radius: 2px;
        }

        /* Short, Dignified Preamble */
        .memo-preamble {
            font-size: 12.5px;
            line-height: 1.6;
            color: #334155;
            margin-bottom: 16px;
        }

        .memo-preamble p {
            margin: 0;
        }

        /* Sleek Modern Table Design (Light & Elegant, not bulky) */
        .table-container {
            margin-bottom: 20px;
            width: 100%;
        }

        .promotions-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .promotions-table thead th {
            background-color: #fafaf9;
            color: #475569;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 9px 12px;
            border-top: 1.5px solid #cbd5e1;
            border-bottom: 1.5px solid #cbd5e1;
            text-align: left;
        }

        .promotions-table tbody td {
            padding: 11px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #1e293b;
        }

        .promotions-table tbody tr:last-child td {
            border-bottom: 1.5px solid #cbd5e1;
        }

        /* Column Specifics */
        .col-code {
            width: 18%;
        }

        .code-pill {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11px;
            font-weight: 700;
            color: var(--primary-text);
            background-color: var(--primary-light);
            border: 1px solid var(--primary-border);
            padding: 2px 7px;
            border-radius: 4px;
            display: inline-block;
        }

        .col-name {
            width: 27%;
        }

        .employee-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 12.5px;
            letter-spacing: -0.01em;
        }

        .col-date {
            width: 18%;
            color: #475569;
            font-weight: 500;
            font-size: 11.5px;
            white-space: nowrap;
        }

        .col-changes {
            width: 37%;
        }

        /* Change Type Details */
        .change-category-tag {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 1.5px 7px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            background-color: var(--primary-light);
            color: var(--primary-text);
            border: 1px solid var(--primary-border);
            margin-bottom: 2px;
        }

        .change-transition {
            font-size: 11px;
            color: #475569;
            margin-top: 2px;
            line-height: 1.4;
        }

        .change-transition .from-val {
            color: #64748b;
        }

        .change-transition .arrow-icon {
            color: var(--primary);
            font-weight: bold;
            padding: 0 3px;
        }

        .change-transition .to-val {
            font-weight: 600;
            color: #0f172a;
        }

        /* Short Concluding Note */
        .memo-closing {
            font-size: 12px;
            line-height: 1.55;
            color: #475569;
            margin-bottom: 35px;
        }

        .memo-closing p {
            margin: 0;
        }

        /* HR Signature Block Only (Right Aligned) */
        .signature-wrapper {
            margin-top: auto;
            padding-top: 20px;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .hr-signature-box {
            width: 220px;
            text-align: center;
        }

        .signature-line {
            height: 1px;
            background-color: #94a3b8;
            margin-bottom: 8px;
        }

        .signer-title {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .signer-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        .signer-date {
            font-size: 10.5px;
            color: #94a3b8;
            margin-top: 8px;
        }

        /* Empty state notification */
        .empty-records-card {
            max-width: 500px;
            margin: 60px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Print Media Styles */
        @media print {
            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .letterhead-screen-guide {
                display: none !important;
            }

            .page-sheet {
                width: 100% !important;
                max-width: 210mm !important;
                min-height: 297mm !important;
                margin: 0 auto !important;
                box-shadow: none !important;
                border: none !important;
                page-break-after: auto;
            }

            /* Custom Dynamic Margin Top for Letterhead on Print */
            .first-page-letterhead-spacer {
                height: var(--letterhead-margin-top) !important;
                min-height: var(--letterhead-margin-top) !important;
                display: block !important;
            }

            .promotions-table {
                page-break-inside: auto;
            }

            .promotions-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .promotions-table thead {
                display: table-header-group;
            }

            .signature-wrapper {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    <!-- Screen-only Floating Controls -->
    <div class="screen-toolbar no-print">
        <div class="toolbar-left">
            <div class="toolbar-title">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: var(--primary);">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Promotions Congratulatory Note</span>
                <span class="toolbar-badge">{{ $selectedCount }} {{ Str::plural('Record', $selectedCount) }} Selected</span>
            </div>
        </div>

        <!-- Manual Margin-Top Controller -->
        <div class="margin-controller">
            <label for="margin-input">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: var(--primary);">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
                <span>Top Margin:</span>
            </label>
            <input type="range" class="margin-slider" id="margin-slider" min="0" max="300" step="5" value="100" oninput="updateMarginFromSlider(this.value)">
            <input type="number" class="margin-input" id="margin-input" min="0" max="300" step="5" value="100" oninput="updateMarginFromInput(this.value)">
            <span class="margin-unit">px</span>
        </div>

        <div class="toolbar-actions">
            <button type="button" class="btn btn-guide" id="btn-toggle-guide" onclick="toggleLetterheadGuide()">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>Toggle Guide</span>
            </button>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Document</span>
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.close()">
                <span>Close</span>
            </button>
        </div>
    </div>

    @if($promotions->isEmpty())
        <!-- Empty Records Warning -->
        <div class="empty-records-card no-print">
            <svg width="44" height="44" fill="none" viewBox="0 0 24 24" stroke="var(--primary)" stroke-width="1.5" style="margin: 0 auto 12px auto; display: block;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h2 style="font-size: 16px; font-weight: 700; margin: 0 0 8px 0; color: #0f172a;">No Promotions Selected</h2>
            <p style="font-size: 13px; color: #64748b; margin: 0 0 16px 0; line-height: 1.5;">
                Please select one or more promotion records in the Employee Promotions list and click "Print Congratulation Note".
            </p>
            <a href="{{ route('filament.kamkaj.resources.employee-promotions.index') }}" class="btn btn-primary">
                Return to Promotions List
            </a>
        </div>
    @else
        <!-- A4 Page Container -->
        <div class="page-sheet">
            <!-- First Page Top Margin Spacer for Letterhead (Adjustable) -->
            <div class="first-page-letterhead-spacer" id="letterhead-spacer">
                <div class="letterhead-screen-guide" id="screen-guide">
                    Pre-printed Letterhead Clearance (<span id="guide-margin-text">100px</span> Margin-Top) &bull; Content Begins Below
                </div>
            </div>

            <div class="document-inner">
                <!-- Metadata / Ref & Date -->
                <div class="memo-meta">
                    <div class="memo-ref">
                        REF: WLD/HR/PROMO/{{ $generatedDate->format('Y/m') }}/{{ str_pad($promotions->first()->id, 4, '0', STR_PAD_LEFT) }}
                    </div>
                    <div class="memo-date">
                        Date: {{ $generatedDate->format('F d, Y') }}
                    </div>
                </div>

                <!-- Announcement Header -->
                <div class="memo-heading-container">
                    <div class="memo-classification">Official Announcement</div>
                    <h1 class="memo-main-title">Congratulations On Your Promotion</h1>
                    <p class="memo-sub-title">Internal Circular &bull; Employee Promotion Announcement</p>
                    <div class="title-accent-bar"></div>
                </div>

                <!-- Short, Crisp Opening Message -->
                <div class="memo-preamble">
                    <p>
                        Management and the Human Resources Department are pleased to congratulate the following employee(s) on their well-deserved promotions and new appointments. We commend their dedication and valuable contributions to the organization.
                    </p>
                </div>

                <!-- Promotions List (Employee Code, Name, Promotion Date, Change Types Only) -->
                <div class="table-container">
                    <table class="promotions-table">
                        <thead>
                            <tr>
                                <th class="col-code">Employee Code</th>
                                <th class="col-name">Name</th>
                                <th class="col-date">Promotion Date</th>
                                <th class="col-changes">Change Types</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($promotions as $promotion)
                                @php
                                    $isDeptChanged = $promotion->from_department_id !== $promotion->to_department_id;
                                    $isDesigChanged = $promotion->from_designation_id !== $promotion->to_designation_id;

                                    if ($isDesigChanged && $isDeptChanged) {
                                        $categoryTag = 'Promotion & Transfer';
                                    } elseif ($isDesigChanged) {
                                        $categoryTag = 'Promotion';
                                    } elseif ($isDeptChanged) {
                                        $categoryTag = 'Transfer';
                                    } else {
                                        $categoryTag = 'Promotion';
                                    }
                                @endphp
                                <tr>
                                    <td class="col-code">
                                        <span class="code-pill">{{ $promotion->employee_id }}</span>
                                    </td>
                                    <td class="col-name">
                                        <div class="employee-name">{{ $promotion->employee?->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="col-date">
                                        {{ $promotion->promotion_date ? $promotion->promotion_date->format('d M, Y') : '-' }}
                                    </td>
                                    <td class="col-changes">
                                        <span class="change-category-tag">{{ $categoryTag }}</span>

                                        @if($isDesigChanged)
                                            <div class="change-transition">
                                                <span class="from-val">{{ $promotion->fromDesignation?->name ?? 'Designation' }}</span>
                                                <span class="arrow-icon">&rarr;</span>
                                                <span class="to-val">{{ $promotion->toDesignation?->name ?? 'Designation' }}</span>
                                            </div>
                                        @elseif($promotion->toDesignation)
                                            <div class="change-transition">
                                                <span class="to-val">{{ $promotion->toDesignation->name }}</span>
                                            </div>
                                        @endif

                                        @if($isDeptChanged)
                                            <div class="change-transition">
                                                <span class="from-val">{{ $promotion->fromDepartment?->name ?? 'Dept' }}</span>
                                                <span class="arrow-icon">&rarr;</span>
                                                <span class="to-val">{{ $promotion->toDepartment?->name ?? 'Dept' }}</span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Short Concluding Remarks -->
                <div class="memo-closing">
                    <p>
                        We extend our best wishes for their continued success as they assume their new responsibilities.
                    </p>
                </div>

                <!-- Only Human Resources Department Signature (Right Aligned) -->
                <div class="signature-wrapper">
                    <div class="hr-signature-box">
                        <div class="signature-line"></div>
                        <div class="signer-title">Human Resources Department</div>
                        <div class="signer-sub">Authorized Signatory</div>
                        <div class="signer-date">Date: ________________________</div>
                    </div>
                </div>

            </div>
        </div>
    @endif

    <script>
        // Initialize top margin from localStorage if set
        const savedMargin = localStorage.getItem('waldo_promo_print_margin_top');
        if (savedMargin) {
            setMarginTop(parseInt(savedMargin, 10));
        }

        function setMarginTop(val) {
            val = Math.max(0, Math.min(300, parseInt(val, 10) || 0));
            document.documentElement.style.setProperty('--letterhead-margin-top', val + 'px');
            
            const slider = document.getElementById('margin-slider');
            const input = document.getElementById('margin-input');
            const guideText = document.getElementById('guide-margin-text');

            if (slider && slider.value != val) slider.value = val;
            if (input && input.value != val) input.value = val;
            if (guideText) guideText.textContent = val + 'px';

            localStorage.setItem('waldo_promo_print_margin_top', val);
        }

        function updateMarginFromSlider(val) {
            setMarginTop(val);
        }

        function updateMarginFromInput(val) {
            setMarginTop(val);
        }

        function toggleLetterheadGuide() {
            const guide = document.getElementById('screen-guide');
            if (guide) {
                if (guide.style.display === 'none') {
                    guide.style.display = 'flex';
                } else {
                    guide.style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>
