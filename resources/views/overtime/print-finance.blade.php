<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Finance Employee OT Summary - {{ $periodLabel }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        @page { size: A4 landscape; margin: 10mm 12mm; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #0f172a; background: #f1f5f9; margin: 0; padding: 20px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .toolbar { max-width: 1120px; margin: 0 auto 14px auto; display: flex; gap: 10px; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; color: #fff; }
        .btn-primary { background: #0284c7; }
        .btn-secondary { background: #64748b; }
        .sheet { max-width: 1120px; margin: 0 auto; background: #fff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 22px 26px; }
        .header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 16px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 2px 0 0 0; font-size: 12px; color: #475569; }
        table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; }
        th { background: #f8fafc; font-weight: 700; text-align: left; font-size: 10.5px; text-transform: uppercase; color: #334155; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tfoot td { background: #f1f5f9; font-weight: 700; border-top: 2px solid #94a3b8; }
        .text-right { text-align: right; font-variant-numeric: tabular-nums; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .col-normal { background: #eff6ff; }
        .col-special { background: #fffbeb; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .sheet { max-width: 100%; border: none; padding: 0; }
        }
    </style>
</head>

<body>
    <div class="toolbar no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Finance OT Summary</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    <div class="sheet">
        <div class="header">
            <div>
                <h1>Finance Employee OT Summary</h1>
                <p>{{ $periodLabel }} &bull; Normal (×1.5) &amp; Special (×2) include adjustments</p>
            </div>
            <div style="text-align: right; font-size: 11.5px; color: #475569;">
                Printed on: {{ now()->format('d M, Y h:i A') }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 4%;">#</th>
                    <th style="width: 9%;">Code</th>
                    <th style="width: 21%;">Employee Name</th>
                    <th style="width: 15%;">Department</th>
                    <th style="width: 15%;">Designation</th>
                    <th class="text-right col-normal" style="width: 8%;">Normal Adj.</th>
                    <th class="text-right col-normal" style="width: 8%;">Normal (×1.5)</th>
                    <th class="text-right col-special" style="width: 8%;">Special Adj.</th>
                    <th class="text-right col-special" style="width: 8%;">Special (×2)</th>
                    <th class="text-right" style="width: 8%;">Total OT</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employeeBlockSummary as $index => $emp)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $emp['employee_code'] }}</td>
                        <td class="font-bold">{{ $emp['name'] }}</td>
                        <td>{{ $emp['department'] }}</td>
                        <td>{{ $emp['designation'] }}</td>
                        <td class="text-right col-normal">{{ $emp['normal_adj'] != 0 ? ($emp['normal_adj'] > 0 ? '+' : '').number_format($emp['normal_adj'], 2) : '0.00' }}</td>
                        <td class="text-right col-normal font-bold">{{ number_format($emp['normal_hours'], 2) }}</td>
                        <td class="text-right col-special">{{ $emp['special_adj'] != 0 ? ($emp['special_adj'] > 0 ? '+' : '').number_format($emp['special_adj'], 2) : '0.00' }}</td>
                        <td class="text-right col-special font-bold">{{ number_format($emp['special_hours'], 2) }}</td>
                        <td class="text-right font-bold">{{ number_format($emp['total_hours'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">No OT entries or adjustments recorded for {{ $periodLabel }}.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($employeeBlockSummary->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right">Totals ({{ $employeeBlockSummary->count() }} employees)</td>
                        <td class="text-right">{{ number_format($employeeBlockSummary->sum('normal_adj'), 2) }}</td>
                        <td class="text-right">{{ number_format($employeeBlockSummary->sum('normal_hours'), 2) }}</td>
                        <td class="text-right">{{ number_format($employeeBlockSummary->sum('special_adj'), 2) }}</td>
                        <td class="text-right">{{ number_format($employeeBlockSummary->sum('special_hours'), 2) }}</td>
                        <td class="text-right">{{ number_format($employeeBlockSummary->sum('total_hours'), 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</body>

</html>
