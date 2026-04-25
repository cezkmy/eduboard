<x-app-layout>
    <x-slot name="title">System Update</x-slot>

    <div class="space-y-6" x-data="tenantUpdater()">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">System Update</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                    Check GitHub releases and update your school instance.
                </p>
            </div>
        </div>

        @if(tenant('is_updating'))
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 px-4 py-3 rounded-xl text-sm font-bold animate-pulse">
                A system update is currently in progress. Please wait until it finishes.
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Current Version</div>
                <div class="text-3xl font-black text-gray-900 dark:text-white">{{ $currentVersion }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    This is the version currently running on your school's instance.
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Available Version</div>
                <div class="text-3xl font-black text-[var(--accent)]">{{ $latestVersion }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Latest release: {{ $release['name'] ?? 'Release' }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Manual Action</div>

                <div class="flex items-center justify-between gap-3 bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-2">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Auto-Apply</div>
                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200">Apply releases automatically</div>
                    </div>
                    <button type="button"
                            x-data="{ enabled: {{ (tenant('auto_update_enabled') ? 'true' : 'false') }} }"
                            @click="fetch('{{ route('tenant.admin.system.update.auto_toggle') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify({enabled: !enabled})}).then(r=>r.json()).then(d=>{ if(d.success) enabled=d.enabled; })"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                            :class="enabled ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-700'">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                              :class="enabled ? 'translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>

                <div class="space-y-3">
                    <label class="block">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Select Version</span>
                        <select x-model="selectedVersion" :disabled="isUpdating" 
                                class="mt-2 w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold">
                            @foreach(($sorted ?? collect()) as $r)
                                <option value="{{ $r['tag_name'] }}">
                                    {{ $r['tag_name'] }}{{ !empty($r['is_prerelease']) ? ' (pre-release)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <button @click="triggerUpdate()" :disabled="isUpdating"
                            class="w-full px-4 py-3 bg-[var(--accent)] text-white rounded-xl text-xs font-black uppercase tracking-widest hover:opacity-90 transition-all shadow-accent disabled:opacity-50">
                        <span x-show="!isUpdating">Apply Release Now</span>
                        <span x-show="isUpdating">Processing...</span>
                    </button>

                    @if($rollbackAvailable)
                        <button @click="triggerRollback()" :disabled="isUpdating"
                                class="w-full px-4 py-3 bg-orange-50 text-orange-700 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-orange-100 transition-all border border-orange-100 dark:border-orange-900/30 disabled:opacity-50">
                            Revert to {{ tenant('previous_version') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Progress and Logs --}}
        <div x-show="isUpdating || logs.length > 0" x-transition class="space-y-6">
            <div x-show="isUpdating" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-bold text-gray-700 dark:text-gray-200" x-text="currentStep"></div>
                    <div class="text-sm font-black text-[var(--accent)]" x-text="progress + '%'"></div>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-900 rounded-full h-3 overflow-hidden">
                    <div class="bg-[var(--accent)] h-full transition-all duration-500" :style="'width: ' + progress + '%'"></div>
                </div>
            </div>

            <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-lg overflow-hidden">
                <div class="px-4 py-2 border-b border-gray-800 flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                    <div class="w-2.5 h-2.5 rounded-full bg-green-500"></div>
                    <span class="ml-2 text-[10px] font-mono text-gray-500 uppercase tracking-widest">Update Terminal</span>
                </div>
                <div id="terminal-window" class="p-6 font-mono text-xs h-64 overflow-y-auto scroll-smooth">
                    <template x-for="(log, index) in logs" :key="index">
                        <div class="mb-1.5 flex gap-3">
                            <span class="text-emerald-500 shrink-0">tenant@eduboard:~$</span>
                            <span class="text-gray-300" x-text="log"></span>
                        </div>
                    </template>
                    <div x-show="isUpdating" class="mt-2 text-gray-500 animate-pulse font-bold">
                        <span class="inline-block h-3 w-3 rounded-full border-2 border-current border-t-transparent animate-spin mr-2"></span>
                        Processing...
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($release['body']))
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <div class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Release Notes</div>
                <div class="prose dark:prose-invert max-w-none text-sm text-gray-700 dark:text-gray-300">
                    {!! nl2br(e($release['body'])) !!}
                </div>
            </div>
        @endif
    </div>

    <script>
        function tenantUpdater() {
            return {
                updateId: null,
                isUpdating: false,
                logs: [],
                progress: 0,
                currentStep: 'Initializing...',
                selectedVersion: '{{ $latestVersion }}',
                pollInterval: null,

                async triggerUpdate() {
                    if (!confirm(`Apply release ${this.selectedVersion} to your school? This will finalize the update and migrate your database.`)) return;
                    
                    this.isUpdating = true;
                    this.progress = 5;
                    this.logs = ['[INFO] Initializing update session...'];
                    
                    try {
                        const response = await fetch('{{ route('tenant.admin.version.apply') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ version: this.selectedVersion })
                        });
                        
                        const data = await response.json();
                        if (data.success) {
                            this.updateId = data.update_id;
                            this.startPolling();
                        } else {
                            alert(data.message);
                            this.isUpdating = false;
                        }
                    } catch (e) {
                        this.logs.push('[FATAL] Request failed: ' + e.message);
                        this.isUpdating = false;
                    }
                },

                async triggerRollback() {
                    if (!confirm('Revert your school instance back to the previous version? This will restore files from the last backup.')) return;

                    this.isUpdating = true;
                    this.progress = 5;
                    this.logs = ['[INFO] Initializing rollback session...'];

                    try {
                        const response = await fetch('{{ route('tenant.admin.version.rollback') }}', {
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
                            this.startPolling();
                        } else {
                            alert(data.message);
                            this.isUpdating = false;
                        }
                    } catch (e) {
                        this.logs.push('[FATAL] Request failed: ' + e.message);
                        this.isUpdating = false;
                    }
                },

                startPolling() {
                    this.pollInterval = setInterval(async () => {
                        try {
                            const response = await fetch(`{{ url('/admin/version/logs') }}/${this.updateId}`);
                            const data = await response.json();
                            
                            if (data.logs) {
                                this.logs = data.logs;
                                this.calculateProgress(data.logs);
                                this.scrollToBottom();
                            }

                            if (data.finished) {
                                clearInterval(this.pollInterval);
                                this.progress = 100;
                                this.isUpdating = false;
                                
                                const lastLog = data.logs[data.logs.length - 1].toLowerCase();
                                if (lastLog.includes('successfully')) {
                                    setTimeout(() => {
                                        alert('Operation completed successfully! Reloading...');
                                        window.location.reload();
                                    }, 1000);
                                } else {
                                    alert('Operation failed. Check logs for details.');
                                }
                            }
                        } catch (e) {
                            console.error('Polling error:', e);
                        }
                    }, 2000);
                },

                calculateProgress(logs) {
                    const steps = [
                        { pattern: 'maintenance mode', progress: 10, label: 'Entering maintenance mode...' },
                        { pattern: 'Creating backup', progress: 25, label: 'Backing up system...' },
                        { pattern: 'Downloading', progress: 40, label: 'Downloading release...' },
                        { pattern: 'Extracting', progress: 50, label: 'Extracting files...' },
                        { pattern: 'Applying new files', progress: 60, label: 'Applying updates...' },
                        { pattern: 'Composer', progress: 75, label: 'Installing dependencies...' },
                        { pattern: 'NPM', progress: 85, label: 'Building assets...' },
                        { pattern: 'migrations', progress: 95, label: 'Migrating database...' },
                        { pattern: 'successfully', progress: 100, label: 'Complete!' }
                    ];

                    for (let i = steps.length - 1; i >= 0; i--) {
                        if (logs.some(log => log.includes(steps[i].pattern))) {
                            this.progress = steps[i].progress;
                            this.currentStep = steps[i].label;
                            break;
                        }
                    }
                },

                scrollToBottom() {
                    const terminal = document.getElementById('terminal-window');
                    if (terminal) {
                        setTimeout(() => {
                            terminal.scrollTop = terminal.scrollHeight;
                        }, 50);
                    }
                }
            };
        }
    </script>
</x-app-layout>


