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

        <div class="text-right">
            <div class="text-xs uppercase font-bold text-gray-400 tracking-wider">Current Version</div>
            <div class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-700 to-gray-500 dark:from-gray-200 dark:to-gray-400">
                {{ $currentVersion }}
            </div>
        </div>
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
                        New Update Available!
                    @else
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        System is Up to Date
                    @endif
                </h3>
                
                @if($hasUpdate)
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Version <strong>{{ $latestVersion }}</strong> is available. Published on <span class="text-gray-400">{{ \Carbon\Carbon::parse($release['published_at'])->format('M d, Y') }}</span>.
                    </p>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        You are currently running the latest stable release. No immediate action is required.
                    </p>
                @endif
            </div>

            <div class="flex items-center gap-4">
                @if($hasUpdate)
                    <button @click="triggerUpdate" :disabled="isUpdating"
                        class="px-8 py-3 bg-[var(--accent)] text-white rounded-xl text-sm font-bold shadow-lg hover:bg-[var(--accent-dark)] transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isUpdating">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
                            Download & Update to {{ $latestVersion }}
                        </span>
                        <span x-show="isUpdating">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Update in Progress...
                        </span>
                    </button>
                @else
                    <button @click="triggerUpdate" :disabled="isUpdating"
                        class="px-6 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-bold border-2 border-transparent hover:border-gray-200 dark:hover:border-gray-600 transition-all flex items-center gap-2">
                        <span x-show="!isUpdating">Force Reinstall</span>
                        <span x-show="isUpdating">Reinstalling...</span>
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
        logs: [],
        pollInterval: null,

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
