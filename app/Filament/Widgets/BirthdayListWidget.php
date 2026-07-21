<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class BirthdayListWidget extends Widget
{
    use HasWidgetShield;

    protected string $view = 'filament.widgets.birthday-list-widget';

    protected static ?int $sort = 1;

    // Full column span so container extends, but width is capped in blade for elegant look
    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    public $month;

    public $year;

    public function mount()
    {
        $this->month = date('n'); // current month
        $this->year = date('Y');  // current year
    }

    /**
     * Get birthdays celebrating in the selected month.
     */
    public function getBirthdaysProperty(): Collection
    {
        if (empty($this->month) || empty($this->year)) {
            return collect();
        }

        try {
            $month = (int) $this->month;
            $year = (int) $this->year;

            $selectedDate = Carbon::createFromDate($year, $month, 1);
            $previousMonth = $selectedDate->copy()->subMonth();
            $cutoffDate = $previousMonth->copy()->setDay(10)->format('Y-m-d');

            return Employee::with(['department', 'designation'])
                ->whereIn('employee_status', ['Active', 'Resigning this month'])
                ->whereMonth('dob_ad', $month)
                ->get()
                ->filter(function ($emp) use ($cutoffDate) {
                    if (empty($emp->join_date_formatted)) {
                        return false;
                    }
                    try {
                        $cleanedDate = str_replace(',', '', $emp->join_date_formatted);

                        return Carbon::parse($cleanedDate)->format('Y-m-d') < $cutoffDate;
                    } catch (\Exception) {
                        return false;
                    }
                })
                ->map(function ($emp) {
                    $emp->dob_day = $emp->dob_ad ? $emp->dob_ad->day : 99;

                    return $emp;
                })
                ->sortBy([
                    ['dp_rank', 'asc'],
                    ['rank', 'asc'],
                    ['dob_day', 'asc'],
                ]);
        } catch (\Exception $e) {
            return collect();
        }
    }
}
