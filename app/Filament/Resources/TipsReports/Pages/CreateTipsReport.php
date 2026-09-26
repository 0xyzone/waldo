<?php

namespace App\Filament\Resources\TipsReports\Pages;

use App\Filament\Resources\TipsReports\TipsReportResource;
use App\Services\TipsCalculationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateTipsReport extends CreateRecord
{
    protected static string $resource = TipsReportResource::class;

    /**
     * Store temporary wizard data for job dispatch.
     *
     * @var array<string, mixed>
     */
    protected array $wizardExtraData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['status'] = 'draft';

        // Keep extra wizard data for generation
        $this->wizardExtraData = [
            'company_errors' => $data['company_errors'] ?? [],
            'left_outs' => $data['left_outs'] ?? [],
        ];

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        // Generate Tips immediately via calculation service
        try {
            app(TipsCalculationService::class)->generate($record, $this->wizardExtraData);

            Notification::make()
                ->title('Tips Distribution Generated')
                ->body("Successfully processed attendance and generated {$record->items()->count()} distribution records.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Generation Notice')
                ->body('Report created, but calculation encountered an issue: '.$e->getMessage())
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
