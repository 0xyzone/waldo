<?php

namespace App\Filament\Components;

use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ActionLogSection
{
    public static function make(string $relationship = 'actionLogs'): Section
    {
        return Section::make('Action Log History')
            ->icon('heroicon-o-clock')
            ->description('Automated history of status changes, field modifications, and user actions.')
            ->schema([
                Repeater::make($relationship)
                    ->relationship($relationship)
                    ->itemLabel(fn (array $state, $container): HtmlString => self::formatItemLabel($state, $container?->getRecord()))
                    ->truncateItemLabel(false)
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 4])
                            ->schema([
                                TextInput::make('user_name')
                                    ->label('Done By')
                                    ->disabled(),

                                TextInput::make('to_status')
                                    ->label('Status Set')
                                    ->formatStateUsing(fn ($state, $record) => $state ?: ($record?->action ?? 'updated'))
                                    ->disabled(),

                                TextInput::make('action_description')
                                    ->label('Action Log')
                                    ->disabled(),

                                DateTimePicker::make('created_at')
                                    ->label('Timestamp')
                                    ->disabled(),
                            ]),

                        Placeholder::make('changes')
                            ->hiddenLabel()
                            ->visible(fn (?Model $record, Get $get): bool => ! empty($record?->changes) || ! empty($get('changes')))
                            ->view('filament.components.action-log-changes-table')
                            ->columnSpanFull(),
                    ])
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),
            ])
            ->hidden(fn (string $operation): bool => $operation === 'create')
            ->columnSpanFull()
            ->collapsible();
    }

    /**
     * Format a clear, scannable summary row for the repeater item header even when collapsed.
     *
     * @param  array<string, mixed>  $state
     */
    public static function formatItemLabel(array $state, ?Model $record = null): HtmlString
    {
        $userName = trim((string) ($state['user_name'] ?? $record?->user_name ?? 'System'));
        if ($userName === '') {
            $userName = 'System';
        }

        $status = trim((string) ($state['to_status'] ?? $record?->to_status ?? ''));
        if ($status === '') {
            $status = trim((string) ($state['action'] ?? $record?->action ?? 'updated'));
        }

        $description = trim((string) ($state['action_description'] ?? $record?->action_description ?? 'Record updated'));

        $rawCreatedAt = $state['created_at'] ?? $record?->created_at ?? null;
        $formattedTime = '';
        if (! empty($rawCreatedAt)) {
            try {
                $formattedTime = Carbon::parse($rawCreatedAt)->format('M d, Y h:i:s A');
            } catch (\Throwable) {
                $formattedTime = (string) $rawCreatedAt;
            }
        }

        $changes = $record?->changes ?? ($state['changes'] ?? null);
        if (is_string($changes)) {
            $decoded = json_decode($changes, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $changes = $decoded;
            }
        }
        $changesCount = is_array($changes) ? count($changes) : 0;

        $statusLower = strtolower($status);
        $badgeClasses = match (true) {
            in_array($statusLower, ['created', 'approved', 'completed', 'active', 'released', 'release', 'validated']) => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/30',
            in_array($statusLower, ['deleted', 'rejected', 'terminated', 'suspended', 'cancelled', 'hold']) => 'bg-rose-500/15 text-rose-700 dark:text-rose-300 border-rose-500/30',
            in_array($statusLower, ['pending', 'draft', 'resigning this month']) => 'bg-amber-500/15 text-amber-700 dark:text-amber-300 border-amber-500/30',
            default => 'bg-blue-500/15 text-blue-700 dark:text-blue-300 border-blue-500/30',
        };

        $escapedUser = e($userName);
        $escapedStatus = e(ucfirst($status));
        $escapedDesc = e($description);
        $escapedTime = e($formattedTime);

        $changesPill = '';
        if ($changesCount > 0) {
            $label = $changesCount === 1 ? '1 field diff' : "{$changesCount} field diffs";
            $changesPill = '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-medium bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200/80 dark:border-gray-700/80 shrink-0">'.$label.'</span>';
        }

        $timeHtml = $escapedTime !== ''
            ? '<span class="text-[11px] font-mono text-gray-400 dark:text-gray-500 shrink-0 ml-auto">'.$escapedTime.'</span>'
            : '';

        return new HtmlString(
            '<div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 w-full text-xs pr-2">'
            .'<div class="flex flex-wrap items-center gap-2 min-w-0">'
            .'<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border '.$badgeClasses.' shrink-0">'.$escapedStatus.'</span>'
            .'<span class="font-bold text-gray-900 dark:text-gray-100 truncate max-w-md">'.$escapedDesc.'</span>'
            .$changesPill
            .'<span class="text-[11px] text-gray-500 dark:text-gray-400 shrink-0">by <strong class="font-semibold text-gray-700 dark:text-gray-300">'.$escapedUser.'</strong></span>'
            .'</div>'
            .$timeHtml
            .'</div>'
        );
    }
}
