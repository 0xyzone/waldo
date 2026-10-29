<?php

namespace App\Filament\Pages;

use Agroezinger\FilamentShieldEnhanced\Traits\HasPageShield;
use App\Models\AppSetting;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageRoyaltyBonusSettings extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::AdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Royalty Bonus Management';

    protected static ?string $navigationLabel = 'Royalty Settings';

    protected static ?string $title = 'Royalty Bonus Settings';

    protected static ?string $slug = 'royalty-bonus/settings';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.manage-royalty-bonus-settings';

    /**
     * Form state properties.
     */
    public ?string $royalty_absent_mode = 'boolean';

    public ?string $royalty_absent_amount = '500.00';

    public ?string $royalty_late_early_mode = 'countable';

    public ?string $royalty_late_early_threshold = '3';

    public ?string $royalty_late_early_penalty = '100.00';

    public ?string $royalty_missing_thumb_mode = 'countable';

    public ?string $royalty_missing_thumb_threshold = '2';

    public ?string $royalty_missing_thumb_penalty = '100.00';

    public bool $royalty_enforce_min_floor = true;

    public static function getShieldPagePermissions(): array
    {
        return [
            'view' => 'View Royalty Settings',
            'save' => 'Save Royalty Settings',
        ];
    }

    public function mount(): void
    {
        $this->royalty_absent_mode = AppSetting::get('royalty_absent_mode', 'boolean');
        $this->royalty_absent_amount = AppSetting::get('royalty_absent_amount', '500.00');
        $this->royalty_late_early_mode = AppSetting::get('royalty_late_early_mode', 'countable');
        $this->royalty_late_early_threshold = AppSetting::get('royalty_late_early_threshold', '3');
        $this->royalty_late_early_penalty = AppSetting::get('royalty_late_early_penalty', '100.00');
        $this->royalty_missing_thumb_mode = AppSetting::get('royalty_missing_thumb_mode', 'countable');
        $this->royalty_missing_thumb_threshold = AppSetting::get('royalty_missing_thumb_threshold', '2');
        $this->royalty_missing_thumb_penalty = AppSetting::get('royalty_missing_thumb_penalty', '100.00');
        $this->royalty_enforce_min_floor = (bool) AppSetting::get('royalty_enforce_min_floor', '1');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Absent Days Deduction Rules')
                    ->description('Specify how absences impact the monthly royalty bonus.')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('royalty_absent_mode')
                                ->label('Absent Deduction Mode')
                                ->options([
                                    'boolean' => 'Flat Deduction (Deduct once if absent days > 0)',
                                    'countable' => 'Per Absent Day (Multiply penalty by number of absent days)',
                                ])
                                ->required()
                                ->native(false),
                            TextInput::make('royalty_absent_amount')
                                ->label('Absent Deduction Amount (Rs.)')
                                ->numeric()
                                ->prefix('Rs.')
                                ->required()
                                ->helperText('Flat amount deducted if absent, or per absent day.'),
                        ]),
                    ]),

                Section::make('Late In & Early Out Deduction Rules')
                    ->description('Set tolerance grace thresholds and penalties for tardiness.')
                    ->icon(Heroicon::OutlinedClock)
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('royalty_late_early_mode')
                                ->label('Deduction Mode')
                                ->options([
                                    'countable' => 'Per Excess Incident (Multiply penalty by occurrences beyond threshold)',
                                    'boolean' => 'Flat Penalty (Deduct once if count exceeds threshold)',
                                ])
                                ->required()
                                ->native(false),
                            TextInput::make('royalty_late_early_threshold')
                                ->label('Grace Threshold (Count)')
                                ->numeric()
                                ->default(3)
                                ->required()
                                ->helperText('Occurrences up to this number incur no deduction.'),
                            TextInput::make('royalty_late_early_penalty')
                                ->label('Penalty Amount (Rs.)')
                                ->numeric()
                                ->prefix('Rs.')
                                ->required()
                                ->helperText('Amount per excess occurrence or flat penalty.'),
                        ]),
                    ]),

                Section::make('Missing Thumb / Biometric Punch Rules')
                    ->description('Configure penalties for unpunched / missing biometric entries.')
                    ->icon(Heroicon::OutlinedFingerPrint)
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('royalty_missing_thumb_mode')
                                ->label('Deduction Mode')
                                ->options([
                                    'countable' => 'Per Excess Incident (Multiply penalty by occurrences beyond threshold)',
                                    'boolean' => 'Flat Penalty (Deduct once if count exceeds threshold)',
                                ])
                                ->required()
                                ->native(false),
                            TextInput::make('royalty_missing_thumb_threshold')
                                ->label('Grace Threshold (Count)')
                                ->numeric()
                                ->default(2)
                                ->required()
                                ->helperText('Occurrences up to this number incur no deduction.'),
                            TextInput::make('royalty_missing_thumb_penalty')
                                ->label('Penalty Amount (Rs.)')
                                ->numeric()
                                ->prefix('Rs.')
                                ->required()
                                ->helperText('Amount per excess occurrence or flat penalty.'),
                        ]),
                    ]),

                Section::make('Payout Protection & Floor')
                    ->description('Guarantee a minimum payout baseline for employees with bonuses.')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->schema([
                        Toggle::make('royalty_enforce_min_floor')
                            ->label('Enforce Designation Monthly Minimum Payout Floor')
                            ->helperText('When enabled, the final monthly payout after attendance and performance deductions will never be lower than the designation\'s Monthly Minimum Payout.')
                            ->default(true),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        AppSetting::set('royalty_absent_mode', (string) ($data['royalty_absent_mode'] ?? 'boolean'));
        AppSetting::set('royalty_absent_amount', (string) ($data['royalty_absent_amount'] ?? '500.00'));
        AppSetting::set('royalty_late_early_mode', (string) ($data['royalty_late_early_mode'] ?? 'countable'));
        AppSetting::set('royalty_late_early_threshold', (string) ($data['royalty_late_early_threshold'] ?? '3'));
        AppSetting::set('royalty_late_early_penalty', (string) ($data['royalty_late_early_penalty'] ?? '100.00'));
        AppSetting::set('royalty_missing_thumb_mode', (string) ($data['royalty_missing_thumb_mode'] ?? 'countable'));
        AppSetting::set('royalty_missing_thumb_threshold', (string) ($data['royalty_missing_thumb_threshold'] ?? '2'));
        AppSetting::set('royalty_missing_thumb_penalty', (string) ($data['royalty_missing_thumb_penalty'] ?? '100.00'));
        AppSetting::set('royalty_enforce_min_floor', ! empty($data['royalty_enforce_min_floor']) ? '1' : '0');

        Notification::make()
            ->title('Settings Saved Successfully')
            ->body('Royalty bonus calculation and deduction rules have been updated.')
            ->success()
            ->send();
    }
}
