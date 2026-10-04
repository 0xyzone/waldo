<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Promotions Congratulatory Announcement - A4 Letterhead Print</title>
    <style>
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
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
            background-color: #f1f5f9;
            -webkit-font-smoothing: antialiased;
        }

        /* Screen-only Toolbar */
        .screen-toolbar {
            max-width: 210mm;
            margin: 16px auto 14px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .toolbar-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toolbar-badge {
            background-color: #e0f2fe;
            color: #0369a1;
            font-size: 12px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 9999px;
            border: 1px solid #bae6fd;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }

        .btn-primary {
            background-color: #0284c7;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #0369a1;
        }

        .btn-secondary {
            background-color: #f8fafc;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-secondary:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .btn-guide {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .btn-guide:hover {
            background-color: #fde68a;
        }

        /* Printable A4 Document Container */
        .page-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 30px auto;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* First Page Letterhead Spacer (Exact 50px Margin Top) */
        .first-page-letterhead-spacer {
            height: 50px;
            min-height: 50px;
            width: 100%;
            position: relative;
            box-sizing: border-box;
        }

        /* Visual guide for screen preview only */
        .letterhead-screen-guide {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 50px;
            border-bottom: 2px dashed #f59e0b;
            background-color: rgba(254, 243, 199, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #b45309;
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
            border-bottom: 1.5px solid #cbd5e1;
            padding-bottom: 10px;
            margin-bottom: 18px;
            font-size: 12px;
            color: #475569;
        }

        .memo-ref {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 600;
            color: #1e293b;
        }

        .memo-date {
            font-weight: 600;
            color: #1e293b;
        }

        /* Title Block */
        .memo-heading-container {
            text-align: center;
            margin-bottom: 22px;
        }

        .memo-classification {
            display: inline-block;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #0284c7;
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            padding: 3px 12px;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .memo-main-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 4px 0;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }

        .memo-sub-title {
            font-size: 12.5px;
            font-weight: 600;
            color: #64748b;
            margin: 0;
        }

        .title-accent-bar {
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #0284c7, #38bdf8);
            margin: 10px auto 0 auto;
            border-radius: 2px;
        }

        /* Preamble / Congratulatory Note */
        .memo-preamble {
            font-size: 12.5px;
            line-height: 1.65;
            color: #334155;
            margin-bottom: 20px;
            text-align: justify;
        }

        .memo-preamble p {
            margin: 0 0 8px 0;
        }

        /* Table Styling */
        .table-container {
            margin-bottom: 24px;
            width: 100%;
        }

        .promotions-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            border: 1px solid #cbd5e1;
        }

        .promotions-table thead th {
            background-color: #f8fafc;
            color: #1e293b;
            font-weight: 700;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }

        .promotions-table tbody td {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #1e293b;
        }

        .promotions-table tbody tr:nth-child(even) {
            background-color: #fcfdfe;
        }

        .col-code {
            width: 18%;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 700;
            color: #0369a1;
        }

        .col-name {
            width: 28%;
            font-weight: 700;
            color: #0f172a;
            font-size: 12.5px;
        }

        .col-date {
            width: 18%;
            color: #475569;
            font-weight: 500;
            white-space: nowrap;
        }

        .col-changes {
            width: 36%;
        }

        /* Change Type Display Styles */
        .change-category {
            font-weight: 700;
            color: #0f172a;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .change-category-tag {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .tag-promotion {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .tag-transfer {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .change-detail-line {
            font-size: 11px;
            color: #475569;
            margin-top: 3px;
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        .change-detail-line .from-label {
            color: #64748b;
        }

        .change-detail-line .arrow-icon {
            color: #94a3b8;
            font-weight: bold;
        }

        .change-detail-line .to-label {
            font-weight: 600;
            color: #0f172a;
        }

        /* Concluding Remarks */
        .memo-closing {
            font-size: 12.5px;
            line-height: 1.65;
            color: #334155;
            margin-bottom: 30px;
            text-align: justify;
        }

        .memo-closing p {
            margin: 0 0 8px 0;
        }

        /* Sign-off Blocks */
        .signatures-container {
            margin-top: auto;
            padding-top: 15px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .signature-box {
            width: 42%;
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
            letter-spacing: 0.3px;
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

        /* Footer */
        .memo-footer {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
            letter-spacing: 0.4px;
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

            /* The first page margin-top of 50px for letterhead */
            .first-page-letterhead-spacer {
                height: 50px !important;
                min-height: 50px !important;
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

            .signatures-container {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    <!-- Screen-only Floating Controls -->
    <div class="screen-toolbar no-print">
        <div class="toolbar-title">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: #0284c7;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Promotions Congratulatory Announcement</span>
            <span class="toolbar-badge">{{ $selectedCount }} {{ Str::plural('Record', $selectedCount) }} Selected</span>
        </div>
        <div class="toolbar-actions">
            <button type="button" class="btn btn-guide" id="btn-toggle-guide" onclick="toggleLetterheadGuide()">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>Letterhead Guide (50px)</span>
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
            <svg width="44" height="44" fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="1.5" style="margin: 0 auto 12px auto; display: block;">
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
            <!-- First Page 50px Margin Top Spacer for Letterhead -->
            <div class="first-page-letterhead-spacer">
                <div class="letterhead-screen-guide" id="screen-guide">
                    Pre-printed Letterhead Clearance (50px Margin-Top) &bull; Content Begins Below
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
                    <p class="memo-sub-title">Celebrating Staff Excellence, Dedication & Career Milestones</p>
                    <div class="title-accent-bar"></div>
                </div>

                <!-- Congratulatory Opening Message -->
                <div class="memo-preamble">
                    <p>
                        The Management and Human Resources Department are delighted to formally announce and celebrate the promotions of our esteemed team members. Through consistent dedication, exemplary work ethic, and professional commitment, they have distinguished themselves and achieved significant milestones in their respective careers within our organization.
                    </p>
                    <p>
                        We extend our warmest congratulations to each of the following individuals on their well-deserved appointments:
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
                                        $categoryTitle = 'Designation & Department Promotion';
                                        $showPromotionTag = true;
                                        $showTransferTag = true;
                                    } elseif ($isDesigChanged) {
                                        $categoryTitle = 'Designation Promotion';
                                        $showPromotionTag = true;
                                        $showTransferTag = false;
                                    } elseif ($isDeptChanged) {
                                        $categoryTitle = 'Department Transfer';
                                        $showPromotionTag = false;
                                        $showTransferTag = true;
                                    } else {
                                        $categoryTitle = 'Promotion';
                                        $showPromotionTag = true;
                                        $showTransferTag = false;
                                    }
                                @endphp
                                <tr>
                                    <td class="col-code">
                                        {{ $promotion->employee_id }}
                                    </td>
                                    <td class="col-name">
                                        {{ $promotion->employee?->name ?? 'N/A' }}
                                    </td>
                                    <td class="col-date">
                                        {{ $promotion->promotion_date ? $promotion->promotion_date->format('d M, Y') : '-' }}
                                    </td>
                                    <td class="col-changes">
                                        <div class="change-category">
                                            <span>{{ $categoryTitle }}</span>
                                            @if($showPromotionTag)
                                                <span class="change-category-tag tag-promotion">Promotion</span>
                                            @endif
                                            @if($showTransferTag)
                                                <span class="change-category-tag tag-transfer">Transfer</span>
                                            @endif
                                        </div>

                                        @if($isDesigChanged)
                                            <div class="change-detail-line">
                                                <span class="from-label">{{ $promotion->fromDesignation?->name ?? 'Designation' }}</span>
                                                <span class="arrow-icon">&rarr;</span>
                                                <span class="to-label">{{ $promotion->toDesignation?->name ?? 'Designation' }}</span>
                                            </div>
                                        @elseif($promotion->toDesignation)
                                            <div class="change-detail-line">
                                                <span class="to-label">{{ $promotion->toDesignation->name }}</span>
                                            </div>
                                        @endif

                                        @if($isDeptChanged)
                                            <div class="change-detail-line">
                                                <span class="from-label">{{ $promotion->fromDepartment?->name ?? 'Dept' }}</span>
                                                <span class="arrow-icon">&rarr;</span>
                                                <span class="to-label">{{ $promotion->toDepartment?->name ?? 'Dept' }}</span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Congratulatory Closing Remarks -->
                <div class="memo-closing">
                    <p>
                        Your perseverance, leadership, and positive contributions continue to inspire your colleagues and strengthen our team. We are confident that in your new roles, you will reach even greater heights and continue to foster an environment of excellence and collaboration.
                    </p>
                    <p>
                        Please join us in extending our heartfelt congratulations and wishing them every success in their elevated responsibilities.
                    </p>
                </div>

                <!-- Official Sign-off Blocks -->
                <div class="signatures-container">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signer-title">Human Resources Department</div>
                        <div class="signer-sub">Authorized Issuing Authority</div>
                        <div class="signer-date">Date: ________________________</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signer-title">Executive Management</div>
                        <div class="signer-sub">Approval & Endorsement</div>
                        <div class="signer-date">Date: ________________________</div>
                    </div>
                </div>

                <!-- Memo Footer -->
                <div class="memo-footer">
                    Waldo Management System &bull; Internal Circular &bull; Official Employee Record
                </div>
            </div>
        </div>
    @endif

    <script>
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
