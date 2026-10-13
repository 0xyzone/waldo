<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report->title }} - Adjustments & Error Penalties Audit Sheet</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11.5px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
        }

        .header-container {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }

        .header-subtitle {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin: 0 0 6px 0;
        }

        .header-meta-box {
            margin-top: 8px;
            padding: 6px 12px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11.5px;
        }

        .meta-col {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .meta-label {
            font-weight: 600;
            color: #475569;
        }

        .meta-value {
            font-weight: bold;
            color: #0f172a;
        }

        .section-header {
            margin-top: 14px;
            margin-bottom: 6px;
            padding: 5px 8px;
            background-color: #f1f5f9;
            border-left: 4px solid #0f172a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 12.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            margin: 0;
        }

        .section-badge {
            font-size: 10.5px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 3px;
            background: #e2e8f0;
            color: #334155;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 10px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            vertical-align: top;
        }

        table.data-table th {
            background-color: #e2e8f0;
            font-weight: bold;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #0f172a;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        .badge-add {
            color: #166534;
            font-weight: bold;
        }

        .badge-deduct {
            color: #991b1b;
            font-weight: bold;
        }

        .badge-status {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 9.5px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-updated {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .remarks-box {
            font-size: 10.5px;
            color: #1e293b;
            background-color: #f8fafc;
            padding: 3px 6px;
            border-radius: 3px;
            border: 1px dashed #cbd5e1;
            line-height: 1.3;
        }

        .empty-cell {
            color: #94a3b8;
            font-style: italic;
        }

        .kpi-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 10px;
            margin-bottom: 6px;
        }

        .kpi-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 10px;
            background: #fff;
        }

        .kpi-title {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }

        .kpi-val {
            font-size: 14px;
            font-weight: bold;
            margin-top: 2px;
        }

        .signatures-container {
            margin-top: 28px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .sig-box {
            width: 22%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 11px;
            font-weight: 600;
        }

        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 12px; text-align: right;">
        <button onclick="window.print()" style="padding: 6px 16px; background: #0f172a; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 12px;">
            Print / Save PDF
        </button>
    </div>

    {{-- Main Header --}}
    <div class="header-container">
        <h1 class="header-title">{{ $report->title }}</h1>
        <div class="header-subtitle">Applied Tips Adjustments & Company Error Penalties Audit Sheet</div>

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
                    <span class="meta-label">Audit Status:</span>
                    <span class="meta-value" style="color: {{ $report->isValidated() ? '#166534' : '#b45309' }}">
                        {{ $report->isValidated() ? 'VALIDATED & LOCKED' : 'PENDING VALIDATION' }}
                    </span>
                </div>
                <div class="meta-col">
                    <span class="meta-label">Generated On:</span>
                    <span class="meta-value">{{ now()->format('d M, Y h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick KPI Cards --}}
    <div class="kpi-container">
        <div class="kpi-card">
            <div class="kpi-title">Add Adjustments (+)</div>
            <div class="kpi-val" style="color: #166534;">+ Rs. {{ number_format($totalAdd, 0) }}</div>
            <div style="font-size: 9.5px; color: #64748b;">{{ $adjustments->where('type', 'add')->count() }} staff entries</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Deduct Adjustments (-)</div>
            <div class="kpi-val" style="color: #991b1b;">- Rs. {{ number_format($totalDeduct, 0) }}</div>
            <div style="font-size: 9.5px; color: #64748b;">{{ $adjustments->where('type', 'deduct')->count() }} staff entries</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Company Error Penalties</div>
            <div class="kpi-val" style="color: #b45309;">- Rs. {{ number_format($totalErrorDeduction, 0) }}</div>
            <div style="font-size: 9.5px; color: #64748b;">{{ $errors->count() }} staff infractions</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Net Adjustment Payout</div>
            <div class="kpi-val" style="color: {{ ($totalAdd - $totalDeduct) >= 0 ? '#166534' : '#991b1b' }}">
                Rs. {{ number_format($totalAdd - $totalDeduct, 0) }}
            </div>
            <div style="font-size: 9.5px; color: #64748b;">Net adjustment fund</div>
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
                    <th style="width: 28px;" class="text-center">#</th>
                    <th style="width: 60px;" class="text-center">Status</th>
                    <th style="width: 65px;">Emp Code</th>
                    <th style="width: 130px;">Employee Name</th>
                    <th style="width: 90px;">Department</th>
                    <th style="width: 95px;">Designation</th>
                    <th style="width: 65px;" class="text-center">Type</th>
                    <th style="width: 80px;" class="text-right">Amount</th>
                    <th>Adjustment Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($adjustments as $idx => $adj)
                    <tr>
                        <td class="text-center font-bold">{{ $idx + 1 }}</td>
                        <td class="text-center">
                            @if($adj['status'] === 'updated')
                                <span class="badge-status status-updated">Updated</span>
                            @elseif($adj['status'] === 'cancelled')
                                <span class="badge-status" style="background:#e2e8f0; color:#475569;">Cancelled</span>
                            @else
                                <span class="badge-status status-pending">Pending</span>
                            @endif
                        </td>
                        <td class="font-bold">{{ $adj['employee_code'] }}</td>
                        <td>{{ $adj['employee_name'] }}</td>
                        <td>{{ $adj['department'] }}</td>
                        <td>{{ $adj['designation'] }}</td>
                        <td class="text-center">
                            @if($adj['type'] === 'add')
                                <span class="badge-add">+ ADD</span>
                            @else
                                <span class="badge-deduct">- DEDUCT</span>
                            @endif
                        </td>
                        <td class="text-right font-bold {{ $adj['type'] === 'add' ? 'badge-add' : 'badge-deduct' }}">
                            {{ $adj['type'] === 'add' ? '+' : '-' }} Rs. {{ number_format($adj['amount'], 0) }}
                        </td>
                        <td>
                            @if(!empty($adj['remarks']))
                                <div class="remarks-box">{{ $adj['remarks'] }}</div>
                            @else
                                <span class="empty-cell">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td colspan="7" class="text-right">Total Applied Adjustments Net:</td>
                    <td class="text-right" style="color: {{ ($totalAdd - $totalDeduct) >= 0 ? '#166534' : '#991b1b' }};">
                        Rs. {{ number_format($totalAdd - $totalDeduct, 0) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- SECTION 2: COMPANY ERROR PENALTIES --}}
    <div class="section-header" style="margin-top: 16px;">
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
                    <th style="width: 28px;" class="text-center">#</th>
                    <th style="width: 65px;">Emp Code</th>
                    <th style="width: 140px;">Employee Name</th>
                    <th style="width: 100px;">Department</th>
                    <th style="width: 100px;">Designation</th>
                    <th style="width: 65px;" class="text-center">Deduct %</th>
                    <th style="width: 85px;" class="text-right">Base Tips</th>
                    <th style="width: 90px;" class="text-right">Est. Deduction</th>
                    <th>Error Reason / Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($errors as $idx => $err)
                    <tr>
                        <td class="text-center font-bold">{{ $idx + 1 }}</td>
                        <td class="font-bold">{{ $err['employee_code'] }}</td>
                        <td>{{ $err['employee_name'] }}</td>
                        <td>{{ $err['department'] }}</td>
                        <td>{{ $err['designation'] }}</td>
                        <td class="text-center font-bold" style="color: #991b1b;">
                            - {{ $err['percentage_to_deduct'] }}%
                        </td>
                        <td class="text-right">
                            Rs. {{ number_format($err['base_tips'], 0) }}
                        </td>
                        <td class="text-right font-bold" style="color: #991b1b;">
                            - Rs. {{ number_format($err['estimated_deduction'], 0) }}
                        </td>
                        <td>
                            @if(!empty($err['reason']))
                                <div class="remarks-box" style="border-color: #fca5a5; background-color: #fff1f2; color: #881337;">
                                    {{ $err['reason'] }}
                                </div>
                            @else
                                <span class="empty-cell">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td colspan="7" class="text-right">Total Estimated Error Deduction:</td>
                    <td class="text-right" style="color: #991b1b;">
                        - Rs. {{ number_format($totalErrorDeduction, 0) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- Signatures --}}
    <div class="signatures-container">
        <div class="sig-box">Prepared By (HR)</div>
        <div class="sig-box">Checked By (Accounts)</div>
        <div class="sig-box">Audited By (Internal Audit)</div>
        <div class="sig-box">Approved By (Management)</div>
    </div>
</body>
</html>
