<?php

namespace App\Filament\Widgets;

use App\Helpers\NepaliDate\NepaliDate;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class DateConverterWidget extends Widget
{
    use HasWidgetShield;

    protected string $view = 'filament.widgets.date-converter-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    public string $activeTab = 'ad_to_bs'; // 'ad_to_bs' or 'bs_to_ad'

    // AD to BS properties
    public ?string $adDate = null;

    public ?string $convertedBsDate = null;

    public ?string $convertedBsDateNp = null;

    public ?string $convertedBsWeekday = null;

    // BS to AD properties
    public ?string $bsYear = null;

    public ?string $bsMonth = null;

    public ?string $bsDay = null;

    public ?string $convertedAdDate = null;

    public ?string $convertedAdWeekday = null;

    // Available BS years and days options
    public array $bsYears = [];

    public array $bsDays = [];

    public function mount(): void
    {
        // Populate BS years: range is 2000 to 2089
        $this->bsYears = range(2000, 2089);
        $this->bsDays = range(1, 32);

        // Initialize with today's date
        $today = Carbon::today();
        $this->adDate = $today->format('Y-m-d');

        $converter = new NepaliDate;

        // Initial AD to BS conversion
        $this->convertAdToBs();

        // Initial BS to AD properties (initialized with today's equivalent BS date)
        $convertedToday = $converter->convertAdToBs($today->year, $today->month, $today->day);
        if (! empty($convertedToday)) {
            $this->bsYear = (string) $convertedToday['year'];
            $this->bsMonth = (string) $convertedToday['month'];
            $this->bsDay = (string) $convertedToday['day'];
        } else {
            $this->bsYear = '2083';
            $this->bsMonth = '4';
            $this->bsDay = '5';
        }

        $this->convertBsToAd();
    }

    public function updatedAdDate(): void
    {
        $this->convertAdToBs();
    }

    public function updatedBsYear(): void
    {
        $this->convertBsToAd();
    }

    public function updatedBsMonth(): void
    {
        $this->convertBsToAd();
    }

    public function updatedBsDay(): void
    {
        $this->convertBsToAd();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function convertAdToBs(): void
    {
        if (empty($this->adDate)) {
            $this->convertedBsDate = null;
            $this->convertedBsDateNp = null;
            $this->convertedBsWeekday = null;

            return;
        }

        try {
            $date = Carbon::parse($this->adDate);
            $converter = new NepaliDate;

            // Check if year is within range 1944 to 2033
            if ($date->year < 1944 || $date->year > 2033) {
                $this->convertedBsDate = 'Out of supported range (1944 - 2033)';
                $this->convertedBsDateNp = null;
                $this->convertedBsWeekday = null;

                return;
            }

            $converted = $converter->convertAdToBs($date->year, $date->month, $date->day);
            if (! empty($converted)) {
                $this->convertedBsDate = sprintf('%04d.%02d.%02d', $converted['year'], $converted['month'], $converted['day']);

                // Get detailed localized date information
                $detailsNp = $converter->getDetails($date->year, $date->month, $date->day, 'ad', 'np');

                if (! empty($detailsNp)) {
                    $this->convertedBsDateNp = sprintf('%s %s %s', $detailsNp['Y'], $detailsNp['F'], $detailsNp['j']);
                    $this->convertedBsWeekday = $detailsNp['l']; // weekday in nepali
                } else {
                    $this->convertedBsDateNp = null;
                    $this->convertedBsWeekday = null;
                }
            } else {
                $this->convertedBsDate = 'Conversion failed';
                $this->convertedBsDateNp = null;
                $this->convertedBsWeekday = null;
            }
        } catch (\Exception $e) {
            $this->convertedBsDate = 'Invalid Date';
            $this->convertedBsDateNp = null;
            $this->convertedBsWeekday = null;
        }
    }

    public function convertBsToAd(): void
    {
        if (empty($this->bsYear) || empty($this->bsMonth) || empty($this->bsDay)) {
            $this->convertedAdDate = null;
            $this->convertedAdWeekday = null;

            return;
        }

        try {
            $converter = new NepaliDate;
            $y = (int) $this->bsYear;
            $m = (int) $this->bsMonth;
            $d = (int) $this->bsDay;

            // Validate date in BS
            $validation = $converter->validateDate($y, $m, $d, 'bs');
            if (empty($validation)) {
                $this->convertedAdDate = 'Invalid BS Date';
                $this->convertedAdWeekday = null;

                return;
            }

            $converted = $converter->convertBsToAd($y, $m, $d);
            if (! empty($converted)) {
                $carbonDate = Carbon::createFromDate($converted['year'], $converted['month'], $converted['day']);
                $this->convertedAdDate = $carbonDate->format('Y-m-d');
                $this->convertedAdWeekday = $carbonDate->format('l'); // Day of week e.g. Tuesday
            } else {
                $this->convertedAdDate = 'Conversion failed';
                $this->convertedAdWeekday = null;
            }
        } catch (\Exception $e) {
            $this->convertedAdDate = 'Invalid Date';
            $this->convertedAdWeekday = null;
        }
    }
}
