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
                ->get()
                ->sort(function (EmployeePromotion $a, EmployeePromotion $b): int {
                    $rankA = $a->toDesignation?->rank;
                    $rankB = $b->toDesignation?->rank;

                    if ($rankA === null && $rankB !== null) {
                        return 1;
                    }
                    if ($rankA !== null && $rankB === null) {
                        return -1;
                    }
                    if ($rankA !== $rankB) {
                        return $rankA <=> $rankB;
                    }

                    $nameA = $a->toDesignation?->name ?? '';
                    $nameB = $b->toDesignation?->name ?? '';
                    $nameCmp = strcasecmp($nameA, $nameB);
                    if ($nameCmp !== 0) {
                        return $nameCmp;
                    }

                    return strcmp((string) $a->employee_id, (string) $b->employee_id);
                })
                ->values();
        }

        return view('employee-promotions.print-congratulations', [
            'promotions' => $promotions,
            'selectedCount' => $promotions->count(),
            'generatedDate' => now(),
        ]);
    }
}
