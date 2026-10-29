<div
    x-data="sidebarMenuSearch()"
    x-init="init()"
    class="sidebar-search-container -mx-2 px-2 -mt-4 mb-3 sticky -top-8 z-20 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-b border-gray-200/60 dark:border-gray-800/80 pb-2.5 pt-1.5 transition-all duration-200"
    data-sidebar-search-root
>
    {{-- Expanded Search Bar (when sidebar is open) --}}
    <div
        x-show="$store.sidebar ? $store.sidebar.isOpen : true"
        x-cloak
        class="w-full relative"
    >
        <div class="relative flex items-center">
            {{-- Search Icon / Spinner --}}
            <div class="absolute left-3 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                <x-heroicon-m-magnifying-glass class="w-4 h-4" />
            </div>

            {{-- Input Field --}}
            <input
                x-ref="searchInput"
                type="text"
                x-model="query"
                x-on:input="filterMenu()"
                x-on:keydown.escape.prevent.stop="clear()"
                x-on:keydown.enter.prevent="navigateToFirstMatch()"
                placeholder="Search menus & resources..."
                autocomplete="off"
                spellcheck="false"
                class="w-full text-xs pl-9 pr-14 py-2 rounded-lg border border-gray-200/90 dark:border-gray-700/80 bg-gray-50/80 dark:bg-gray-800/80 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:bg-white dark:focus:bg-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition duration-150 shadow-2xs"
            />

            {{-- Right Badges / Actions --}}
            <div class="absolute right-2.5 flex items-center gap-1">
                {{-- Clear Button when query has text --}}
                <button
                    type="button"
                    x-show="query.length > 0"
                    x-cloak
                    x-on:click="clear()"
                    class="p-0.5 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                    title="Clear search (Esc)"
                >
                    <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                </button>

                {{-- Matches Count Badge --}}
                <span
                    x-show="isFiltering && query.length > 0 && hasResults"
                    x-cloak
                    class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-primary-100 text-primary-800 dark:bg-primary-950 dark:text-primary-300"
                    x-text="matchCount"
                ></span>

                {{-- Keyboard Shortcut Hint when query is empty --}}
                <span
                    x-show="query.length === 0"
                    class="hidden sm:inline-block text-[10px] font-mono font-medium px-1.5 py-0.5 rounded bg-gray-200/70 dark:bg-gray-700/60 text-gray-500 dark:text-gray-400 border border-gray-300/50 dark:border-gray-600/50 pointer-events-none select-none"
                >
                    Ctrl+K
                </span>
            </div>
        </div>
    </div>

    {{-- Collapsed Sidebar Icon Button (when desktop sidebar is collapsed to 4.25rem) --}}
    <div
        x-show="$store.sidebar && !$store.sidebar.isOpen"
        x-cloak
        class="w-full flex justify-center py-0.5"
    >
        <button
            type="button"
            x-on:click="$store.sidebar.open(); $nextTick(() => { setTimeout(() => { $refs.searchInput.focus(); }, 120); })"
            class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 transition"
            title="Search resources (Ctrl+K)"
        >
            <x-heroicon-m-magnifying-glass class="w-5 h-5" />
        </button>
    </div>

    {{-- "No resource menu is available" Empty State Message --}}
    <div
        x-show="isFiltering && !hasResults"
        x-cloak
        class="mt-3 p-4 text-center rounded-xl bg-gray-50/90 dark:bg-gray-800/60 border border-dashed border-gray-300/80 dark:border-gray-700 mx-0.5 shadow-2xs animate-fade-in"
    >
        <div class="w-9 h-9 rounded-full bg-gray-200/80 dark:bg-gray-700/80 flex items-center justify-center mx-auto mb-2 text-gray-500 dark:text-gray-400">
            <x-heroicon-m-magnifying-glass class="w-5 h-5 opacity-70" />
        </div>
        <p class="text-xs font-bold text-gray-800 dark:text-gray-200">
            No resource menu is available
        </p>
        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
            Nothing found for <span class="font-semibold text-primary-600 dark:text-primary-400" x-text="'\"' + query + '\"'"></span>
        </p>
        <button
            type="button"
            x-on:click="clear()"
            class="mt-2.5 inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-md bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 shadow-2xs transition"
        >
            <x-heroicon-m-x-mark class="w-3 h-3" />
            <span>Clear search</span>
        </button>
    </div>
