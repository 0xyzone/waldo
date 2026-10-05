<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EOM & GOM Report - {{ $monthName }} {{ $evaluatedYear ?? $report->year }}</title>
    <x-favicon />
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 15mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11.5px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }

        .header-container {
            text-align: center;
            margin-bottom: 14px;
            border-bottom: 2px solid #111;
            padding-bottom: 10px;
        }

        .header-title {
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }

        .header-subtitle {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin: 0 0 8px 0;
        }

        .header-meta-box {
            margin-top: 10px;
            padding: 8px 14px;
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
            gap: 6px;
        }

        .meta-label {
            font-weight: bold;
            color: #475569;
        }

        .meta-value {
            font-weight: 700;
            color: #0f172a;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 15px;
        }

        table.report-table th,
        table.report-table td {
            border: 1px solid #334155;
            padding: 9px 8px;
            vertical-align: middle;
        }

        table.report-table th {
            background-color: #f1f5f9;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.3px;
            text-align: left;
        }

        table.report-table tbody tr {
            page-break-inside: avoid;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .badge-eom {
            display: inline-block;
            padding: 2px 6px;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            border-radius: 3px;
            font-weight: 800;
            font-size: 9.5px;
            text-transform: uppercase;
        }

        .badge-gom {
            display: inline-block;
            padding: 2px 6px;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-radius: 3px;
            font-weight: 800;
            font-size: 9.5px;
            text-transform: uppercase;
        }

        .sig-box {
            display: inline-block;
            width: 100%;
            min-height: 28px;
        }


        .print-footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
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

    <div class="no-print" style="margin-bottom: 15px; display: flex; gap: 10px;">
        <button onclick="window.print()" style="padding: 8px 18px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 12px;">
            🖨️ Print Monthly Sheet
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
            Close Window
        </button>
    </div>

    @php
        $e1 = $entries->firstWhere('entry_number', 1);
        $e2 = $entries->firstWhere('entry_number', 2);
        $e3 = $entries->firstWhere('entry_number', 3);

        $rows = [
            [
                'cat' => 'EOM',
                'badge_class' => 'badge-eom',
                'category_label' => 'Employee of the Month (EOM)',
                'department' => $e1?->eomEmployee1?->department?->name ?? 'Gaming / Slot',
                'emp' => $e1?->eomEmployee1,
                'remarks' => $e1?->eom_remarks_1,
            ],
            [
                'cat' => 'EOM',
                'badge_class' => 'badge-eom',
                'category_label' => 'Employee of the Month (EOM)',
                'department' => $e1?->eomEmployee2?->department?->name ?? 'Gaming / Slot',
                'emp' => $e1?->eomEmployee2,
                'remarks' => $e1?->eom_remarks_2,
            ],
            [
                'cat' => 'GOM',
                'badge_class' => 'badge-gom',
                'category_label' => 'Grooming of the Month (GOM)',
                'department' => $e1?->gomEmployee1?->department?->name ?? 'Gaming / Slot',
                'emp' => $e1?->gomEmployee1,
                'remarks' => $e1?->gom_remarks_1,
            ],
            [
                'cat' => 'EOM',
                'badge_class' => 'badge-eom',
                'category_label' => 'Employee of the Month (EOM)',
                'department' => $e2?->department_name ?? ($e2?->department?->name ?? 'Allowed Department'),
                'emp' => $e2?->eomEmployee1,
                'remarks' => $e2?->eom_remarks_1,
            ],
            [
                'cat' => 'GOM',
                'badge_class' => 'badge-gom',
                'category_label' => 'Grooming of the Month (GOM)',
                'department' => $e3?->department_name ?? ($e3?->department?->name ?? 'Allowed Department'),
                'emp' => $e3?->gomEmployee1,
                'remarks' => $e3?->gom_remarks_1,
            ],
        ];
    @endphp

    <div class="header-container">
        <h1 class="header-title">MONTHLY EOM & GOM REPORT</h1>
        <div class="header-subtitle">Employee of the Month (EOM) & Grooming of the Month (GOM) Recognition</div>

        <div class="header-meta-box">
            <div class="meta-row">
                <div class="meta-col">
                    <span class="meta-label">Evaluated Period:</span>
                    <span class="meta-value">{{ $monthName }} {{ $evaluatedYear ?? $report->year }} <span style="font-size: 10px; font-weight: 500; color: #64748b;">({{ $releaseLabel ?? ($releaseMonth . ' ' . $report->year) }})</span></span>
                </div>
            </div>
        </div>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 45px;" class="text-center">S.N.</th>
                <th style="width: 200px;">Award Category</th>
                <th style="width: 200px;">Department</th>
                <th style="width: 110px;" class="text-center">Emp Code</th>
                <th style="width: 260px;">Employee Name</th>
                <th style="width: 220px;">Designation</th>
                <th style="width: 160px;" class="text-center">Signature</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $idx => $r)
                <tr>
                    <td class="text-center font-bold">{{ $idx + 1 }}</td>
                    <td>
                        <span class="{{ $r['badge_class'] }}">{{ $r['cat'] }}</span>
                        <div style="font-size: 9.5px; color: #475569; margin-top: 2px;">{{ $r['category_label'] }}</div>
                    </td>
                    <td class="font-bold">{{ $r['department'] }}</td>
                    <td class="text-center font-bold font-mono">
                        {{ $r['emp']?->employee_code ?: '-' }}
                    </td>
                    <td style="font-weight: 600;">
                        {{ $r['emp']?->name ?: 'Not Assigned' }}
                    </td>
                    <td>
                        {{ $r['emp']?->designation?->name ?: '-' }}
                    </td>
                    <td class="text-center">
                        <span class="sig-box"></span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="print-footer">
        Waldo HRMS Portal • EOM/GOM Annual Cycle {{ $report->year }} • Printed on: {{ now()->format('d M, Y h:i A') }}
    </div>

</body>
</html>
