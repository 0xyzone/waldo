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
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
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
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }

        .header-subtitle {
            font-size: 12px;
            font-weight: 600;
            color: #222;
            margin: 0;
        }

        .meta-box {
            margin: 10px 0 14px 0;
            padding: 8px 12px;
            background-color: #fafafa;
            border: 1px solid #000;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
        }

        table.sheet-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5px;
        }

        table.sheet-table th, table.sheet-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            vertical-align: middle;
        }

        table.sheet-table th {
            background-color: #f0f0f0 !important;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            text-align: center;
        }

        table.sheet-table tfoot td {
            font-weight: bold;
            background-color: #f5f5f5 !important;
            border-top: 2px solid #000;
            font-size: 10px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

        .badge {
            display: inline-block;
            padding: 2px 4px;
            border: 1px solid #000;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .print-actions {
            margin-bottom: 12px;
            text-align: right;
        }

        .btn-print {
            background: #111827;
            color: #fff;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: bold;
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
                <th style="width: 65px;">Code</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Designation</th>
                <th style="width: 75px;" class="text-center">Join Date</th>
                <th style="width: 140px;" class="text-center">12-Mo Cycle</th>
                <th style="width: 80px;" class="text-right">12-Mo Budget</th>
                <th style="width: 80px;" class="text-right">Utilized</th>
                <th style="width: 85px;" class="text-right">Remaining</th>
                <th style="width: 55px;" class="text-center">Usage</th>
                <th style="width: 70px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $r)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="text-center font-mono">{{ $r['employee_code'] }}</td>
                    <td><strong>{{ $r['employee_name'] }}</strong></td>
                    <td>{{ $r['department'] }}</td>
                    <td>{{ $r['designation'] }}</td>
                    <td class="text-center font-mono">{{ $r['joining_date'] }}</td>
                    <td class="text-center font-mono" style="font-size: 8.5px;">{{ $r['tenure_cycle_label'] }}</td>
                    <td class="text-right font-mono">Rs. {{ number_format($r['annual_budget'], 2) }}</td>
                    <td class="text-right font-mono">Rs. {{ number_format($r['consumed_budget'], 2) }}</td>
                    <td class="text-right font-mono"><strong>Rs. {{ number_format($r['remaining_budget'], 2) }}</strong></td>
                    <td class="text-center font-mono">{{ $r['utilization_rate'] }}%</td>
                    <td class="text-center">
                        @if($r['status'] === 'Exhausted')
                            <span class="badge" style="background: #fee2e2;">Exhausted</span>
                        @elseif($r['status'] === 'Low Budget')
                            <span class="badge" style="background: #fef3c7;">Low</span>
                        @else
                            <span class="badge" style="background: #dcfce7;">Healthy</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 14px;">No records found matching criteria.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">Total:</td>
                <td class="text-right font-mono">Rs. {{ number_format($sumBudget, 2) }}</td>
                <td class="text-right font-mono">Rs. {{ number_format($sumConsumed, 2) }}</td>
                <td class="text-right font-mono">Rs. {{ number_format($sumRemaining, 2) }}</td>
                <td class="text-center font-mono">{{ $avgUtil }}%</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px; display: flex; justify-content: space-between; font-size: 11px;">
        <div style="border-top: 1px solid #000; width: 180px; text-align: center; padding-top: 4px;">
            Prepared By (HR)
        </div>
        <div style="border-top: 1px solid #000; width: 180px; text-align: center; padding-top: 4px;">
            Checked By (Finance)
        </div>
        <div style="border-top: 1px solid #000; width: 180px; text-align: center; padding-top: 4px;">
            Approved By (Management)
        </div>
    </div>
</body>
</html>
