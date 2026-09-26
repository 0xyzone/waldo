<?php

namespace App\Filament\Resources\TipsReports\Pages;

use App\Filament\Resources\TipsReports\TipsReportResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTipsReport extends EditRecord
{
    protected static string $resource = TipsReportResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->getRecord()->isValidated()) {
            Notification::make()
                ->title('Editing Disabled')
                ->body('This report has been validated and locked. Editing is not permitted.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->getRecord()]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn () => $this->getRecord()->isValidated()),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        Notification::make()
            ->title('Report Saved')
            ->body('Report updated successfully. Existing generated calculations remain intact.')
            ->success()
            ->send();

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
