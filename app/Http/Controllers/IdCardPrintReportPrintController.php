<?php

namespace App\Http\Controllers;

use App\Models\IdCardPrintReport;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdCardPrintReportPrintController extends Controller
{
    /**
     * Print ID card distribution sheet for report/batch.
     */
    public function print(Request $request, IdCardPrintReport $report): Response
    {
        $department = $request->query('department');

        $query = $report->items()->where('status', 'printed');

        if ($department && strtoupper($department) !== 'ALL') {
            $query->where('department', $department);
            $deptTitle = $department;
        } else {
            $deptTitle = 'All Departments';
        }

        $items = $query
            ->orderBy('department')
            ->orderBy('designation')
            ->orderByNumericCode()
            ->get();

        return response()->view('id-card-reports.print-sheet', [
            'report' => $report,
            'department' => $deptTitle,
            'status' => 'Printed',
            'items' => $items,
        ]);
    }
}
