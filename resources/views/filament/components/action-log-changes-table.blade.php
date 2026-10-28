@php
    $record = (isset($getRecord) && is_callable($getRecord)) ? $getRecord() : ($record ?? null);
    $rawState = (isset($getState) && is_callable($getState)) ? $getState() : ($state ?? null);

    // Check directly passed $changes, then rawState, then record's changes attribute
    $resolvedChanges = $changes ?? (! empty($rawState) ? $rawState : ($record?->changes ?? null));
    $changes = $resolvedChanges;

    if (is_string($changes)) {
        $decoded = json_decode($changes, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $changes = $decoded;
        }
    }

    $formatVal = function ($val) {
        if (is_null($val) || $val === '') {
            return '<span class="text-gray-400 dark:text-gray-500 italic font-mono text-[11px]">null</span>';
        }
        if (is_bool($val)) {
            return $val
                ? '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25">true</span>'
                : '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/25">false</span>';
        }
        if (is_array($val)) {
            return '<pre class="text-[10px] leading-tight font-mono whitespace-pre-wrap max-h-32 overflow-y-auto bg-gray-50 dark:bg-gray-800/60 p-1.5 rounded">' . e(json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . '</pre>';
        }
        return '<span class="font-mono text-xs break-all">' . e((string) $val) . '</span>';
    };
@endphp

@if (empty($changes) || ! is_array($changes))
    <div class="text-xs text-gray-500 dark:text-gray-400 italic py-2">
        No attribute modifications recorded.
    </div>
@else
    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 bg-white/60 dark:bg-gray-900/60 shadow-sm mt-1">
        <table class="w-full text-xs text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/80 text-gray-600 dark:text-gray-300 font-semibold border-b border-gray-200 dark:border-gray-800">
                    <th class="px-3.5 py-2.5 w-1/3">Field</th>
                    <th class="px-3.5 py-2.5 w-1/3">Old Value</th>
                    <th class="px-3.5 py-2.5 w-1/3">New Value</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                @foreach ($changes as $field => $val)
                    @php
                        $isDiffArray = is_array($val) && (array_key_exists('old', $val) || array_key_exists('new', $val));
                        $old = $isDiffArray ? ($val['old'] ?? null) : null;
                        $new = $isDiffArray ? ($val['new'] ?? null) : $val;
                        $fieldName = \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $field));
                    @endphp
                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="px-3.5 py-2.5 font-medium text-gray-800 dark:text-gray-200 align-top">
                            {{ $fieldName }}
                        </td>
                        <td class="px-3.5 py-2.5 text-rose-600 dark:text-rose-400 bg-rose-50/40 dark:bg-rose-950/20 align-top">
                            {!! $formatVal($old) !!}
                        </td>
                        <td class="px-3.5 py-2.5 text-emerald-600 dark:text-emerald-400 bg-emerald-50/40 dark:bg-emerald-950/20 font-semibold align-top">
                            {!! $formatVal($new) !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
