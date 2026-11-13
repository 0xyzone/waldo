<?php

namespace App\Filament\Pages;

use Agroezinger\FilamentShieldEnhanced\Traits\HasPageShield;
use App\Models\AppSetting;
use App\Models\LetterTemplate;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageLetterTemplateSettings extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::DocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'System Settings';

    protected static ?string $navigationLabel = 'Letter Template Linking';

    protected static ?string $title = 'Letter Template Linking Settings';

    protected static ?string $slug = 'system-settings/letter-templates';

    protected static ?int $navigationSort = 15;

    protected string $view = 'filament.pages.manage-letter-template-settings';

    public ?string $letter_template_promotion_id = null;

    public ?string $letter_template_transfer_id = null;

    public ?string $letter_template_leaver_id = null;

    public ?string $letter_template_terminated_id = null;

    public function mount(): void
    {
        $this->letter_template_promotion_id = AppSetting::getValue('letter_template_promotion_id');
        $this->letter_template_transfer_id = AppSetting::getValue('letter_template_transfer_id');
        $this->letter_template_leaver_id = AppSetting::getValue('letter_template_leaver_id');
        $this->letter_template_terminated_id = AppSetting::getValue('letter_template_terminated_id');
    }

    public function form(Schema $schema): Schema
    {
        $templateOptions = fn (): array => LetterTemplate::query()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->toArray();

        return $schema
            ->components([
                Section::make('Resource Letter Template Mapping')
                    ->description('Link a Letter Template Studio template to each HR/Finance resource. Clicking "Create Letter" on any record will open the Letter Studio Generator pre-filled with the linked template, employee details, and record-specific variables.')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                Select::make('letter_template_promotion_id')
                                    ->label('Promotions Letter Template')
                                    ->options($templateOptions)
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select a template for Promotions...')
                                    ->helperText('Available variables: {{promotion_date}}, {{from_department}}, {{from_designation}}, {{to_department}}, {{to_designation}}, {{promotion_remarks}}'),

                                Select::make('letter_template_transfer_id')
                                    ->label('Transfers Letter Template')
                                    ->options($templateOptions)
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select a template for Transfers...')
                                    ->helperText('Available variables: {{transfer_date}}, {{from_department}}, {{from_designation}}, {{to_department}}, {{to_designation}}, {{transfer_remarks}}'),

                                Select::make('letter_template_leaver_id')
                                    ->label('Leavers (Resignation) Letter Template')
                                    ->options($templateOptions)
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select a template for Leavers...')
                                    ->helperText('Available variables: {{last_working_date}}, {{resignation_date}}, {{leaver_reason}}, {{leaver_remarks}}'),

                                Select::make('letter_template_terminated_id')
                                    ->label('Terminated Employees Letter Template')
                                    ->options($templateOptions)
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select a template for Terminated Employees...')
                                    ->helperText('Available variables: {{termination_date}}, {{last_working_date}}, {{termination_reason}}'),
                            ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        AppSetting::setValue('letter_template_promotion_id', $this->letter_template_promotion_id ?: null);
        AppSetting::setValue('letter_template_transfer_id', $this->letter_template_transfer_id ?: null);
        AppSetting::setValue('letter_template_leaver_id', $this->letter_template_leaver_id ?: null);
        AppSetting::setValue('letter_template_terminated_id', $this->letter_template_terminated_id ?: null);

        Notification::make()
            ->title('Letter Template Settings Saved')
            ->body('Your resource-to-template mappings have been updated.')
            ->success()
            ->send();
    }
}
