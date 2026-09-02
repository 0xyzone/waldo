@php
    $images = $candidate->getCvImageUrls();
    $imageCount = count($images);
    $statusColor = match($candidate->status) {
        'approved' => 'text-emerald-700 bg-emerald-50 dark:bg-emerald-950/50 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
        'contacted' => 'text-blue-700 bg-blue-50 dark:bg-blue-950/50 dark:text-blue-400 border-blue-200 dark:border-blue-800',
        'rejected' => 'text-rose-700 bg-rose-50 dark:bg-rose-950/50 dark:text-rose-400 border-rose-200 dark:border-rose-800',
        'not_coming', 'no_show' => 'text-amber-700 bg-amber-50 dark:bg-amber-950/50 dark:text-amber-400 border-amber-200 dark:border-amber-800',
        'unreachable' => 'text-purple-700 bg-purple-50 dark:bg-purple-950/50 dark:text-purple-400 border-purple-200 dark:border-purple-800',
        default => 'text-slate-700 bg-slate-100 dark:bg-zinc-800 dark:text-zinc-300 border-slate-200 dark:border-zinc-700',
    };
@endphp

<div x-data="{
    isPrinting: false,
    images: {{ json_encode($images) }},
    printAll() {
        this.executePrint(this.images);
    },
    printSinglePage(url) {
        this.executePrint([url]);
    },
    executePrint(imageList) {
        if (!imageList || imageList.length === 0) return;
        this.isPrinting = true;

        let oldFrame = document.getElementById('candidate_print_iframe');
        if (oldFrame) {
            oldFrame.remove();
        }

        const iframe = document.createElement('iframe');
        iframe.id = 'candidate_print_iframe';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);

        const doc = iframe.contentWindow.document;

        const imagesHtml = imageList.map((url, idx) => `
            <div class='print-page' style='page-break-after: ${idx === imageList.length - 1 ? 'auto' : 'always'}; break-after: ${idx === imageList.length - 1 ? 'auto' : 'page'}; text-align: center; margin: 0; padding: 0;'>
                <img src='${url}' style='width: 100%; height: auto; max-height: 99vh; object-fit: contain; display: block; margin: 0 auto;' />
            </div>
        `).join('');

        const content = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>CV — {{ addslashes($candidate->name) }}</title>
                <style>
                    * { box-sizing: border-box; margin: 0; padding: 0; }
                    html, body { margin: 0; padding: 0; background: #fff; }
                    @page { size: auto; margin: 0mm; }
                    @media print {
                        html, body { margin: 0; padding: 0; }
                        .print-page { page-break-inside: avoid; break-inside: avoid; margin: 0; padding: 0; }
                        img { max-height: 100vh; max-width: 100vw; }
                    }
                </style>
            </head>
            <body>
                ${imagesHtml}
            </body>
            </html>
        `;

        doc.open();
        doc.write(content);
        doc.close();

        const trigger = () => {
            setTimeout(() => {
                this.isPrinting = false;
                try {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                } catch (e) {
                    console.error('Print error:', e);
                }
            }, 350);
        };

        const imgs = doc.images;
        let loaded = 0;
        const total = imgs.length;

        if (total === 0) {
            trigger();
        } else {
            for (let i = 0; i < total; i++) {
                if (imgs[i].complete) {
                    loaded++;
                    if (loaded === total) trigger();
                } else {
                    imgs[i].onload = () => {
                        loaded++;
                        if (loaded === total) trigger();
                    };
                    imgs[i].onerror = () => {
                        loaded++;
                        if (loaded === total) trigger();
                    };
                }
            }
        }
    }
}" class="space-y-4">

    <!-- Candidate Summary Info Bar (For on-screen modal review only) -->
    <div class="rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-zinc-800/80">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold text-base shadow-sm shrink-0">
                    {{ strtoupper(substr($candidate->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-bold text-base text-slate-900 dark:text-white leading-tight">
                            {{ $candidate->name }}
                        </h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold border {{ $statusColor }} capitalize">
                            {{ str_replace('_', ' ', $candidate->status ?? 'pending') }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        <span>{{ $candidate->department?->name ?? 'No Department' }}</span>
                        <span>•</span>
                        <span>{{ $candidate->phone_number ?? 'No Phone' }}</span>
                    </div>
                </div>
            </div>

            <!-- Action Toolbar in Header -->
            <div class="flex items-center gap-2 shrink-0">
                @if($imageCount > 0)
                    <button type="button"
                            @click="printAll()"
                            :disabled="isPrinting"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold shadow-xs transition cursor-pointer disabled:opacity-50">
                        <template x-if="!isPrinting">
                            <x-filament::icon icon="heroicon-m-printer" class="h-4 w-4" />
                        </template>
                        <template x-if="isPrinting">
                            <span class="inline-block animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                        </template>
                        <span>Print Attachments ({{ $imageCount }})</span>
                    </button>

                    <a href="{{ route('candidates.print', ['candidate' => $candidate->id, 'autoprint' => 1]) }}"
                       target="_blank"
                       title="Open print layout in new window"
                       class="inline-flex items-center justify-center p-2 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-700 transition">
                        <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-4 w-4" />
                    </a>
                @endif
            </div>
        </div>

        <!-- Additional Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 text-xs">
            <div>
                <span class="text-slate-400 dark:text-zinc-500 block">Date of Birth:</span>
                <span class="font-medium text-slate-700 dark:text-zinc-200">
                    {{ $candidate->dob_ad ? $candidate->dob_ad->format('d M, Y') : '-' }}
                    @if($candidate->dob_bs) <span class="text-slate-500 text-[11px]">({{ $candidate->dob_bs }})</span> @endif
                </span>
            </div>
            <div>
                <span class="text-slate-400 dark:text-zinc-500 block">Reference:</span>
                <span class="font-medium text-slate-700 dark:text-zinc-200">{{ $candidate->reference ?: 'None' }}</span>
            </div>
            <div>
                <span class="text-slate-400 dark:text-zinc-500 block">Applied Date:</span>
                <span class="font-medium text-slate-700 dark:text-zinc-200">{{ $candidate->created_at ? $candidate->created_at->format('d M, Y') : '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 dark:text-zinc-500 block">Attachments:</span>
                <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $imageCount }} {{ $imageCount === 1 ? 'Image' : 'Images' }}</span>
            </div>
        </div>
    </div>

    <!-- Images Document Preview Sheets -->
    @if($imageCount > 0)
        <div class="space-y-6 max-h-[62vh] overflow-y-auto pr-1">
            @foreach($images as $index => $imageUrl)
                <div class="group relative rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-950 p-3.5 shadow-sm transition hover:border-slate-300 dark:hover:border-zinc-700">
                    <!-- Page Header Bar -->
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200/80 dark:border-zinc-800/80 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 font-bold text-slate-700 dark:text-zinc-200 text-[11px]">
                                Page {{ $index + 1 }} of {{ $imageCount }}
                            </span>
                            <span class="text-slate-400 dark:text-zinc-500 text-[11px]">Attachment Image</span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button type="button"
                                    @click="printSinglePage('{{ $imageUrl }}')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300 text-[11px] font-medium hover:bg-slate-50 dark:hover:bg-zinc-700 transition cursor-pointer">
                                <x-filament::icon icon="heroicon-m-printer" class="h-3.5 w-3.5" />
                                <span>Print This Attachment</span>
                            </button>

                            <a href="{{ $imageUrl }}"
                               target="_blank"
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-blue-600 dark:text-blue-400 text-[11px] font-medium hover:bg-slate-50 dark:hover:bg-zinc-700 transition">
                                <x-filament::icon icon="heroicon-m-magnifying-glass-plus" class="h-3.5 w-3.5" />
                                <span>Full Size</span>
                            </a>
                        </div>
                    </div>

                    <!-- Image Display (Paper container) -->
                    <div class="relative flex justify-center bg-white dark:bg-zinc-900 rounded-lg p-2 border border-slate-200/60 dark:border-zinc-800/60 overflow-hidden shadow-xs">
                        <img src="{{ $imageUrl }}"
                             alt="CV Page {{ $index + 1 }}"
                             class="max-h-[500px] w-auto max-w-full object-contain rounded transition duration-200 hover:scale-[1.01]"
                             loading="lazy" />
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="rounded-xl border-2 border-dashed border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-8 text-center">
            <div class="h-12 w-12 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-400 dark:text-zinc-500 mx-auto flex items-center justify-center mb-3">
                <x-filament::icon icon="heroicon-o-document-text" class="h-6 w-6" />
            </div>
            <h4 class="font-semibold text-slate-800 dark:text-zinc-200 text-sm mb-1">
                No CV Images Uploaded
            </h4>
            <p class="text-xs text-slate-500 dark:text-zinc-400 max-w-sm mx-auto mb-4">
                This candidate does not have any uploaded CV, portfolio, or document images to print.
            </p>
        </div>
    @endif
</div>
