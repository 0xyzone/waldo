<?php

namespace App\Filament\Resources\Candidates\Tables;

use Alsaloul\ImageGallery\Tables\Columns\ImageGalleryColumn;
use App\Models\Department;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CandidatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone_number')
                    ->label('Phone Number')
                    ->searchable(),
                ImageGalleryColumn::make('cv_image')
                    ->label('CV / Images')
                    ->disk('public')
                    ->visibility('public')
                    ->stacked()
                    ->limit(3)
                    ->overlap(4)
                    ->limitedRemainingText()
                    ->remainingTextBadge(true),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->badge(),
                SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'contacted' => 'Contacted',
                        'unreachable' => 'Unreachable',
                        'not_coming' => 'Not Coming',
                        'approved' => 'Approved',
                        'no_show' => 'No Show',
                        'rejected' => 'Rejected',
                    ]),
                TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable(),
                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(20)
                    ->tooltip(fn ($state) => $state),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(fn () => Department::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'contacted' => 'Contacted',
                        'unreachable' => 'Unreachable',
                        'not_coming' => 'Not Coming',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Applied From')
                            ->native(false),
                        DatePicker::make('created_to')
                            ->label('Applied To')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
