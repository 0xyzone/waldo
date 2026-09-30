<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ ($isRecord && $record) ? ($record->request_number . ' - Salary Increment Authorization Form') : 'Salary Increment Authorization Form (2x A5 on A4)' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 9mm 10mm 9mm 10mm;
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
            margin: 0 auto 14px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .toolbar-title {
            font-size: 13.5px;
            font-weight: bold;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 18px;
            font-size: 12.5px;
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
            font-size: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            background: #ffffff;
        }

        /* CONTAINER 1: RECORD MODE (Prints ONLY 1 Half-Page Form) */
        .a4-sheet.single-record-sheet {
            width: 190mm;
            margin: 0 auto;
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 0;
        }

        /* CONTAINER 2: BLANK MODE (2 Half-Page Forms covering Full A4) */
        .a4-sheet.two-forms-sheet {
            width: 190mm;
            height: 278mm;
            min-height: 278mm;
            margin: 0 auto 20px auto;
            background: #ffffff;
            border: 1px solid #94a3b8;
            border-radius: 4px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            padding: 2mm 3mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
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

        /* Half-Page Form Box (~134mm height) */
        .half-page-form {
            border: 1.8px solid #0f172a;
            border-radius: 4px;
            padding: 3.5mm 6mm 3mm 6mm;
            background: #ffffff;
            height: 134mm;
            max-height: 134mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .single-record-sheet .half-page-form {
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        }

        /* Form Header Bar */
        .form-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 3px;
            margin-bottom: 2.5mm;
        }

        .brand-block {
            display: flex;
            align-items: baseline;
            gap: 12px;
        }

        .casino-title {
            font-size: 17px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #0f172a;
            margin: 0;
            line-height: 1;
        }

        .doc-title {
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #475569;
            margin: 0;
            line-height: 1;
        }

        .header-meta-group {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
            white-space: nowrap;
        }

        .meta-item {
            display: flex;
            align-items: flex-end;
            gap: 5px;
            font-size: 11px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .meta-lbl {
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            font-size: 9.5px;
            letter-spacing: 0.4px;
            white-space: nowrap !important;
            flex-shrink: 0;
        }

        .meta-val {
            border-bottom: 1.5px solid #0f172a;
            font-weight: 700;
            color: #0f172a;
            padding: 0 5px;
            min-width: 28mm;
            line-height: 16px;
            text-align: center;
        }

        .font-mono {
            font-family: monospace;
            font-size: 11.5px;
        }

        /* Section Badges & Blocks */
        .section-badge {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 2px 7px;
            display: inline-block;
            border-radius: 2px 2px 0 0;
            line-height: 1.2;
            margin-bottom: 0;
        }

        /* Tables */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.3px solid #0f172a;
            margin-bottom: 2mm;
            background: #ffffff;
        }

        .form-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 7px;
            font-size: 10.5px;
            vertical-align: middle;
            color: #0f172a;
            line-height: 1.25;
        }

        .form-table td.lbl {
            background-color: #f8fafc;
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.3px;
        }

        .form-table td.bold {
            font-weight: 800;
        }

        .highlight-effective {
            color: #0284c7;
            font-weight: 800;
        }

        /* Salary Proposal & Decision Table */
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.4px solid #0f172a;
            margin-bottom: 2mm;
            background: #ffffff;
        }

        .salary-table th {
            background-color: #f1f5f9;
            border: 1.2px solid #0f172a;
            padding: 3.5px 8px;
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            text-align: left;
        }

        .salary-table th.center,
        .salary-table td.center {
            text-align: center;
        }

        .salary-table td {
            border: 1.2px solid #0f172a;
            padding: 5px 8px;
            vertical-align: middle;
        }

        .salary-val {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
        }

        .decision-options {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .box-sq {
            display: inline-block;
            width: 13px;
            height: 13px;
            border: 1.4px solid #0f172a;
            border-radius: 2px;
            text-align: center;
            line-height: 11px;
            font-size: 10px;
            font-weight: 900;
            background: #ffffff;
            vertical-align: middle;
            margin-right: 3px;
        }

        .decision-label {
            display: inline-flex;
            align-items: center;
            font-size: 10.5px;
            font-weight: 800;
            color: #1e293b;
        }

        /* Reason & Justification Section */
        .justification-section {
            border: 1.3px solid #0f172a;
            padding: 4px 8px;
            background: #ffffff;
            margin-bottom: 2mm;
            box-sizing: border-box;
        }

        .reason-checks-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding-bottom: 3px;
            border-bottom: 1px dotted #cbd5e1;
            margin-bottom: 3px;
        }

        .reason-label {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #475569;
            letter-spacing: 0.4px;
        }

        .reason-check-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 9.5px;
            font-weight: 700;
            color: #1e293b;
        }

        .box-check-sm {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1.2px solid #0f172a;
            border-radius: 2px;
            text-align: center;
            line-height: 9px;
            font-size: 8.5px;
            font-weight: 900;
            background: #ffffff;
        }

        .justification-notes-row {
            display: flex;
            align-items: baseline;
            gap: 8px;
            min-height: 11mm;
        }

        .notes-lbl {
            font-size: 9px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            white-space: nowrap;
        }

        .notes-content {
            font-size: 10px;
            line-height: 1.4;
            color: #0f172a;
            flex: 1;
        }

        /* Management Authorization Grid */
        .auth-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            border: 1.4px solid #0f172a;
            background: #ffffff;
        }

        .auth-sig-col {
            border-right: 1.4px solid #0f172a;
            padding: 4px 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 24mm;
        }

        .auth-sig-title {
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #475569;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }

        .auth-sig-canvas {
            height: 13mm;
            /* Room for wet signature and rubber stamp */
        }

        .auth-sig-line {
            border-bottom: 1.4px solid #0f172a;
            margin-bottom: 2px;
        }

        .auth-sig-subtext {
            font-size: 8.5px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .auth-meta-col {
            padding: 4px 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 24mm;
            background: #fafafa;
        }

        .auth-meta-body {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            flex: 1;
            gap: 8px;
            margin-top: 4px;
            margin-bottom: 2px;
        }

        .auth-meta-row {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            font-size: 10.5px;
        }

        .auth-meta-lbl {
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.4px;
            white-space: nowrap;
            min-width: 12mm;
        }

        .auth-meta-val {
            flex: 1;
            border-bottom: 1.3px solid #0f172a;
            height: 18px;
            line-height: 18px;
            font-size: 10.5px;
            font-weight: 700;
            color: #0f172a;
            padding: 0 4px;
            text-align: center;
        }

        /* Cut Divider (Between 2 Forms in Blank Mode) */
        .cut-divider {
            height: 5mm;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            margin: 1mm 0;
        }

        .cut-line {
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            border-top: 1.3px dashed #94a3b8;
        }

        .cut-badge {
            position: relative;
            background: #ffffff;
            padding: 0 10px;
            font-size: 8px;
            font-weight: 800;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 5px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        /* Cut Guideline for Single Record on A4 */
        .single-record-cut-guide {
            margin-top: 4mm;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* PRINT OPTIMIZATION — ZERO TRAILING BLANK PAGE */
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

            /* Record mode: prints only 1 half-page form */
            .a4-sheet.single-record-sheet {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 190mm !important;
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow: visible !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
            }

            .single-record-sheet .half-page-form {
                box-shadow: none !important;
                border: 1.8px solid #0f172a !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
            }

            /* Blank mode: 2 forms covering full A4 */
            .a4-sheet.two-forms-sheet {
                margin: 0 auto !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 190mm !important;
                height: 278mm !important;
                max-height: 278mm !important;
                overflow: hidden !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
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
            <span>{{ ($isRecord && $record) ? ('Salary Increment Request: ' . $record->request_number . ' (Half Page / A5 Form)') : 'Blank Salary Increment Authorization Forms (2 Forms on 1x A4 Sheet)' }}</span>
        </div>
        <div class="toolbar-actions">
            @if(! $isRecord)
                <label style="font-size: 11.5px; font-weight: 600; color: #475569;">
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
                🖨️ {{ $isRecord ? 'Print Form' : 'Print Forms' }}
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                Close
            </button>
        </div>
    </div>

    {{-- A4 Page(s) --}}
    @for($sheetIndex = 1; $sheetIndex <= $sheets; $sheetIndex++)
        @if($isRecord && $record)
            {{-- RECORD PRINT: Prints ONLY 1 Form with available details (covers half of the page) --}}
            <div class="a4-sheet single-record-sheet a4-last-sheet">
                
                {{-- Half Page Form (1 of 1) --}}
                <div class="half-page-form">

                    {{-- 1. Top Bar: Branding & Meta --}}
                    <div class="form-top-bar">
                        <div class="brand-block">
                            <h1 class="casino-title">WALDO CASINO</h1>
                            <span class="doc-title">SALARY INCREMENT AUTHORIZATION</span>
                        </div>
                        <div class="header-meta-group">
                            <div class="meta-item">
                                <span class="meta-lbl">Ref&nbsp;#:</span>
                                <span class="meta-val font-mono">{{ $record->request_number }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-lbl">Date:</span>
                                <span class="meta-val">{{ $record->date_requested?->format('d / m / Y') ?? '' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Section 1: Employee Particulars & Employment Record --}}
                    <div>
                        <div class="section-badge">1. Employee Information</div>
                        <table class="form-table">
                            <tbody>
                                <tr>
                                    <td class="lbl" style="width: 17%;">Employee Code:</td>
                                    <td class="font-mono bold" style="width: 33%;">{{ $record->employee_id ?? '' }}</td>
                                    <td class="lbl" style="width: 17%;">Employee Name:</td>
                                    <td class="bold" style="width: 33%;">{{ $record->employee?->name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Department:</td>
                                    <td>{{ $record->department?->name ?? ($record->employee?->department?->name ?? '—') }}</td>
                                    <td class="lbl">Designation:</td>
                                    <td>{{ $record->currentDesignation?->name ?? ($record->employee?->designation?->name ?? '—') }}</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Date of Joining:</td>
                                    <td>{{ $record->employee?->join_date_formatted ?? ($record->employee?->join_date?->format('d M Y') ?? '—') }}</td>
                                    <td class="lbl">Effective Date:</td>
                                    <td class="highlight-effective">{{ $record->date_applicable?->format('d / m / Y') ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- 3. Section 2: Salary Recommendation & Management Decision (Increment Amount removed) --}}
                    <div>
                        <div class="section-badge">2. Salary Proposal & Decision</div>
                        <table class="salary-table">
                            <thead>
                                <tr>
                                    <th style="width: 33%;">Current Salary</th>
                                    <th style="width: 33%;">Proposed Salary</th>
                                    <th style="width: 34%;" class="center">Management Decision</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="salary-val">{{ $record->current_salary ? 'NPR ' . number_format((float)$record->current_salary, 2) : '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="salary-val" style="color: #0284c7;">{{ $record->proposed_salary ? 'NPR ' . number_format((float)$record->proposed_salary, 2) : '—' }}</div>
                                    </td>
                                    <td class="center">
                                        <div class="decision-options">
                                            <label class="decision-label">
                                                <span class="box-sq">{{ $record->status === 'approved' ? '✓' : '' }}</span> Approved
                                            </label>
                                            <label class="decision-label">
                                                <span class="box-sq">{{ $record->status === 'rejected' ? '✓' : '' }}</span> Rejected
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- 4. Section 3: Justification & Reason --}}
                    @php
                        $reason = $record->reason ?? '';
                        $isAnnual = str_contains(strtolower($reason), 'annual') || str_contains(strtolower($reason), 'appraisal');
                        $isMerit = str_contains(strtolower($reason), 'merit') || str_contains(strtolower($reason), 'performance');
                        $isPromo = str_contains(strtolower($reason), 'promotion') || str_contains(strtolower($reason), 'role') || !empty($record->proposed_designation_id);
                        $isMarket = str_contains(strtolower($reason), 'market');
                        $isOther = $reason && ! ($isAnnual || $isMerit || $isPromo || $isMarket);
                    @endphp
                    <div>
                        <div class="section-badge">3. Justification & Remarks</div>
                        <div class="justification-section">
                            <div class="reason-checks-row">
                                <span class="reason-label">Category:</span>
                                <label class="reason-check-item">
                                    <span class="box-check-sm">{{ $isAnnual ? '✓' : '' }}</span> Annual Review
                                </label>
                                <label class="reason-check-item">
                                    <span class="box-check-sm">{{ $isMerit ? '✓' : '' }}</span> Merit / Performance
                                </label>
                                <label class="reason-check-item">
                                    <span class="box-check-sm">{{ $isPromo ? '✓' : '' }}</span> Promotion / Role
                                </label>
                                <label class="reason-check-item">
                                    <span class="box-check-sm">{{ $isMarket ? '✓' : '' }}</span> Market Adjustment
                                </label>
                                <label class="reason-check-item">
                                    <span class="box-check-sm">{{ $isOther ? '✓' : '' }}</span> Other
                                </label>
                            </div>
                            <div class="justification-notes-row">
                                <span class="notes-lbl">Remarks:</span>
                                <div class="notes-content">
                                    {{ $record->notes ?: ($record->reason ?: 'Recommended for salary increment approval.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Section 4: Management Authorization --}}
                    <div>
                        <div class="section-badge">4. Management Authorization</div>
                        <div class="auth-grid">
                            <div class="auth-sig-col">
                                <div class="auth-sig-title">Managing Director / General Manager</div>
                                <div class="auth-sig-canvas"></div>
                                <div class="auth-sig-line"></div>
                                <div class="auth-sig-subtext">Signature & Official Stamp</div>
                            </div>
                            <div class="auth-meta-col">
                                <div class="auth-sig-title">Approval Details</div>
                                <div class="auth-meta-body">
                                    <div class="auth-meta-row">
                                        <span class="auth-meta-lbl">Name:</span>
                                        <span class="auth-meta-val bold">{{ $record->status === 'approved' ? 'Managing Director' : '' }}</span>
                                    </div>
                                    <div class="auth-meta-row">
                                        <span class="auth-meta-lbl">Date:</span>
                                        <span class="auth-meta-val">{{ $record->date_approved?->format('d / m / Y') ?? '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Subtle Cut Guideline for slicing A4 paper in half --}}
                <div class="single-record-cut-guide">
                    <div class="cut-line"></div>
                    <div class="cut-badge">
                        <span>✂</span>
                        <span>CUT HERE (A5 HALF SHEET)</span>
                        <span>✂</span>
                    </div>
                </div>

            </div>
        @else
            {{-- BLANK PRINT: 2 Half-Page Forms covering Full A4 page --}}
            <div class="a4-sheet two-forms-sheet {{ $sheetIndex === (int)$sheets ? 'a4-last-sheet' : '' }}">
                @for($formIndex = 1; $formIndex <= 2; $formIndex++)
                    <div class="half-page-form">

                        {{-- 1. Top Bar: Branding & Meta --}}
                        <div class="form-top-bar">
                            <div class="brand-block">
                                <h1 class="casino-title">WALDO CASINO</h1>
                                <span class="doc-title">SALARY INCREMENT AUTHORIZATION</span>
                            </div>
                            <div class="header-meta-group">
                                <div class="meta-item">
                                    <span class="meta-lbl">Ref&nbsp;#:</span>
                                    <span class="meta-val font-mono"></span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-lbl">Date:</span>
                                    <span class="meta-val"></span>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Section 1: Employee Information --}}
                        <div>
                            <div class="section-badge">1. Employee Information</div>
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <td class="lbl" style="width: 17%;">Employee Code:</td>
                                        <td class="font-mono bold" style="width: 33%;"></td>
                                        <td class="lbl" style="width: 17%;">Employee Name:</td>
                                        <td class="bold" style="width: 33%;"></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Department:</td>
                                        <td></td>
                                        <td class="lbl">Designation:</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="lbl">Date of Joining:</td>
                                        <td></td>
                                        <td class="lbl">Effective Date:</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- 3. Section 2: Salary Recommendation & Management Decision (Increment Amount removed) --}}
                        <div>
                            <div class="section-badge">2. Salary Proposal & Decision</div>
                            <table class="salary-table">
                                <thead>
                                    <tr>
                                        <th style="width: 33%;">Current Salary</th>
                                        <th style="width: 33%;">Proposed Salary</th>
                                        <th style="width: 34%;" class="center">Management Decision</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="salary-val">NPR </div>
                                        </td>
                                        <td>
                                            <div class="salary-val">NPR </div>
                                        </td>
                                        <td class="center">
                                            <div class="decision-options">
                                                <label class="decision-label">
                                                    <span class="box-sq"></span> Approved
                                                </label>
                                                <label class="decision-label">
                                                    <span class="box-sq"></span> Rejected
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- 4. Section 3: Justification & Reason --}}
                        <div>
                            <div class="section-badge">3. Justification & Remarks</div>
                            <div class="justification-section">
                                <div class="reason-checks-row">
                                    <span class="reason-label">Category:</span>
                                    <label class="reason-check-item">
                                        <span class="box-check-sm"></span> Annual Review
                                    </label>
                                    <label class="reason-check-item">
                                        <span class="box-check-sm"></span> Merit / Performance
                                    </label>
                                    <label class="reason-check-item">
                                        <span class="box-check-sm"></span> Promotion / Role
                                    </label>
                                    <label class="reason-check-item">
                                        <span class="box-check-sm"></span> Market Adjustment
                                    </label>
                                    <label class="reason-check-item">
                                        <span class="box-check-sm"></span> Other
                                    </label>
                                </div>
                                <div class="justification-notes-row">
                                    <span class="notes-lbl">Remarks:</span>
                                    <div class="notes-content"></div>
                                </div>
                            </div>
                        </div>

                        {{-- 5. Section 4: Management Authorization --}}
                        <div>
                            <div class="section-badge">4. Management Authorization</div>
                            <div class="auth-grid">
                                <div class="auth-sig-col">
                                    <div class="auth-sig-title">Managing Director / General Manager</div>
                                    <div class="auth-sig-canvas"></div>
                                    <div class="auth-sig-line"></div>
                                    <div class="auth-sig-subtext">Signature & Official Stamp</div>
                                </div>
                                <div class="auth-meta-col">
                                    <div class="auth-sig-title">Approval Details</div>
                                    <div class="auth-meta-body">
                                        <div class="auth-meta-row">
                                            <span class="auth-meta-lbl">Name:</span>
                                            <span class="auth-meta-val"></span>
                                        </div>
                                        <div class="auth-meta-row">
                                            <span class="auth-meta-lbl">Date:</span>
                                            <span class="auth-meta-val"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    @if($formIndex < 2)
                        {{-- Cut Divider between Form 1 & Form 2 --}}
                        <div class="cut-divider">
                            <div class="cut-line"></div>
                            <div class="cut-badge">
                                <span>✂</span>
                                <span>CUT HERE (A5 HALF SHEET)</span>
                                <span>✂</span>
                            </div>
                        </div>
                    @endif
                @endfor
            </div>
        @endif
    @endfor

</body>
</html>
