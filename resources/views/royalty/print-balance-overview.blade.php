<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Royalty Budget Balance Overview Sheet</title>
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
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 10px;
            color: #0f172a;
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
            font-size: 17px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
            color: #0f172a;
        }

        .header-subtitle {
            font-size: 11.5px;
            font-weight: 600;
            color: #334155;
            margin: 0;
        }

        .meta-box {
            margin: 10px 0 14px 0;
            padding: 8px 12px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #334155;
        }

        table.sheet-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10px;
            border: 1px solid #000;
        }

        table.sheet-table th, table.sheet-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }

        table.sheet-table th {
            background-color: #f1f5f9 !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            text-align: center;
            letter-spacing: 0.3px;
        }

        table.sheet-table tfoot td {
            font-weight: 700;
            background-color: #f1f5f9 !important;
            border-top: 1.5px solid #000;
            font-size: 10px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-num { font-variant-numeric: tabular-nums; }

        .print-actions {
            margin-bottom: 12px;
            text-align: right;
        }

        .btn-print {
            background: #0f172a;
            color: #fff;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-row-group !important;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <button onclick="window.print()" class="btn-print">Print Sheet</button>
    </div>

    <div class="header-container">
        <h1 class="header-title">Royalty Budget Balance Overview Sheet</h1>
        <div class="header-subtitle">
            12-Month Rolling Budget & Consumed Balance Breakdown
            @if($report)
                - Scope: {{ $report->title }} ({{ ucfirst($report->month) }} {{ $report->year }})
            @else
                - Scope: All Active Cumulative 12-Month Balances
            @endif
        </div>
    </div>

    @php
        $sumBudget = array_sum(array_column($records, 'annual_budget'));
        $sumConsumed = array_sum(array_column($records, 'consumed_budget'));
        $sumRemaining = array_sum(array_column($records, 'remaining_budget'));
        $avgUtil = $sumBudget > 0 ? round(($sumConsumed / $sumBudget) * 100, 1) : 0;
    @endphp

    <div class="meta-box">
        <div>
            <strong>Department:</strong> {{ $department }}
        </div>
        <div>
            <strong>Total Staff:</strong> {{ count($records) }}
        </div>
        <div>
            <strong>Total Budget:</strong> Rs. {{ number_format($sumBudget, 2) }}
        </div>
        <div>
            <strong>Total Consumed:</strong> Rs. {{ number_format($sumConsumed, 2) }} ({{ $avgUtil }}%)
        </div>
        <div>
            <strong>Total Remaining:</strong> Rs. {{ number_format($sumRemaining, 2) }}
        </div>
    </div>

    <table class="sheet-table">
        <thead>
            <tr>
                <th style="width: 25px;">#</th>
                <th style="width: 70px;">Code</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Designation</th>
                <th style="width: 80px;" class="text-center">Join Date</th>
                <th style="width: 150px;" class="text-center">12-Mo Cycle</th>
                <th style="width: 90px;" class="text-right">12-Mo Budget</th>
                <th style="width: 90px;" class="text-right">Utilized</th>
                <th style="width: 95px;" class="text-right">Remaining</th>
                <th style="width: 60px;" class="text-center">Usage</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $r)
                <tr>
                    <td class="text-center font-num">{{ $index + 1 }}</td>
                    <td class="text-center font-num">{{ $r['employee_code'] }}</td>
                    <td><strong>{{ $r['employee_name'] }}</strong></td>
                    <td>{{ $r['department'] }}</td>
                    <td>{{ $r['designation'] }}</td>
                    <td class="text-center">{{ $r['join_date'] ?? $r['joining_date'] ?? '-' }}</td>
                    <td class="text-center" style="font-size: 8.5px;">{{ $r['tenure_cycle_label'] ?? (($r['tenure_start'] ?? '-') . ' - ' . ($r['tenure_end'] ?? '-')) }}</td>
                    <td class="text-right font-num">Rs. {{ number_format($r['annual_budget'], 2) }}</td>
                    <td class="text-right font-num">Rs. {{ number_format($r['consumed_budget'], 2) }}</td>
                    <td class="text-right font-num"><strong>Rs. {{ number_format($r['remaining_budget'], 2) }}</strong></td>
                    <td class="text-center font-num">{{ $r['utilization_rate'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 14px;">No records found matching criteria.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">Total:</td>
                <td class="text-right font-num">Rs. {{ number_format($sumBudget, 2) }}</td>
                <td class="text-right font-num">Rs. {{ number_format($sumConsumed, 2) }}</td>
                <td class="text-right font-num">Rs. {{ number_format($sumRemaining, 2) }}</td>
                <td class="text-center font-num">{{ $avgUtil }}%</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

