<?php

namespace App\Filament\Actions;

use App\Models\AppSetting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreateRecordLetterAction
{
    /**
     * Build a reusable "Create Letter" table row action for a given resource type.
     */
    public static function make(string $sourceType): Action
    {
        $settingKey = match ($sourceType) {
            'promotion' => 'letter_template_promotion_id',
            'transfer' => 'letter_template_transfer_id',
            'leaver' => 'letter_template_leaver_id',
            'terminated' => 'letter_template_terminated_id',
            default => throw new \InvalidArgumentException("Unsupported letter source type: {$sourceType}"),
        };

        return Action::make('createLetter')
            ->label('Create Letter')
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->button()
            ->action(function (Model $record, $livewire) use ($settingKey, $sourceType): void {
                $templateId = AppSetting::getValue($settingKey);

                if (empty($templateId)) {
                    Notification::make()
                        ->title('No Letter Template Linked')
                        ->body('Please link a letter template for this resource under System Settings → Letter Template Linking first.')
                        ->warning()
                        ->send();

                    return;
                }

                $url = route('letters.generate', [
                    'template_id' => $templateId,
                    'employee_code' => $record->employee_id,
                    'source_type' => $sourceType,
                    'source_id' => $record->getKey(),
                ]);

                $livewire->js('window.open('.json_encode($url).', "_blank")');
            });
    }
}
