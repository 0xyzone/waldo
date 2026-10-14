<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report->title }} - Applied Tips Adjustments & Error Penalties</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #0f172a;
            background: #f1f5f9;
            margin: 0;
            padding: 16px 10px;
            line-height: 1.35;
        }

        .sheet-container {
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
            padding: 14px 16px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .header-container {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
        }

        .header-title {
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 3px 0;
            color: #0f172a;
        }

        .header-subtitle {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin: 0 0 6px 0;
        }

        .header-meta-box {
            margin-top: 6px;
            padding: 6px 12px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10.5px;
        }

        .meta-col {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .meta-label {
            font-weight: 600;
            color: #64748b;
        }

        .meta-value {
            font-weight: bold;
            color: #0f172a;
        }

        /* KPI Cards */
        .kpi-container {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }

        .kpi-card {
            flex: 1;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            background: #f8fafc;
        }

        .kpi-title {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .kpi-val {
            font-size: 13px;
            font-weight: bold;
            margin-top: 2px;
            white-space: nowrap;
        }

        .kpi-sub {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 1px;
        }

        /* Section Headings */
        .section-header {
            margin-top: 12px;
            margin-bottom: 6px;
            padding: 4px 8px;
            background-color: #f1f5f9;
            border-left: 4px solid #0f172a;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 0 4px 4px 0;
            page-break-after: avoid;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            letter-spacing: 0.3px;
            margin: 0;
        }

        .section-badge {
            font-size: 9.5px;
            font-weight: 600;
            padding: 1.5px 6px;
            border-radius: 3px;
            background: #e2e8f0;
            color: #334155;
        }

        /* Data Tables */
        table.data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 12px;
            border: 1px solid #cbd5e1;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            vertical-align: middle;
        }

        table.data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #0f172a;
            border-bottom: 2px solid #cbd5e1;
            white-space: nowrap;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table.data-table tfoot td {
            background-color: #f1f5f9;
            border-top: 2px solid #cbd5e1;
            font-weight: bold;
            padding: 5px 6px;
            font-size: 10px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .nowrap { white-space: nowrap; }

        .emp-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: bold;
            color: #0f172a;
            white-space: nowrap;
        }

        .badge-add {
            color: #166534;
            font-weight: bold;
            white-space: nowrap;
        }

        .badge-deduct {
            color: #991b1b;
            font-weight: bold;
            white-space: nowrap;
        }

        .remarks-text {
            font-size: 9.5px;
            color: #334155;
            line-height: 1.35;
            word-break: normal;
            overflow-wrap: break-word;
            hyphens: none;
        }

        .error-reason-text {
            font-size: 9.5px;
            color: #991b1b;
            line-height: 1.35;
            font-weight: 500;
            word-break: normal;
            overflow-wrap: break-word;
            hyphens: none;
        }

        .empty-cell {
            color: #94a3b8;
            font-style: italic;
        }

        .print-footer-info {
            margin-top: 14px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #64748b;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
            html, body {
                background: #fff;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                font-size: 10px;
            }
            .sheet-container {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none;
                border-radius: 0;
            }
            .no-print {
                display: none !important;
            }
            table.data-table {
                width: 100% !important;
                max-width: 100% !important;
                page-break-inside: auto;
            }
            table.data-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            table.data-table thead {
                display: table-header-group;
            }
            table.data-table tfoot {
                display: table-footer-group;
            }
            .section-header {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="sheet-container">
    {{-- Top Action Toolbar (No-Print) --}}
    <div class="no-print" style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
        <a href="javascript:window.close()" style="text-decoration: none; color: #475569; font-size: 12px; font-weight: 500;">
            &larr; Back / Close
        </a>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" style="padding: 6px 16px; background: #0f172a; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 12px;">
                🖨️ Print / Save PDF
            </button>
        </div>
    </div>

    {{-- Main Header --}}
    <div class="header-container">
        <h1 class="header-title">{{ $report->title }}</h1>
        <div class="header-subtitle">Applied Tips Adjustments, Company Error Penalties & Held Tips Report</div>

        <div class="header-meta-box">
            <div class="meta-row">
                <div class="meta-col">
                    <span class="meta-label">Billing Period:</span>
                    <span class="meta-value">{{ ucfirst($report->month) }} {{ $report->year }}</span>
                </div>
                <div class="meta-col">
                    <span class="meta-label">Cutoff Date:</span>
                    <span class="meta-value">{{ \Carbon\Carbon::parse($report->cutoff_date)->format('d F, Y') }}</span>
                </div>
                <div class="meta-col">
                    <span class="meta-label">Generated On:</span>
                    <span class="meta-value">{{ now()->format('d M, Y h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Summary Cards --}}
    <div class="kpi-container">
        <div class="kpi-card">
            <div class="kpi-title">Tips Additions (+)</div>
            <div class="kpi-val" style="color: #166534;">+ Rs. {{ number_format($totalAdd, 0) }}</div>
            <div class="kpi-sub">{{ $adjustments->where('type', 'add')->count() }} staff entries</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Tips Deductions (-)</div>
            <div class="kpi-val" style="color: #991b1b;">- Rs. {{ number_format($totalDeduct, 0) }}</div>
            <div class="kpi-sub">{{ $adjustments->where('type', 'deduct')->count() }} staff entries</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Company Error Penalties</div>
            <div class="kpi-val" style="color: #b45309;">- Rs. {{ number_format($totalErrorDeduction, 0) }}</div>
            <div class="kpi-sub">{{ $errors->count() }} staff infractions</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Tips On Hold</div>
            <div class="kpi-val" style="color: #b45309;">Rs. {{ number_format($totalHeldAmount, 0) }}</div>
            <div class="kpi-sub">{{ $heldTips->count() }} staff tips held</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Net Adjustment Impact</div>
            <div class="kpi-val" style="color: {{ ($totalAdd - $totalDeduct) >= 0 ? '#166534' : '#991b1b' }}">
                {{ ($totalAdd - $totalDeduct) >= 0 ? '+' : '' }} Rs. {{ number_format($totalAdd - $totalDeduct, 0) }}
            </div>
            <div class="kpi-sub">Net payout shift</div>
        </div>
    </div>

    {{-- SECTION 1: TIPS ADJUSTMENTS --}}
    <div class="section-header">
        <div class="section-title">1. Tips Adjustments Applied (Additions & Deductions)</div>
        <div class="section-badge">{{ $adjustments->count() }} Records</div>
    </div>

    @if($adjustments->isEmpty())
        <div style="padding: 12px; text-align: center; border: 1px dashed #cbd5e1; border-radius: 4px; color: #64748b; font-size: 11px; margin-bottom: 12px;">
            No individual tips adjustments were recorded for {{ ucfirst($report->month) }} {{ $report->year }}.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 3.5%;" class="text-center nowrap">#</th>
                    <th style="width: 8.5%;" class="text-center nowrap">Code</th>
                    <th style="width: 18%;" class="nowrap">Employee Name</th>
                    <th style="width: 9%;" class="nowrap">Department</th>
                    <th style="width: 13%;" class="nowrap">Designation</th>
                    <th style="width: 11%;" class="text-right nowrap">Amount</th>
                    <th style="width: 37%;">Adjustment Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($adjustments as $idx => $adj)
                    <tr>
                        <td class="text-center font-bold nowrap" style="color: #64748b;">{{ $idx + 1 }}</td>
                        <td class="text-center emp-code nowrap">{{ $adj['employee_code'] }}</td>
                        <td style="font-weight: 600;">{{ $adj['employee_name'] }}</td>
                        <td class="nowrap">{{ $adj['department'] }}</td>
                        <td>{{ $adj['designation'] }}</td>
                        <td class="text-right font-bold nowrap {{ $adj['type'] === 'add' ? 'badge-add' : 'badge-deduct' }}">
                            {{ $adj['type'] === 'add' ? '+' : '-' }} Rs. {{ number_format($adj['amount'], 0) }}
                        </td>
                        <td>
                            @if(!empty($adj['remarks']))
                                <div class="remarks-text">{{ $adj['remarks'] }}</div>
                            @else
                                <span class="empty-cell">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right nowrap">Total Applied Adjustments Net:</td>
                    <td class="text-right nowrap" style="color: {{ ($totalAdd - $totalDeduct) >= 0 ? '#166534' : '#991b1b' }};">
                        Rs. {{ number_format($totalAdd - $totalDeduct, 0) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- SECTION 2: COMPANY ERROR PENALTIES --}}
    <div class="section-header" style="margin-top: 14px;">
        <div class="section-title">2. Company Error Penalty Deductions</div>
        <div class="section-badge">{{ $errors->count() }} Penalties</div>
    </div>

    @if($errors->isEmpty())
        <div style="padding: 12px; text-align: center; border: 1px dashed #cbd5e1; border-radius: 4px; color: #64748b; font-size: 11px; margin-bottom: 12px;">
            No company error deductions were registered for {{ ucfirst($report->month) }} {{ $report->year }}.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 3.5%;" class="text-center nowrap">#</th>
                    <th style="width: 8.5%;" class="text-center nowrap">Code</th>
                    <th style="width: 15%;" class="nowrap">Employee Name</th>
                    <th style="width: 8%;" class="nowrap">Department</th>
                    <th style="width: 11%;" class="nowrap">Designation</th>
                    <th style="width: 7%;" class="text-center nowrap">Deduct %</th>
                    <th style="width: 8.5%;" class="text-right nowrap">Base Tips</th>
                    <th style="width: 9.5%;" class="text-right nowrap">Est. Deduct</th>
                    <th style="width: 29%;">Error Reason / Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($errors as $idx => $err)
                    <tr>
                        <td class="text-center font-bold nowrap" style="color: #64748b;">{{ $idx + 1 }}</td>
                        <td class="text-center emp-code nowrap">{{ $err['employee_code'] }}</td>
                        <td style="font-weight: 600;">{{ $err['employee_name'] }}</td>
                        <td class="nowrap">{{ $err['department'] }}</td>
                        <td>{{ $err['designation'] }}</td>
                        <td class="text-center font-bold nowrap" style="color: #991b1b;">
                            - {{ $err['percentage_to_deduct'] }}%
                        </td>
                        <td class="text-right nowrap" style="color: #475569;">
                            Rs. {{ number_format($err['base_tips'], 0) }}
                        </td>
                        <td class="text-right font-bold nowrap" style="color: #991b1b;">
                            - Rs. {{ number_format($err['estimated_deduction'], 0) }}
                        </td>
                        <td>
                            @if(!empty($err['reason']))
                                <div class="error-reason-text">{{ $err['reason'] }}</div>
                            @else
                                <span class="empty-cell">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" class="text-right nowrap">Total Estimated Error Deduction:</td>
                    <td class="text-right nowrap" style="color: #991b1b;">
                        - Rs. {{ number_format($totalErrorDeduction, 0) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- SECTION 3: EMPLOYEES WITH TIPS HELD --}}
    <div class="section-header" style="margin-top: 14px;">
        <div class="section-title">3. Employees With Tips Held (On Hold Status)</div>
        <div class="section-badge">{{ $heldTips->count() }} Staff</div>
    </div>

    @if($heldTips->isEmpty())
        <div style="padding: 12px; text-align: center; border: 1px dashed #cbd5e1; border-radius: 4px; color: #64748b; font-size: 11px; margin-bottom: 12px;">
            No employee tips are placed on hold for {{ ucfirst($report->month) }} {{ $report->year }}.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 3.5%;" class="text-center nowrap">#</th>
                    <th style="width: 6.5%;" class="text-center nowrap">Status</th>
                    <th style="width: 8.5%;" class="text-center nowrap">Code</th>
                    <th style="width: 16%;" class="nowrap">Employee Name</th>
                    <th style="width: 11%; white-space: normal; word-break: break-word; text-wrap: wrap;">Department</th>
                    <th style="width: 14%; white-space: normal; word-break: break-word; text-wrap: wrap;">Designation</th>
                    <th style="width: 9%;" class="text-right nowrap">Base Tips</th>
                    <th style="width: 9.5%;" class="text-right nowrap">Held Amount</th>
                    <th style="width: 22%;">Hold Reason / Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($heldTips as $idx => $held)
                    <tr>
                        <td class="text-center font-bold nowrap" style="color: #64748b;">{{ $idx + 1 }}</td>
                        <td class="text-center nowrap">
                            @if(($held['tips_status'] ?? '') === 'Release' || $report->isValidated())
                                <span style="display: inline-block; padding: 1px 5px; font-size: 8.5px; font-weight: bold; background: #dcfce7; color: #166534; border: 1px solid #86efac; border-radius: 3px;">
                                    RELEASE
                                </span>
                            @else
                                <span style="display: inline-block; padding: 1px 5px; font-size: 8.5px; font-weight: bold; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; border-radius: 3px;">
                                    HOLD
                                </span>
                            @endif
                        </td>
                        <td class="text-center emp-code nowrap">{{ $held['employee_code'] }}</td>
                        <td style="font-weight: 600;">{{ $held['employee_name'] }}</td>
                        <td style="white-space: normal; word-break: break-word; overflow-wrap: break-word; text-wrap: wrap; line-height: 1.25;">{{ $held['department'] }}</td>
                        <td style="white-space: normal; word-break: break-word; overflow-wrap: break-word; text-wrap: wrap; line-height: 1.25;">{{ $held['designation'] }}</td>
                        <td class="text-right nowrap" style="color: #475569;">
                            Rs. {{ number_format($held['base_tips'], 0) }}
                        </td>
                        <td class="text-right font-bold nowrap" style="color: #b45309;">
                            Rs. {{ number_format($held['held_amount'], 0) }}
                        </td>
                        <td>
                            @if(!empty($held['reason']))
                                <div class="remarks-text" style="color: #78350f;">{{ $held['reason'] }}</div>
                            @else
                                <span class="empty-cell">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" class="text-right nowrap">Total Held Tips Amount:</td>
                    <td class="text-right nowrap" style="color: #b45309;">
                        Rs. {{ number_format($totalHeldAmount, 0) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- Clean Print Footer Info (No Signatory Box) --}}
    <div class="print-footer-info">
        <span>Report Document: {{ $report->title }} &bull; Period: {{ ucfirst($report->month) }} {{ $report->year }}</span>
        <span>Generated from Waldo HR System on {{ now()->format('d M, Y h:i A') }}</span>
    </div>
    </div>
</body>
</html>
