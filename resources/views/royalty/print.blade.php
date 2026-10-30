<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report->title }} - {{ $department }} Payout Sheet</title>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            size: A4 landscape;
            margin: 12mm 10mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
            width: 100%;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .header-container {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
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
            color: #111;
            margin: 0 0 6px 0;
        }

        .header-meta-box {
            margin-top: 8px;
            padding: 8px 14px;
            background-color: #fafafa;
            border: 1px solid #000;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .meta-primary-info {
            display: flex;
            align-items: center;
            gap: 24px;
            font-size: 12px;
            flex-wrap: wrap;
        }

        .meta-financial-summary {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 4px 12px;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            min-width: 260px;
            text-align: right;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }

        .fin-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            font-size: 11px;
            line-height: 1.35;
        }

        .fin-row.fin-total {
            border-top: 1px solid #cbd5e1;
            padding-top: 2px;
            margin-top: 2px;
            font-size: 12px;
        }

        .fin-label {
            font-weight: 600;
            color: #475569;
            text-align: left;
        }

        .fin-val {
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: #0f172a;
        }

        .fin-val.text-red {
            color: #dc2626;
        }

        .fin-val.text-green {
            color: #047857;
            font-size: 12.5px;
            font-weight: 800;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2px 0;
            font-size: 12px;
        }

        .meta-col {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .meta-label {
            font-weight: 600;
            color: #4b5563;
        }

        .meta-value {
            font-weight: bold;
            color: #111;
        }

        .text-red {
            color: #dc2626;
        }

        .font-payout {
            font-size: 13px;
            color: #047857;
            font-weight: 800;
        }

        table.sheet-table {
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border-spacing: 0;
            border: 1.5px solid #000 !important;
            margin-bottom: 20px;
        }

        table.sheet-table th,
        table.sheet-table td {
            border: 1px solid #000 !important;
            padding: 5px 4px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            font-size: 10.5px;
            line-height: 1.25;
        }

        table.sheet-table th {
            background-color: #f1f5f9 !important;
            font-weight: bold;
            font-size: 10px;
            text-align: center;
            text-transform: uppercase;
            padding: 6px 3px;
            border: 1px solid #000 !important;
        }

        table.sheet-table td.text-center {
            text-align: center;
        }

        table.sheet-table td.text-right {
            text-align: right;
        }

        .print-footer {
            margin-top: 16px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 4px;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 12mm 10mm;
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            table.sheet-table {
                width: 100% !important;
                max-width: 100% !important;
                page-break-inside: auto;
                border-collapse: collapse !important;
                border: 1.5px solid #000 !important;
            }
            table.sheet-table th,
            table.sheet-table td {
                border: 1px solid #000 !important;
            }
            table.sheet-table tr {
                page-break-inside: avoid;
            }
            table.sheet-table tfoot {
                display: table-row-group !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 6px; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 12px;">
                🖨️ Print Royalty Sheet
            </button>
            <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
                Close Window
            </button>
        </div>
        <div style="display: flex; align-items: center; gap: 12px; font-size: 12px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <label for="dept-select" style="font-weight: bold; color: #334155;">Filter Department:</label>
                <select id="dept-select" onchange="applyFilters()" style="padding: 6px 10px; border-radius: 4px; border: 1px solid #cbd5e1; font-size: 12px; background: #fff;">
                    <option value="All" {{ $department === 'All Departments' ? 'selected' : '' }}>All Departments ({{ $departments ? count($departments) : 0 }})</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <label for="desig-select" style="font-weight: bold; color: #334155;">Filter Designation:</label>
                <select id="desig-select" onchange="applyFilters()" style="padding: 6px 10px; border-radius: 4px; border: 1px solid #cbd5e1; font-size: 12px; background: #fff;">
                    <option value="All" {{ $designation === 'All Designations' ? 'selected' : '' }}>All Designations ({{ $designations ? count($designations) : 0 }})</option>
                    @foreach($designations as $desig)
                        <option value="{{ $desig }}" {{ $designation === $desig ? 'selected' : '' }}>{{ $desig }}</option>
                    @endforeach
                </select>
            </div>
            <script>
                function applyFilters() {
                    const dept = document.getElementById('dept-select').value;
                    const desig = document.getElementById('desig-select').value;
                    const params = new URLSearchParams();
                    if (dept && dept !== 'All') params.set('department', dept);
                    if (desig && desig !== 'All') params.set('designation', desig);
                    window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                }
            </script>
        </div>
    </div>

    <div class="header-container">
        <h1 class="header-title">{{ $report->title }}</h1>
        <div class="header-subtitle">
            DEPARTMENT: {{ strtoupper($department) }}
            @if($designation !== 'All Designations')
                | DESIGNATION: {{ strtoupper($designation) }}
            @endif
            PAYOUT SHEET
        </div>

        @php
            $sumMaxPayout = (float) $items->sum('monthly_max_payout');
            $sumTotalDeductions = (float) $items->sum('total_deduction_amount');
            $sumFinalPayout = (float) $items->sum('final_payout');
        @endphp

        <div class="header-meta-box">
            <div class="meta-primary-info">
                <div class="meta-col">
                    <span class="meta-label">Payout Period:</span>
                    <span class="meta-value">{{ ucfirst($report->month) }} {{ $report->year }}</span>
                </div>
                <div class="meta-col">
                    <span class="meta-label">Cutoff Date:</span>
                    <span class="meta-value">{{ \Carbon\Carbon::parse($report->cutoff_date)->format('d M, Y') }}</span>
                </div>
                <div class="meta-col">
                    <span class="meta-label">Total Staff:</span>
                    <span class="meta-value">{{ count($items) }} Eligible</span>
                </div>
            </div>

            <div class="meta-financial-summary">
                <div class="fin-row">
                    <span class="fin-label">Total Payout W/o Deductions:</span>
                    <span class="fin-val">Rs. {{ number_format($sumMaxPayout, 2) }}</span>
                </div>
                <div class="fin-row">
                    <span class="fin-label">Total Deductions:</span>
                    <span class="fin-val text-red">Rs. {{ number_format($sumTotalDeductions, 2) }}</span>
                </div>
                <div class="fin-row fin-total">
                    <span class="fin-label">Total Payout:</span>
                    <span class="fin-val text-green">Rs. {{ number_format($sumFinalPayout, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <table class="sheet-table">
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 7%;">Code</th>
                <th style="width: 16%;">Employee Name</th>
                <th style="width: 10%;">Department</th>
                <th style="width: 11%;">Designation</th>
                <th style="width: 8.5%;">Max Payout</th>
                <th style="width: 4%;">Absent</th>
                <th style="width: 4.5%;">Late/Early</th>
                <th style="width: 4%;">Thumb</th>
                <th style="width: 6.5%;">Attn Ded.</th>
                <th style="width: 6.5%;">Susp. Ded.</th>
                <th style="width: 4.5%;">Perf %</th>
                <th style="width: 6.5%;">Perf Ded.</th>
                <th style="width: 6.5%;">Total Ded.</th>
                <th style="width: 8.5%;">Net Payout</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ $item->employee_code }}</td>
                    <td style="font-weight: 600;">
                        {{ $item->employee_name }}
                        @if($item->is_exempt)
                            <span style="display: inline-block; font-size: 9px; background: #e0f2fe; color: #0369a1; padding: 0 4px; border-radius: 2px; margin-left: 2px;">EXEMPT</span>
                        @endif
                    </td>
                    <td>{{ $item->department ?? '-' }}</td>
                    <td>{{ $item->designation ?? '-' }}</td>
                    <td class="text-center">{{ number_format($item->monthly_max_payout, 0) }}</td>
                    <td class="text-center" style="{{ $item->absent_days > 0 ? 'color: #dc2626; font-weight: bold;' : '' }}">
                        {{ $item->absent_days + 0 }}
                    </td>
                    <td class="text-center" style="{{ $item->late_early_count > 0 ? 'color: #d97706;' : '' }}">
                        {{ $item->late_early_count }}
                    </td>
                    <td class="text-center" style="{{ $item->missing_thumb_count > 0 ? 'color: #d97706;' : '' }}">
                        {{ $item->missing_thumb_count }}
                    </td>
                    <td class="text-center">{{ number_format($item->attendance_deduction_amount, 0) }}</td>
                    <td class="text-center" style="{{ ($item->suspension_deduction_amount ?? 0) > 0 ? 'color: #dc2626; font-weight: bold;' : '' }}">
                        {{ number_format($item->suspension_deduction_amount ?? 0, 0) }}
                    </td>
                    <td class="text-center">{{ $item->performance_deduction_percent > 0 ? ($item->performance_deduction_percent + 0) . '%' : '-' }}</td>
                    <td class="text-center">{{ number_format($item->performance_deduction_amount, 0) }}</td>
                    <td class="text-center" style="color: #b91c1c; font-weight: 600;">{{ number_format($item->total_deduction_amount, 0) }}</td>
                    <td class="text-center" style="font-weight: bold; color: {{ $item->payout_status === 'Hold' ? '#b45309' : '#047857' }}; font-size: 11px;">
                        {{ number_format($item->final_payout, 2) }}
                        @if($item->payout_status === 'Hold')
                            <div style="font-size: 8.5px; color: #b91c1c; font-weight: bold; text-transform: uppercase;">[HELD]</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="text-center" style="padding: 20px; color: #64748b;">
                        No staff items found for this selection.
                    </td>
                </tr>
            @endforelse
            @if(count($items) > 0)
                <tr style="background: #f8fafc; font-weight: bold; page-break-inside: avoid;">
                    <td colspan="5" style="text-align: right; padding-right: 8px;">TOTAL ({{ count($items) }} Staff):</td>
                    <td class="text-center">{{ number_format($items->sum('monthly_max_payout'), 0) }}</td>
                    <td class="text-center">{{ $items->sum('absent_days') + 0 }}</td>
                    <td class="text-center">{{ $items->sum('late_early_count') }}</td>
                    <td class="text-center">{{ $items->sum('missing_thumb_count') }}</td>
                    <td class="text-center">{{ number_format($items->sum('attendance_deduction_amount'), 0) }}</td>
                    <td class="text-center">{{ number_format($items->sum('suspension_deduction_amount'), 0) }}</td>
                    <td class="text-center">-</td>
                    <td class="text-center">{{ number_format($items->sum('performance_deduction_amount'), 0) }}</td>
                    <td class="text-center" style="color: #b91c1c;">{{ number_format($items->sum('total_deduction_amount'), 0) }}</td>
                    <td class="text-center" style="color: #047857; font-size: 11.5px; font-weight: 800;">
                        Rs. {{ number_format($items->sum('final_payout'), 2) }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- <div class="footer-signatures">
        <div class="sig-line-block">
            <div class="sig-line"></div>
            <div class="sig-title">Prepared By (HR)</div>
        </div>
        <div class="sig-line-block">
            <div class="sig-line"></div>
            <div class="sig-title">Checked By (Finance)</div>
        </div>
        <div class="sig-line-block">
            <div class="sig-line"></div>
            <div class="sig-title">Approved By (Management)</div>
        </div>
    </div>

    <div class="print-footer">
        <div>Generated via Waldo Royalty Bonus Management &bull; Status: {{ strtoupper($report->status) }}</div>
        <div>Printed on {{ now()->format('d M, Y H:i:s') }}</div>
    </div> --}}

</body>
</html>