</div>

<script>
    function sidebarMenuSearch() {
        return {
            query: '',
            hasResults: true,
            matchCount: 0,
            isFiltering: false,

            init() {
                // Listen for global Ctrl+K / Cmd+K to focus search input
                window.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                        e.preventDefault();
                        if (this.$store.sidebar && !this.$store.sidebar.isOpen) {
                            this.$store.sidebar.open();
                        }
                        this.$nextTick(() => {
                            setTimeout(() => {
                                if (this.$refs.searchInput) {
                                    this.$refs.searchInput.focus();
                                    this.$refs.searchInput.select();
                                }
                            }, 100);
                        });
                    }
                });
            },

            filterMenu() {
                const q = this.query.trim().toLowerCase();
                const nav = this.$el.closest('.fi-sidebar-nav') || document.querySelector('.fi-sidebar-nav');
                if (!nav) return;

                const groups = nav.querySelectorAll('.fi-sidebar-group');
                const standaloneItems = nav.querySelectorAll('.fi-sidebar-nav-groups > .fi-sidebar-item');

                if (!q) {
                    this.isFiltering = false;
                    this.hasResults = true;
                    this.matchCount = 0;

                    // Restore all items
                    nav.querySelectorAll('.fi-sidebar-item').forEach(el => {
                        el.style.display = '';
                    });

                    // Restore all groups and original collapse state
                    groups.forEach(group => {
                        group.style.display = '';
                        const itemsCtn = group.querySelector('.fi-sidebar-group-items');
                        if (itemsCtn) {
                            if (itemsCtn.dataset.prevDisplay !== undefined) {
                                itemsCtn.style.display = itemsCtn.dataset.prevDisplay;
                                delete itemsCtn.dataset.prevDisplay;
                            } else {
                                itemsCtn.style.display = '';
                            }
                        }
                    });

                    return;
                }

                this.isFiltering = true;
                let totalMatches = 0;

                // 1. Filter Groups & their child items
                groups.forEach(group => {
                    const groupLabelEl = group.querySelector('.fi-sidebar-group-label');
                    const groupLabel = groupLabelEl ? groupLabelEl.textContent.trim().toLowerCase() : '';
                    const groupLabelMatches = groupLabel.includes(q);
                    const itemsCtn = group.querySelector('.fi-sidebar-group-items');
                    const items = group.querySelectorAll('.fi-sidebar-item');
                    let groupHasMatchedItem = false;

                    items.forEach(item => {
                        const labelEl = item.querySelector('.fi-sidebar-item-label');
                        const itemText = (labelEl ? labelEl.textContent : item.textContent).trim().toLowerCase();

                        if (itemText.includes(q) || groupLabelMatches) {
                            item.style.display = '';
                            groupHasMatchedItem = true;
                            totalMatches++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    if (groupHasMatchedItem) {
                        group.style.display = '';
                        // If group was collapsed, temporarily open its item container so results are visible
                        if (itemsCtn) {
                            if (itemsCtn.dataset.prevDisplay === undefined) {
                                itemsCtn.dataset.prevDisplay = itemsCtn.style.display || '';
                            }
                            itemsCtn.style.display = 'flex';
                        }
                    } else {
                        group.style.display = 'none';
                    }
                });

                // 2. Filter Top-Level Standalone Items outside groups
                standaloneItems.forEach(item => {
                    const labelEl = item.querySelector('.fi-sidebar-item-label');
                    const itemText = (labelEl ? labelEl.textContent : item.textContent).trim().toLowerCase();

                    if (itemText.includes(q)) {
                        item.style.display = '';
                        totalMatches++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                this.matchCount = totalMatches;
                this.hasResults = totalMatches > 0;
            },

            navigateToFirstMatch() {
                const nav = this.$el.closest('.fi-sidebar-nav') || document.querySelector('.fi-sidebar-nav');
                if (!nav) return;

                const firstItem = Array.from(nav.querySelectorAll('.fi-sidebar-item')).find(item => item.style.display !== 'none');
                if (firstItem) {
                    const link = firstItem.querySelector('a');
                    if (link && link.href) {
                        link.click();
                    }
                }
            },

            clear() {
                this.query = '';
                this.filterMenu();
                this.$nextTick(() => {
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            }
        };
    }
</script>
