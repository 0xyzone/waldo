<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee SSID Directory & Search</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Styles & Tailwind -->
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae0fd',
                            500: '#0284c7',
                            600: '#0284c7',
                            700: '#0369a1',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .glass-input {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .glass-input:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
        }

        .glow-effect {
            box-shadow: 0 0 25px -5px rgba(14, 165, 233, 0.25);
        }
    </style>
</head>

<body class="min-h-screen bg-slate-950 text-slate-100 antialiased selection:bg-sky-500 selection:text-white pb-16">
    <!-- Header / Navbar -->
    <header class="border-b border-slate-800/80 bg-slate-900/60 sticky top-0 z-50 backdrop-blur-md">
        <div class="max-w-[80%] mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div
                    class="h-10 w-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-sky-500/20 text-white font-bold text-xl">
                    S
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                        Employee SSID Directory
                        <span
                            class="text-xs font-semibold px-2 py-0.5 rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/20">Public
                            View</span>
                    </h1>
                    <p class="text-xs text-slate-400">Search and verify employee SSID records by Employee Code</p>
                </div>
            </div>

            <div class="hidden sm:flex items-center gap-4 text-sm text-slate-400">
                <div class="flex items-center gap-2 bg-slate-800/50 px-3 py-1.5 rounded-lg border border-slate-700/50">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Total Employees: <strong
                            class="text-slate-200">{{ number_format($totalCount) }}</strong></span>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-[80%] mx-auto px-4 sm:px-6 lg:px-8 pt-8">

        <!-- Search & Filter Card -->
        <div class="glass-card rounded-2xl p-6 mb-8 glow-effect">
            <form action="{{ route('employees.ssid-list') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">

                    <!-- Search Input -->
                    <div class="md:col-span-7 relative">
                        <label for="search"
                            class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                            Search Employee Code or Name
                        </label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ $search }}"
                                placeholder="Enter Employee Code (e.g. CWD015) or Name..."
                                class="w-full pl-11 pr-10 py-3 rounded-xl text-white placeholder-slate-500 glass-input focus:outline-none transition"
                                autofocus>
                            @if (!empty($search))
                                <a href="{{ route('employees.ssid-list') }}"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white transition"
                                    title="Clear Search">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Department Filter -->
                    <div class="md:col-span-3">
                        <label for="department_id"
                            class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                            Department
                        </label>
                        <select name="department_id" id="department_id" onchange="this.form.submit()"
                            class="w-full py-3 px-3.5 rounded-xl text-white glass-input focus:outline-none transition bg-slate-900">
                            <option value="">All Departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ $selectedDepartment == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="md:col-span-2 flex items-end gap-2 pt-6 md:pt-0">
                        <button type="submit"
                            class="w-full py-3 px-4 bg-sky-500 hover:bg-sky-400 text-white font-semibold rounded-xl transition shadow-lg shadow-sky-500/25 flex items-center justify-center gap-2">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Search
                        </button>
                    </div>

                </div>

                @if (!empty($search) || !empty($selectedDepartment))
                    <div
                        class="flex items-center justify-between pt-2 border-t border-slate-800 text-xs text-slate-400">
                        <span>Showing search results for:
                            @if (!empty($search))
                                <strong class="text-sky-400">"{{ $search }}"</strong>
                            @endif
                            @if (!empty($selectedDepartment))
                                in <strong
                                    class="text-sky-400">{{ optional($departments->firstWhere('id', $selectedDepartment))->name }}</strong>
                            @endif
                        </span>
                        <a href="{{ route('employees.ssid-list') }}"
                            class="text-slate-400 hover:text-white underline">Reset Filters</a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Employee SSID List Table -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800/80 flex items-center justify-between flex-wrap gap-4">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    Employee SSID Records
                    <span class="text-xs bg-slate-800 text-slate-300 px-2.5 py-1 rounded-full border border-slate-700">
                        {{ $employees->total() }} {{ Str::plural('record', $employees->total()) }} found
                    </span>
                </h2>
            </div>

            @if ($employees->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm text-slate-300">
                        <thead>
                            <tr
                                class="bg-slate-900/80 border-b border-slate-800 text-slate-400 uppercase text-xs tracking-wider">
                                <th class="py-4 px-6 font-semibold">Employee Code</th>
                                <th class="py-4 px-6 font-semibold">Employee Name</th>
                                <th class="py-4 px-6 font-semibold">Department</th>
                                <th class="py-4 px-6 font-semibold">Designation</th>
                                <th class="py-4 px-6 font-semibold">SSID</th>
                                <th class="py-4 px-6 font-semibold text-center">Status</th>
                                <th class="py-4 px-6 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach ($employees as $employee)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <!-- Employee Code -->
                                    <td class="py-4 px-6 font-mono font-bold text-sky-400">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full bg-sky-400"></span>
                                            {{ $employee->employee_code }}
                                        </div>
                                    </td>

                                    <!-- Employee Name -->
                                    <td class="py-4 px-6 font-medium text-white">
                                        {{ $employee->name ?? trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: 'N/A' }}
                                    </td>

                                    <!-- Department -->
                                    <td class="py-4 px-6">
                                        {{ optional($employee->department)->name ?? 'N/A' }}
                                    </td>

                                    <!-- Designation -->
                                    <td class="py-4 px-6">
                                        {{ optional($employee->designation)->name ?? 'N/A' }}
                                    </td>

                                    <!-- SSID -->
                                    <td class="py-4 px-6 font-mono">
                                        @if (!empty($employee->ssid))
                                            <button onclick="copyToClipboard('{{ e($employee->ssid) }}', this)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-semibold border border-emerald-500/20 transition cursor-pointer group"
                                                title="Click to copy SSID">
                                                <svg class="w-3.5 h-3.5 group-hover:scale-110 transition-transform"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                                <span>{{ $employee->ssid }}</span>
                                            </button>
                                        @else
                                            <span class="text-slate-500 italic">Not set</span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="py-4 px-6 text-center">
                                        @php
                                            $status = strtolower($employee->employee_status ?? '');
                                            $isResigned = $status == 'resigned';
                                            $isTerminated = $status == 'terminated';
                                            $isResigningThisMonth = $status == 'resigning this month';
                                        @endphp
                                        @if ($isResigningThisMonth)
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-semibold bg-violet-500/10 text-violet-400 border border-violet-500/20">
                                                {{ $employee->employee_status }}
                                            </span>
                                        @elseif($isResigned)
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                                {{ $employee->employee_status }}
                                            </span>
                                        @elseif($isTerminated)
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20 shrink-0 flex-1">
                                                {{ $employee->employee_status }}
                                            </span>
                                        @else
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                {{ $employee->employee_status ?: 'Active' }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Copy Action -->
                                    <td class="py-4 px-6 text-right">
                                        @if (!empty($employee->ssid))
                                            <button onclick="copyToClipboard('{{ e($employee->ssid) }}', this)"
                                                class="copy-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium border border-slate-700 transition cursor-pointer"
                                                title="Copy SSID">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                <span class="flex-1 shrink-0">Copy SSID</span>
                                            </button>
                                        @else
                                            <span class="text-slate-600 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-slate-800/80 bg-slate-900/40">
                    {{ $employees->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="p-12 text-center">
                    <div
                        class="h-16 w-16 mx-auto mb-4 rounded-full bg-slate-800/80 flex items-center justify-center text-slate-500">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-white mb-1">No employee records found</h3>
                    <p class="text-sm text-slate-400 max-w-sm mx-auto mb-6">
                        We couldn't find any employee matching your search criteria. Try searching with a different
                        employee code or clearing the filters.
                    </p>
                    <a href="{{ route('employees.ssid-list') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-sky-400 font-medium text-sm rounded-xl border border-slate-700 transition">
                        Clear Search & Filters
                    </a>
                </div>
            @endif
        </div>
    </main>

    <!-- Floating Toast Notification -->
    <div id="copy-toast"
        class="fixed bottom-6 right-6 z-50 transform translate-y-16 opacity-0 transition-all duration-300 pointer-events-none">
        <div
            class="flex items-center gap-3 bg-slate-900/90 border border-emerald-500/40 text-white px-4 py-3 rounded-xl shadow-2xl backdrop-blur-md">
            <div
                class="h-8 w-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Copied to Clipboard</p>
                <p class="text-sm font-mono font-bold text-emerald-400" id="toast-text">SSID copied!</p>
            </div>
        </div>
    </div>

    <script>
        function showToast(text) {
            const toast = document.getElementById('copy-toast');
            const toastText = document.getElementById('toast-text');
            if (toast && toastText) {
                toastText.textContent = text;
                toast.classList.remove('translate-y-16', 'opacity-0', 'pointer-events-none');
                toast.classList.add('translate-y-0', 'opacity-100');

                setTimeout(() => {
                    toast.classList.remove('translate-y-0', 'opacity-100');
                    toast.classList.add('translate-y-16', 'opacity-0', 'pointer-events-none');
                }, 3000);
            }
        }

        function copyToClipboard(text, btnElement) {
            if (!text) return;

            function onCopySuccess() {
                showToast('SSID: ' + text);

                if (btnElement) {
                    const originalHtml = btnElement.innerHTML;
                    btnElement.innerHTML = `
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-emerald-400 font-bold">Copied!</span>
                    `;
                    btnElement.classList.add('border-emerald-500/50', 'bg-emerald-500/20');

                    setTimeout(() => {
                        btnElement.innerHTML = originalHtml;
                        btnElement.classList.remove('border-emerald-500/50', 'bg-emerald-500/20');
                    }, 2000);
                }
            }

            // Modern API with fallback for non-secure HTTP / unsupported contexts
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text)
                    .then(onCopySuccess)
                    .catch(function() {
                        fallbackCopyTextToClipboard(text, onCopySuccess);
                    });
            } else {
                fallbackCopyTextToClipboard(text, onCopySuccess);
            }
        }

        function fallbackCopyTextToClipboard(text, callback) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            textArea.style.opacity = "0";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful && typeof callback === 'function') {
                    callback();
                }
            } catch (err) {
                console.error('Fallback copy failed', err);
            }
            document.body.removeChild(textArea);
        }
    </script>
</body>

</html>
