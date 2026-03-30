<x-app-layout>
    <x-slot name="title">Settings - Buksu Eduboard</x-slot>

    <div class="admin-content" x-data="{ 
        theme: '{{ $appearance['theme'] ?? 'light' }}',
        activeSection: '{{ request()->query('tab') ?? ((tenant('plan') ?? 'Basic') === 'Basic' ? 'branding' : 'appearance') }}',
        successModal: {{ session('success') ? 'true' : 'false' }},
        basicWarningModal: false,
        plan: '{{ tenant('plan') ?? 'Basic' }}',
        hasUpdatedSettings: {{ tenant('has_updated_settings') ? 'true' : 'false' }},
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
                else this.showSuccess();
            }
        },
        confirmSave() {
            this.basicWarningModal = false;
            if (this.pendingAction === 'general') document.getElementById('general-form').submit();
            else if (this.pendingAction === 'branding') document.getElementById('branding-form').submit();
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
                        :class="activeSection === 'appearance' ? 'bg-teal-50 text-teal-600 dark:bg-teal-900/20 dark:text-teal-400' : 'text-gray-600 dark:text-gray-400 @if((tenant('plan') ?? 'Basic') !== 'Basic') hover:bg-gray-50 dark:hover:bg-gray-800 @endif'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all @if((tenant('plan') ?? 'Basic') === 'Basic') opacity-50 cursor-not-allowed @endif">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                    Appearance
                    @if((tenant('plan') ?? 'Basic') === 'Basic')
                    <svg class="w-4 h-4 ml-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    @endif
                </button>
                <button @click="activeSection = 'branding'" 
                        :class="activeSection === 'branding' ? 'bg-teal-50 text-teal-600 dark:bg-teal-900/20 dark:text-teal-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Branding
                </button>
                <button @click="activeSection = 'general'" 
                        :class="activeSection === 'general' ? 'bg-teal-50 text-teal-600 dark:bg-teal-900/20 dark:text-teal-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924-1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    General
                </button>
                <button @click="activeSection = 'system_info'" 
                        :class="activeSection === 'system_info' ? 'bg-teal-50 text-teal-600 dark:bg-teal-900/20 dark:text-teal-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    System Info
                </button>
                <button @click="activeSection = 'system_updates'" 
                        :class="activeSection === 'system_updates' ? 'bg-teal-50 text-teal-600 dark:bg-teal-900/20 dark:text-teal-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4-4m4 4v12" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v2m0 0l-2-2m2 2l2-2" /></svg>
                    System Software
                    @if(tenant('system_version') === 'v1.0')
                    <span class="ml-auto flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                    @endif
                </button>
                <div class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-800">
                    <button @click="activeSection = 'danger'" 
                            :class="activeSection === 'danger' ? 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400' : 'text-gray-600 dark:text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/10'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Danger Zone
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
                            <p class="text-sm text-gray-500 mt-1">Customize the visual experience of your dashboard.</p>
                        </div>
                        <button @click="showSuccess()" class="px-5 py-2 bg-teal-500 text-white rounded-xl text-sm font-bold hover:bg-teal-600 transition-all shadow-lg shadow-teal-500/20 active:scale-95">
                            Save Appearance
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="theme" value="light" x-model="theme" class="peer sr-only">
                            <div class="p-4 rounded-2xl border-2 border-gray-100 dark:border-gray-700 peer-checked:border-teal-500 peer-checked:bg-teal-50/30 transition-all">
                                <div class="aspect-video rounded-lg bg-gray-50 border border-gray-100 mb-3 overflow-hidden flex">
                                    <div class="w-1/4 bg-white border-r border-gray-100"></div>
                                    <div class="flex-1 p-2 space-y-2">
                                        <div class="h-2 w-3/4 bg-gray-200 rounded"></div>
                                        <div class="h-2 w-1/2 bg-gray-200 rounded"></div>
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">Light Mode</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="theme" value="dark" x-model="theme" class="peer sr-only">
                            <div class="p-4 rounded-2xl border-2 border-gray-100 dark:border-gray-700 peer-checked:border-teal-500 peer-checked:bg-teal-50/30 transition-all">
                                <div class="aspect-video rounded-lg bg-gray-900 border border-gray-800 mb-3 overflow-hidden flex">
                                    <div class="w-1/4 bg-gray-800 border-r border-gray-700"></div>
                                    <div class="flex-1 p-2 space-y-2">
                                        <div class="h-2 w-3/4 bg-gray-700 rounded"></div>
                                        <div class="h-2 w-1/2 bg-gray-700 rounded"></div>
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">Dark Mode</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="theme" value="system" x-model="theme" class="peer sr-only">
                            <div class="p-4 rounded-2xl border-2 border-gray-100 dark:border-gray-700 peer-checked:border-teal-500 peer-checked:bg-teal-50/30 transition-all">
                                <div class="aspect-video rounded-lg bg-gray-100 border border-gray-200 mb-3 overflow-hidden flex relative">
                                    <div class="absolute inset-0 bg-gray-900 translate-x-1/2"></div>
                                    <div class="w-1/4 bg-white border-r border-gray-100 z-10"></div>
                                    <div class="flex-1 p-2 space-y-2 z-10">
                                        <div class="h-2 w-3/4 bg-gray-200 rounded"></div>
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">System Default</span>
                            </div>
                        </label>
                    </div>

                    <div class="pt-8 border-t border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider mb-6">Custom Brand Colors</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <label class="block">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Primary Brand Color</span>
                                    <div class="mt-2 flex items-center gap-3">
                                        <input type="color" value="#0d9488" class="w-12 h-12 rounded-xl border-none p-0 cursor-pointer overflow-hidden shadow-sm">
                                        <input type="text" value="#0d9488" class="flex-1 px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm font-mono uppercase">
                                    </div>
                                </label>
                                <label class="block">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Sidebar Background</span>
                                    <div class="mt-2 flex items-center gap-3">
                                        <input type="color" value="#111827" class="w-12 h-12 rounded-xl border-none p-0 cursor-pointer overflow-hidden shadow-sm">
                                        <input type="text" value="#111827" class="flex-1 px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm font-mono uppercase">
                                    </div>
                                </label>
                            </div>
                            <div class="space-y-4">
                                <label class="block">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Topbar Color</span>
                                    <div class="mt-2 flex items-center gap-3">
                                        <input type="color" value="#ffffff" class="w-12 h-12 rounded-xl border-none p-0 cursor-pointer overflow-hidden shadow-sm">
                                        <input type="text" value="#ffffff" class="flex-1 px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm font-mono uppercase">
                                    </div>
                                </label>
                                <label class="block">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Active Item Accent</span>
                                    <div class="mt-2 flex items-center gap-3">
                                        <input type="color" value="#0d9488" class="w-12 h-12 rounded-xl border-none p-0 cursor-pointer overflow-hidden shadow-sm">
                                        <input type="text" value="#0d9488" class="flex-1 px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm font-mono uppercase">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Branding Section --}}
                <div x-show="activeSection === 'branding'" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 space-y-8" x-cloak>
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-black text-gray-900 dark:text-white">Branding</h2>
                            <p class="text-sm text-gray-500 mt-1">Manage your school's identity and logos.</p>
                        </div>
                        <button x-show="!(plan === 'Basic' && hasUpdatedSettings)" @click="handleSave('branding')" class="px-5 py-2 bg-teal-500 text-white rounded-xl text-sm font-bold hover:bg-teal-600 transition-all shadow-lg shadow-teal-500/20 active:scale-95">
                            Save Branding
                        </button>
                        <span x-cloak x-show="plan === 'Basic' && hasUpdatedSettings" class="text-xs font-bold text-red-500 bg-red-50 dark:bg-red-900/20 px-4 py-2 rounded-xl border border-red-100 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Locked (Upgrade to Pro)
                        </span>
                    </div>

                    <form id="branding-form" method="POST" action="{{ route('tenant.admin.settings.update') }}" class="flex flex-col md:flex-row gap-8 items-start">
                        @csrf
                        <div class="w-full md:w-64 aspect-square bg-gray-50 dark:bg-gray-900 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center p-8 text-center group relative overflow-hidden">
                            <img src="{{ asset('images/Logo.jpg') }}" class="w-full h-full object-contain opacity-50 group-hover:opacity-20 transition-opacity">
                            <div class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-all transform translate-y-4 group-hover:translate-y-0">
                                <svg class="w-10 h-10 text-teal-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4-4m4 4v12" /></svg>
                                <span class="text-xs font-bold text-gray-900 dark:text-white">Upload New Logo</span>
                            </div>
                            <input type="file" class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                        <div class="flex-1 space-y-6">
                            <div class="p-6 bg-teal-50/50 dark:bg-teal-900/10 rounded-2xl border border-teal-100 dark:border-teal-900/30">
                                <h4 class="text-sm font-bold text-teal-700 dark:text-teal-400 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                    Logo Requirements
                                </h4>
                                <ul class="mt-2 space-y-1 text-xs text-teal-600 dark:text-teal-500 font-medium">
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
                                    <input type="text" value="Buksu" class="mt-2 w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm font-bold">
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
                        <button x-show="!(plan === 'Basic' && hasUpdatedSettings)" @click="handleSave('general')" class="px-5 py-2 bg-teal-500 text-white rounded-xl text-sm font-bold hover:bg-teal-600 transition-all shadow-lg shadow-teal-500/20 active:scale-95">
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
                            <label class="block">
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Timezone</span>
                                <select class="mt-2 w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm font-bold appearance-none">
                                    <option>Asia/Manila (GMT+08:00)</option>
                                    <option>UTC</option>
                                </select>
                            </label>
                        </div>
                    </form>
                </div>

                {{-- System Updates Section --}}
                <div x-show="activeSection === 'system_updates'" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 space-y-8" x-cloak>
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">System Software</h2>
                        <p class="text-sm text-gray-500 mt-1">Manage your platform version and opt-in updates.</p>
                    </div>

                    @if(tenant('system_version') === 'v1.0')
                    <div class="p-6 bg-amber-50 dark:bg-amber-900/10 rounded-2xl border border-amber-100 dark:border-amber-900/30">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 text-amber-500 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-black text-amber-900 dark:text-amber-400">Update Available: Version 2.0</h3>
                                <p class="text-sm text-amber-700 dark:text-amber-500 mt-2 font-medium">A massive redesign of the main Sidebar navigation is available! We've modernized the aesthetics and structure. Your tenant users will see this change immediately.</p>
                                
                                <form method="POST" action="{{ route('tenant.admin.settings.system_version') }}" class="mt-6">
                                    @csrf
                                    <input type="hidden" name="action" value="upgrade">
                                    <button class="px-6 py-3 bg-amber-500 text-white rounded-xl text-sm font-black hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20 active:scale-95">Apply Version 2.0 Patch</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="p-6 bg-teal-50 dark:bg-teal-900/10 rounded-2xl border border-teal-100 dark:border-teal-900/30">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-teal-100 dark:bg-teal-900/30 text-teal-600 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-black text-teal-900 dark:text-teal-400">System Up to Date</h3>
                                <p class="text-sm text-teal-700 dark:text-teal-500 mt-2 font-medium">You are running the latest stable release (Version 2.0). If you prefer the classic layout, you can safely rollback at any time.</p>
                                
                                <form method="POST" action="{{ route('tenant.admin.settings.system_version') }}" class="mt-6">
                                    @csrf
                                    <input type="hidden" name="action" value="rollback">
                                    <button onclick="return confirm('Are you sure you want to rollback to the old layout?')" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-50 transition-all">Rollback to v1.0</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
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
                                    <span class="text-xs font-bold text-gray-500">Laravel Version</span>
                                    <span class="text-xs font-black text-teal-600">v11.x</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-500">PHP Version</span>
                                    <span class="text-xs font-black text-teal-600">v8.2.x</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-500">Database</span>
                                    <span class="text-xs font-black text-teal-600">MySQL 8.0</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-4">
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider">Storage Usage</h3>
                            <div class="space-y-4">
                                <div class="space-y-1.5">
                                    <div class="flex justify-between text-[10px] font-black uppercase tracking-tighter">
                                        <span class="text-gray-500">Disk Space</span>
                                        <span class="text-teal-600">1.2 GB / 5 GB</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-teal-500" style="width: 24%"></div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-500">Media Uploads</span>
                                    <span class="text-xs font-black text-gray-900 dark:text-white">842 Files</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-blue-50 dark:bg-blue-900/10 rounded-2xl border border-blue-100 dark:border-blue-900/30 flex gap-4">
                        <div class="text-blue-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-blue-700 dark:text-blue-400 uppercase tracking-widest mb-1">Update Status</h4>
                            <p class="text-[11px] text-blue-600 dark:text-blue-500 leading-relaxed">Your system is currently up to date. Last checked today at 10:42 AM.</p>
                        </div>
                    </div>
                </div>

                {{-- Danger Zone Section --}}
                <div x-show="activeSection === 'danger'" class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-red-100 dark:border-red-900/30 shadow-sm p-8 space-y-8" x-cloak>
                    <div>
                        <h2 class="text-xl font-black text-red-600">Danger Zone</h2>
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
                    <div class="w-20 h-20 bg-teal-50 dark:bg-teal-900/30 text-teal-500 rounded-2xl flex items-center justify-center mx-auto">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Settings Updated!</h3>
                        <p class="text-sm text-gray-500 mt-2">All changes have been successfully applied to your instance.</p>
                    </div>
                    <button @click="successModal = false" class="w-full py-3 bg-teal-500 text-white rounded-2xl font-bold hover:bg-teal-600 transition-all shadow-lg shadow-teal-500/20">Great!</button>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>