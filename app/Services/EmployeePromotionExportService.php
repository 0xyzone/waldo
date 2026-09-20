<?php

namespace App\Services;

use App\Models\EmployeePromotion;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeePromotionExportService
{
    /**
     * Format a string into a slug-like string:
     * - All lowercase
     * - Remove dots
     * - Spaces and punctuation become dashes (-)
     *
     * Example: "Jr. Dealer" -> "jr-dealer"
     */
    public static function formatSlugValue(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        // Remove dots
        $cleaned = str_replace('.', '', $value);

        // Convert to lowercase slug using hyphens
        return Str::slug($cleaned, '-');
    }

    /**
     * Export employee promotions to an Excel (.xlsx) file matching the change-type sample structure.
     *
     * @param  Collection<int, EmployeePromotion>  $promotions
     */
    public function export(Collection $promotions): StreamedResponse
    {
        $fileName = 'employee_promotions_import_'.now()->format('Y_m_d_His').'.xlsx';

        return response()->streamDownload(function () use ($promotions) {
            $writer = new XlsxWriter;
            $writer->openToFile('php://output');

            // Header row style
            $headerStyle = (new Style)
                ->setFontBold()
                ->setFontColor('000000');

            // Exact headings from sample file
            $writer->addRow(Row::fromValues([
                'username',
                'start_date',
                'end_date',
                'job_title',
                'branch',
                'division',
                'employment_type',
                'employment_level',
                'employment_step',
                'change_type',
                'use_existing_payroll_package',
            ], $headerStyle));

            foreach ($promotions as $promotion) {
                // username: employee code all lowercase (e.g. CWD001 -> cwd001)
                $username = strtolower((string) ($promotion->employee_id ?? ''));

                // start_date: promotion date in YYYY-MM-DD format
                $startDate = $promotion->promotion_date ? $promotion->promotion_date->format('Y-m-d') : '';

                // end_date: empty
                $endDate = '';

                // job_title: updated designation, lowercase, no dots, spaces replaced by '-'
                $jobTitle = self::formatSlugValue($promotion->toDesignation?->name);

                // branch: constant casino-ktm
                $branch = 'casino-ktm';

                // division: updated department, lowercase, no dots, spaces replaced by '-'
                $division = self::formatSlugValue($promotion->toDepartment?->name);

                // employment_type: constant full-time
                $employmentType = 'full-time';

                // employment_level: constant basic-staff
                $employmentLevel = 'basic-staff';

                // employment_step: constant 1
                $employmentStep = 1;

                // change_type: constant promotion
                $changeType = 'promotion';

                // use_existing_payroll_package: constant Yes
                $useExistingPayrollPackage = 'Yes';

                $writer->addRow(Row::fromValues([
                    $username,
                    $startDate,
                    $endDate,
                    $jobTitle,
                    $branch,
                    $division,
                    $employmentType,
                    $employmentLevel,
                    $employmentStep,
                    $changeType,
                    $useExistingPayrollPackage,
                ]));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
