<?php

namespace App\Services;

use App\Models\EmployeePromotion;
use DOMDocument;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class EmployeePromotionExportService
{
    public const COLUMNS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];

    public const HEADINGS = [
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
    ];

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
     * Uses the official change-type template workbook preserving all reference sheets and data validations.
     *
     * @param  Collection<int, EmployeePromotion>  $promotions
     */
    public function export(Collection $promotions, ?string $uploadedTemplatePath = null): StreamedResponse
    {
        $fileName = 'employee_promotions_import_'.now()->format('Y_m_d_His').'.xlsx';

        $records = [];
        foreach ($promotions as $promotion) {
            $records[] = [
                'username' => strtolower((string) ($promotion->employee_id ?? '')),
                'start_date' => $promotion->promotion_date ? $promotion->promotion_date->format('Y-m-d') : '',
                'end_date' => '',
                'job_title' => self::formatSlugValue($promotion->toDesignation?->name ?? $promotion->employee?->designation?->name),
                'branch' => 'casino-ktm',
                'division' => self::formatSlugValue($promotion->toDepartment?->name ?? $promotion->employee?->department?->name),
                'employment_type' => 'full-time',
                'employment_level' => 'basic-staff',
                'employment_step' => '1',
                'change_type' => 'promotion',
                'use_existing_payroll_package' => 'Yes',
            ];
        }

        $storageDir = storage_path('app/templates');
        $customTemplatePath = storage_path('app/templates/change-type-import-sample.xlsx');
        $defaultTemplatePath = resource_path('templates/change-type-import-sample.xlsx');

        if ($uploadedTemplatePath && file_exists($uploadedTemplatePath)) {
            $testZip = new ZipArchive;
            if ($testZip->open($uploadedTemplatePath) === true) {
                $testZip->close();
                if (! is_dir($storageDir)) {
                    @mkdir($storageDir, 0755, true);
                }
                @copy($uploadedTemplatePath, $customTemplatePath);
                $templatePath = $uploadedTemplatePath;
            } else {
                $templatePath = file_exists($customTemplatePath) ? $customTemplatePath : $defaultTemplatePath;
                if (! file_exists($templatePath)) {
                    $templatePath = base_path('change-type-import-sample.xlsx');
                }
            }
        } else {
            $templatePath = file_exists($customTemplatePath) ? $customTemplatePath : $defaultTemplatePath;
            if (! file_exists($templatePath)) {
                $templatePath = base_path('change-type-import-sample.xlsx');
            }
        }

        if (file_exists($templatePath)) {
            $tempPath = tempnam(sys_get_temp_dir(), 'promo_export_');
            copy($templatePath, $tempPath);

            try {
                $this->populateTemplate($tempPath, $records);

                return response()->streamDownload(function () use ($tempPath) {
                    $handle = fopen($tempPath, 'rb');
                    if ($handle) {
                        fpassthru($handle);
                        fclose($handle);
                    }
                    @unlink($tempPath);
                }, $fileName, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                ]);
            } catch (\Throwable $e) {
                @unlink($tempPath);
                report($e);
            }
        }

        // Fallback using OpenSpout if template file is missing or population failed
        return response()->streamDownload(function () use ($records) {
            $writer = new XlsxWriter;
            $writer->openToFile('php://output');
            $writer->getCurrentSheet()->setName('Sheet');

            $headerStyle = (new Style)
                ->setFontBold()
                ->setFontColor('000000');

            $writer->addRow(Row::fromValues(self::HEADINGS, $headerStyle));

            foreach ($records as $record) {
                $writer->addRow(Row::fromValues(array_values($record)));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    /**
     * Populate promotion records into the official change-type template XLSX archive,
     * preserving all 8 sheets, formatting, and data validation rules.
     *
     * @param  array<int, array<string, string>>  $records
     */
    protected function populateTemplate(string $zipPath, array $records): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Unable to open template XLSX archive for writing.');
        }

        $sheet1Xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $sheetPath = 'xl/worksheets/sheet1.xml';

        if ($sheet1Xml === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (preg_match('#^xl/worksheets/sheet\d+\.xml$#i', $name)) {
                    $sheet1Xml = $zip->getFromName($name);
                    $sheetPath = $name;
                    break;
                }
            }
        }

        if ($sheet1Xml === false) {
            $zip->close();
            throw new \RuntimeException('No worksheet XML found in template XLSX.');
        }

        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        $useSharedStrings = ($sharedStringsXml !== false);

        if ($useSharedStrings) {
            $sstDoc = new DOMDocument;
            $sstDoc->loadXML($sharedStringsXml);
            $sstRoot = $sstDoc->documentElement;

            $stringCache = [];
            $index = 0;
            foreach ($sstRoot->getElementsByTagName('si') as $si) {
                $t = $si->getElementsByTagName('t')->item(0);
                $val = $t ? $t->nodeValue : '';
                if (! isset($stringCache[$val])) {
                    $stringCache[$val] = $index;
                }
                $index++;
            }

            $getSharedStringIndex = function (string $str) use ($sstDoc, $sstRoot, &$stringCache): int {
                if (isset($stringCache[$str])) {
                    return $stringCache[$str];
                }

                $newIndex = $sstRoot->getElementsByTagName('si')->length;
                $si = $sstDoc->createElement('si');
                $t = $sstDoc->createElement('t');
                $t->textContent = $str;
                $si->appendChild($t);
                $sstRoot->appendChild($si);

                $count = (int) $sstRoot->getAttribute('count') + 1;
                $uniqueCount = (int) $sstRoot->getAttribute('uniqueCount') + 1;
                $sstRoot->setAttribute('count', (string) $count);
                $sstRoot->setAttribute('uniqueCount', (string) $uniqueCount);

                $stringCache[$str] = $newIndex;

                return $newIndex;
            };
        }

        $keys = [
            'A' => 'username',
            'B' => 'start_date',
            'C' => 'end_date',
            'D' => 'job_title',
            'E' => 'branch',
            'F' => 'division',
            'G' => 'employment_type',
            'H' => 'employment_level',
            'I' => 'employment_step',
            'J' => 'change_type',
            'K' => 'use_existing_payroll_package',
        ];

        $rowsXml = '';
        $rowIdx = 2;

        foreach ($records as $record) {
            $rowContent = "<row r=\"{$rowIdx}\" spans=\"1:11\" x14ac:dyDescent=\"0.25\">";
            foreach (self::COLUMNS as $col) {
                $cellRef = "{$col}{$rowIdx}";
                $val = trim((string) ($record[$keys[$col]] ?? ''));

                if ($col === 'I') {
                    if ($val !== '') {
                        $rowContent .= "<c r=\"{$cellRef}\"><v>{$val}</v></c>";
                    } else {
                        $rowContent .= "<c r=\"{$cellRef}\"/>";
                    }
                } elseif ($val === '') {
                    $rowContent .= "<c r=\"{$cellRef}\"/>";
                } elseif ($useSharedStrings) {
                    $sId = $getSharedStringIndex($val);
                    $rowContent .= "<c r=\"{$cellRef}\" t=\"s\"><v>{$sId}</v></c>";
                } else {
                    $escaped = htmlspecialchars($val, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                    $rowContent .= "<c r=\"{$cellRef}\" t=\"inlineStr\"><is><t>{$escaped}</t></is></c>";
                }
            }
            $rowContent .= '</row>';
            $rowsXml .= $rowContent;
            $rowIdx++;
        }

        // Replace all rows after row 1 in sheet XML
        $pattern = '/(<sheetData><row r="1"[^>]*>.*?<\/row>).*?(<\/sheetData>)/s';
        $sheet1Xml = preg_replace($pattern, '$1'.$rowsXml.'$2', $sheet1Xml);

        // Update dimension ref to actual rows count
        $totalRows = count($records) + 1;
        $sheet1Xml = preg_replace('/<dimension ref="A1:[^"]*"/', "<dimension ref=\"A1:K{$totalRows}\"", $sheet1Xml);

        $zip->addFromString($sheetPath, $sheet1Xml);

        if ($useSharedStrings) {
            $zip->addFromString('xl/sharedStrings.xml', $sstDoc->saveXML());
        }

        $zip->close();
    }
}
