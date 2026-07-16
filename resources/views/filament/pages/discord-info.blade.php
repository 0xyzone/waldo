@php
    $guild = \App\Services\DiscordService::getGuildInfo();
    $channels = \App\Services\DiscordService::getChannels();
    $roles = \App\Services\DiscordService::getRoles();

    $categories = [];
    $groupedChannels = [];
    
    if ($channels) {
        foreach ($channels as $channel) {
            if (($channel['type'] ?? null) === 4) {
                $categories[$channel['id']] = $channel['name'] ?? 'Unnamed Category';
            }
        }
        foreach ($channels as $channel) {
            if (($channel['type'] ?? null) === 0) {
                $parentId = $channel['parent_id'] ?? 'uncategorized';
                $groupedChannels[$parentId][] = $channel;
            }
        }
    }
@endphp

@if ($guild)
    <div class="space-y-6">
        <!-- Guild Summary Header Card -->
        <div class="flex flex-col md:flex-row items-center gap-6 p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex-shrink-0">
                @if ($guild['icon'] ?? null)
                    <img class="w-20 h-20 rounded-full shadow-inner ring-2 ring-primary-500" 
                         src="https://cdn.discordapp.com/icons/{{ $guild['id'] }}/{{ $guild['icon'] }}.webp?size=128" 
                         alt="{{ $guild['name'] }}">
                @else
                    <div class="w-20 h-20 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-2xl ring-2 ring-primary-500">
                        {{ strtoupper(substr($guild['name'] ?? 'D', 0, 2)) }}
                    </div>
                @endif
            </div>
            
            <div class="flex-1 text-center md:text-left space-y-1">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $guild['name'] ?? 'Discord Server' }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-mono">
                    ID: {{ $guild['id'] }}
                </p>
                <div class="flex flex-wrap gap-2 justify-center md:justify-start pt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                        Bot Connected
                    </span>
                    @if ($guild['premium_tier'] ?? 0)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200">
                            Boost Tier {{ $guild['premium_tier'] }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Categories and Channels Column -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                <h4 class="text-md font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                    </svg>
                    Channels & Categories
                </h4>
                
                <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                    @if (!empty($groupedChannels))
                        @foreach ($groupedChannels as $parentId => $chanList)
                            <div class="space-y-1">
                                <div class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 font-mono">
                                    {{ $parentId === 'uncategorized' ? 'Text Channels' : ($categories[$parentId] ?? 'Uncategorized') }}
                                </div>
                                <div class="pl-2 space-y-1">
                                    @foreach ($chanList as $c)
                                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                            <span class="text-gray-400 font-bold">#</span>
                                            <span>{{ $c['name'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No channels found.</p>
                    @endif
                </div>
            </div>

            <!-- Server Roles Column -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                <h4 class="text-md font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Server Roles
                </h4>
                
                <div class="space-y-2 max-h-96 overflow-y-auto pr-2">
                    @if ($roles)
                        @foreach ($roles as $role)
                            @if (($role['name'] ?? '@everyone') !== '@everyone')
                                @php
                                    $colorHex = $role['color'] ? '#' . str_pad(dechex($role['color']), 6, '0', STR_PAD_LEFT) : null;
                                @endphp
                                <div class="flex items-center gap-2.5 py-1">
                                    <div class="w-3.5 h-3.5 rounded-full border border-gray-200 dark:border-gray-600 shadow-sm"
                                         style="background-color: {{ $colorHex ?? '#99aab5' }}"></div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200" 
                                          style="color: {{ $colorHex }}">
                                        {{ $role['name'] }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No roles found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@else
    <div class="p-6 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-900/50 flex items-start gap-4">
        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div class="space-y-1">
            <h4 class="text-sm font-bold text-amber-800 dark:text-amber-200">
                Discord Bot Configuration Incomplete
            </h4>
            <p class="text-sm text-amber-700 dark:text-amber-300">
                Please enter a valid Discord Bot Token and Guild ID, then save the configuration. Once successfully connected, server info, channels, categories, and roles will be displayed here in real time.
            </p>
        </div>
    </div>
@endif
