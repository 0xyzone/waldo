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
        if ($count > 4) {
            $data = $row->toArray();
            // print any non-zero leave usage
            $leavesUsed = [];
            for ($i = 8; $i < count($data); $i += 2) {
                $val = $data[$i] ?? '-';
                if ($val !== '-' && is_numeric($val) && (float) $val > 0) {
                    $cat = $row3[$i] ?? "Col {$i}";
                    $leavesUsed[$cat] = (float) $val;
                }
            }
            if (! empty($leavesUsed)) {
                echo "Employee: {$data[2]} ({$data[1]}):\n";
                print_r($leavesUsed);
            }
        }
    }
    break;
}
$reader->close();
