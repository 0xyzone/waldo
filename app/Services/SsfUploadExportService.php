<?php

namespace App\Services;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Export the prepared SSF records to an Excel file with sheet named 'Entry'.
     *
     * @param  array<int, array<string, mixed>>  $records
     */
    public function export(array $records): StreamedResponse
    {
        $fileName = 'ssf_upload_file_'.now()->format('Y_m_d_His').'.xlsx';

        return response()->streamDownload(function () use ($records) {
            $writer = new XlsxWriter;
            $writer->openToFile('php://output');

            // Set the internal worksheet name to 'Entry' as required
            $writer->getCurrentSheet()->setName('Entry');

            // Clean text format with no color styling
            $writer->addRow(Row::fromValues(self::HEADINGS));

            // Data Rows
            $sno = 1;
            foreach ($records as $record) {
                $rowValues = [
                    $sno++,
                    (string) ($record['first_name'] ?? ''),
                    (string) ($record['mid_name'] ?? ''),
                    (string) ($record['last_name'] ?? ''),
                    (string) ($record['gender'] ?? ''),
                    (string) ($record['dob_bs'] ?? ''),
                    (string) ($record['country'] ?? 'Nepal'),
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
}
