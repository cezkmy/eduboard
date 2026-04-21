@extends('central.layouts.admin-layout')

@section('content')
<div class="px-6 py-8 mx-auto max-w-5xl" x-data="systemUpdater()">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <div class="p-2 bg-blue-50 dark:bg-blue-900/30 text-blue-500 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                System Updater
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Keep your core SaaS environment up to date with the latest features and patches.</p>
        </div>

        <div class="flex flex-col items-end gap-2 text-right">
            <div class="flex items-center gap-4 bg-gray-50 dark:bg-gray-800/50 p-2 pl-4 rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="flex flex-col items-center pr-4 border-r border-gray-200 dark:border-gray-700">
                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Auto-Update</span>
                    <button @click="toggleAutoUpdate()" 
                            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                            :class="autoUpdate ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-700'">
                        <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                              :class="autoUpdate ? 'translate-x-4' : 'translate-x-0'"></span>
                    </button>
                </div>
                <div class="flex flex-col items-end pr-4 border-r border-gray-200 dark:border-gray-700">
                    <div class="text-[10px] uppercase font-black text-gray-400 tracking-widest">Current</div>
                    <div class="text-lg font-black text-gray-700 dark:text-gray-200">
                        {{ $currentVersion }}
                    </div>
                </div>
                <div class="flex flex-col items-end">
                    <div class="text-[10px] uppercase font-black text-gray-400 tracking-widest">Latest</div>
                    <div class="text-lg font-black text-blue-500">
                        {{ $latestVersion }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Manual Controls --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <button @click="checkUpdates()" :disabled="isChecking"
                class="flex items-center justify-center gap-3 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-blue-500 transition-all group">
            <div class="p-2 bg-blue-50 dark:bg-blue-900/30 text-blue-500 rounded-lg group-hover:scale-110 transition-transform">
                <svg :class="isChecking ? 'animate-spin' : ''" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </div>
            <div class="text-left">
                <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Manual Action</div>
                <div class="text-sm font-bold text-gray-900 dark:text-white">Check for Releases</div>
            </div>
        </button>

        <button @click="triggerUpdate()" :disabled="isUpdating"
                class="flex items-center justify-center gap-3 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-emerald-500 transition-all group">
            <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 rounded-lg group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
            </div>
            <div class="text-left">
                <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Manual Action</div>
                <div class="text-sm font-bold text-gray-900 dark:text-white">Run Manual Update</div>
            </div>
        </button>

        @if($rollbackAvailable)
        <button @click="triggerRollback()" :disabled="isUpdating"
                class="flex items-center justify-center gap-3 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-amber-500 transition-all group">
            <div class="p-2 bg-amber-50 dark:bg-amber-900/30 text-amber-500 rounded-lg group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
            </div>
            <div class="text-left">
                <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Manual Action</div>
                <div class="text-sm font-bold text-gray-900 dark:text-white">Rollback to {{ $rollbackVersion }}</div>
            </div>
        </button>
        @else
        <div class="flex items-center justify-center gap-3 p-4 bg-gray-50/50 dark:bg-gray-800/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 opacity-60">
            <div class="text-center">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">No Backups Available</div>
            </div>
        </div>
        @endif
    </div>

    {{-- Main Update Board --}}
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
        <div class="p-8 pb-6 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/80">
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                    @if($hasUpdate)
                        <span class="relative flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        New Update Ready
                        @if($release['is_prerelease'] ?? false)
                            <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-[10px] font-black rounded-md uppercase tracking-widest">Pre-release</span>
                        @endif
                    @else
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        System is Optimized
                    @endif
                </h3>
                
                @if($hasUpdate)
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Version <strong>{{ $latestVersion }}</strong> is waiting to be installed.
                    </p>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        You are running the latest stable release.
                    </p>
                @endif
            </div>

            <div class="flex items-center gap-4">
                @if($hasUpdate)
                    <button @click="triggerUpdate()" :disabled="isUpdating"
                        class="px-8 py-3 bg-emerald-600 text-white rounded-xl text-sm font-bold shadow-lg hover:bg-emerald-700 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isUpdating">Install Update Now</span>
                        <span x-show="isUpdating">Installing...</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Terminal / Logs Output --}}
        <div x-show="logs.length > 0" x-transition x-cloak class="border-t border-gray-100 dark:border-gray-700 bg-gray-900">
            <div class="px-4 py-2 border-b border-gray-800 flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                <span class="ml-2 text-xs font-mono text-gray-500">system_updater_terminal</span>
            </div>
            <div id="terminal-window" class="p-6 font-mono text-sm h-[400px] overflow-y-auto scroll-smooth">
                <template x-for="(log, index) in logs" :key="index">
                    <div class="mb-1">
                        <span class="text-green-500">eduboard@server:~$</span>
                        <span class="text-gray-300 ml-2" x-text="log"></span>
                    </div>
                </template>
                <div x-show="isUpdating" class="mt-2 text-gray-500 animate-pulse">_</div>
            </div>
        </div>
    </div>
    
    {{-- Release Notes (Optional placeholder) --}}
    @if($hasUpdate && isset($release['body']))
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Release Notes</h3>
        <div class="prose dark:prose-invert max-w-none text-sm text-gray-600 dark:text-gray-300">
            {!! nl2br(e($release['body'])) !!}
        </div>
    </div>
    @endif
