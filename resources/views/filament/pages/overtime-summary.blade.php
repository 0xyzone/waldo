<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-wrap items-end gap-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Month</label>
                <select wire:model.live="month"
                    class="rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-white">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Year</label>
                <select wire:model.live="year"
                    class="rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-white">
                    @foreach (range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php
            $blockSummary = $this->getBlockSummary();
            $employeeSummary = $this->getEmployeeSummary();
        @endphp

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
            <h2 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Block-wise OT Summary</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2">Block</th>
                            <th class="px-3 py-2 text-right">Normal OT (×1.5)</th>
                            <th class="px-3 py-2 text-right">Special OT (×2)</th>
                            <th class="px-3 py-2 text-right">Total Hours</th>
                            <th class="px-3 py-2 text-right">Entries</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse ($blockSummary as $row)
                            <tr>
                                <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $row['block'] }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($row['normal_hours'], 2) }}</td>
                                <td class="px-3 py-2 text-right text-amber-600 dark:text-amber-400">{{ number_format($row['special_hours'], 2) }}</td>
                                <td class="px-3 py-2 text-right font-semibold">{{ number_format($row['total_hours'], 2) }}</td>
                                <td class="px-3 py-2 text-right text-gray-500">{{ $row['entries_count'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-gray-500">No OT entries for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
            <h2 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Employee OT Summary (Finance &amp; Block Breakdown)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2">Code</th>
                            <th class="px-3 py-2">Name</th>
                            <th class="px-3 py-2">Department</th>
                            <th class="px-3 py-2 text-right">Normal OT (×1.5)</th>
                            <th class="px-3 py-2 text-right">Special OT (×2)</th>
                            <th class="px-3 py-2 text-right">Total Hours</th>
                            <th class="px-3 py-2">Blocks Breakdown</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse ($employeeSummary as $row)
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs">{{ $row['employee_code'] }}</td>
                                <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row['department'] }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($row['normal_hours'], 2) }}</td>
                                <td class="px-3 py-2 text-right text-amber-600 dark:text-amber-400">{{ number_format($row['special_hours'], 2) }}</td>
                                <td class="px-3 py-2 text-right font-semibold">{{ number_format($row['total_hours'], 2) }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($row['blocks'] as $blockName => $hrs)
                                            <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-200">
                                                {{ $blockName }}: {{ number_format($hrs, 2) }}h
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-gray-500">No OT entries for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
