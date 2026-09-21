@extends('letters.layout')

@section('title', 'Global Custom Variables')

@section('styles')
<style>
.var-card {
    transition: transform 0.15s, box-shadow 0.15s;
}
.var-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
}
</style>
@endsection

@section('content')
<div class="h-full overflow-y-auto bg-slate-50 dark:bg-zinc-950" x-data="globalVariablesManager()">

    <div class="max-w-6xl mx-auto p-6 md:p-8 space-y-8">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-50 tracking-tight">Global Custom Variables</h1>
                <p class="text-sm text-slate-500 dark:text-zinc-400 mt-1">Define reusable variable schemas with optional defaults that can be pulled into letter templates.</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="openCreateModal()"
                        class="flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl shadow-md shadow-amber-500/20 active:scale-95 transition-all cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i> New Variable
                </button>
                <a href="{{ route('letters.index') }}"
                   class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 text-sm font-semibold transition-all">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Templates
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium">
            <i class="fa-solid fa-circle-check text-emerald-500"></i>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800/60 rounded-xl text-rose-600 dark:text-rose-400 text-sm space-y-1">
            @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
        </div>
        @endif

        <!-- Search & Filter Bar -->
        <div class="flex items-center justify-between gap-4 bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-xs">
            <form method="GET" action="{{ route('letters.variables.index') }}" class="flex-1 flex items-center gap-2">
                <div class="relative flex-1 max-w-md">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by key, label, or description..."
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs text-slate-800 dark:text-zinc-200 focus:outline-none focus:border-amber-500 transition-all">
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300 text-xs font-bold rounded-xl transition-all cursor-pointer">
                    Search
                </button>
                @if(request('search'))
                <a href="{{ route('letters.variables.index') }}" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 font-semibold">Clear</a>
                @endif
            </form>
            <span class="text-xs font-bold text-slate-400 dark:text-zinc-500">
                {{ $variables->total() }} {{ \Illuminate\Support\Str::plural('variable', $variables->total()) }}
            </span>
        </div>

        <!-- Variables Grid -->
        @if($variables->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($variables as $v)
            <div class="var-card bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 p-5 flex flex-col justify-between shadow-xs">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider
                                    @if($v->type === 'calculated') bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20
                                    @elseif($v->type === 'richtext') bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20
                                    @elseif($v->type === 'date') bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20
                                    @elseif($v->type === 'dropdown') bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20
                                    @elseif($v->type === 'number') bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20
                                    @elseif($v->type === 'boolean') bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20
                                    @else bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20 @endif">
                                    {{ $v->type }}
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-zinc-100 mt-1.5">{{ $v->label }}</h3>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" @click="openEditModal({{ json_encode($v) }})"
                                    class="p-1.5 text-slate-400 hover:text-amber-500 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                                    title="Edit Variable">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                            <form action="{{ route('letters.variables.destroy', $v->id) }}" method="POST" onsubmit="return confirm('Delete this global variable?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer" title="Delete Variable">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Variable Placeholder Token -->
                    {{-- <div class="flex items-center justify-between p-2 bg-slate-50 dark:bg-zinc-950 rounded-xl border border-slate-200/70 dark:border-zinc-800">
                        <code class="text-xs font-bold font-mono text-amber-600 dark:text-amber-400">{!! '{{ ' . e($v->key) . ' }}' !!}</code>
                        <button type="button" @click="copyToken('{{ $v->key }}')"
                                class="text-[10px] text-slate-400 hover:text-amber-500 font-bold cursor-pointer">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div> --}}

                    @if($v->description)
                    <p class="text-xs text-slate-500 dark:text-zinc-400 line-clamp-2 leading-relaxed">{{ $v->description }}</p>
                    @endif

                    @if($v->default_value)
                    <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                        <span class="font-bold text-slate-400">Default:</span> <span class="font-mono">{{ $v->default_value }}</span>
                    </div>
                    @endif

                    @if($v->options)
                    <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                        <span class="font-bold text-slate-400">Options:</span> <span class="text-xs text-slate-600 dark:text-zinc-300">{{ $v->options }}</span>
                    </div>
                    @endif

                    @if($v->type === 'calculated' && !empty($v->formulas))
                    <div class="space-y-1 pt-1 border-t border-slate-100 dark:border-zinc-800">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-400">Formulas ({{ count($v->formulas) }}):</span>
                        <div class="space-y-1">
                            @foreach($v->formulas as $f)
                            <div class="flex items-center justify-between text-[10px] bg-indigo-50/50 dark:bg-indigo-950/20 px-2 py-1 rounded border border-indigo-100 dark:border-indigo-900/40">
                                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{!! '{{ ' . e($f['key'] ?? '') . ' }}' !!}</span>
                                <span class="font-mono text-slate-500 text-[9px]">{{ $f['expression'] ?? '' }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <div class="pt-4 mt-3 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between text-[10px] text-slate-400">
                    <span>Created {{ $v->created_at ? $v->created_at->diffForHumans() : 'recently' }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $variables->links() }}
        </div>
        @else
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-dashed border-slate-200 dark:border-zinc-800 p-12 text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-2xl mx-auto">
                <i class="fa-solid fa-sliders"></i>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-slate-800 dark:text-zinc-200">No global variables found</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">Create global custom variables that can be imported anytime you build or edit letter templates.</p>
            </div>
            <button type="button" @click="openCreateModal()"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-md shadow-amber-500/20 active:scale-95 transition-all cursor-pointer">
                <i class="fa-solid fa-plus mr-1"></i> Add First Variable
            </button>
        </div>
        @endif

    </div>

    <!-- Create / Edit Modal -->
    <div x-show="modalOpen" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;" @keydown.escape.window="modalOpen = false">

        <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden"
             @click.outside="modalOpen = false">

            <div class="p-6 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-zinc-50" x-text="isEdit ? 'Edit Global Variable' : 'New Global Variable'"></h3>
                    <p class="text-xs text-slate-400 mt-0.5">Reusable placeholder schema across templates.</p>
                </div>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form :action="isEdit ? `/letters/variables/${form.id}` : '{{ route('letters.variables.store') }}'" method="POST" class="p-6 space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Key -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Key (Variable Name) *</label>
                        <input type="text" name="key" x-model="form.key" required placeholder="e.g. probation_period"
                               class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs font-mono text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none">
                        <p class="text-[10px] text-slate-400">Letters, numbers, dashes, underscores.</p>
                    </div>

                    <!-- Type -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Type *</label>
                        <select name="type" x-model="form.type" required
                                class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs font-semibold text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none cursor-pointer">
                            <option value="text">Text</option>
                            <option value="date">Date</option>
                            <option value="number">Number</option>
                            <option value="boolean">Yes/No (Boolean)</option>
                            <option value="dropdown">Dropdown</option>
                            <option value="richtext">Rich Text</option>
                            <option value="calculated">Calculated</option>
                        </select>
                    </div>
                </div>

                <!-- Label -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Display Label *</label>
                    <input type="text" name="label" x-model="form.label" required placeholder="e.g. Probation Period (Months)"
                           class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs font-semibold text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none">
                </div>

                <!-- Default / Dummy Value -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Default Value / Sample <span class="font-normal text-slate-400">(optional)</span></label>
                    <input type="text" name="default_value" x-model="form.default_value" placeholder="e.g. 6"
                           class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none">
                </div>

                <!-- Dropdown Options -->
                <div x-show="form.type === 'dropdown'" class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Options (Comma separated)</label>
                    <input type="text" name="options" x-model="form.options" placeholder="Option 1, Option 2, Option 3"
                           class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none">
                </div>

                <!-- Calculated Formulas Sub-editor -->
                <div x-show="form.type === 'calculated'" class="space-y-2.5 p-3 bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/50 rounded-xl">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-500">Formulas</span>
                        <button type="button" @click="addFormula()" class="px-2 py-0.5 bg-indigo-500 text-white text-[10px] font-bold rounded hover:bg-indigo-600 cursor-pointer">
                            <i class="fa-solid fa-plus mr-0.5"></i> Add Formula
                        </button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(f, fi) in form.formulas" :key="fi">
                            <div class="relative grid grid-cols-2 gap-2 p-2.5 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-lg pr-7">
                                <button type="button" @click="removeFormula(fi)" class="absolute top-2 right-2 text-slate-400 hover:text-rose-500 cursor-pointer">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                                <div>
                                    <label class="block text-[8px] font-bold uppercase text-indigo-400 mb-0.5">Key *</label>
                                    <input type="text" :name="`formulas[${fi}][key]`" x-model="f.key" required placeholder="formula_key"
                                           class="w-full px-2 py-1 border border-slate-200 dark:border-zinc-700 rounded bg-slate-50 dark:bg-zinc-950 text-xs font-mono outline-none">
                                </div>
                                <div>
                                    <label class="block text-[8px] font-bold uppercase text-indigo-400 mb-0.5">Label</label>
                                    <input type="text" :name="`formulas[${fi}][label]`" x-model="f.label" placeholder="Formula Label"
                                           class="w-full px-2 py-1 border border-slate-200 dark:border-zinc-700 rounded bg-slate-50 dark:bg-zinc-950 text-xs outline-none">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[8px] font-bold uppercase text-indigo-400 mb-0.5">Expression *</label>
                                    <input type="text" :name="`formulas[${fi}][expression]`" x-model="f.expression" required placeholder="e.g. gross * 0.6"
                                           class="w-full px-2 py-1 border border-slate-200 dark:border-zinc-700 rounded bg-slate-50 dark:bg-zinc-950 text-xs font-mono outline-none">
                                </div>
                            </div>
                        </template>
                        <div x-show="form.formulas.length === 0" class="py-2 text-center text-[10px] text-slate-400 italic">No formula rows yet.</div>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Description / Usage Notes <span class="font-normal text-slate-400">(optional)</span></label>
                    <textarea name="description" x-model="form.description" rows="2" placeholder="Brief note explaining where or how this variable is used..."
                              class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none"></textarea>
                </div>

                <!-- Modal Footer -->
                <div class="pt-4 border-t border-slate-200 dark:border-zinc-800 flex items-center justify-end gap-2">
                    <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-md shadow-amber-500/20 active:scale-95 transition-all cursor-pointer">
                        <span x-text="isEdit ? 'Save Changes' : 'Create Variable'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
function globalVariablesManager() {
    return {
        modalOpen: false,
        isEdit: false,
        form: {
            id: null,
            key: '',
            label: '',
            type: 'text',
            default_value: '',
            options: '',
            description: '',
            formulas: []
        },

        openCreateModal() {
            this.isEdit = false;
            this.form = {
                id: null,
                key: '',
                label: '',
                type: 'text',
                default_value: '',
                options: '',
                description: '',
                formulas: []
            };
            this.modalOpen = true;
        },

        openEditModal(v) {
            this.isEdit = true;
            this.form = {
                id: v.id,
                key: v.key,
                label: v.label,
                type: v.type,
                default_value: v.default_value || '',
                options: v.options || '',
                description: v.description || '',
                formulas: Array.isArray(v.formulas) ? JSON.parse(JSON.stringify(v.formulas)) : []
            };
            this.modalOpen = true;
        },

        addFormula() {
            this.form.formulas.push({ key: '', label: '', expression: '' });
        },

        removeFormula(idx) {
            this.form.formulas.splice(idx, 1);
        },

        copyToken(key) {
            navigator.clipboard.writeText(`@{{ ${key} }}`);
            alert(`Copied @{{ ${key} }} to clipboard!`);
        }
    };
}
</script>
@endsection