</div>

<script>
function systemUpdater() {
    return {
        updateId: null,
        isUpdating: false,
        isChecking: false,
        logs: [],
        pollInterval: null,
        autoUpdate: {{ $autoUpdate ? 'true' : 'false' }},

        async checkUpdates() {
            this.isChecking = true;
            this.logs = ['Contacting GitHub API for latest releases...'];
            try {
                // We'll just reload the page since the controller index already fetches latest
                window.location.reload();
            } catch (error) {
                this.isChecking = false;
            }
        },

        async toggleAutoUpdate() {
            const newState = !this.autoUpdate;
            try {
                const response = await fetch("{{ route('central.admin.system.update.auto_toggle') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ enabled: newState })
                });
                const data = await response.json();
                if (data.success) {
                    this.autoUpdate = data.enabled;
                }
            } catch (error) {
                console.error("Failed to toggle auto-update", error);
            }
        },

        async triggerUpdate() {
            if (!confirm("Are you sure you want to begin the update? This will extract new files and could cause momentary downtime. A rollback zip will be created automatically.")) {
                return;
            }

            this.isUpdating = true;
            this.logs = ['Initializing update protocol...'];

            try {
                const response = await fetch("{{ route('central.admin.system.update.trigger') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.updateId = data.update_id;
                    this.logs.push(data.message);
                    this.startPolling();
                } else {
                    this.logs.push(`ERROR: ${data.message}`);
                    this.isUpdating = false;
                }
            } catch (error) {
                this.logs.push(`FATAL ERROR: Could not dispatch update job.`);
                this.isUpdating = false;
            }
        },

        async triggerRollback() {
            if (!confirm("Are you sure you want to rollback? This will restore files from the backup created before the last update. Database changes will not be undone automatically.")) {
                return;
            }

            this.isUpdating = true;
            this.logs = ['Initializing rollback protocol...'];

            try {
                const response = await fetch("{{ route('central.admin.system.update.rollback') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.updateId = data.update_id;
                    this.logs.push(data.message);
                    this.startPolling();
                } else {
                    this.logs.push(`ERROR: ${data.message}`);
                    this.isUpdating = false;
                }
            } catch (error) {
                this.logs.push(`FATAL ERROR: Could not dispatch rollback job.`);
                this.isUpdating = false;
            }
        },

        startPolling() {
            this.pollInterval = setInterval(async () => {
                try {
                    const response = await fetch(`{{ url('/admin/system/update/logs') }}/${this.updateId}`);
                    const data = await response.json();
                    
                    if (data.logs && data.logs.length > 0) {
                        this.logs = data.logs;
                        this.scrollToBottom();
                    }

                    if (data.finished) {
                        clearInterval(this.pollInterval);
                        this.isUpdating = false;
                        if (data.logs[data.logs.length - 1].includes('successfully')) {
                            setTimeout(() => {
                                alert("Update complete! The page will now reload.");
                                window.location.reload();
                            }, 2000);
                        }
                    }
                } catch (error) {
                    console.error("Polling error", error);
                }
            }, 2000);
        },
        
        scrollToBottom() {
            setTimeout(() => {
                const terminal = document.getElementById('terminal-window');
                if (terminal) {
                    terminal.scrollTop = terminal.scrollHeight;
                }
            }, 50);
        }
    }
}
</script>
@endsection
