<?php

namespace App\Filament\Resources\TipsDepartmentMappings\Tables;

use App\Models\Department;
use App\Models\TipsDepartmentMapping;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TipsDepartmentMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('page_name')
                    ->label('Report Page / Tab Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('department_ids')
                    ->label('Assigned Departments')
                    ->badge()
                    ->color('info')
                    ->state(function (TipsDepartmentMapping $record): array {
                        $ids = (array) ($record->department_ids ?? []);
                        if (empty($ids)) {
                            return [];
                        }

                        static $departmentMap = null;
                        if ($departmentMap === null) {
                            $departmentMap = Department::pluck('name', 'id')->toArray();
                        }

                        $names = [];
                        foreach ($ids as $id) {
                            if (isset($departmentMap[$id])) {
                                $names[] = $departmentMap[$id];
                            } elseif (in_array($id, $departmentMap, true)) {
                                $names[] = (string) $id;
                            } elseif (is_string($id) && filled($id)) {
                                $names[] = $id;
                            }
                        }

                        return $names;
                    })
                    ->searchable(query: function ($query, string $search) {
                        $deptIds = Department::where('name', 'like', "%{$search}%")->pluck('id')->toArray();

                        return $query->where(function ($q) use ($deptIds, $search) {
                            if (! empty($deptIds)) {
                                foreach ($deptIds as $deptId) {
                                    $q->orWhereJsonContains('department_ids', $deptId)
                                        ->orWhereJsonContains('department_ids', (string) $deptId);
                                }
                            }
                            $q->orWhere('department_ids', 'like', "%{$search}%");
                        });
                    })
                    ->placeholder('—')
                    ->wrap(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
