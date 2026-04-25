<x-app-layout>
    <x-slot name="title">System Update</x-slot>

    <div class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">System Update</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                    Check GitHub releases and update your tenant instance.
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-xl text-sm font-bold">
                {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 px-4 py-3 rounded-xl text-sm font-bold">
                {{ session('info') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl text-sm font-bold">
                {{ session('error') }}
            </div>
        @endif

        @if(tenant('is_updating'))
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 px-4 py-3 rounded-xl text-sm font-bold">
                A system update is already running for this tenant. Please wait until it finishes before submitting another update.
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
                    Latest version released on GitHub: {{ $release['name'] ?? 'Release' }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Manual Action</div>

                <div class="flex items-center justify-between gap-3 bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-2">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Auto-Apply</div>
                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200">Apply new releases automatically</div>
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

                <form action="{{ route('tenant.admin.version.apply') }}" method="POST" class="space-y-3 apply-release-form" id="tenant-update-form">
                    @csrf
                    <div id="tenant-update-status" class="hidden text-sm font-bold text-gray-700 dark:text-gray-200 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-3 py-3 rounded-xl">
                        <span id="tenant-update-status-text"></span>
                    </div>
                    <label class="block">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Select Version</span>
                        <select name="version" class="mt-2 w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold">
                            @foreach(($sorted ?? collect()) as $r)
                                <option value="{{ $r['tag_name'] }}" {{ ($r['tag_name'] ?? '') === $latestVersion ? 'selected' : '' }}>
                                    {{ $r['tag_name'] }}{{ !empty($r['is_prerelease']) ? ' (pre-release)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="text-[11px] text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700 rounded-xl px-3 py-3">
                        This update will run:
                        <ul class="list-disc list-inside ml-2 mt-1 space-y-1">
                            <li>composer install</li>
                            <li>npm install</li>
                            <li>npm run build</li>
                            <li>tenant migrate (with seed and cache refresh)</li>
                        </ul>
                    </div>

                @if($hasUpdate)
                        <button type="submit"
                                onclick="return confirmAction(event, 'Apply this release to your school? This will finalize the update and migrate your database.')"
                                class="w-full px-4 py-3 bg-[var(--accent)] text-white rounded-xl text-xs font-black uppercase tracking-widest hover:opacity-90 transition-all shadow-accent">
                            Apply Release Now
                        </button>
                @else
                    <div class="space-y-3">
                        <div class="text-xs font-bold text-green-900 dark:text-green-100 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-3 py-2 rounded-xl">
                            No new updates available. You are currently running the latest release.
                        </div>
                        <button type="button" disabled
                                class="w-full px-4 py-3 bg-gray-200 text-gray-600 rounded-xl text-xs font-black uppercase tracking-widest transition-all opacity-60 cursor-not-allowed border border-gray-200">
                            Apply Release Now
                        </button>
                    </div>
                @endif
                </form>

                @if($rollbackAvailable)
                    <form action="{{ route('tenant.admin.version.rollback') }}" method="POST" class="space-y-3 rollback-release-form">
                        @csrf
                        <button type="submit"
                                onclick="return confirmAction(event, 'Revert your school from {{ tenant('system_version') }} back to {{ tenant('previous_version') }}? This will restore your previous version marker.')"
                                class="w-full px-4 py-3 bg-orange-50 text-orange-700 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-orange-100 transition-all border border-orange-100 dark:border-orange-900/30">
                            Revert to {{ tenant('previous_version') }}
                        </button>
                    </form>
                @else
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Rollback</div>
                        <div class="text-[11px] font-medium text-gray-500 dark:text-gray-400 italic">
                            Rollback will be available after your first manual update.
                        </div>
                    </div>
                @endif
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
        document.addEventListener('DOMContentLoaded', function () {
            const statusBox = document.getElementById('tenant-update-status');
            const statusText = document.getElementById('tenant-update-status-text');
            const updateForm = document.querySelector('form.apply-release-form');
            const rollbackForm = document.querySelector('form.rollback-release-form');
            const spinner = '<span class="inline-block mr-2 h-4 w-4 rounded-full border-2 border-current border-t-transparent animate-spin"></span>';

            function attachForm(form, actionLabel) {
                if (!form) {
                    return;
                }

                form.addEventListener('submit', function () {
                    const submitButton = form.querySelector('button[type=submit]');
                    if (!submitButton || submitButton.disabled) {
                        return;
                    }

                    submitButton.disabled = true;
                    submitButton.classList.add('cursor-wait', 'opacity-70');
                    submitButton.innerHTML = spinner + actionLabel;

                    if (statusBox && statusText) {
                        statusText.textContent = actionLabel + ' This may take several minutes. Composer install, NPM install, build, and tenant migrations will run. Do not refresh or close this page.';
                        statusBox.classList.remove('hidden');
                    }
                });
            }

            attachForm(updateForm, 'Updating tenant instance...');
            attachForm(rollbackForm, 'Rolling back tenant instance...');
        });
    </script>
</x-app-layout>

