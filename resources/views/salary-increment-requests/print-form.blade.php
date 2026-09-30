<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ ($isRecord && $record) ? ($record->request_number . ' - Salary Increment Form') : 'Salary Increment Authorization Form (4x on A4)' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            color: #0f172a;
            background-color: #f1f5f9;
        }

        body {
            padding: 16px 0;
        }

        /* Screen-only Toolbar */
        .screen-toolbar {
            max-width: 190mm;
            margin: 0 auto 12px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .toolbar-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 16px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #0284c7;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #0369a1;
        }

        .btn-secondary {
            background-color: #64748b;
            color: #ffffff;
        }
        .btn-secondary:hover {
            background-color: #475569;
        }

        .copies-select {
            padding: 5px 8px;
            font-size: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
        }

        /* Full A4 Sheet (Holding 4 Identical Slips Vertically) */
        .a4-sheet {
            width: 190mm;
            margin: 0 auto 16px auto;
            background: #ffffff;
            border: 1px solid #94a3b8;
            border-radius: 4px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            padding: 3mm 4mm;
            display: flex;
            flex-direction: column;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .a4-sheet:not(.a4-last-sheet) {
            page-break-after: always;
            break-after: page;
        }

        .a4-last-sheet,
        .a4-sheet:last-child {
            page-break-after: avoid !important;
            break-after: avoid !important;
            margin-bottom: 0 !important;
        }

        /* Single Executive Slip (1 of 4) */
        .executive-slip {
            border: 1.5px solid #0f172a;
            border-radius: 3px;
            padding: 3mm 5mm 2.5mm 5mm;
            background: #ffffff;
            height: 55mm;
            max-height: 55mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            box-sizing: border-box;
        }

        /* Slip Header */
        .slip-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 2px;
            margin-bottom: 3px;
        }

        .slip-brand {
            display: flex;
            align-items: baseline;
            gap: 10px;
        }

        .casino-title {
            font-size: 13.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #0f172a;
            margin: 0;
            line-height: 1;
        }

        .doc-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #475569;
            margin: 0;
            line-height: 1;
        }

        .slip-meta-group {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .meta-entry {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            font-size: 9.5px;
        }

        .meta-label {
            font-weight: 800;
            color: #1e293b;
        }

        .meta-val {
            border-bottom: 1.3px solid #0f172a;
            font-weight: 700;
            color: #0f172a;
            padding: 0 4px;
            min-width: 25mm;
            line-height: 14px;
        }

        .font-mono {
            font-family: monospace;
            font-size: 10.5px;
        }

        /* Wide Full-Width Data Tables */
        .wide-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #0f172a;
            margin-bottom: 2.5px;
        }

        .wide-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 2px 5px;
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #334155;
            text-align: left;
            line-height: 1.1;
        }

        .wide-table td {
            border: 1px solid #cbd5e1;
            padding: 2.5px 5px;
            font-size: 9.5px;
            vertical-align: middle;
            color: #0f172a;
            height: 16px;
            line-height: 1.15;
        }

        .wide-table td.bold {
            font-weight: 800;
        }

        .wide-table td.text-center {
            text-align: center;
        }

        .wide-table td.text-right {
            text-align: right;
        }

        .wide-table td.success {
            color: #15803d;
            font-weight: 800;
        }

        .wide-table th.center,
        .wide-table td.center {
            text-align: center;
        }

        /* Checkbox styling */
        .box-sq {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1.3px solid #0f172a;
            border-radius: 2px;
            text-align: center;
            line-height: 9px;
            font-size: 8px;
            font-weight: bold;
            background: #ffffff;
            vertical-align: middle;
            margin-right: 2px;
        }

        .decision-label {
            display: inline-flex;
            align-items: center;
            font-size: 9px;
            font-weight: 700;
            color: #1e293b;
        }

        /* Management Sign & Date Bottom Row */
        .slip-bottom-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            padding-top: 1px;
        }

        .sign-col {
            flex: 1.4;
            display: flex;
            flex-direction: column;
        }

        .sign-open-space {
            height: 9mm;
            /* Generous area for official pen signature & rubber seal */
        }

        .sign-underline {
            border-bottom: 1.3px solid #0f172a;
            margin-bottom: 2px;
        }

        .sign-title {
            font-size: 8px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1;
        }

        .date-col {
            flex: 1;
            max-width: 52mm;
            display: flex;
            align-items: flex-end;
            gap: 5px;
            padding-bottom: 2px;
        }

        .date-lbl {
            font-size: 9.5px;
            font-weight: 800;
            color: #0f172a;
            white-space: nowrap;
            line-height: 16px;
        }

        .date-underline {
            flex: 1;
            border-bottom: 1.3px solid #0f172a;
            height: 16px;
            line-height: 16px;
            font-size: 9.5px;
            font-weight: 700;
            padding: 0 4px;
            color: #0f172a;
            text-align: center;
        }

        /* Cut Divider (Between Slips) */
        .cut-divider {
            height: 4mm;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1mm 0;
            box-sizing: border-box;
        }

        .cut-line {
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            border-top: 1.2px dashed #94a3b8;
        }

        .cut-badge {
            position: relative;
            background: #ffffff;
            padding: 0 8px;
            font-size: 7.5px;
            font-weight: 700;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* PRINT OPTIMIZATION — 100% ELIMINATES BLANK PAGE */
        @media print {
            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
            }

            .screen-toolbar {
                display: none !important;
            }

            .a4-sheet {
                margin: 0 auto !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 190mm !important;
                height: auto !important;
                max-height: none !important;
                min-height: 0 !important;
                overflow: visible !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                display: block !important;
            }

            .a4-sheet:not(.a4-last-sheet) {
                page-break-after: always !important;
                break-after: page !important;
            }

            .a4-sheet.a4-last-sheet,
            .a4-sheet:last-child {
                page-break-after: avoid !important;
                break-after: avoid !important;
                margin-bottom: 0 !important;
                padding-bottom: 0 !important;
            }
        }
    </style>
