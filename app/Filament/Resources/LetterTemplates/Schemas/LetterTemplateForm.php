<?php

namespace App\Filament\Resources\LetterTemplates\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class LetterTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 12])
                    ->schema([
                        // Left Column (5/12) - Configuration Inputs
                        Grid::make(1)
                            ->schema([
                                Section::make('Template Details')
                                    ->schema([
                                        TextInput::make('title')
                                            ->required()
                                            ->maxLength(255),

                                        RichEditor::make('content')
                                            ->required()
                                            ->placeholder('Draft template body here...')
                                            ->toolbarButtons([
                                                'blockquote',
                                                'bold',
                                                'bulletList',
                                                'codeBlock',
                                                'h2',
                                                'h3',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'underline',
                                                'undo',
                                            ]),
                                    ]),

                                Section::make('Page Layout & Margins (mm)')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                TextInput::make('margin_top')
                                                    ->label('Top')
                                                    ->numeric()
                                                    ->integer()
                                                    ->required()
                                                    ->default(25),
                                                TextInput::make('margin_bottom')
                                                    ->label('Bottom')
                                                    ->numeric()
                                                    ->integer()
                                                    ->required()
                                                    ->default(25),
                                                TextInput::make('margin_left')
                                                    ->label('Left')
                                                    ->numeric()
                                                    ->integer()
                                                    ->required()
                                                    ->default(20),
                                                TextInput::make('margin_right')
                                                    ->label('Right')
                                                    ->numeric()
                                                    ->integer()
                                                    ->required()
                                                    ->default(20),
                                            ]),
                                    ]),

                                Section::make('Custom Template Variables')
                                    ->description('Define variables for custom entry when generating letters (refer to them as {{ custom_variable_name }}):')
                                    ->schema([
                                        Repeater::make('variables')
                                            ->hiddenLabel()
                                            ->schema([
                                                TextInput::make('key')
                                                    ->label('Variable Key')
                                                    ->required()
                                                    ->placeholder('e.g., promotion_date')
                                                    ->rules(['regex:/^[a-zA-Z0-9_]+$/'])
                                                    ->helperText('No spaces or dots'),
                                                Select::make('type')
                                                    ->label('Type')
                                                    ->options([
                                                        'text' => 'Text',
                                                        'date' => 'Date',
                                                        'number' => 'Number',
                                                        'boolean' => 'Boolean (Yes/No)',
                                                        'dropdown' => 'Dropdown',
                                                    ])
                                                    ->live()
                                                    ->required()
                                                    ->default('text'),
                                                TextInput::make('options')
                                                    ->label('Dropdown Options')
                                                    ->placeholder('Option A, Option B, Option C')
                                                    ->helperText('Comma-separated list')
                                                    ->visible(fn ($get) => $get('type') === 'dropdown')
                                                    ->required(fn ($get) => $get('type') === 'dropdown'),

                                            ])
                                            ->columns(3)
                                            ->default([])
                                            ->itemLabel(fn (array $state): ?string => $state['key'] ?? null),
                                    ]),
                            ])
                            ->columnSpan(['default' => 1, 'lg' => 5]),

                        // Right Column (7/12) - Placeholders Help & Live A4 Preview
                        Grid::make(1)
                            ->schema([
                                Section::make('Employee Placeholders')
                                    ->description('Copy and paste these placeholder tags into the template editor (click to copy):')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Placeholder::make('employee_variables')
                                            ->hiddenLabel()
                                            ->content(new HtmlString('
                                                <div class="space-y-2">
                                                    <div class="grid grid-cols-1 gap-2 text-xs font-mono">
                                                        <div class="flex flex-col gap-1">
                                                             <span class="text-gray-500 font-sans font-semibold">General Info <span class="text-[10px] text-amber-600 dark:text-amber-400 font-normal">(employee_name includes Mr. / Miss based on gender)</span>:</span>
                                                            <div class="flex flex-wrap gap-1.5">
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_name &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_employee_code &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_gender &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_marital_status &#125;&#125;</code>
                                                            </div>
                                                        </div>
                                                        <div class="flex flex-col gap-1 mt-2">
                                                            <span class="text-gray-500 font-sans font-semibold">Employment Details:</span>
                                                            <div class="flex flex-wrap gap-1.5">
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_department &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_designation &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_join_date &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_employee_status &#125;&#125;</code>
                                                            </div>
                                                        </div>
                                                        <div class="flex flex-col gap-1 mt-2">
                                                            <span class="text-gray-500 font-sans font-semibold">Identity & Documents:</span>
                                                            <div class="flex flex-wrap gap-1.5">
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_citizenship_number &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_citizenship_issue_date &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_citizenship_issue_place &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_ssid &#125;&#125;</code>
                                                            </div>
                                                        </div>
                                                        <div class="flex flex-col gap-1 mt-2">
                                                            <span class="text-gray-500 font-sans font-semibold">Dates & Contact:</span>
                                                            <div class="flex flex-wrap gap-1.5">
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_dob_ad &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_dob_bs &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_email &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_contact_number &#125;&#125;</code>
                                                            </div>
                                                        </div>
                                                        <div class="flex flex-col gap-1 mt-2">
                                                            <span class="text-gray-500 font-sans font-semibold">Tips & Points:</span>
                                                            <div class="flex flex-wrap gap-1.5">
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_tips_amount &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_tips_status &#125;&#125;</code>
                                                                <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-semibold text-amber-600 dark:text-amber-400 cursor-pointer select-all" onclick="navigator.clipboard.writeText(this.innerText)">&#123;&#123; employee_point_value &#125;&#125;</code>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ')),
                                    ]),

                                Section::make('Live A4 Paper Preview')
                                    ->schema([
                                        Placeholder::make('live_preview')
                                            ->hiddenLabel()
                                            ->view('filament.components.letter-preview'),
                                    ]),
                            ])
                            ->columnSpan(['default' => 1, 'lg' => 7]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
