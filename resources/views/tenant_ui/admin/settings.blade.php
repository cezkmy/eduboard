<x-app-layout>
    <x-slot name="title">Settings - Buksu Eduboard</x-slot>

    <div class="admin-content" x-data="{ 
        activeSection: '{{ request()->query('tab') ?? ((tenant('plan') ?? 'Basic') === 'Basic' ? 'branding' : 'appearance') }}',
        successModal: {{ session('success') ? 'true' : 'false' }},
        basicWarningModal: false,
        plan: '{{ tenant('plan') ?? 'Basic' }}',
        hasUpdatedSettings: {{ tenant('has_updated_settings') ? 'true' : 'false' }},
        logoPreview: '{{ tenant('logo') ? asset('storage/' . tenant('logo')) : asset('images/Logo.jpg') }}',
        handleLogoChange(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.logoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        showSuccess() {
            this.successModal = true;
            setTimeout(() => { this.successModal = false }, 3000);
        },
        pendingAction: null,
        handleSave(section) {
            if (this.plan === 'Basic' && (section === 'branding' || section === 'general')) {
                this.pendingAction = section;
                this.basicWarningModal = true;
            } else {
                if (section === 'general') document.getElementById('general-form').submit();
                else if (section === 'branding') document.getElementById('branding-form').submit();
                else if (section === 'appearance') document.getElementById('appearance-form').submit();
                else this.showSuccess();
            }
        },
        confirmSave() {
            this.basicWarningModal = false;
            if (this.pendingAction === 'general') document.getElementById('general-form').submit();
            else if (this.pendingAction === 'branding') document.getElementById('branding-form').submit();
            else if (this.pendingAction === 'appearance') document.getElementById('appearance-form').submit();
        }
    }">
        @if(session('error'))
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="text-sm font-bold">{{ session('error') }}</span>
        </div>
        @endif

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">System Settings</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Configure and customize your Buksu Eduboard instance</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Navigation Sidebar --}}
            <div class="lg:col-span-3 space-y-2">
                <button @if((tenant('plan') ?? 'Basic') !== 'Basic') @click="activeSection = 'appearance'" @endif
                        :class="activeSection === 'appearance' ? 'bg-[rgba(var(--accent-rgb),0.10)] text-[var(--accent)] dark:bg-[rgba(var(--accent-rgb),0.18)] dark:text-[var(--accent)]' : 'text-gray-600 dark:text-gray-400 @if((tenant('plan') ?? 'Basic') !== 'Basic') hover:bg-gray-50 dark:hover:bg-gray-800 @endif'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all @if((tenant('plan') ?? 'Basic') === 'Basic') opacity-50 cursor-not-allowed @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                    Appearance
                    @if((tenant('plan') ?? 'Basic') === 'Basic')
                    <svg class="w-4 h-4 ml-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    @endif
                </button>
                <button @click="activeSection = 'branding'" 
                        :class="activeSection === 'branding' ? 'bg-[rgba(var(--accent-rgb),0.10)] text-[var(--accent)] dark:bg-[rgba(var(--accent-rgb),0.18)] dark:text-[var(--accent)]' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Branding
                </button>
                <button @click="activeSection = 'general'" 
                        :class="activeSection === 'general' ? 'bg-[rgba(var(--accent-rgb),0.10)] text-[var(--accent)] dark:bg-[rgba(var(--accent-rgb),0.18)] dark:text-[var(--accent)]' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924-1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    General
                </button>
                <button @click="activeSection = 'system_info'" 
                        :class="activeSection === 'system_info' ? 'bg-[rgba(var(--accent-rgb),0.10)] text-[var(--accent)] dark:bg-[rgba(var(--accent-rgb),0.18)] dark:text-[var(--accent)]' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    System Info
                </button>
                <div class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-800">
                    <button @click="activeSection = 'danger'" 
                            :class="activeSection === 'danger' ? 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400' : 'text-gray-600 dark:text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/10'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Critical Actions
                    </button>
                </div>
            </div>

            {{-- Main Content Area --}}
            <div class="lg:col-span-9 space-y-6">
                
                {{-- Appearance Section --}}
                <div x-show="activeSection === 'appearance'" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 space-y-8" x-cloak>
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-black text-gray-900 dark:text-white">Appearance</h2>
                            <p class="text-sm text-gray-500 mt-1">Choose a color theme for your school's dashboard and interface.</p>
                        </div>
                        <button @click="handleSave('appearance')" class="px-5 py-2 bg-[var(--accent)] text-white rounded-xl text-sm font-bold hover:bg-[var(--accent-dark)] transition-all shadow-lg active:scale-95" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);">
                            Save Appearance
                        </button>
                    </div>

                    <form id="appearance-form" method="POST" action="{{ route('tenant.admin.settings.update') }}">
                        @csrf
                        <div>
                            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider mb-6">Domain Layout / Theme Preset</h3>
                            @php $currentTheme = tenant('theme_color') ?? 'blue'; @endphp
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="theme_color" value="blue" class="peer sr-only" {{ $currentTheme === 'blue' ? 'checked' : '' }}>
                                    <div class="p-4 rounded-2xl border-2 border-gray-100 dark:border-gray-700 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all text-center">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 mx-auto mb-2 flex items-center justify-center font-black">Bb</div>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">Blue</span>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="theme_color" value="green" class="peer sr-only" {{ $currentTheme === 'green' ? 'checked' : '' }}>
                                    <div class="p-4 rounded-2xl border-2 border-gray-100 dark:border-gray-700 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all text-center">
                                        <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 mx-auto mb-2 flex items-center justify-center font-black">Gg</div>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">Green</span>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="theme_color" value="pink" class="peer sr-only" {{ $currentTheme === 'pink' ? 'checked' : '' }}>
                                    <div class="p-4 rounded-2xl border-2 border-gray-100 dark:border-gray-700 peer-checked:border-fuchsia-500 peer-checked:bg-fuchsia-50 transition-all text-center">
                                        <div class="w-10 h-10 rounded-full bg-fuchsia-100 text-fuchsia-600 mx-auto mb-2 flex items-center justify-center font-black">Pp</div>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">Pink</span>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="theme_color" value="yellow" class="peer sr-only" {{ $currentTheme === 'yellow' ? 'checked' : '' }}>
                                    <div class="p-4 rounded-2xl border-2 border-gray-100 dark:border-gray-700 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all text-center">
                                        <div class="w-10 h-10 rounded-full bg-yellow-100 text-yellow-600 mx-auto mb-2 flex items-center justify-center font-black">Yy</div>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">Yellow</span>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="theme_color" value="orange" class="peer sr-only" {{ $currentTheme === 'orange' ? 'checked' : '' }}>
                                    <div class="p-4 rounded-2xl border-2 border-gray-100 dark:border-gray-700 peer-checked:border-orange-500 peer-checked:bg-orange-50 transition-all text-center">
                                        <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 mx-auto mb-2 flex items-center justify-center font-black">Oo</div>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">Orange</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Branding Section --}}
                <div x-show="activeSection === 'branding'" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 space-y-8" x-cloak>
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-black text-gray-900 dark:text-white">Branding</h2>
                            <p class="text-sm text-gray-500 mt-1">Manage your school's identity and logos.</p>
                        </div>
                        <button x-show="!(plan === 'Basic' && hasUpdatedSettings)" @click="handleSave('branding')" class="px-5 py-2 bg-[var(--accent)] text-white rounded-xl text-sm font-bold hover:bg-[var(--accent-dark)] transition-all shadow-lg active:scale-95" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);">
                            Save Branding
                        </button>
                        <span x-cloak x-show="plan === 'Basic' && hasUpdatedSettings" class="text-xs font-bold text-red-500 bg-red-50 dark:bg-red-900/20 px-4 py-2 rounded-xl border border-red-100 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Locked (Upgrade to Pro)
                        </span>
                    </div>

                    <form id="branding-form" method="POST" action="{{ route('tenant.admin.settings.update') }}" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-8 items-start">
                        @csrf
                        <div class="w-full md:w-64 aspect-square bg-gray-50 dark:bg-gray-900 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center p-8 text-center group relative overflow-hidden">
                            <img :src="logoPreview" class="w-full h-full object-contain opacity-70 group-hover:opacity-20 transition-opacity">
                            <div class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-all transform translate-y-4 group-hover:translate-y-0">
                                <svg class="w-10 h-10 text-[var(--accent)] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4-4m4 4v12" /></svg>
                                <span class="text-xs font-bold text-gray-900 dark:text-white">Upload New Logo</span>
                            </div>
                            <input type="file" name="logo" @change="handleLogoChange" class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                        <div class="flex-1 space-y-6">
                            <div class="p-6 rounded-2xl border" style="background: rgba(var(--accent-rgb), 0.08); border-color: rgba(var(--accent-rgb), 0.22);">
                                <h4 class="text-sm font-bold text-[var(--accent)] flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                    Logo Requirements
                                </h4>
                                <ul class="mt-2 space-y-1 text-xs text-[var(--accent)] font-medium opacity-90">
                                    <li>• Recommended size: 512x512 pixels</li>
                                    <li>• Format: Transparent PNG preferred</li>
                                    <li>• Max file size: 2MB</li>
                                </ul>
                            </div>
                            <div class="space-y-4">
                                <label class="block">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">School Full Name</span>
                                    <input type="text" name="school_name" value="{{ tenant('school_name') ?? 'Bukidnon State University' }}" class="mt-2 w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm font-bold">
                                </label>
                                <label class="block">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">School Short Name</span>
                                    <input type="text" name="school_short_name" value="{{ tenant('school_short_name') ?? 'Buksu' }}" class="mt-2 w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm font-bold">
                                </label>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- General Section --}}
                <div x-show="activeSection === 'general'" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 space-y-8" x-cloak>
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-black text-gray-900 dark:text-white">General Settings</h2>
                            <p class="text-sm text-gray-500 mt-1">Basic system configurations and behavior.</p>
                        </div>
                        <button x-show="!(plan === 'Basic' && hasUpdatedSettings)" @click="handleSave('general')" class="px-5 py-2 bg-[var(--accent)] text-white rounded-xl text-sm font-bold hover:bg-[var(--accent-dark)] transition-all shadow-lg active:scale-95" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);">
                            Save General Settings
                        </button>
                        <span x-cloak x-show="plan === 'Basic' && hasUpdatedSettings" class="text-xs font-bold text-red-500 bg-red-50 dark:bg-red-900/20 px-4 py-2 rounded-xl border border-red-100 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Locked (Upgrade to Pro)
                        </span>
                    </div>

                    <form id="general-form" method="POST" action="{{ route('tenant.admin.settings.update') }}" class="space-y-6">
                        @csrf
                        <label class="block">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Site Description</span>
                            <textarea name="site_description" rows="3" class="mt-2 w-full px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl text-sm font-medium">{{ tenant('site_description') ?? 'A modern digital announcement board for the Bukidnon State University community.' }}</textarea>
                        </label>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <label class="block">
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Primary Email</span>
                                <input type="email" name="primary_email" value="{{ tenant('primary_email') ?? 'admin@buksu.edu.ph' }}" class="mt-2 w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm font-bold">
                            </label>
                            <label class="block opacity-50 cursor-not-allowed">
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Timezone (Managed by Central)</span>
                                <input type="text" value="{{ tenant('timezone') ?? 'Asia/Manila' }}" disabled class="mt-2 w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm font-bold">
                            </label>
                        </div>
                    </form>
                </div>


                {{-- System Info Section --}}
                <div x-show="activeSection === 'system_info'" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 space-y-8" x-cloak>
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">System Information</h2>
                        <p class="text-sm text-gray-500 mt-1">Technical details about your Buksu Eduboard installation.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-6 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-4">
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider">Software Environment</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-500">EduBoard Version</span>
                                    <div class="flex flex-col items-end">
                                        <span class="text-xs font-black text-[var(--accent)]">{{ tenant('system_version') ?? 'v2.0.0-stable' }}</span>
                                        @if($latestRelease && ($latestRelease['tag_name'] ?? '') !== tenant('system_version'))
                                            <span class="text-[9px] font-black text-blue-500 uppercase tracking-tighter mt-1">UPDATE AVAILABLE: {{ $latestRelease['tag_name'] }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-500">Platform License</span>
                                    <span class="text-xs font-black text-[var(--accent)]">{{ ucfirst(tenant('plan')) ?? 'Basic' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-500">Database Engine</span>
                                    <span class="text-xs font-black text-[var(--accent)]">MySQL 8.0</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-4">
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider">Storage Usage</h3>
                            <div class="space-y-4">
                                <div class="space-y-1.5">
                                    <div class="flex justify-between text-[10px] font-black uppercase tracking-tighter">
                                        <span class="text-gray-500">Disk Space</span>
                                        <span class="text-[var(--accent)]">{{ number_format(tenant('storage_used_gb') ?? 0, 1) }} GB / {{ tenant('storage_limit_gb') ?? 5 }} GB</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full" style="width: {{ (tenant('storage_used_gb') ?? 1) / (tenant('storage_limit_gb') ?? 5) * 100 }}%; background: var(--accent);"></div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-500">Media Uploads</span>
                                    <span class="text-xs font-black text-gray-900 dark:text-white">{{ \App\Models\Announcement::count() + \App\Models\User::count() }} Entities</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($latestRelease && ($latestRelease['tag_name'] ?? '') !== tenant('system_version'))
                    <div class="p-6 bg-blue-50 dark:bg-blue-500/5 rounded-2xl border border-blue-100 dark:border-blue-500/20 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex gap-4">
                            <div class="text-blue-500 dark:text-blue-400 mt-1">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-blue-700 dark:text-blue-400 uppercase tracking-widest mb-1">New Version Available! ({{ $latestRelease['tag_name'] }})</h4>
                                <p class="text-[11px] text-blue-600 dark:text-blue-400/70 leading-relaxed max-w-md">
                                    {{ Str::limit($latestRelease['body'] ?? 'Performance improvements and bug fixes.', 120) }}
                                </p>
                            </div>
                        </div>
                        <form action="{{ route('tenant.admin.version.apply') }}" method="POST">
                            @csrf
                            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 whitespace-nowrap uppercase tracking-widest">
                                Apply Update
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="p-6 bg-green-50 dark:bg-green-500/5 rounded-2xl border border-green-100 dark:border-green-500/20 flex gap-4">
                        <div class="text-green-500 dark:text-green-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-green-700 dark:text-green-400 uppercase tracking-widest mb-1">System Status: Up to Date</h4>
                            <p class="text-[11px] text-green-600 dark:text-green-400/70 leading-relaxed">Your school instance is currently running the latest platform version.</p>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Danger Zone Section --}}
                <div x-show="activeSection === 'danger'" class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-red-100 dark:border-red-900/30 shadow-sm p-8 space-y-8" x-cloak>
                    <div>
                        <h2 class="text-xl font-black text-red-600">Critical Actions</h2>
                        <p class="text-sm text-gray-500 mt-1">Actions in this section are permanent and cannot be undone.</p>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        <div class="py-6 flex items-center justify-between gap-4">
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Reset System Cache</h4>
                                <p class="text-xs text-gray-500">Clears all temporary data and reloads system configurations.</p>
                            </div>
                            <button class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl text-xs font-black hover:bg-gray-200 transition-all">PURGE CACHE</button>
                        </div>
                        <div class="py-6 flex items-center justify-between gap-4">
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Wipe All Data</h4>
                                <p class="text-xs text-gray-500">Permanently deletes all announcements, users, and categories.</p>
                            </div>
                            <button class="px-4 py-2 bg-red-50 text-red-600 rounded-xl text-xs font-black hover:bg-red-100 transition-all">FACTORY RESET</button>
                        </div>

                        @if(tenant('previous_version'))
                        <div class="py-6 flex items-center justify-between gap-4">
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Rollback to Previous Version</h4>
                                <p class="text-xs text-gray-500">Revert your school instance from <strong>{{ tenant('system_version') }}</strong> back to <strong>{{ tenant('previous_version') }}</strong>.</p>
                            </div>
                            <form action="{{ route('tenant.admin.version.rollback') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-orange-50 text-orange-600 rounded-xl text-xs font-black hover:bg-orange-100 transition-all uppercase">RESTORE {{ tenant('previous_version') }}</button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Basic Plan Warning Modal --}}
        <template x-teleport="body">
            <div x-show="basicWarningModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="basicWarningModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-8 max-w-md w-full text-center space-y-6 transform transition-all"
                     x-show="basicWarningModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="w-16 h-16 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-500 rounded-full flex items-center justify-center mx-auto border-4 border-yellow-100 dark:border-yellow-900/50">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Are you sure?</h3>
                        <p class="text-sm text-gray-500 mt-3 leading-relaxed">
                            Are you sure you want to save changes? This cannot be undone. 
                            <br><br>
                            For unlimited customization, please upgrade to <strong>Premium</strong> or better <strong>Ultimate</strong>.
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <button @click="basicWarningModal = false" class="w-full py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">Cancel</button>
                        <button @click="confirmSave()" class="w-full py-2.5 bg-yellow-500 text-white rounded-xl font-bold hover:bg-yellow-600 transition-all shadow-lg shadow-yellow-500/20">Yes, Save</button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Success Modal --}}
        <template x-teleport="body">
            <div x-show="successModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
                <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="successModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-8 max-w-sm w-full text-center space-y-6 transform transition-all"
                     x-show="successModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto text-[var(--accent)]" style="background: rgba(var(--accent-rgb), 0.12);">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Settings Updated!</h3>
                        <p class="text-sm text-gray-500 mt-2">All changes have been successfully applied to your instance.</p>
                    </div>
                    <button @click="successModal = false" class="w-full py-3 bg-[var(--accent)] text-white rounded-2xl font-bold hover:bg-[var(--accent-dark)] transition-all shadow-lg" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);">Great!</button>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>