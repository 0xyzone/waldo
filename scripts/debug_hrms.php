<?php

require 'vendor/autoload.php';

use OpenSpout\Reader\XLSX\Reader;

$reader = new Reader;
$reader->open('storage/app/public/hrms-tips/01M3EGN65647JZ0K8NTWXCYMD0.xlsx');

$sheetIndex = 0;
foreach ($reader->getSheetIterator() as $sheet) {
    $sheetIndex++;
    echo "Sheet {$sheetIndex}: ".$sheet->getName().PHP_EOL;
    $count = 0;
    foreach ($sheet->getRowIterator() as $row) {
        $count++;
        if ($count <= 4) {
            echo "  Row {$count}: ".json_encode($row->toArray(), JSON_UNESCAPED_UNICODE).PHP_EOL;
        } else {
            break;
        }
    }
}
$reader->close();
