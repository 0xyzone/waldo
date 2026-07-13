<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class BirthdayListWidget extends Widget
{
    use HasWidgetShield;
    protected string $view = 'filament.widgets.birthday-list-widget';

    // Full column span so container extends, but width is capped in blade for elegant look
    protected int|string|array $columnSpan = 'full';

    public $month;

    public $year;

    public function mount()
    {
        $this->month = date('n'); // current month
        $this->year = date('Y');  // current year
    }
}
