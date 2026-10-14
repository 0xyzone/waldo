<?php

namespace App\Services;

use DOMDocument;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class SsfUploadExportService
{
    /**
     * Exact Excel column headings required for SSF upload.
     *
     * @var list<string>
     */
    public const HEADINGS = [
        'Sno',
        'First name',
        'Mid name',
        'Last Name',
        'Gender',
        'DOB(YYYY.MM.DD)',
        'Country',
        'Email',
        'Contact Type',
        'Contact No',
        'Document',
        'Document No',
        'Issuing Office',
        'Issued Date(YYYY.MM.DD)',
        'Employement Type',
        'Post',
        'Join Date(YYYY.MM.DD)',
    ];

    /**
     * Column styles as defined in the official SSF template (ContributorENtry.xlsx).
     */
    protected const COLUMN_STYLES = [
        'A' => 1,
        'B' => 3,
        'C' => 3,
        'D' => 3,
        'E' => 3,
        'F' => 2,
        'G' => 2,
        'H' => 4,
        'I' => 4,
        'J' => 9,
        'K' => 3,
        'L' => 6,
        'M' => 3,
        'N' => 3,
        'O' => 3,
        'P' => 3,
        'Q' => 2,
    ];

    protected const COLUMNS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'];

    /**
     * Export the prepared SSF records. Uses the official SSF template workbook (with Entry and Sheet2)
     * so that the SSF portal column format check passes 100%.
     *
     * @param  array<int, array<string, mixed>>  $records
     */
    public function export(array $records): StreamedResponse
    {
        $fileName = 'ContributorEntry_'.now()->format('Y_m_d_His').'.xlsx';

        $templatePath = resource_path('templates/ContributorENtry.xlsx');
        if (! file_exists($templatePath)) {
            $templatePath = base_path('ContributorENtry.xlsx');
        }

        if (file_exists($templatePath)) {
            $tempPath = tempnam(sys_get_temp_dir(), 'ssf_upload_');
            copy($templatePath, $tempPath);

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
        }

        // Fallback using OpenSpout if template file is somehow missing
        return response()->streamDownload(function () use ($records) {
            $writer = new XlsxWriter;
            $writer->openToFile('php://output');

            $writer->getCurrentSheet()->setName('Entry');
            $writer->addRow(Row::fromValues(self::HEADINGS));

            $sno = 1;
            foreach ($records as $record) {
                $rowValues = [
                    $sno++,
                    (string) ($record['first_name'] ?? ''),
                    (string) ($record['mid_name'] ?? ''),
                    (string) ($record['last_name'] ?? ''),
                    (string) ($record['gender'] ?? ''),
                    (string) ($record['dob_bs'] ?? ''),
                    (string) ($record['country'] ?? 'NEPAL'),
                    (string) ($record['email'] ?? ''),
                    (string) ($record['contact_type'] ?? 'Mobile'),
                    (string) ($record['contact_no'] ?? ''),
                    (string) ($record['document'] ?? 'ना. प्र. प'),
                    (string) ($record['document_no'] ?? ''),
                    (string) ($record['issuing_office'] ?? ''),
                    (string) ($record['issued_date'] ?? ''),
                    (string) ($record['employement_type'] ?? 'कार्यगत'),
                    (string) ($record['post'] ?? ''),
                    (string) ($record['join_date'] ?? ''),
                ];

                $writer->addRow(Row::fromValues($rowValues));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    /**
     * Populate employee records into the official template XLSX archive.
     *
     * @param  array<int, array<string, mixed>>  $records
     */
    protected function populateTemplate(string $zipPath, array $records): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Unable to open template XLSX archive for writing.');
        }

        $sheet1Xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sheet1Xml === false || $sharedStringsXml === false) {
            $zip->close();
            throw new \RuntimeException('Required worksheet or sharedStrings not found in template XLSX.');
        }

        // Parse sharedStrings.xml
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

        // Build data rows starting at row 2
        $rowsXml = '';
        $rowIdx = 2;

        foreach ($records as $record) {
            $values = [
                'A' => (string) ($rowIdx - 1),
                'B' => trim((string) ($record['first_name'] ?? '')),
                'C' => trim((string) ($record['mid_name'] ?? '')),
                'D' => trim((string) ($record['last_name'] ?? '')),
                'E' => trim((string) ($record['gender'] ?? '')),
                'F' => trim((string) ($record['dob_bs'] ?? '')),
                'G' => trim((string) ($record['country'] ?? 'NEPAL')),
                'H' => trim((string) ($record['email'] ?? '')),
                'I' => trim((string) ($record['contact_type'] ?? 'Mobile')),
                'J' => trim((string) ($record['contact_no'] ?? '')),
                'K' => trim((string) ($record['document'] ?? 'ना. प्र. प')),
                'L' => trim((string) ($record['document_no'] ?? '')),
                'M' => trim((string) ($record['issuing_office'] ?? '')),
                'N' => trim((string) ($record['issued_date'] ?? '')),
                'O' => trim((string) ($record['employement_type'] ?? 'कार्यगत')),
                'P' => trim((string) ($record['post'] ?? '')),
                'Q' => trim((string) ($record['join_date'] ?? '')),
            ];

            $rowContent = "<row r=\"{$rowIdx}\" spans=\"1:17\" x14ac:dyDescent=\"0.25\">";
            foreach (self::COLUMNS as $col) {
                $cellRef = "{$col}{$rowIdx}";
                $styleId = self::COLUMN_STYLES[$col];
                $val = $values[$col];

                if ($col === 'A') {
                    $rowContent .= "<c r=\"{$cellRef}\" s=\"{$styleId}\"><v>{$val}</v></c>";
                } elseif ($val === '') {
                    $rowContent .= "<c r=\"{$cellRef}\" s=\"{$styleId}\"/>";
                } else {
                    $sId = $getSharedStringIndex($val);
                    $rowContent .= "<c r=\"{$cellRef}\" s=\"{$styleId}\" t=\"s\"><v>{$sId}</v></c>";
                }
            }
            $rowContent .= '</row>';
            $rowsXml .= $rowContent;
            $rowIdx++;
        }

        // Replace all rows after row 1 in sheet1.xml
        $pattern = '/(<sheetData><row r="1"[^>]*>.*?<\/row>).*?(<\/sheetData>)/s';
        $sheet1Xml = preg_replace($pattern, '$1'.$rowsXml.'$2', $sheet1Xml);

        // Update dimension ref to actual rows count
        $totalRows = count($records) + 1;
        $sheet1Xml = preg_replace('/<dimension ref="A1:[^"]*"/', "<dimension ref=\"A1:Q{$totalRows}\"", $sheet1Xml);

        // Write modified sheet1 and sharedStrings into zip
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet1Xml);
        $zip->addFromString('xl/sharedStrings.xml', $sstDoc->saveXML());

        $zip->close();
    }
}
