<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $isRecord ? ($record->request_number . ' - Salary Increment Form') : 'Salary Increment Recommendation Form (Blank - 2x A5 on A4)' }}</title>
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

        body {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 10px;
            color: #0f172a;
            background-color: #f1f5f9;
            margin: 0;
            padding: 12px;
        }

        .screen-toolbar {
            max-width: 210mm;
            margin: 0 auto 14px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
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
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
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

        /* A4 Page Container */
        .a4-sheet {
            width: 210mm;
            min-height: 284mm;
            max-height: 284mm;
            margin: 0 auto 16px auto;
            background: #ffffff;
            border: 1px solid #94a3b8;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 6mm 7mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-after: always;
            break-after: page;
        }

        .a4-sheet:last-child {
            page-break-after: auto;
            break-after: auto;
            margin-bottom: 0;
        }

        /* Each A5 Half Form */
        .a5-form {
            width: 100%;
            height: 133mm;
            max-height: 133mm;
            border: 1.5px solid #0f172a;
            border-radius: 4px;
            padding: 6px 9px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #ffffff;
            position: relative;
        }

        /* Cut Divider Line between the 2 A5 halves */
        .cut-divider {
            width: 100%;
            height: 8mm;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 2mm 0;
        }

        .cut-line {
            width: 100%;
            border-top: 1.5px dashed #64748b;
            position: absolute;
            top: 50%;
            left: 0;
            z-index: 1;
        }

        .cut-pill {
            background: #ffffff;
            border: 1px solid #94a3b8;
            border-radius: 12px;
            padding: 1px 12px;
            font-size: 9px;
            font-weight: bold;
            color: #475569;
            letter-spacing: 0.5px;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Form Header */
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }

        .header-left {
            flex: 1;
        }

        .company-title {
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin: 0;
        }

        .form-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #1e293b;
            margin: 1px 0 0 0;
        }

        .header-meta {
            text-align: right;
            font-size: 9px;
            color: #334155;
            min-width: 48mm;
        }

        .copy-badge {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #475569;
            color: #0f172a;
            padding: 1.5px 6px;
            font-weight: bold;
            font-size: 8.5px;
            border-radius: 3px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        /* Sections and Grids */
        .section-box {
            border: 1px solid #334155;
            border-radius: 3px;
            margin-bottom: 4px;
            overflow: hidden;
        }

        .section-title {
            background: #f1f5f9;
            border-bottom: 1px solid #334155;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 2px 6px;
            color: #0f172a;
        }

        .section-body {
            padding: 4px 6px;
        }

        .grid-row {
            display: flex;
            align-items: center;
            margin-bottom: 3px;
            font-size: 9.5px;
        }

        .grid-row:last-child {
            margin-bottom: 0;
        }

        .field-col {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .field-label {
            font-weight: 700;
            color: #334155;
            font-size: 9px;
            white-space: nowrap;
        }

        .field-value {
            flex: 1;
            font-weight: 600;
            color: #0f172a;
            border-bottom: 1px dotted #94a3b8;
            min-height: 14px;
            padding: 0 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .field-value.strong {
            font-weight: bold;
            color: #09090b;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 8.5px;
            margin-top: 3px;
            padding-top: 2px;
            border-top: 1px dotted #cbd5e1;
        }

        .check-item {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            color: #1e293b;
        }

        .box-sq {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #334155;
            border-radius: 2px;
            text-align: center;
            line-height: 9px;
            font-size: 8px;
            font-weight: bold;
            background: #ffffff;
        }

        /* Justification text box */
        .notes-box {
            min-height: 22mm;
            max-height: 22mm;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            background-color: #fafafa;
            padding: 3px 6px;
            font-size: 9px;
            line-height: 1.35;
            color: #1e293b;
            position: relative;
            overflow: hidden;
        }

        .blank-lines {
            width: 100%;
            height: 100%;
            background-image: repeating-linear-gradient(transparent, transparent 13px, #cbd5e1 13px, #cbd5e1 14px);
            position: absolute;
            top: 2px;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            opacity: 0.6;
        }

        /* Signatures Grid */
        .signatures-grid {
            display: grid;
            grid-template-columns: 1.1fr 1fr 1fr 1.3fr;
            border: 1px solid #334155;
            border-radius: 3px;
            margin-top: 2px;
            background: #ffffff;
        }

        .sig-col {
            border-right: 1px solid #334155;
            padding: 3px 5px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 22mm;
        }

        .sig-col:last-child {
            border-right: none;
        }

        .sig-header {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e293b;
            border-bottom: 1px dotted #cbd5e1;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }

        .sig-space {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            min-height: 12mm;
        }

        .sig-name {
            font-size: 8.5px;
            font-weight: 600;
            color: #0f172a;
        }

        .sig-line {
            border-bottom: 1px solid #64748b;
            margin-top: 2px;
            margin-bottom: 2px;
        }

        .sig-date {
            font-size: 8px;
            color: #475569;
            display: flex;
            justify-content: space-between;
        }

        .status-badge-inline {
            font-size: 8px;
            padding: 1px 4px;
            border-radius: 2px;
            font-weight: bold;
            text-transform: uppercase;
        }

        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
            }
            .screen-toolbar {
                display: none !important;
            }
            .a4-sheet {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
                width: 100%;
                min-height: 100%;
            }
        }
    </style>
</head>
<body>

    {{-- Screen Toolbar --}}
    <div class="screen-toolbar">
        <div class="toolbar-title">
            <span>📄</span>
            <span>{{ $isRecord ? 'Salary Increment Request: ' . $record->request_number : 'Blank Salary Increment Form (2x A5 on 1x A4 Sheet)' }}</span>
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

            {{-- TOP HALF: FORM 1 (A5) --}}
            @php $copyTitle = $isRecord ? 'ORIGINAL (HR & MANAGEMENT RECORD)' : 'OFFICE COPY (HR & MANAGEMENT)'; @endphp
            @include('salary-increment-requests.partials.a5-single-form', ['copyTitle' => $copyTitle, 'isRecord' => $isRecord, 'record' => $record])

            {{-- CUT HERE SEPARATOR LINE --}}
            <div class="cut-divider">
                <div class="cut-line"></div>
                <div class="cut-pill">
                    <span>✂</span>
                    <span>CUT HERE (A5 HALF SHEET)</span>
                    <span>✂</span>
                </div>
            </div>

            {{-- BOTTOM HALF: FORM 2 (A5) --}}
            @php $copyTitle = $isRecord ? 'DUPLICATE (FINANCE & PAYROLL RECORD)' : 'FINANCE & PAYROLL COPY'; @endphp
            @include('salary-increment-requests.partials.a5-single-form', ['copyTitle' => $copyTitle, 'isRecord' => $isRecord, 'record' => $record])

        </div>
    @endfor

</body>
</html>
