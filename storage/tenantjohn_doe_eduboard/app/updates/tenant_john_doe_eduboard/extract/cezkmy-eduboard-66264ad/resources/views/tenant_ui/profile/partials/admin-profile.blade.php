<div class="flex flex-col lg:grid lg:grid-cols-[320px_1fr] lg:items-start gap-6 w-full">
    {{-- Left Column: User Overview --}}
    <div class="min-w-0">
        <div class="profile-card h-auto p-5 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center text-center self-start">
            <div class="relative group">
                <div class="w-20 h-20 rounded-2xl text-white flex items-center justify-center font-bold text-2xl shadow-lg mb-3 overflow-hidden" id="profile-photo-preview" style="background: var(--accent); box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ (function_exists('tenant_asset') && tenant()) ? tenant_asset(auth()->user()->profile_photo) : asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML = '{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}'">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>
                {{-- Camera Icon --}}
                <label for="profile_photo_input" class="absolute bottom-1.5 -right-2 w-9 h-9 bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 flex items-center justify-center cursor-pointer shadow-lg transition-all group-hover:scale-110 z-10" style="color: var(--accent);" title="Change Profile Photo">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </label>
                <input type="file" id="profile_photo_input" class="hidden" accept="image/*">
            </div>
            
            {{-- Upload Status --}}
            <div id="upload-status" class="hidden text-[10px] font-bold mt-2 animate-pulse"></div>

            {{-- Action Buttons --}}
            <div id="photo-actions" class="hidden flex gap-2 mt-4">
                <button type="button" id="save-photo-btn" class="px-3 py-1.5 text-white text-[10px] font-bold rounded-lg transition-all shadow-sm" style="background: var(--accent);">SAVE PHOTO</button>
                <button type="button" id="cancel-photo-btn" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-500 text-[10px] font-bold rounded-lg hover:bg-gray-200 transition-all">CANCEL</button>
            </div>

            <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100 mt-2">{{ auth()->user()->name }}</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ auth()->user()->email }}</p>
            
            <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider" style="background: rgba(var(--accent-rgb), 0.10); color: var(--accent);">Administrator</span>

            <div class="mt-4 space-y-3 text-left w-full">
                <div class="flex items-center gap-3 justify-start w-full">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-rgb), 0.10); color: var(--accent);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Last Login</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ auth()->user()->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 justify-start w-full">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-rgb), 0.10); color: var(--accent);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">DB Storage Used</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ isset($dbSize) ? number_format($dbSize / 1024 / 1024, 2) : 'Calculating...' }} MB</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Personal Info + Security (stacked) --}}
    <div class="min-w-0 space-y-6">
        {{-- Container 2: Personal Info --}}
        <div class="profile-card p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-rgb), 0.10); color: var(--accent);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Personal Info</h2>
                    <p class="text-[10px] text-gray-500 dark:text-gray-400">Update your basic information</p>
                </div>
            </div>

            <form method="post" action="{{ route('tenant.profile.update') }}" class="space-y-4">
                @csrf
                @method('patch')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Employee ID</label>
                        <input name="employee_id" type="text" value="{{ old('employee_id', $user->employee_id) }}" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                        <x-input-error :messages="$errors->get('employee_id')" class="mt-1" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Department</label>
                        <input name="department" type="text" value="{{ old('department', $user->department) }}" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                        <x-input-error :messages="$errors->get('department')" class="mt-1" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Full Name</label>
                    <input name="name" type="text" value="{{ old('name', $user->name) }}" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);" required>
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Email Address</label>
                    <input name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);" required>
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Preferred Language</label>
                    <select name="language" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                        <option value="en" {{ (old('language', $user->settings['language'] ?? 'en')) == 'en' ? 'selected' : '' }}>English</option>
                        <option value="fil" {{ (old('language', $user->settings['language'] ?? '')) == 'fil' ? 'selected' : '' }}>Filipino</option>
                    </select>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-blue-600/20">SAVE CHANGES</button>
                </div>
            </form>
        </div>

        {{-- Container 3: Security (below personal info) --}}
        <div class="profile-card p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Security</h2>
                    <p class="text-[10px] text-gray-500 dark:text-gray-400">Update your account password</p>
                </div>
            </div>

            <form method="post" action="{{ route('tenant.password.update') }}" class="space-y-4">
                @csrf
                @method('put')

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Current Password</label>
                    <input name="current_password" type="password" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">New Password</label>
                    <input name="password" type="password" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Confirm Password</label>
                    <input name="password_confirmation" type="password" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-gray-800 dark:hover:bg-white transition-all shadow-md">UPDATE PASSWORD</button>
                </div>
            </form>
        </div>
    </div>
</div>
