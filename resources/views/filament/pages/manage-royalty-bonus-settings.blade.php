<x-filament-panels::page>
    <div class="max-w-4xl mx-auto space-y-6">
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                <x-filament::button
                    type="submit"
                    size="lg"
                    icon="heroicon-m-check"
                    color="primary"
                    class="shadow-sm"
                >
                    Save Royalty Settings
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