</head>
<body>

    {{-- Screen Toolbar --}}
    <div class="screen-toolbar">
        <div class="toolbar-title">
            <span>📄</span>
            <span>{{ ($isRecord && $record) ? ('Salary Increment Request: ' . $record->request_number) : 'Blank Salary Increment Authorization Slips (4 Slips on 1x A4 Sheet)' }}</span>
        </div>
        <div class="toolbar-actions">
            @if(! $isRecord)
                <label style="font-size: 11px; font-weight: 600; color: #475569;">
                    A4 Sheets:
                    <select class="copies-select" onchange="window.location.search = '?sheets=' + this.value">
                        <option value="1" {{ $sheets == 1 ? 'selected' : '' }}>1 A4 Sheet (4 Slips)</option>
                        <option value="2" {{ $sheets == 2 ? 'selected' : '' }}>2 A4 Sheets (8 Slips)</option>
                        <option value="5" {{ $sheets == 5 ? 'selected' : '' }}>5 A4 Sheets (20 Slips)</option>
                        <option value="10" {{ $sheets == 10 ? 'selected' : '' }}>10 A4 Sheets (40 Slips)</option>
                    </select>
                </label>
            @endif
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Print Slips
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                Close
            </button>
        </div>
    </div>

    {{-- A4 Page(s) --}}
    @for($sheetIndex = 1; $sheetIndex <= $sheets; $sheetIndex++)
        <div class="a4-sheet {{ $sheetIndex === (int)$sheets ? 'a4-last-sheet' : '' }}">

            @for($slipIndex = 1; $slipIndex <= 4; $slipIndex++)
                {{-- Single Executive Slip (1 of 4) --}}
                <div class="executive-slip">

                    {{-- Header Top Bar --}}
                    <div class="slip-top-bar">
                        <div class="slip-brand">
                            <span class="casino-title">WALDO CASINO</span>
                            <span class="doc-title">SALARY INCREMENT AUTHORIZATION</span>
                        </div>
                        <div class="slip-meta-group">
                            <div class="meta-entry">
                                <span class="meta-label">Ref #:</span>
                                <span class="meta-val font-mono">{{ $record?->request_number ?? '' }}</span>
                            </div>
                            <div class="meta-entry">
                                <span class="meta-label">Date:</span>
                                <span class="meta-val">{{ $record?->date_requested?->format('d / m / Y') ?? '' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Table 1: Employee Particulars & Dates --}}
                    <table class="wide-table">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Employee Code</th>
                                <th style="width: 35%;">Employee Name</th>
                                <th style="width: 22%;">Date of Joining</th>
                                <th style="width: 23%;">Effective Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-mono bold">{{ $record?->employee_id ?? '' }}</td>
                                <td class="bold">{{ $record?->employee?->name ?? '' }}</td>
                                <td>{{ $record?->employee?->join_date_formatted ?? '' }}</td>
                                <td class="bold" style="color: #0284c7;">{{ $record?->date_applicable?->format('d / m / Y') ?? '' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Table 2: Salary Proposal & Management Decision --}}
                    <table class="wide-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Current Salary</th>
                                <th style="width: 25%;">Proposed Salary</th>
                                <th style="width: 25%;">Increment Amount</th>
                                <th style="width: 25%;" class="center">Management Decision</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $record && $record->current_salary ? 'NPR ' . number_format((float)$record->current_salary, 2) : '' }}</td>
                                <td class="bold">{{ $record && $record->proposed_salary ? 'NPR ' . number_format((float)$record->proposed_salary, 2) : '' }}</td>
                                <td class="success">{{ $record && $record->increment_amount ? 'NPR ' . number_format((float)$record->increment_amount, 2) : '' }}</td>
                                <td class="center">
                                    <label class="decision-label">
                                        <span class="box-sq">{{ $record?->status === 'approved' ? '✓' : '' }}</span> Approved
                                    </label>
                                    &nbsp;&nbsp;&nbsp;
                                    <label class="decision-label">
                                        <span class="box-sq">{{ $record?->status === 'rejected' ? '✓' : '' }}</span> Rejected
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Bottom: Management Signature & Date --}}
                    <div class="slip-bottom-row">
                        <div class="sign-col">
                            <div class="sign-open-space"></div>
                            <div class="sign-underline"></div>
                            <div class="sign-title">Managing Director / General Manager</div>
                        </div>
                        <div class="date-col">
                            <span class="date-lbl">Date:</span>
                            <span class="date-underline">{{ $record?->date_approved?->format('d / m / Y') ?? '' }}</span>
                        </div>
                    </div>

                </div>

                @if($slipIndex < 4)
                    {{-- Cut Divider between Slips --}}
                    <div class="cut-divider">
                        <div class="cut-line"></div>
                        <div class="cut-badge">
                            <span>✂</span>
                            <span>CUT HERE</span>
                            <span>✂</span>
                        </div>
                    </div>
                @endif
            @endfor

        </div>
    @endfor

</body>
</html>
