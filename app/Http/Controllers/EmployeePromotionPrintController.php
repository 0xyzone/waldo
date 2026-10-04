<?php

namespace App\Http\Controllers;

use App\Models\EmployeePromotion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EmployeePromotionPrintController extends Controller
{
    /**
     * Display a print-ready A4 congratulatory letter/announcement for selected promotion records.
     */
    public function printCongratulations(Request $request): View
    {
        $rawIds = $request->query('ids', '');
        $ids = [];

        if (is_array($rawIds)) {
            $ids = array_filter(array_map('intval', $rawIds));
        } elseif (is_string($rawIds) && trim($rawIds) !== '') {
            $ids = array_filter(array_map('intval', explode(',', $rawIds)));
        }

        $promotions = collect();

        if (! empty($ids)) {
            $promotions = EmployeePromotion::with([
                'employee',
                'fromDepartment',
                'fromDesignation',
                'toDepartment',
                'toDesignation',
            ])
                ->whereIn('id', $ids)
                ->orderBy('promotion_date', 'desc')
                ->orderBy('employee_id', 'asc')
                ->get();
        }

        return view('employee-promotions.print-congratulations', [
            'promotions' => $promotions,
            'selectedCount' => $promotions->count(),
            'generatedDate' => now(),
        ]);
    }
}
