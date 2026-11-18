<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Block-wise OT Summary - {{ $periodLabel }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        @page { size: A4 landscape; margin: 10mm 12mm; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12.5px; color: #0f172a; background: #f1f5f9; margin: 0; padding: 20px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .toolbar { max-width: 1060px; margin: 0 auto 14px auto; display: flex; gap: 10px; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; color: #fff; }
        .btn-primary { background: #0284c7; }
        .btn-secondary { background: #64748b; }
        .sheet { max-width: 1060px; margin: 0 auto; background: #fff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 22px 26px; }
        .header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 18px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 2px 0 0 0; font-size: 12px; color: #475569; }
        h2 { font-size: 14px; margin: 18px 0 8px 0; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 7px 10px; }
        th { background: #f8fafc; font-weight: 700; text-align: left; font-size: 11px; text-transform: uppercase; color: #334155; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        .text-right { text-align: right; font-variant-numeric: tabular-nums; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .sheet { max-width: 100%; border: none; padding: 0; }
        }
    </style>
</head>

<body>
    <div class="toolbar no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Block-wise OT</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    <div class="sheet">
        <div class="header">
            <div>
                <h1>Block-wise Overtime Summary</h1>
                <p>{{ $periodLabel }} &bull; Normal OT (×1.5) &amp; Special OT (×2)</p>
            </div>
            <div style="text-align: right; font-size: 11.5px; color: #475569;">
                Printed on: {{ now()->format('d M, Y h:i A') }}
            </div>
        </div>

        <h2>1. Block-wise Totals</h2>
        <table>
            <thead>
                <tr>
                    <th>Block</th>
                    <th class="text-center">Staff Count</th>
                    <th class="text-right">Normal OT (×1.5)</th>
                    <th class="text-right">Special OT (×2)</th>
                    <th class="text-right">Total Hours</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($blockSummary as $row)
                    <tr>
                        <td class="font-bold">{{ $row['block'] }}</td>
                        <td class="text-center">{{ $row['employees_count'] }}</td>
                        <td class="text-right">{{ number_format($row['normal_hours'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['special_hours'], 2) }}</td>
                        <td class="text-right font-bold">{{ number_format($row['total_hours'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No OT entries recorded for {{ $periodLabel }}.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>2. Employee-wise &amp; Block Breakdown</h2>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Employee Name</th>
                    <th>Department</th>
                    @foreach ($allBlocks as $blk)
                        <th class="text-right">{{ $blk }}</th>
                    @endforeach
                    <th class="text-right">Normal Adj.</th>
                    <th class="text-right">Normal (×1.5)</th>
                    <th class="text-right">Special Adj.</th>
                    <th class="text-right">Special (×2)</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employeeBlockSummary as $emp)
                    <tr>
                        <td>{{ $emp['employee_code'] }}</td>
                        <td class="font-bold">{{ $emp['name'] }}</td>
                        <td>{{ $emp['department'] }}</td>
                        @foreach ($allBlocks as $blk)
                            <td class="text-right">{{ isset($emp['blocks'][$blk]) ? number_format($emp['blocks'][$blk], 2) : '—' }}</td>
                        @endforeach
                        <td class="text-right">{{ $emp['normal_adj'] != 0 ? ($emp['normal_adj'] > 0 ? '+' : '').number_format($emp['normal_adj'], 2) : '0.00' }}</td>
                        <td class="text-right">{{ number_format($emp['normal_hours'], 2) }}</td>
                        <td class="text-right">{{ $emp['special_adj'] != 0 ? ($emp['special_adj'] > 0 ? '+' : '').number_format($emp['special_adj'], 2) : '0.00' }}</td>
                        <td class="text-right">{{ number_format($emp['special_hours'], 2) }}</td>
                        <td class="text-right font-bold">{{ number_format($emp['total_hours'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ 8 + count($allBlocks) }}" class="text-center">No employee OT entries recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
