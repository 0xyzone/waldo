<?php

namespace App\Http\Controllers;

use App\Models\SalaryIncrementRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SalaryIncrementRequestPrintController extends Controller
{
    /**
     * Print blank salary increment recommendation form (2x A5 on 1x A4 sheet).
     */
    public function printBlank(Request $request): Response
    {
        $sheets = max(1, min(20, (int) $request->query('sheets', 1)));

        return response()->view('salary-increment-requests.print-form', [
            'isRecord' => false,
            'record' => null,
            'sheets' => $sheets,
        ]);
    }

    /**
     * Print salary increment recommendation form for a specific record (2x A5 on 1x A4 sheet).
     */
    public function printRecord(Request $request, SalaryIncrementRequest $record): Response
    {
        $record->loadMissing([
            'employee',
            'hod',
            'department',
            'currentDesignation',
            'proposedDesignation',
            'hrAcknowledgedBy',
            'financeAcknowledgedBy',
        ]);

        return response()->view('salary-increment-requests.print-form', [
            'isRecord' => true,
            'record' => $record,
            'sheets' => 1,
        ]);
    }
}
