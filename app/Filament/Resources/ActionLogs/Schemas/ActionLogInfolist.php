<?php

namespace App\Filament\Resources\ActionLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ActionLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Action Log Overview')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                TextEntry::make('user_name')
                                    ->label('Performed By')
                                    ->badge()
                                    ->icon('heroicon-o-user')
                                    ->color('primary')
                                    ->placeholder('System / Auto'),

                                TextEntry::make('actionable_type')
                                    ->label('Resource Type')
                                    ->formatStateUsing(fn ($record) => $record->resource_name)
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('actionable_id')
                                    ->label('Record Identifier')
                                    ->formatStateUsing(fn ($record) => $record->record_title)
                                    ->badge()
                                    ->color('gray'),
                            ]),

                        Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                TextEntry::make('action')
                                    ->label('Action')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'created' => 'success',
                                        'status_changed' => 'info',
                                        'updated' => 'warning',
                                        'deleted' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => Str::headline($state)),

                                TextEntry::make('from_status')
                                    ->label('From Status')
                                    ->placeholder('—')
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('to_status')
                                    ->label('To Status')
                                    ->placeholder('—')
                                    ->badge()
                                    ->color('success'),
                            ]),

                        TextEntry::make('action_description')
                            ->label('Action Description')
                            ->columnSpanFull(),

                        TextEntry::make('created_at')
                            ->label('Timestamp')
                            ->dateTime()
                            ->timezone(config('app.timezone', 'Asia/Kathmandu')),
                    ]),

                Section::make('Attribute Modifications')
                    ->icon('heroicon-o-document-text')
                    ->description('Detailed breakdown of before and after field values.')
                    ->schema([
                        TextEntry::make('changes')
                            ->label('')
                            ->columnSpanFull()
                            ->placeholder('No specific field diff recorded.')
                            ->formatStateUsing(function ($state) {
                                if (empty($state) || ! is_array($state)) {
                                    return '<div class="text-xs text-gray-500 italic">No attribute modifications recorded.</div>';
                                }

                                $html = '<div class="overflow-x-auto"><table class="w-full text-xs text-left border border-gray-200 dark:border-gray-700 rounded-lg">';
                                $html .= '<thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200"><tr>';
                                $html .= '<th class="px-3 py-2 border-b">Field</th>';
                                $html .= '<th class="px-3 py-2 border-b">Old Value</th>';
                                $html .= '<th class="px-3 py-2 border-b">New Value</th>';
                                $html .= '</tr></thead><tbody>';

                                foreach ($state as $field => $val) {
                                    $old = is_array($val) && array_key_exists('old', $val) ? $val['old'] : null;
                                    $new = is_array($val) && array_key_exists('new', $val) ? $val['new'] : null;

                                    $oldStr = is_null($old) || $old === '' ? '<span class="text-gray-400 italic">null</span>' : htmlspecialchars((string) $old);
                                    $newStr = is_null($new) || $new === '' ? '<span class="text-gray-400 italic">null</span>' : htmlspecialchars((string) $new);

                                    $fieldName = htmlspecialchars(Str::headline(str_replace('_', ' ', $field)));

                                    $html .= '<tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50">';
                                    $html .= "<td class=\"px-3 py-2 font-mono font-medium text-gray-900 dark:text-gray-100\">{$fieldName}</td>";
                                    $html .= "<td class=\"px-3 py-2 text-rose-600 dark:text-rose-400 bg-rose-50/30 dark:bg-rose-950/20\">{$oldStr}</td>";
                                    $html .= "<td class=\"px-3 py-2 text-emerald-600 dark:text-emerald-400 bg-emerald-50/30 dark:bg-emerald-950/20 font-semibold\">{$newStr}</td>";
                                    $html .= '</tr>';
                                }

                                $html .= '</tbody></table></div>';

                                return $html;
                            })
                            ->html(),
                    ])
                    ->collapsible(),
            ]);
    }
}
