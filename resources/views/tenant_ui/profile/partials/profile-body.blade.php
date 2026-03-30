<div class="profile-layout grid grid-cols-1 lg:grid-cols-2 gap-8">
    {{-- Left: Profile Information --}}
    <div class="profile-card p-10 bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-5 mb-10">
            <div class="w-14 h-14 rounded-2xl bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            </div>
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-gray-100">Personal Information</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Update your account identity</p>
            </div>
        </div>

        <form method="post" action="{{ route('tenant.profile.update') }}" class="space-y-8">
            @csrf
            @method('patch')

            <div class="space-y-6">
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Full Name</label>
                    <input name="name" type="text" value="{{ old('name', $user->name) }}" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all" required>
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Email Address</label>
                    <input name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all" required>
                </div>

                @if(auth()->user()->role === 'student')
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Course</label>
                            <input name="course" type="text" value="{{ old('course', $user->course) }}" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Year Level</label>
                            <select name="year_level" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all appearance-none">
                                <option value="">Select year</option>
                                @foreach(['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $year)
                                    <option value="{{ $year }}" {{ old('year_level', $user->year_level) === $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="w-full py-4 bg-teal-500 text-white rounded-[1.25rem] text-sm font-black hover:bg-teal-600 transition-all shadow-xl shadow-teal-500/20 active:scale-95">SAVE CHANGES</button>
            </div>
        </form>
    </div>

    {{-- Right: Security & Password --}}
    <div class="profile-card p-10 bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-5 mb-10">
            <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            </div>
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-gray-100">Security Settings</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Protect your account</p>
            </div>
        </div>

        <form method="post" action="{{ route('tenant.password.update') }}" class="space-y-8">
            @csrf
            @method('put')

            <div class="space-y-6">
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Current Password</label>
                    <input name="current_password" type="password" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-rose-500/20 transition-all">
                </div>

                <div class="space-y-2">
                    <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">New Password</label>
                    <input name="password" type="password" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-rose-500/20 transition-all">
                </div>

                <div class="space-y-2">
                    <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Confirm Password</label>
                    <input name="password_confirmation" type="password" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-rose-500/20 transition-all">
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="w-full py-4 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-[1.25rem] text-sm font-black hover:opacity-90 transition-all shadow-xl active:scale-95">UPDATE PASSWORD</button>
            </div>
        </form>
    </div>
</div>









