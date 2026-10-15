<?php

namespace App\Filament\Resources\TipsAdjustments\Schemas;

use App\Models\Employee;
use App\Models\TipsAdjustment;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class TipsAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('employee_info')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->content(function (Get $get, ?TipsAdjustment $record): HtmlString {
                        $code = $get('employee_id') ?? $record?->employee_id;
                        if (! $code) {
                            return new HtmlString('
                                <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 p-4 text-center">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Select an employee below to view their details and base tips amount.
                                    </p>
                                </div>
                            ');
                        }

                        $employee = Employee::with(['department', 'designation'])
                            ->where('employee_code', $code)
                            ->orWhere('id', $code)
                            ->first();

                        if (! $employee) {
                            return new HtmlString('
                                <div class="rounded-xl border border-amber-300 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-950/30 p-3 text-xs text-amber-600 dark:text-amber-400">
                                    Employee record not found.
                                </div>
                            ');
                        }

                        $baseTips = (float) ($employee->tips_amount ?? 0);
                        $formattedBase = 'Rs. '.number_format($baseTips, 2);
                        $name = htmlspecialchars((string) ($employee->name ?? '—'), ENT_QUOTES, 'UTF-8');
                        $empCode = htmlspecialchars(strtoupper((string) ($employee->employee_code ?? '—')), ENT_QUOTES, 'UTF-8');
                        $dept = htmlspecialchars((string) ($employee->department?->name ?? '—'), ENT_QUOTES, 'UTF-8');
                        $desig = htmlspecialchars((string) ($employee->designation?->name ?? '—'), ENT_QUOTES, 'UTF-8');

                        return new HtmlString("
                            <div class=\"rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-900/60 p-4 shadow-xs\">
                                <div class=\"grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm\">
                                    <div>
                                        <span class=\"text-xs font-medium text-gray-500 dark:text-gray-400 block mb-0.5\">Employee</span>
                                        <span class=\"font-semibold text-gray-900 dark:text-gray-100 text-base block leading-tight\">{$name}</span>
                                        <span class=\"text-xs text-gray-500 dark:text-gray-400 font-mono\">{$empCode}</span>
                                    </div>
                                    <div>
                                        <span class=\"text-xs font-medium text-gray-500 dark:text-gray-400 block mb-0.5\">Department</span>
                                        <span class=\"font-medium text-gray-800 dark:text-gray-200 block\">{$dept}</span>
                                    </div>
                                    <div>
                                        <span class=\"text-xs font-medium text-gray-500 dark:text-gray-400 block mb-0.5\">Designation</span>
                                        <span class=\"font-medium text-gray-800 dark:text-gray-200 block\">{$desig}</span>
                                    </div>
                                    <div class=\"rounded-lg border border-emerald-200 dark:border-emerald-800/60 bg-emerald-50/80 dark:bg-emerald-950/40 p-2.5\">
                                        <span class=\"text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block mb-0.5\">Base Tips Amount</span>
                                        <span class=\"text-base font-bold text-emerald-600 dark:text-emerald-300 block\">{$formattedBase}</span>
                                    </div>
                                </div>
                            </div>
                        ");
                    }),

                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => strtoupper($record->employee_code).' | '.$record->name)
                    ->preload()
                    ->searchable(['name', 'employee_code'])
                    ->native(false)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                        if (! $state) {
                            return;
                        }

                        $percentage = $get('percentage');
                        if ($percentage !== null && $percentage !== '' && is_numeric($percentage)) {
                            $employee = Employee::where('employee_code', $state)->orWhere('id', $state)->first();
                            $base = (float) ($employee?->tips_amount ?? 0);
                            if ($base > 0) {
                                $calculated = round($base * ((float) $percentage / 100), 2);
                                $set('amount', $calculated);
                            }
                        }
                    }),

                Select::make('type')
                    ->options([
                        'add' => 'Add',
                        'deduct' => 'Deduct',
                    ])
                    ->native(false)
                    ->required(),

                Select::make('for_month')
                    ->label('For Month')
                    ->options([
                        'january' => 'January Release',
                        'february' => 'February Release',
                        'march' => 'March Release',
                        'april' => 'April Release',
                        'may' => 'May Release',
                        'june' => 'June Release',
                        'july' => 'July Release',
                        'august' => 'August Release',
                        'september' => 'September Release',
                        'october' => 'October Release',
                        'november' => 'November Release',
                        'december' => 'December Release',
                    ])
                    ->default(strtolower(now()->addMonth()->format('F')))
                    ->native(false)
                    ->required(),

                TextInput::make('year')
                    ->label('Year')
                    ->default(now()->format('Y'))
                    ->required(),

                TextInput::make('percentage')
                    ->label('Percentage (%)')
                    ->placeholder('e.g. 10')
                    ->suffix('%')
                    ->numeric()
                    ->minValue(0)
                    ->helperText('Optional. Calculates amount automatically based on the employee\'s base tips.')
                    ->live(onBlur: true)
                    ->dehydrated(false)
                    ->default(function (?TipsAdjustment $record): ?float {
                        if (! $record || ! $record->amount) {
                            return null;
                        }
                        $base = (float) ($record->employee?->tips_amount ?? 0);
                        if ($base <= 0) {
                            return null;
                        }

                        return (float) round(((float) $record->amount / $base) * 100, 2);
                    })
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                        if ($state === null || $state === '' || ! is_numeric($state)) {
                            return;
                        }

                        $employeeId = $get('employee_id');
                        if (! $employeeId) {
                            return;
                        }

                        $employee = Employee::where('employee_code', $employeeId)->orWhere('id', $employeeId)->first();
                        $base = (float) ($employee?->tips_amount ?? 0);

                        if ($base > 0) {
                            $calculated = round($base * ((float) $state / 100), 2);
                            $set('amount', $calculated);
                        }
                    }),

                TextInput::make('amount')
                    ->label('Adjustment Amount')
                    ->required()
                    ->numeric()
                    ->prefix('Rs.')
                    ->helperText('Type manually or enter a percentage above to auto-calculate.'),

                Textarea::make('remarks')
                    ->columnSpanFull(),
            ]);
    }
}
