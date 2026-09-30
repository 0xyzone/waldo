<div class="a5-form">
    {{-- Header --}}
    <div class="form-header">
        <div class="header-left">
            <h1 class="company-title">WALDO CASINO</h1>
            <h2 class="form-title">DEPARTMENTAL SALARY INCREMENT RECOMMENDATION FORM</h2>
        </div>
        <div class="header-meta">
            <div class="copy-badge">{{ $copyTitle }}</div>
            <div><strong>Ref #:</strong> {{ $record?->request_number ?? 'HR-SIR-________' }}</div>
            <div><strong>Date:</strong> {{ $record?->date_requested?->format('d M, Y') ?? date('d/m/Y') }}</div>
        </div>
    </div>

    {{-- Section 1: HOD & Employee Details --}}
    <div class="section-box">
        <div class="section-title">1. Employee & Department Particulars</div>
        <div class="section-body">
            <div class="grid-row">
                <div class="field-col" style="flex: 1.2;">
                    <span class="field-label">Requesting Dept:</span>
                    <span class="field-value strong">{{ $record?->department?->name ?? '' }}</span>
                </div>
                <div class="field-col" style="flex: 1.3;">
                    <span class="field-label">Recommending HOD:</span>
                    <span class="field-value">{{ $record?->hod?->name ? ($record->hod->name . ' (' . $record->hod_id . ')') : '' }}</span>
                </div>
            </div>
            <div class="grid-row">
                <div class="field-col" style="flex: 0.9;">
                    <span class="field-label">Emp Code:</span>
                    <span class="field-value strong">{{ $record?->employee_id ?? '' }}</span>
                </div>
                <div class="field-col" style="flex: 1.6;">
                    <span class="field-label">Employee Name:</span>
                    <span class="field-value strong">{{ $record?->employee?->name ?? '' }}</span>
                </div>
            </div>
            <div class="grid-row">
                <div class="field-col" style="flex: 1.5;">
                    <span class="field-label">Current Designation:</span>
                    <span class="field-value">{{ $record?->currentDesignation?->name ?? $record?->employee?->designation?->name ?? '' }}</span>
                </div>
                <div class="field-col" style="flex: 1;">
                    <span class="field-label">Date of Joining:</span>
                    <span class="field-value">{{ $record?->employee?->join_date_formatted ?? '' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 2: Salary & Increment Proposal --}}
    <div class="section-box">
        <div class="section-title">2. Salary Increment Proposal & Effective Dates</div>
        <div class="section-body">
            <div class="grid-row">
                <div class="field-col">
                    <span class="field-label">Current Salary:</span>
                    <span class="field-value">{{ $record && $record->current_salary ? 'NPR ' . number_format((float)$record->current_salary, 2) : '' }}</span>
                </div>
                <div class="field-col">
                    <span class="field-label">Proposed Salary:</span>
                    <span class="field-value strong">{{ $record && $record->proposed_salary ? 'NPR ' . number_format((float)$record->proposed_salary, 2) : '' }}</span>
                </div>
                <div class="field-col">
                    <span class="field-label">Increment Amount:</span>
                    <span class="field-value strong">{{ $record && $record->increment_amount ? 'NPR ' . number_format((float)$record->increment_amount, 2) : '' }}</span>
                </div>
                <div class="field-col" style="flex: 0.8;">
                    <span class="field-label">Incr (%):</span>
                    <span class="field-value">{{ $record && $record->increment_percentage ? $record->increment_percentage . '%' : '' }}</span>
                </div>
            </div>

            <div class="grid-row" style="margin-top: 2px;">
                <div class="field-col">
                    <span class="field-label">Requested Date:</span>
                    <span class="field-value">{{ $record?->date_requested?->format('d M, Y') ?? '' }}</span>
                </div>
                <div class="field-col">
                    <span class="field-label">Effective Date:</span>
                    <span class="field-value strong">{{ $record?->date_applicable?->format('d M, Y') ?? '' }}</span>
                </div>
                <div class="field-col">
                    <span class="field-label">Approved Date:</span>
                    <span class="field-value">{{ $record?->date_approved?->format('d M, Y') ?? '' }}</span>
                </div>
                @if($record?->proposedDesignation)
                    <div class="field-col">
                        <span class="field-label">New Desig:</span>
                        <span class="field-value strong">{{ $record->proposedDesignation->name }}</span>
                    </div>
                @endif
            </div>

            {{-- Justification Type Checkboxes --}}
            <div class="checkbox-group">
                @php
                    $reason = $record?->reason ?? '';
                    $isAnnual = str_contains($reason, 'Annual');
                    $isMerit = str_contains($reason, 'Merit') || str_contains($reason, 'Performance');
                    $isPromo = str_contains($reason, 'Promotion') || str_contains($reason, 'Role Expansion');
                    $isMarket = str_contains($reason, 'Market');
                    $isOther = $reason && ! ($isAnnual || $isMerit || $isPromo || $isMarket);
                @endphp
                <span class="check-item"><span class="box-sq">{{ $isAnnual ? '✓' : '' }}</span> Annual Appraisal</span>
                <span class="check-item"><span class="box-sq">{{ $isMerit ? '✓' : '' }}</span> High Performance / Merit</span>
                <span class="check-item"><span class="box-sq">{{ $isPromo ? '✓' : '' }}</span> Promotion / Role Expansion</span>
                <span class="check-item"><span class="box-sq">{{ $isMarket ? '✓' : '' }}</span> Market Adjustment</span>
                <span class="check-item"><span class="box-sq">{{ $isOther ? '✓' : '' }}</span> Other{{ $isOther ? ": {$reason}" : '' }}</span>
            </div>
        </div>
    </div>

    {{-- Section 3: HOD Justification & Recommendation Notes --}}
    <div class="section-box" style="margin-bottom: 3px;">
        <div class="section-title">3. HOD Justification & Performance Appraisal Notes</div>
        <div class="section-body">
            <div class="notes-box">
                @if($record && $record->notes)
                    {{ $record->notes }}
                @else
                    <div class="blank-lines"></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Section 4: Verifications & Multi-Tier Approvals --}}
    <div class="signatures-grid">
        {{-- HOD --}}
        <div class="sig-col">
            <div class="sig-header">1. HOD Recommendation</div>
            <div class="sig-space">
                <div class="sig-name">{{ $record?->hod?->name ?? 'Name: ____________________' }}</div>
                <div class="sig-line"></div>
                <div class="sig-date">
                    <span>Sign & Official Stamp</span>
                    <span>Date: {{ $record?->date_requested?->format('d/m/Y') ?? '___/___/____' }}</span>
                </div>
            </div>
        </div>

        {{-- HR --}}
        <div class="sig-col">
            <div class="sig-header">2. HR Verification</div>
            <div class="sig-space">
                <div class="sig-name">
                    @if($record?->hr_acknowledged)
                        <span style="color: #15803d; font-weight: bold;">✓ Ack: {{ $record->hrAcknowledgedBy?->name ?? 'HR Officer' }}</span>
                    @else
                        <span>Verified By: ________________</span>
                    @endif
                </div>
                <div class="sig-line"></div>
                <div class="sig-date">
                    <span>Signature</span>
                    <span>Date: {{ $record?->hr_acknowledged_at?->format('d/m/Y') ?? '___/___/____' }}</span>
                </div>
            </div>
        </div>

        {{-- Finance --}}
        <div class="sig-col">
            <div class="sig-header">3. Finance Verification</div>
            <div class="sig-space">
                <div class="sig-name">
                    @if($record?->finance_acknowledged)
                        <span style="color: #0284c7; font-weight: bold;">✓ Ack: {{ $record->financeAcknowledgedBy?->name ?? 'Finance Officer' }}</span>
                    @else
                        <span>Budget Check: ______________</span>
                    @endif
                </div>
                <div class="sig-line"></div>
                <div class="sig-date">
                    <span>Signature</span>
                    <span>Date: {{ $record?->finance_acknowledged_at?->format('d/m/Y') ?? '___/___/____' }}</span>
                </div>
            </div>
        </div>

        {{-- Approver / GM --}}
        <div class="sig-col">
            <div class="sig-header">4. Management Approval</div>
            <div style="font-size: 8px; margin-bottom: 2px; display: flex; gap: 8px;">
                <span><span class="box-sq">{{ $record?->status === 'approved' ? '✓' : '' }}</span> Approved</span>
                <span><span class="box-sq">{{ $record?->status === 'rejected' ? '✓' : '' }}</span> Rejected</span>
            </div>
            <div class="sig-space">
                <div class="sig-name">Approved Salary: NPR {{ $record && $record->status === 'approved' ? number_format((float)$record->proposed_salary, 2) : '________________' }}</div>
                <div class="sig-line"></div>
                <div class="sig-date">
                    <span>Managing Director / GM</span>
                    <span>Date: {{ $record?->date_approved?->format('d/m/Y') ?? '___/___/____' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
