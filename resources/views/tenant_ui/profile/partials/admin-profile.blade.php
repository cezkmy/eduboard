<div class="profile-layout flex flex-col lg:flex-row gap-6 w-full">
    {{-- Left Column: User Overview --}}
    <div class="flex-1 min-w-0 space-y-6">
        <div class="profile-card p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center text-center border-t-4 border-t-blue-600">
            <div class="relative group">
                <div class="w-24 h-24 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold text-3xl shadow-lg shadow-blue-600/20 mb-4 overflow-hidden" id="profile-photo-preview">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>
                {{-- Camera Icon --}}
                <label for="profile_photo_input" class="absolute bottom-2 -right-2 w-10 h-10 bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 flex items-center justify-center text-blue-600 hover:text-blue-700 cursor-pointer shadow-lg transition-all group-hover:scale-110 z-10" title="Change Profile Photo">
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
                <button type="button" id="save-photo-btn" class="px-3 py-1.5 bg-blue-600 text-white text-[10px] font-bold rounded-lg hover:bg-blue-700 transition-all shadow-sm">SAVE PHOTO</button>
                <button type="button" id="cancel-photo-btn" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-500 text-[10px] font-bold rounded-lg hover:bg-gray-200 transition-all">CANCEL</button>
            </div>

            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ auth()->user()->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ auth()->user()->email }}</p>
            
            <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-50 text-blue-600">Administrator</span>

            <div class="w-full mt-6 pt-6 border-t border-gray-50 dark:border-gray-700 text-left space-y-4">
                <div>
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Role</div>
                    <div class="text-sm font-bold text-gray-700 dark:text-gray-300">System Administrator</div>
                </div>
                <div>
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Status</div>
                    <div class="text-sm font-bold text-green-600">Active</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Center Column: Profile Information --}}
    <div class="flex-1 min-w-0 space-y-6">
        <div class="profile-card p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
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

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Full Name</label>
                    <input name="name" type="text" value="{{ old('name', $user->name) }}" class="w-full bg-gray-50 dark:bg-gray-700/50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500/20 transition-all" required>
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Email Address</label>
                    <input name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full bg-gray-50 dark:bg-gray-700/50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500/20 transition-all" required>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-all shadow-md shadow-blue-500/20">SAVE CHANGES</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Right Column: Security Settings --}}
    <div class="flex-1 min-w-0 space-y-6">
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
                    <input name="current_password" type="password" class="w-full bg-gray-50 dark:bg-gray-700/50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">New Password</label>
                    <input name="password" type="password" class="w-full bg-gray-50 dark:bg-gray-700/50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Confirm Password</label>
                    <input name="password_confirmation" type="password" class="w-full bg-gray-50 dark:bg-gray-700/50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-xl text-xs font-bold hover:opacity-90 transition-all shadow-md">UPDATE PASSWORD</button>
                </div>
            </form>
        </div>
    </div>
</div>
