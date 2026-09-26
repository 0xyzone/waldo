<?php

namespace App\Filament\Resources\TipsReports\Pages;

use App\Filament\Resources\TipsReports\TipsReportResource;
use App\Services\TipsCalculationService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTipsReport extends EditRecord
{
    protected static string $resource = TipsReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        try {
            app(TipsCalculationService::class)->generate($record, [
                'company_errors' => $record->company_errors ?? [],
                'left_outs' => $record->left_outs ?? [],
            ]);

            Notification::make()
                ->title('Tips Distribution Re-calculated')
                ->body("Successfully recalculated {$record->items()->count()} distribution records.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Calculation Notice')
                ->body($e->getMessage())
                ->warning()
                ->send();
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
