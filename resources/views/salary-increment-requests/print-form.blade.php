<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $isRecord ? ($record->request_number . ' - Salary Increment Form') : 'Salary Increment Recommendation Form (A4)' }}</title>
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

        /* Full A4 Sheet Container */
        .a4-sheet {
            width: 190mm;
            height: 277mm;
            max-height: 277mm;
            margin: 0 auto 16px auto;
            background: #ffffff;
            border: 1.5px solid #0f172a;
            border-radius: 4px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            padding: 8mm 9mm;
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
            page-break-after: avoid;
            break-after: avoid;
            margin-bottom: 0;
        }

        /* Form Header */
        .form-header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .company-title {
            font-size: 19px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f172a;
            margin: 0 0 4px 0;
            line-height: 1.1;
        }

        .form-title {
            font-size: 13.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #1e293b;
            margin: 0;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        /* Spacious Ref # & Date Bar */
        .ref-date-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 10px;
            padding: 0 4px;
        }

        .ref-box, .date-box {
            display: flex;
            align-items: flex-end;
            gap: 8px;
        }

        .ref-box {
            flex: 1.4;
        }

        .date-box {
            flex: 1;
        }

        .bar-label {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            white-space: nowrap;
        }

        .bar-line {
            flex: 1;
            border-bottom: 1.5px solid #0f172a;
            height: 24px;
            line-height: 24px;
            font-size: 12.5px;
            font-weight: 700;
            padding: 0 6px;
            color: #0f172a;
        }

        /* Section Box */
        .section-box {
            border: 1.5px solid #334155;
            border-radius: 4px;
            margin-bottom: 10px;
            overflow: hidden;
            background: #ffffff;
        }

        .section-title {
            background: #f1f5f9;
            border-bottom: 1.5px solid #334155;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 10px;
            color: #0f172a;
        }

        .section-body {
            padding: 8px 10px;
        }

        /* Field Rows */
        .field-row {
            display: flex;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 8px;
        }

        .field-row:last-child {
            margin-bottom: 0;
        }

        .field-group {
            display: flex;
            align-items: flex-end;
            gap: 6px;
        }

        .field-label {
            font-weight: 700;
            color: #334155;
            font-size: 11px;
            white-space: nowrap;
            line-height: 24px;
        }

        .field-line {
            flex: 1;
            border-bottom: 1.5px solid #334155;
            height: 24px;
            line-height: 24px;
            font-size: 11.5px;
            font-weight: 600;
            padding: 0 6px;
            color: #0f172a;
            min-width: 40px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .field-line.strong {
            font-weight: 800;
            color: #000000;
        }

        .text-center {
            text-align: center;
        }

        /* Reason Checkboxes */
        .reason-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px dotted #cbd5e1;
            font-size: 10.5px;
        }

        .reason-label {
            font-weight: 800;
            color: #0f172a;
            margin-right: 4px;
        }

        .reason-check {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: default;
            color: #1e293b;
            font-weight: 500;
        }

        .box-check {
            display: inline-block;
            width: 13px;
            height: 13px;
            border: 1.5px solid #0f172a;
            border-radius: 2px;
            text-align: center;
            line-height: 12px;
            font-size: 10.5px;
            font-weight: bold;
            background: #ffffff;
        }

        /* Ruled Writing Lines for Justification */
        .ruled-writing-box {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 6px 2px 4px 2px;
        }

        .ruled-line {
            width: 100%;
            height: 22px;
            border-bottom: 1.2px solid #64748b;
        }

        .notes-filled-box {
            font-size: 11px;
            line-height: 1.5;
            color: #0f172a;
            min-height: 38mm;
            max-height: 48mm;
            overflow: hidden;
            padding: 4px 6px;
        }

        /* Signatures Grid */
        .signatures-grid {
            display: grid;
            grid-template-columns: 1.15fr 1fr 1fr 1.35fr;
            border: 1.5px solid #334155;
            border-radius: 4px;
            background: #ffffff;
            margin-top: 4px;
        }

        .sig-box {
            border-right: 1.5px solid #334155;
            padding: 6px 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 44mm;
        }

        .sig-box-approval {
            border-right: none;
            background: #fafafa;
        }

        .sig-title {
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }

        .sig-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sig-line-text {
            display: flex;
            align-items: flex-end;
            gap: 5px;
            font-size: 9.5px;
        }

        .sig-lbl {
            font-weight: 700;
            color: #334155;
            white-space: nowrap;
        }

        .sig-underline {
            flex: 1;
            border-bottom: 1.2px solid #475569;
            height: 18px;
            line-height: 18px;
            padding: 0 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sig-signature-area {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1.2px solid #475569;
            margin: 4px 0;
            min-height: 18mm;
        }

        .sig-placeholder {
            font-size: 9px;
            color: #94a3b8;
            font-style: italic;
        }

        .approval-checks {
            display: flex;
            gap: 12px;
            font-size: 10px;
            margin-bottom: 4px;
        }

        /* PRINT OPTIMIZATION — ELIMINATES BLANK PAGE */
        @media print {
            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
            }

            .screen-toolbar {
                display: none !important;
            }

            .a4-sheet {
                margin: 0 auto !important;
                padding: 4mm 6mm !important;
                box-shadow: none !important;
                border: 1.5px solid #0f172a !important;
                width: 100% !important;
                max-width: 190mm !important;
                height: 277mm !important;
                max-height: 277mm !important;
                overflow: hidden !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .a4-sheet:not(:last-child) {
                page-break-after: always !important;
                break-after: page !important;
            }

            .a4-sheet:last-child {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
        }
    </style>
</head>
<body>

    {{-- Screen Toolbar --}}
    <div class="screen-toolbar">
        <div class="toolbar-title">
            <span>📄</span>
            <span>{{ $isRecord ? 'Salary Increment Request: ' . $record->request_number : 'Blank Salary Increment Form (A4 Size)' }}</span>
        </div>
        <div class="toolbar-actions">
            @if(! $isRecord)
                <label style="font-size: 11px; font-weight: 600; color: #475569;">
                    A4 Pages:
                    <select class="copies-select" onchange="window.location.search = '?sheets=' + this.value">
                        <option value="1" {{ $sheets == 1 ? 'selected' : '' }}>1 Page (1 Form)</option>
                        <option value="2" {{ $sheets == 2 ? 'selected' : '' }}>2 Pages (2 Forms)</option>
                        <option value="5" {{ $sheets == 5 ? 'selected' : '' }}>5 Pages (5 Forms)</option>
                        <option value="10" {{ $sheets == 10 ? 'selected' : '' }}>10 Pages (10 Forms)</option>
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

            <div>
                {{-- Form Header --}}
                <div class="form-header">
                    <h1 class="company-title">WALDO CASINO</h1>
                    <h2 class="form-title">DEPARTMENTAL SALARY INCREMENT RECOMMENDATION FORM</h2>
                </div>

                {{-- Spacious Ref # & Date Top Bar --}}
                <div class="ref-date-bar">
                    <div class="ref-box">
                        <span class="bar-label">Ref #:</span>
                        <span class="bar-line">{{ $record?->request_number ?? '' }}</span>
                    </div>
                    <div class="date-box">
                        <span class="bar-label">Date:</span>
                        <span class="bar-line">{{ $record?->date_requested?->format('d / m / Y') ?? '' }}</span>
                    </div>
                </div>

                {{-- Section 1: HOD & Employee Particulars --}}
                <div class="section-box">
                    <div class="section-title">1. Employee & Department Particulars</div>
                    <div class="section-body">
                        <div class="field-row">
                            <div class="field-group" style="flex: 1.1;">
                                <span class="field-label">Requesting Dept:</span>
                                <span class="field-line strong">{{ $record?->department?->name ?? '' }}</span>
                            </div>
                            <div class="field-group" style="flex: 1.3;">
                                <span class="field-label">Recommending HOD:</span>
                                <span class="field-line">{{ $record?->hod?->name ? ($record->hod->name . ' (' . $record->hod_id . ')') : '' }}</span>
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-group" style="flex: 0.9;">
                                <span class="field-label">Emp Code:</span>
                                <span class="field-line strong">{{ $record?->employee_id ?? '' }}</span>
                            </div>
                            <div class="field-group" style="flex: 1.5;">
                                <span class="field-label">Employee Name:</span>
                                <span class="field-line strong">{{ $record?->employee?->name ?? '' }}</span>
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-group" style="flex: 1.3;">
                                <span class="field-label">Current Designation:</span>
                                <span class="field-line">{{ $record?->currentDesignation?->name ?? $record?->employee?->designation?->name ?? '' }}</span>
                            </div>
                            <div class="field-group" style="flex: 1.1;">
                                <span class="field-label">Date of Joining:</span>
                                <span class="field-line">{{ $record?->employee?->join_date_formatted ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Salary Increment Proposal & Effective Dates --}}
                <div class="section-box">
                    <div class="section-title">2. Salary Increment Proposal & Effective Dates</div>
                    <div class="section-body">
                        <div class="field-row">
                            <div class="field-group" style="flex: 1.2;">
                                <span class="field-label">Current Salary:</span>
                                <span class="field-line">{{ $record && $record->current_salary ? 'NPR ' . number_format((float)$record->current_salary, 2) : '' }}</span>
                            </div>
                            <div class="field-group" style="flex: 1.2;">
                                <span class="field-label">Proposed Salary:</span>
                                <span class="field-line strong">{{ $record && $record->proposed_salary ? 'NPR ' . number_format((float)$record->proposed_salary, 2) : '' }}</span>
                            </div>
                            <div class="field-group" style="flex: 1.2;">
                                <span class="field-label">Increment Amount:</span>
                                <span class="field-line strong">{{ $record && $record->increment_amount ? 'NPR ' . number_format((float)$record->increment_amount, 2) : '' }}</span>
                            </div>
                            <div class="field-group" style="flex: 0.75;">
                                <span class="field-label">Incr (%):</span>
                                <span class="field-line text-center">{{ $record && $record->increment_percentage ? $record->increment_percentage . '%' : '' }}</span>
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-group" style="flex: 1;">
                                <span class="field-label">Effective Date:</span>
                                <span class="field-line strong">{{ $record?->date_applicable?->format('d / m / Y') ?? '' }}</span>
                            </div>
                            <div class="field-group" style="flex: 1;">
                                <span class="field-label">Requested Date:</span>
                                <span class="field-line">{{ $record?->date_requested?->format('d / m / Y') ?? '' }}</span>
                            </div>
                            <div class="field-group" style="flex: 1.2;">
                                <span class="field-label">New Desig (if promoted):</span>
                                <span class="field-line">{{ $record?->proposedDesignation?->name ?? '' }}</span>
                            </div>
                        </div>

                        {{-- Reason Checkboxes --}}
                        <div class="reason-row">
                            <span class="reason-label">Reason:</span>
                            @php
                                $reason = $record?->reason ?? '';
                                $isAnnual = str_contains($reason, 'Annual');
                                $isMerit = str_contains($reason, 'Merit') || str_contains($reason, 'Performance');
                                $isPromo = str_contains($reason, 'Promotion') || str_contains($reason, 'Role Expansion');
                                $isMarket = str_contains($reason, 'Market');
                                $isOther = $reason && ! ($isAnnual || $isMerit || $isPromo || $isMarket);
                            @endphp
                            <label class="reason-check"><span class="box-check">{{ $isAnnual ? '✓' : '' }}</span> Annual Appraisal</label>
                            <label class="reason-check"><span class="box-check">{{ $isMerit ? '✓' : '' }}</span> Merit / Performance</label>
                            <label class="reason-check"><span class="box-check">{{ $isPromo ? '✓' : '' }}</span> Promotion / Role</label>
                            <label class="reason-check"><span class="box-check">{{ $isMarket ? '✓' : '' }}</span> Market Adjustment</label>
                            <label class="reason-check"><span class="box-check">{{ $isOther ? '✓' : '' }}</span> Other{{ $isOther ? ": {$reason}" : '' }}</label>
                        </div>
                    </div>
                </div>

                {{-- Section 3: HOD Justification & Remarks (Handwriting lines) --}}
                <div class="section-box">
                    <div class="section-title">3. HOD Justification & Recommendation Remarks</div>
                    <div class="section-body">
                        @if($record && $record->notes)
                            <div class="notes-filled-box">
                                {{ $record->notes }}
                            </div>
                        @else
                            <div class="ruled-writing-box">
                                <div class="ruled-line"></div>
                                <div class="ruled-line"></div>
                                <div class="ruled-line"></div>
                                <div class="ruled-line"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Section 4: Signatures & Multi-Tier Approvals --}}
            <div class="signatures-grid">
                {{-- HOD Recommendation --}}
                <div class="sig-box">
                    <div class="sig-title">1. HOD Recommendation</div>
                    <div class="sig-body">
                        <div class="sig-line-text">
                            <span class="sig-lbl">Name:</span>
                            <span class="sig-underline">{{ $record?->hod?->name ?? '' }}</span>
                        </div>
                        <div class="sig-signature-area">
                            <span class="sig-placeholder">Signature & Stamp</span>
                        </div>
                        <div class="sig-line-text">
                            <span class="sig-lbl">Date:</span>
                            <span class="sig-underline">{{ $record?->date_requested?->format('d / m / Y') ?? '' }}</span>
                        </div>
                    </div>
                </div>

                {{-- HR Verification --}}
                <div class="sig-box">
                    <div class="sig-title">2. HR Verification</div>
                    <div class="sig-body">
                        <div class="sig-line-text">
                            <span class="sig-lbl">Verified:</span>
                            <span class="sig-underline">
                                @if($record?->hr_acknowledged)
                                    <strong style="color: #15803d;">✓ {{ $record->hrAcknowledgedBy?->name ?? 'HR Officer' }}</strong>
                                @endif
                            </span>
                        </div>
                        <div class="sig-signature-area">
                            <span class="sig-placeholder">Signature</span>
                        </div>
                        <div class="sig-line-text">
                            <span class="sig-lbl">Date:</span>
                            <span class="sig-underline">{{ $record?->hr_acknowledged_at?->format('d / m / Y') ?? '' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Finance Verification --}}
                <div class="sig-box">
                    <div class="sig-title">3. Finance Verification</div>
                    <div class="sig-body">
                        <div class="sig-line-text">
                            <span class="sig-lbl">Budget:</span>
                            <span class="sig-underline">
                                @if($record?->finance_acknowledged)
                                    <strong style="color: #0284c7;">✓ {{ $record->financeAcknowledgedBy?->name ?? 'Finance Officer' }}</strong>
                                @endif
                            </span>
                        </div>
                        <div class="sig-signature-area">
                            <span class="sig-placeholder">Signature</span>
                        </div>
                        <div class="sig-line-text">
                            <span class="sig-lbl">Date:</span>
                            <span class="sig-underline">{{ $record?->finance_acknowledged_at?->format('d / m / Y') ?? '' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Management Approval --}}
                <div class="sig-box sig-box-approval">
                    <div class="sig-title">4. Management Approval</div>
                    <div class="sig-body">
                        <div class="approval-checks">
                            <label class="reason-check"><span class="box-check">{{ $record?->status === 'approved' ? '✓' : '' }}</span> Approved</label>
                            <label class="reason-check"><span class="box-check">{{ $record?->status === 'rejected' ? '✓' : '' }}</span> Rejected</label>
                        </div>
                        <div class="sig-line-text">
                            <span class="sig-lbl">Approved:</span>
                            <span class="sig-underline">{{ $record && $record->status === 'approved' ? 'NPR ' . number_format((float)$record->proposed_salary, 2) : '' }}</span>
                        </div>
                        <div class="sig-signature-area">
                            <span class="sig-placeholder">Managing Director / GM</span>
                        </div>
                        <div class="sig-line-text">
                            <span class="sig-lbl">Date:</span>
                            <span class="sig-underline">{{ $record?->date_approved?->format('d / m / Y') ?? '' }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endfor

</body>
</html>
