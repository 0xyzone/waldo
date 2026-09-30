<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ ($isRecord && $record) ? ($record->request_number . ' - Salary Increment Form') : 'Salary Increment Recommendation Form (2x A5)' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 8mm;
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
            max-width: 194mm;
            margin: 0 auto 14px auto;
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

        /* Full A4 Sheet Container (strictly constrained to eliminate trailing blank page) */
        .a4-sheet {
            width: 194mm;
            height: 272mm;
            max-height: 272mm;
            margin: 0 auto 16px auto;
            background: #ffffff;
            border: 1px solid #94a3b8;
            border-radius: 4px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            padding: 5mm 7mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .a4-sheet:not(:last-child) {
            page-break-after: always;
            break-after: page;
        }

        .a4-sheet:last-child {
            page-break-after: avoid !important;
            break-after: avoid !important;
            margin-bottom: 0 !important;
        }

        /* A5 Form Half (Minimalist Layout) */
        .a5-form-half {
            height: 126mm;
            max-height: 126mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: 2mm 0;
        }

        /* Form Header */
        .form-header {
            text-align: center;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }

        .company-name {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f172a;
            margin: 0 0 2px 0;
            line-height: 1.1;
        }

        .form-subtitle {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #334155;
            margin: 0;
            line-height: 1.1;
        }

        /* Meta Row (Ref # & Date) */
        .ref-date-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 6px;
            padding: 0 2px;
        }

        .field-item {
            display: flex;
            align-items: flex-end;
            gap: 6px;
        }

        .field-lbl {
            font-size: 11px;
            font-weight: 800;
            color: #1e293b;
            white-space: nowrap;
            line-height: 22px;
        }

        .field-line {
            flex: 1;
            border-bottom: 1.5px solid #0f172a;
            height: 22px;
            line-height: 22px;
            font-size: 11.5px;
            font-weight: 600;
            padding: 0 6px;
            color: #0f172a;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .field-line.bold {
            font-weight: 800;
        }

        .font-mono {
            font-family: monospace;
            font-size: 12px;
        }

        /* Employee Details Grid */
        .details-card {
            border: 1.5px solid #334155;
            border-radius: 3px;
            background: #ffffff;
            padding: 6px 10px;
            margin-bottom: 6px;
        }

        .grid-2col {
            display: grid;
            grid-template-columns: 1fr 1.35fr;
            gap: 16px;
        }

        /* Salary Metrics Table */
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            border: 1.5px solid #334155;
            border-radius: 3px;
            overflow: hidden;
        }

        .salary-table th {
            background-color: #f1f5f9;
            border-bottom: 1.5px solid #334155;
            border-right: 1px solid #cbd5e1;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: #1e293b;
            text-align: center;
            width: 33.33%;
        }

        .salary-table th:last-child {
            border-right: none;
        }

        .salary-table td {
            border-right: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 11.5px;
            text-align: center;
            font-weight: 600;
            color: #0f172a;
            height: 26px;
        }

        .salary-table td:last-child {
            border-right: none;
        }

        .salary-table td.bold {
            font-weight: 800;
        }

        .salary-table td.text-success {
            color: #15803d;
        }

        /* Management Approval Box (Only Management Sign!) */
        .approval-card {
            border: 1.5px solid #334155;
            border-radius: 3px;
            background: #fafbfc;
            padding: 6px 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 38mm;
        }

        .approval-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }

        .approval-heading {
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }

        .approval-options {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .check-opt {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 10px;
            font-weight: 700;
            color: #1e293b;
        }

        .box-sq {
            display: inline-block;
            width: 13px;
            height: 13px;
            border: 1.5px solid #0f172a;
            border-radius: 2px;
            text-align: center;
            line-height: 12px;
            font-size: 10px;
            font-weight: bold;
            background: #ffffff;
        }

        .mgmt-sign-space {
            flex: 1;
            min-height: 16mm;
            /* Open clean signing area for management signature & seal */
        }

        .mgmt-sign-line {
            border-bottom: 1.5px solid #334155;
            margin-bottom: 4px;
        }

        .approval-bottom {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
        }

        .desig-lbl {
            font-size: 9.5px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 22px;
        }

        .date-write-area {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            min-width: 50mm;
        }

        .date-lbl {
            font-size: 11.5px;
            font-weight: 800;
            color: #0f172a;
            white-space: nowrap;
            line-height: 22px;
        }

        .date-line {
            flex: 1;
            border-bottom: 1.5px solid #0f172a;
            height: 22px;
            line-height: 22px;
            font-size: 11.5px;
            font-weight: 700;
            padding: 0 6px;
            color: #0f172a;
            min-width: 35mm;
        }

        /* Cut Divider (Between Form 1 & Form 2) */
        .cut-divider {
            height: 8mm;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 2mm 0;
        }

        .cut-line {
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            border-top: 1.5px dashed #64748b;
        }

        .cut-badge {
            position: relative;
            background: #ffffff;
            padding: 0 12px;
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
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
                overflow: hidden !important;
            }

            .screen-toolbar {
                display: none !important;
            }

            .a4-sheet {
                margin: 0 auto !important;
                padding: 3mm 5mm !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 194mm !important;
                height: 272mm !important;
                max-height: 272mm !important;
                overflow: hidden !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            }

            .a4-sheet:not(:last-child) {
                page-break-after: always !important;
                break-after: page !important;
            }

            .a4-sheet:last-child {
                page-break-after: avoid !important;
                break-after: avoid !important;
                margin-bottom: 0 !important;
            }
        }
    </style>
