<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $employee->name }} ({{ $employee->employee_code }}) - Held Royalty Bonus Sheet</title>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            size: A4 portrait;
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

        .meta-box {
            margin: 12px 0 16px 0;
            padding: 10px 14px;
            background-color: #fafafa;
            border: 1px solid #000;
            border-radius: 4px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            font-size: 11.5px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 2px;
        }

        .meta-val {
            font-weight: bold;
            color: #000;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }

        .stat-card {
            border: 1px solid #000;
            padding: 8px 12px;
            background: #fdfdfd;
            border-radius: 4px;
            text-align: center;
        }

        .stat-card .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 600;
            color: #444;
        }

        .stat-card .stat-value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 3px;
            color: #000;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1.5px solid #000;
            padding-bottom: 4px;
            margin: 16px 0 8px 0;
        }

        table.sheet-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10.5px;
        }

        table.sheet-table th, table.sheet-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: middle;
        }

        table.sheet-table th {
            background-color: #f0f0f0 !important;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9.5px;
            text-align: center;
        }

        table.sheet-table tfoot td {
            font-weight: bold;
            background-color: #f5f5f5 !important;
            border-top: 2px solid #000;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border: 1px solid #000;
            border-radius: 3px;
            font-size: 9px;
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
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <button onclick="window.print()" class="btn-print">Print Sheet</button>
    </div>

    <div class="header-container">
        <h1 class="header-title">Royalty Bonus - Held Balance & Release Statement</h1>
        <div class="header-subtitle">Official Statement of Withheld Royalty Bonus & Disbursements</div>
    </div>

    <div class="meta-box">
        <div class="meta-grid">
            <div class="meta-item">
                <span class="meta-label">Employee Name</span>
                <span class="meta-val">{{ $employee->name }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Employee Code</span>
                <span class="meta-val font-mono">{{ $employee->employee_code }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Department</span>
                <span class="meta-val">{{ $employee->department?->name ?? 'N/A' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Designation</span>
                <span class="meta-val">{{ $employee->designation?->name ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Amount Held / Deducted</div>
            <div class="stat-value font-mono">Rs. {{ number_format($totalHeld, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Released to Date</div>
            <div class="stat-value font-mono">Rs. {{ number_format($totalReleased, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Remaining Balance Payable</div>
            <div class="stat-value font-mono">Rs. {{ number_format($remainingBalance, 2) }}</div>
        </div>
    </div>

    <div class="section-title">1. Monthly Held & Deducted Bonus Breakdown</div>
    <table class="sheet-table">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Report Month / Title</th>
                <th style="width: 80px;">Tenure Month</th>
                <th style="width: 90px;" class="text-right">Max Payout</th>
                <th style="width: 90px;" class="text-right">Floor Min</th>
                <th style="width: 90px;" class="text-right">Net Payout</th>
                <th style="width: 95px;" class="text-right">Amount Held</th>
            </tr>
        </thead>
        <tbody>
            @forelse($heldItems as $index => $item)
                @php
                    $isFullHold = ($item->payout_status === 'Hold');
                    $amtHeld = $isFullHold 
                        ? (float) $item->monthly_max_payout 
                        : max(0, (float) $item->monthly_max_payout - (float) $item->final_payout);
                @endphp
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->report?->title ?? 'Report #' . $item->royalty_report_id }}</strong>
                        <span style="font-size: 9.5px; color: #555;">({{ ucfirst($item->report?->month ?? '') }} {{ $item->report?->year ?? '' }})</span>
                    </td>
                    <td class="text-center font-mono">M{{ $item->tenure_month_number }}/12</td>
                    <td class="text-right font-mono">Rs. {{ number_format($item->monthly_max_payout, 2) }}</td>
                    <td class="text-right font-mono">Rs. {{ number_format($item->monthly_min_payout, 2) }}</td>
                    <td class="text-right font-mono">Rs. {{ number_format($item->final_payout, 2) }}</td>
                    <td class="text-right font-mono"><strong>Rs. {{ number_format($amtHeld, 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 12px;">No held or deducted bonus records found for this employee.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right">Total Held:</td>
                <td class="text-right font-mono">Rs. {{ number_format($totalHeld, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">2. Release & Disbursement History</div>
    <table class="sheet-table">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th style="width: 90px;" class="text-center">Release Date</th>
                <th style="width: 100px;" class="text-right">Amount Released</th>
                <th>Remarks / Reason</th>
                <th style="width: 110px;" class="text-center">Released By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employee->royaltyReleases as $rIndex => $rel)
                <tr>
                    <td class="text-center font-mono">{{ $rIndex + 1 }}</td>
                    <td class="text-center font-mono">{{ $rel->released_date?->format('d M Y') ?? 'N/A' }}</td>
                    <td class="text-right font-mono"><strong>Rs. {{ number_format($rel->amount, 2) }}</strong></td>
                    <td>{{ $rel->remarks ?: '-' }}</td>
                    <td class="text-center">{{ $rel->creator?->name ?? 'System' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 10px;">No release transactions recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right">Total Released:</td>
                <td class="text-right font-mono">Rs. {{ number_format($totalReleased, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 40px; display: flex; justify-content: space-between; font-size: 11px;">
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

