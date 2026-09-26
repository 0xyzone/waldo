<?php

require 'vendor/autoload.php';

use OpenSpout\Reader\XLSX\Reader;

$reader = new Reader;
$reader->open('storage/app/public/hrms-tips/01M3EGN65647JZ0K8NTWXCYMD0.xlsx');

$row3 = [];
$row4 = [];
$count = 0;
foreach ($reader->getSheetIterator() as $sheet) {
    foreach ($sheet->getRowIterator() as $row) {
        $count++;
        if ($count === 3) {
            $row3 = $row->toArray();
        }
        if ($count === 4) {
            $row4 = $row->toArray();
        }
        if ($count >= 5) {
            $data = $row->toArray();
            echo "Employee: {$data[2]} ({$data[1]})\n";
            for ($i = 6; $i < count($data); $i++) {
                $category = $row3[$i] ?? '';
                if (empty($category) && $i > 0 && ($row4[$i] ?? '') === 'Remaining Balance') {
                    $category = $row3[$i - 1] ?? '';
                }
                $type = $row4[$i] ?? '';
                echo "  Col {$i} [{$category} | {$type}]: {$data[$i]}\n";
            }
            break;
        }
    }
    break;
}
$reader->close();
