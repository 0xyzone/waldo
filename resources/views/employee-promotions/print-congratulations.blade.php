<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion Congratulations - {{ $generatedDate->format('d M Y') }}</title>
    <style>
        :root {
            --primary: #d97706;
            --primary-hover: #b45309;
            --primary-light: #fef3c7;
            --primary-text: #92400e;
            --primary-border: #fde68a;
            --first-top: 120px;
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
            background: #e5e7eb;
            -webkit-font-smoothing: antialiased;
        }

        /* ---------- Screen toolbar ---------- */
        .screen-toolbar {
            position: sticky;
            top: 0;
            z-index: 50;
            max-width: 210mm;
            margin: 14px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        }

        .toolbar-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-badge {
            background: var(--primary-light);
            color: var(--primary-text);
            border: 1px solid var(--primary-border);
            font-size: 11px;
            font-weight: 700;
            padding: 1px 8px;
            border-radius: 9999px;
        }

        .margin-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .margin-field {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            color: #475569;
        }

        .margin-field input {
            width: 56px;
            padding: 4px 6px;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            color: #0f172a;
        }

        .margin-field input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .toolbar-actions {
            display: flex;
            gap: 6px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: background-color 0.15s ease;
            white-space: nowrap;
        }

        .btn-primary { background: var(--primary); color: #ffffff; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-secondary { background: #ffffff; color: #334155; border-color: #cbd5e1; }
        .btn-secondary:hover { background: #f1f5f9; }

        /* ---------- Sheet ---------- */
        .page-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 30px auto;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12);
        }

        .first-page-spacer {
            height: var(--first-top);
            position: relative;
        }

        .spacer-guide {
            position: absolute;
            inset: 0;
            border-bottom: 1.5px dashed var(--primary);
            background: rgba(254, 243, 199, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: var(--primary-text);
        }

        .document-inner {
            padding: 0 18mm 12mm 18mm;
        }

        /* ---------- Header ---------- */
        .memo-meta {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 600;
            color: #334155;
            padding-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 14px;
        }

        .memo-ref {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }

        .memo-heading {
            text-align: center;
            margin-bottom: 12px;
        }

        .memo-title {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            color: #0f172a;
            margin: 0;
        }

        .title-accent {
            width: 48px;
            height: 2px;
            background: var(--primary);
            margin: 6px auto 0 auto;
            border-radius: 2px;
        }

        .memo-preamble {
            font-size: 12px;
            line-height: 1.55;
            color: #334155;
            margin: 0 0 12px 0;
        }

        /* ---------- Compact table ---------- */
        .promotions-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }

        .promotions-table thead th {
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--primary-text);
            background: var(--primary-light);
            padding: 6px 8px;
            border-bottom: 1.5px solid var(--primary);
            white-space: nowrap;
        }

        .promotions-table tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #eef0f3;
            vertical-align: middle;
            line-height: 1.35;
        }

        .promotions-table tbody tr:nth-child(even) td {
            background: #fafafa;
        }

        .promotions-table tbody tr:last-child td {
            border-bottom: 1.5px solid #cbd5e1;
        }

        .col-code {
            width: 15%;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-weight: 700;
            font-size: 11px;
            color: var(--primary-text);
            white-space: nowrap;
        }

        .col-name {
            width: 30%;
            font-weight: 600;
            color: #0f172a;
        }

        .col-date {
            width: 14%;
            color: #475569;
            white-space: nowrap;
        }

        .col-changes {
            width: 40%;
            color: #475569;
        }

        .change-line + .change-line {
            margin-top: 1px;
        }

        .change-line .from { color: #64748b; }
        .change-line .arrow { color: var(--primary); font-weight: 700; padding: 0 3px; }
        .change-line .to { color: #0f172a; font-weight: 600; }

        /* ---------- Closing & signature (kept together) ---------- */
        .closing-block {
            margin-top: 14px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .memo-closing {
            font-size: 12px;
            line-height: 1.55;
            color: #334155;
            margin: 0 0 48px 0;
        }

        .signature-row {
            display: flex;
            justify-content: flex-end;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .signature-line {
            height: 1px;
            background: #64748b;
            margin-bottom: 6px;
        }

        .signer-title {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #0f172a;
        }

        .signer-sub {
            font-size: 10.5px;
            color: #64748b;
            margin-top: 1px;
        }

        .empty-records-card {
            max-width: 480px;
            margin: 60px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 28px;
            text-align: center;
        }

        /* ---------- Print ---------- */
        @media print {
            html, body {
                background: #ffffff !important;
            }

            .no-print,
            .spacer-guide {
                display: none !important;
            }

            .page-sheet {
                width: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
            }

            .document-inner {
                padding-bottom: 0 !important;
            }

            .promotions-table thead {
                display: table-header-group;
            }

            .promotions-table tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
    {{-- Page margins are injected dynamically so they can be adjusted from the toolbar --}}
    <style id="dynamic-page-style">
        @page { size: A4 portrait; margin: 40px 0 60px 0; }
        @page :first { margin-top: 0; }
    </style>
</head>
<body>

    <div class="screen-toolbar no-print">
        <div class="toolbar-title">
            <span>Promotion Congratulations</span>
            <span class="toolbar-badge">{{ $selectedCount }} {{ Str::plural('Employee', $selectedCount) }}</span>
        </div>

        <div class="margin-controls">
            <label class="margin-field" title="Space reserved for the letterhead header on page 1">
                First page top
                <input type="number" id="margin-first-top" min="0" max="400" step="5" value="120">
            </label>
            <label class="margin-field" title="Top margin on continuation pages">
                Other pages top
                <input type="number" id="margin-other-top" min="0" max="400" step="5" value="40">
            </label>
            <label class="margin-field" title="Space reserved for the letterhead footer on every page">
                Bottom
                <input type="number" id="margin-bottom" min="0" max="400" step="5" value="60">
            </label>
            <span class="margin-field">px</span>
        </div>

        <div class="toolbar-actions">
            <button type="button" class="btn btn-secondary" onclick="toggleGuide()">Guide</button>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.close()">Close</button>
        </div>
    </div>

    @if($promotions->isEmpty())
        <div class="empty-records-card no-print">
            <h2 style="font-size: 16px; margin: 0 0 8px 0;">No Promotions Selected</h2>
            <p style="font-size: 13px; color: #64748b; margin: 0 0 16px 0;">
                Select one or more promotion records and click "Print Congratulation Note".
            </p>
            <a href="{{ route('filament.kamkaj.resources.employee-promotions.index') }}" class="btn btn-primary">Back to Promotions</a>
        </div>
    @else
        <div class="page-sheet">
            <div class="first-page-spacer">
                <div class="spacer-guide no-print" id="spacer-guide">Letterhead area</div>
            </div>

            <div class="document-inner">
                <div class="memo-meta">
                    <span class="memo-ref">REF: WLD/HR/PROMO/{{ $generatedDate->format('Y/m') }}/{{ str_pad($promotions->first()->id, 4, '0', STR_PAD_LEFT) }}</span>
                    <span>Date: {{ $generatedDate->format('F d, Y') }}</span>
                </div>

                <div class="memo-heading">
                    <h1 class="memo-title">Congratulations on Your Promotion</h1>
                    <div class="title-accent"></div>
                </div>

                <p class="memo-preamble">
                    The Human Resources Department is pleased to congratulate the following {{ Str::plural('employee', $selectedCount) }} on their well-deserved {{ Str::plural('promotion', $selectedCount) }}. We appreciate your dedication and valuable contributions to the organization.
                </p>

                <table class="promotions-table">
                    <thead>
                        <tr>
                            <th class="col-code">Emp. Code</th>
                            <th class="col-name">Name</th>
                            <th class="col-date">Promotion Date</th>
                            <th class="col-changes">Change Types</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($promotions as $promotion)
                            @php
                                $isDesignationChanged = $promotion->from_designation_id !== $promotion->to_designation_id;
                                $isDepartmentChanged = $promotion->from_department_id !== $promotion->to_department_id;
                            @endphp
                            <tr>
                                <td class="col-code">{{ $promotion->employee_id }}</td>
                                <td class="col-name">{{ $promotion->employee?->name ?? 'N/A' }}</td>
                                <td class="col-date">{{ $promotion->promotion_date?->format('d M, Y') ?? '-' }}</td>
                                <td class="col-changes">
                                    @if($isDesignationChanged)
                                        <div class="change-line">
                                            <span class="from">{{ $promotion->fromDesignation?->name ?? '-' }}</span><span class="arrow">&rarr;</span><span class="to">{{ $promotion->toDesignation?->name ?? '-' }}</span>
                                        </div>
                                    @endif
                                    @if($isDepartmentChanged)
                                        <div class="change-line">
                                            <span class="from">{{ $promotion->fromDepartment?->name ?? '-' }}</span><span class="arrow">&rarr;</span><span class="to">{{ $promotion->toDepartment?->name ?? '-' }}</span>
                                        </div>
                                    @endif
                                    @if(! $isDesignationChanged && ! $isDepartmentChanged)
                                        <div class="change-line"><span class="to">{{ $promotion->toDesignation?->name ?? '-' }}</span></div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="closing-block">
                    <p class="memo-closing">
                        We wish you continued success in your new {{ Str::plural('role', $selectedCount) }}.
                    </p>

                    <div class="signature-row">
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signer-title">Human Resources Department</div>
                            <div class="signer-sub">Authorized Signatory</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        (function () {
            const storageKeys = {
                firstTop: 'waldo_promo_print_margin_top',
                otherTop: 'waldo_promo_print_margin_other_top',
                bottom: 'waldo_promo_print_margin_bottom',
            };

            const inputs = {
                firstTop: document.getElementById('margin-first-top'),
                otherTop: document.getElementById('margin-other-top'),
                bottom: document.getElementById('margin-bottom'),
            };

            const clamp = (value) => Math.max(0, Math.min(400, parseInt(value, 10) || 0));

            function applyMargins() {
                const firstTop = clamp(inputs.firstTop.value);
                const otherTop = clamp(inputs.otherTop.value);
                const bottom = clamp(inputs.bottom.value);

                document.documentElement.style.setProperty('--first-top', firstTop + 'px');
                document.getElementById('dynamic-page-style').textContent =
                    '@page { size: A4 portrait; margin: ' + otherTop + 'px 0 ' + bottom + 'px 0; }' +
                    '@page :first { margin-top: 0; }';

                localStorage.setItem(storageKeys.firstTop, firstTop);
                localStorage.setItem(storageKeys.otherTop, otherTop);
                localStorage.setItem(storageKeys.bottom, bottom);
            }

            Object.keys(inputs).forEach((key) => {
                const saved = localStorage.getItem(storageKeys[key]);
                if (saved !== null) {
                    inputs[key].value = clamp(saved);
                }
                inputs[key].addEventListener('input', applyMargins);
            });

            applyMargins();

            window.toggleGuide = function () {
                const guide = document.getElementById('spacer-guide');
                if (guide) {
                    guide.style.display = guide.style.display === 'none' ? 'flex' : 'none';
                }
            };
        })();
    </script>
</body>
</html>
