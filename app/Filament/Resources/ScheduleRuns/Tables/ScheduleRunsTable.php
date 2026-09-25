<?php

namespace App\Filament\Resources\ScheduleRuns\Tables;

use App\Models\ScheduleRun;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

class ScheduleRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('15s')
            ->defaultSort('started_at', 'desc')
            ->columns([
                TextColumn::make('command')
                    ->label('Command / Task')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'running' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'success' => 'heroicon-m-check-circle',
                        'failed' => 'heroicon-m-x-circle',
                        'running' => 'heroicon-m-arrow-path',
                        default => 'heroicon-m-question-mark-circle',
                    }),

                TextColumn::make('started_at')
                    ->label('Started At')
                    ->dateTime()
                    ->timezone(config('app.timezone', 'Asia/Kathmandu'))
                    ->sortable(),

                TextColumn::make('finished_at')
                    ->label('Finished At')
                    ->dateTime()
                    ->timezone(config('app.timezone', 'Asia/Kathmandu'))
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->suffix(' s')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('exit_code')
                    ->label('Exit Code')
                    ->badge()
                    ->color(fn (?int $state): string => $state === 0 ? 'success' : 'danger')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'running' => 'Running',
                    ]),

                SelectFilter::make('command')
                    ->label('Command')
                    ->options(fn () => ScheduleRun::query()->distinct()->pluck('command', 'command')->toArray())
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('runAgain')
                    ->label('Run Now')
                    ->icon('heroicon-m-play')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Execute Scheduled Command')
                    ->modalDescription(fn (ScheduleRun $record) => "Are you sure you want to run '{$record->command}' right now?")
                    ->action(function (ScheduleRun $record) {
                        $rawCmd = $record->command;
                        // Strip 'php artisan ' if present
                        $cmd = preg_replace('/^php\s+artisan\s+/i', '', $rawCmd);

                        try {
                            $exitCode = Artisan::call($cmd);
                            $output = trim(Artisan::output());

                            Notification::make()
                                ->title($exitCode === 0 ? 'Command Executed Successfully' : 'Command Failed')
                                ->body($output ?: "Exit code: {$exitCode}")
                                ->color($exitCode === 0 ? 'success' : 'danger')
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Execution Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([]);
    }
}
