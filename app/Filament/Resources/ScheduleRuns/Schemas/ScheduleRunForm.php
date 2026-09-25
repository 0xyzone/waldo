<?php

namespace App\Filament\Resources\ScheduleRuns\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ScheduleRunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('command')
                    ->label('Command / Task')
                    ->columnSpanFull()
                    ->weight('bold'),

                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'running' => 'warning',
                        default => 'gray',
                    }),

                TextEntry::make('started_at')
                    ->label('Started At')
                    ->dateTime()
                    ->timezone(config('app.timezone', 'Asia/Kathmandu')),

                TextEntry::make('finished_at')
                    ->label('Finished At')
                    ->dateTime()
                    ->timezone(config('app.timezone', 'Asia/Kathmandu'))
                    ->placeholder('-'),

                TextEntry::make('duration_seconds')
                    ->label('Duration')
                    ->suffix(' seconds')
                    ->placeholder('-'),

                TextEntry::make('exit_code')
                    ->label('Exit Code')
                    ->placeholder('-'),

                TextEntry::make('output')
                    ->label('Output / Error Trace')
                    ->columnSpanFull()
                    ->placeholder('No output captured.')
                    ->formatStateUsing(fn ($state) => $state ? '<pre style="max-height: 400px; overflow-y: auto; background-color: #111827; padding: 1rem; border-radius: 0.5rem; color: #f9fafb; font-size: 0.85rem;">'.htmlspecialchars($state).'</pre>' : null)
                    ->html(),
            ]);
    }
}