</head>
<body>

    {{-- Screen Toolbar --}}
    <div class="screen-toolbar">
        <div class="toolbar-title">
            <span>📄</span>
            <span>{{ ($isRecord && $record) ? ('Salary Increment Request: ' . $record->request_number) : 'Blank Salary Increment Form (2x A5 on 1x A4 Sheet)' }}</span>
        </div>
        <div class="toolbar-actions">
            @if(! $isRecord)
                <label style="font-size: 11px; font-weight: 600; color: #475569;">
                    A4 Sheets:
                    <select class="copies-select" onchange="window.location.search = '?sheets=' + this.value">
                        <option value="1" {{ $sheets == 1 ? 'selected' : '' }}>1 A4 Sheet (2 Forms)</option>
                        <option value="2" {{ $sheets == 2 ? 'selected' : '' }}>2 A4 Sheets (4 Forms)</option>
                        <option value="5" {{ $sheets == 5 ? 'selected' : '' }}>5 A4 Sheets (10 Forms)</option>
                        <option value="10" {{ $sheets == 10 ? 'selected' : '' }}>10 A4 Sheets (20 Forms)</option>
                    </select>
                </label>
            @endif
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Print Form(s)
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                Close
            </button>
        </div>
    </div>

    {{-- A4 Page(s) --}}
    @for($sheetIndex = 1; $sheetIndex <= $sheets; $sheetIndex++)
        <div class="a4-sheet">

            @for($copyIndex = 1; $copyIndex <= 2; $copyIndex++)
                {{-- Single A5 Form Half --}}
                <div class="a5-form-half">

                    <div>
                        {{-- Header --}}
                        <div class="form-header">
                            <h1 class="company-name">WALDO CASINO</h1>
                            <h2 class="form-subtitle">SALARY INCREMENT RECOMMENDATION</h2>
                        </div>

                        {{-- Ref # & Date --}}
                        <div class="ref-date-row">
                            <div class="field-item" style="flex: 1.2;">
                                <span class="field-lbl">Ref #:</span>
                                <span class="field-line font-mono bold">{{ $record?->request_number ?? '' }}</span>
                            </div>
                            <div class="field-item" style="flex: 1;">
                                <span class="field-lbl">Date:</span>
                                <span class="field-line bold">{{ $record?->date_requested?->format('d / m / Y') ?? '' }}</span>
                            </div>
                        </div>

                        {{-- Employee Details --}}
                        <div class="details-card">
                            <div class="grid-2col">
                                <div class="field-item">
                                    <span class="field-lbl">Employee Code:</span>
                                    <span class="field-line font-mono bold">{{ $record?->employee_id ?? '' }}</span>
                                </div>
                                <div class="field-item">
                                    <span class="field-lbl">Employee Name:</span>
                                    <span class="field-line bold">{{ $record?->employee?->name ?? '' }}</span>
                                </div>
                            </div>
                            <div class="grid-2col" style="margin-top: 5px;">
                                <div class="field-item">
                                    <span class="field-lbl">Date of Joining:</span>
                                    <span class="field-line">{{ $record?->employee?->join_date_formatted ?? '' }}</span>
                                </div>
                                <div class="field-item">
                                    <span class="field-lbl">Effective Date:</span>
                                    <span class="field-line bold">{{ $record?->date_applicable?->format('d / m / Y') ?? '' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Salary Metrics Table --}}
                        <table class="salary-table">
                            <thead>
                                <tr>
                                    <th>Current Salary</th>
                                    <th>Proposed Salary</th>
                                    <th>Increment Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $record && $record->current_salary ? 'NPR ' . number_format((float)$record->current_salary, 2) : '' }}</td>
                                    <td class="bold">{{ $record && $record->proposed_salary ? 'NPR ' . number_format((float)$record->proposed_salary, 2) : '' }}</td>
                                    <td class="bold text-success">{{ $record && $record->increment_amount ? 'NPR ' . number_format((float)$record->increment_amount, 2) : '' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Management Approval (Sign only from management) --}}
                    <div class="approval-card">
                        <div class="approval-top">
                            <span class="approval-heading">MANAGEMENT APPROVAL</span>
                            <div class="approval-options">
                                <label class="check-opt"><span class="box-sq">{{ $record?->status === 'approved' ? '✓' : '' }}</span> Approved</label>
                                <label class="check-opt"><span class="box-sq">{{ $record?->status === 'rejected' ? '✓' : '' }}</span> Rejected</label>
                            </div>
                        </div>
                        <div class="approval-body">
                            <div class="mgmt-sign-space"></div>
                            <div class="mgmt-sign-line"></div>
                            <div class="approval-bottom">
                                <span class="desig-lbl">Managing Director / General Manager</span>
                                <div class="date-write-area">
                                    <span class="date-lbl">Date:</span>
                                    <span class="date-line">{{ $record?->date_approved?->format('d / m / Y') ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                @if($copyIndex === 1)
                    {{-- Cut Divider between Form 1 and Form 2 --}}
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
