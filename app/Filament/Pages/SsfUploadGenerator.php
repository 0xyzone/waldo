<?php

namespace App\Filament\Pages;

use Agroezinger\FilamentShieldEnhanced\Traits\HasPageShield;
use App\Models\Employee;
use App\Services\SsfUploadExportService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class SsfUploadGenerator extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowDown;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::DocumentArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'HR & Admin';

    protected static ?string $navigationLabel = 'SSF Upload Generator';

    protected static ?string $title = 'SSF Upload File Generator';

    protected static ?string $slug = 'ssf-upload-generator';

    protected static ?int $navigationSort = 11;

    protected string $view = 'filament.pages.ssf-upload-generator';

    public static function getShieldPagePermissions(): array
    {
        return [
            'view' => 'View SSF Generator',
            'download' => 'Download SSF Excel',
            'generate' => 'Generate / Refresh Records',
        ];
    }

    /**
     * Search query string.
     */
    public string $search = '';

    /**
     * Filter tab status: 'all', 'missing', 'complete'.
     */
    public string $filterStatus = 'all';

    /**
     * List of employee codes excluded/deleted from the upload list in the current session.
     *
     * @var list<string>
     */
    public array $excludedCodes = [];

    /**
     * Cached list of generated records.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $records = [];

    /**
     * Timestamp of when the list was last generated.
     */
    public ?string $lastGeneratedAt = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->hasRole(['super_admin', 'HR', 'HR Assist'])) {
            return true;
        }

        foreach (static::resolveAllPermissionKeys() as $key) {
            if ($user->can($key)) {
                return true;
            }
        }

        return false;
    }

    public function mount(): void
    {
        $this->excludedCodes = session('ssf_generator_excluded', []);
        $cachedRecords = session('ssf_generator_records', null);
        $this->lastGeneratedAt = session('ssf_generator_time', null);

        if (! empty($cachedRecords) && is_array($cachedRecords)) {
            $this->records = $cachedRecords;
        } else {
            $this->loadRecordsFromDatabase(clearExcluded: false);
        }
    }

    /**
     * Query active employees with empty SSF ID from database and map to generator records.
     */
    public function loadRecordsFromDatabase(bool $clearExcluded = false): void
    {
        if ($clearExcluded) {
            $this->excludedCodes = [];
            session()->forget('ssf_generator_excluded');
        }

        $employees = Employee::query()
            ->with(['designation'])
            ->where('employee_status', 'Active')
            ->where(function ($query) {
                $query->whereNull('ssid')
                    ->orWhere('ssid', '');
            })
            ->orderBy('employee_code', 'asc')
            ->get();

        $generated = [];

        foreach ($employees as $employee) {
            $record = $this->transformEmployeeToRecord($employee);
            $generated[] = $record;
        }

        $this->records = $generated;
        $this->lastGeneratedAt = now()->format('Y-m-d H:i:s');

        session([
            'ssf_generator_records' => $this->records,
            'ssf_generator_time' => $this->lastGeneratedAt,
            'ssf_generator_excluded' => $this->excludedCodes,
        ]);
    }

    /**
     * Regenerate list fresh from employee database.
     */
    public function regenerateList(): void
    {
        $this->loadRecordsFromDatabase(clearExcluded: false);

        Notification::make()
            ->title('SSF List Regenerated')
            ->body('Active employee records without SSF ID have been refreshed from the database.')
            ->success()
            ->send();
    }

    /**
     * Delete / skip an employee record from the upload list.
     */
    public function deleteRecord(string $employeeCode): void
    {
        if (! in_array($employeeCode, $this->excludedCodes, true)) {
            $this->excludedCodes[] = $employeeCode;
            session(['ssf_generator_excluded' => $this->excludedCodes]);

            Notification::make()
                ->title('Record Excluded')
                ->body("Employee {$employeeCode} was removed from this SSF upload list.")
                ->info()
                ->send();
        }
    }

    /**
     * Restore a previously skipped record back to the list.
     */
    public function restoreRecord(string $employeeCode): void
    {
        $this->excludedCodes = array_values(array_filter(
            $this->excludedCodes,
            fn ($code) => $code !== $employeeCode
        ));

        session(['ssf_generator_excluded' => $this->excludedCodes]);

        Notification::make()
            ->title('Record Restored')
            ->body("Employee {$employeeCode} was restored to the list.")
            ->success()
            ->send();
    }

    /**
     * Restore all excluded records.
     */
    public function restoreAllExcluded(): void
    {
        $count = count($this->excludedCodes);
        $this->excludedCodes = [];
        session(['ssf_generator_excluded' => []]);

        Notification::make()
            ->title('All Records Restored')
            ->body("Restored {$count} previously skipped employee record(s).")
            ->success()
            ->send();
    }

    /**
     * Get all active (non-excluded) records with sequential Sno numbering.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActiveRecords(): array
    {
        $active = [];
        $sno = 1;

        foreach ($this->records as $record) {
            if (in_array($record['employee_code'], $this->excludedCodes, true)) {
                continue;
            }

            $record['sno'] = $sno++;
            $active[] = $record;
        }

        return $active;
    }

    /**
     * Get filtered records according to search and status tab.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFilteredRecords(): array
    {
        $active = $this->getActiveRecords();
        $search = trim(strtolower($this->search));
        $filter = $this->filterStatus;

        return array_values(array_filter($active, function ($record) use ($search, $filter) {
            // Status tab filter
            if ($filter === 'missing' && $record['is_complete']) {
                return false;
            }
            if ($filter === 'complete' && ! $record['is_complete']) {
                return false;
            }

            // Search filter
            if ($search !== '') {
                $haystack = strtolower(implode(' ', [
                    $record['employee_code'] ?? '',
                    $record['name'] ?? '',
                    $record['first_name'] ?? '',
                    $record['last_name'] ?? '',
                    $record['email'] ?? '',
                    $record['contact_no'] ?? '',
                    $record['document_no'] ?? '',
                    $record['post'] ?? '',
                ]));

                return str_contains($haystack, $search);
            }

            return true;
        }));
    }

    /**
     * Check if any active record in the generator has missing required fields.
     */
    public function hasMissingRecords(): bool
    {
        foreach ($this->getActiveRecords() as $record) {
            if (! $record['is_complete']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Count how many active records have missing values.
     */
    public function getMissingRecordsCount(): int
    {
        $count = 0;
        foreach ($this->getActiveRecords() as $record) {
            if (! $record['is_complete']) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Download the verified SSF upload Excel file.
     */
    public function downloadExcel(SsfUploadExportService $exportService): ?StreamedResponse
    {
        if (! $this->canShield('download') && ! (auth()->user()?->hasRole(['super_admin', 'HR']) ?? false)) {
            Notification::make()
                ->title('Download Unauthorized')
                ->body('You do not have permission to download SSF Excel files.')
                ->danger()
                ->send();

            return null;
        }

        $activeRecords = $this->getActiveRecords();

        if (empty($activeRecords)) {
            Notification::make()
                ->title('No Records Available')
                ->body('There are no active employee records to download.')
                ->warning()
                ->send();

            return null;
        }

        $missingCount = $this->getMissingRecordsCount();

        if ($missingCount > 0) {
            Notification::make()
                ->title('Download Blocked')
                ->body("Cannot download Excel: {$missingCount} record(s) have missing required values. Please update the employee data and click Regenerate, or remove those records from the list to proceed.")
                ->danger()
                ->send();

            return null;
        }

        return $exportService->export($activeRecords);
    }

    /**
     * Transform Employee model to formatted generator array.
     *
     * @return array<string, mixed>
     */
    protected function transformEmployeeToRecord(Employee $employee): array
    {
        $firstName = trim((string) ($employee->first_name ?? ''));
        $midName = trim((string) ($employee->middle_name ?? ''));
        $lastName = trim((string) ($employee->last_name ?? ''));
        $gender = trim((string) ($employee->gender ?? ''));
        $dobBs = $this->normalizeDateString($employee->dob_bs);
        $country = 'NEPAL';
        $email = trim((string) ($employee->email ?? ''));
        $contactType = 'Mobile';
        $contactNo = trim((string) ($employee->contact_number ?? ''));
        $document = 'ना. प्र. प';
        $documentNo = trim((string) ($employee->citizenship_number ?? ''));
        $issuingOffice = trim((string) ($employee->citizenship_issue_place ?? ''));
        $issuedDate = $this->normalizeDateString($employee->citizenship_issue_date);
        $employementType = 'कार्यगत';
        $post = trim((string) ($employee->designation?->name ?? ''));
        $joinDate = $this->formatJoinDate($employee->join_date_formatted);

        $missingFields = [];

        if ($firstName === '') {
            $missingFields[] = 'First name';
        }
        // Mid name is optional per user's confirmation
        if ($lastName === '') {
            $missingFields[] = 'Last Name';
        }
        if ($gender === '') {
            $missingFields[] = 'Gender';
        }
        if ($dobBs === '') {
            $missingFields[] = 'DOB(YYYY.MM.DD)';
        }
        if ($email === '') {
            $missingFields[] = 'Email';
        }
        if ($contactNo === '') {
            $missingFields[] = 'Contact No';
        }
        if ($documentNo === '') {
            $missingFields[] = 'Document No';
        }
        if ($issuingOffice === '') {
            $missingFields[] = 'Issuing Office';
        }
        if ($issuedDate === '') {
            $missingFields[] = 'Issued Date(YYYY.MM.DD)';
        }
        if ($post === '') {
            $missingFields[] = 'Post';
        }
        if ($joinDate === '') {
            $missingFields[] = 'Join Date(YYYY.MM.DD)';
        }

        return [
            'employee_code' => $employee->employee_code,
            'name' => $employee->name ?? trim("{$firstName} {$midName} {$lastName}"),
            'first_name' => $firstName,
            'mid_name' => $midName,
            'last_name' => $lastName,
            'gender' => $gender,
            'dob_bs' => $dobBs,
            'country' => $country,
            'email' => $email,
            'contact_type' => $contactType,
            'contact_no' => $contactNo,
            'document' => $document,
            'document_no' => $documentNo,
            'issuing_office' => $issuingOffice,
            'issued_date' => $issuedDate,
            'employement_type' => $employementType,
            'post' => $post,
            'join_date' => $joinDate,
            'missing_fields' => $missingFields,
            'is_complete' => count($missingFields) === 0,
        ];
    }

    /**
     * Format join date from DB string (e.g. '15 September, 2026') to 'YYYY.MM.DD'.
     */
    protected function formatJoinDate(?string $raw): string
    {
        if (blank($raw)) {
            return '';
        }

        try {
            $clean = trim(str_replace(',', '', (string) $raw));

            return Carbon::parse($clean)->format('Y.m.d');
        } catch (\Throwable) {
            $clean = preg_replace('/[-\/]/', '.', trim((string) $raw));

            return (string) $clean;
        }
    }

    /**
     * Normalize BS dates to YYYY.MM.DD format.
     */
    protected function normalizeDateString(?string $raw): string
    {
        if (blank($raw)) {
            return '';
        }

        return trim(str_replace(['-', '/'], '.', (string) $raw));
    }
}
