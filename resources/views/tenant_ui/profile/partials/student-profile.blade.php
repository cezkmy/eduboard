<div class="profile-layout flex flex-col lg:flex-row gap-6 w-full">
    {{-- Left Column: User Overview --}}
    <div class="flex-1 min-w-0 space-y-6">
        <div class="profile-card p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center text-center">
            <div class="relative group">
                <div class="w-24 h-24 rounded-2xl text-white flex items-center justify-center font-bold text-3xl shadow-lg mb-4 overflow-hidden" id="profile-photo-preview" style="background: var(--accent); box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>
                {{-- Camera Icon --}}
                <label for="profile_photo_input" class="absolute bottom-2 -right-2 w-10 h-10 bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 flex items-center justify-center cursor-pointer shadow-lg transition-all group-hover:scale-110 z-10" style="color: var(--accent);" title="Change Profile Photo">
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

            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ auth()->user()->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ auth()->user()->email }}</p>
            
            <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider" style="background: rgba(var(--accent-rgb), 0.10); color: var(--accent);">Student</span>

            <div class="w-full mt-6 pt-6 border-t border-gray-50 dark:border-gray-700 text-left space-y-4">
                <div>
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Course</div>
                    <div class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ auth()->user()->course ?? 'Not Set' }}</div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Year</div>
                        <div class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ auth()->user()->year_level ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Section</div>
                        <div class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ auth()->user()->section ?? 'N/A' }}</div>
                    </div>
                </div>
                <div>
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Strand</div>
                    <div class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ auth()->user()->strand ?? 'Not Set' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Center Column: Profile Information --}}
    <div class="flex-1 min-w-0 space-y-6">
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
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Course</label>
                    <select id="student-course" name="course" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                        <option value="">None</option>
                        @foreach(['BSIT', 'BSCS', 'BSBA', 'BSN', 'BSED'] as $courseOption)
                            <option value="{{ $courseOption }}" {{ old('course', $user->course) === $courseOption ? 'selected' : '' }}>{{ $courseOption }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Year Level</label>
                        <select id="student-year-level" name="year_level" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                            <option value="">Select</option>
                            @php
                                $yearOptions = [
                                    'College Years' => ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'],
                                    'High School Grades' => ['Grade 11', 'Grade 12']
                                ];
                            @endphp
                            @foreach($yearOptions as $group => $options)
                                <optgroup label="{{ $group }}">
                                    @foreach($options as $year)
                                        <option value="{{ $year }}" {{ old('year_level', $user->year_level) === $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Section</label>
                        <select id="student-section" name="section" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                            <option value="">None</option>
                            @foreach(['Section A', 'Section B', 'Section C'] as $sectionOption)
                                <option value="{{ $sectionOption }}" {{ old('section', $user->section) === $sectionOption ? 'selected' : '' }}>{{ $sectionOption }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Strand</label>
                    <select id="student-strand" name="strand" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                        <option value="">None</option>
                        @foreach(['STEM', 'ABM', 'HUMSS', 'GAS', 'TVL'] as $strandOption)
                            <option value="{{ $strandOption }}" {{ old('strand', $user->strand) === $strandOption ? 'selected' : '' }}>{{ $strandOption }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Preferred Language</label>
                    <select name="language" class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                        <option value="en" {{ (old('language', $user->settings['language'] ?? 'en')) == 'en' ? 'selected' : '' }}>English</option>
                        <option value="fil" {{ (old('language', $user->settings['language'] ?? '')) == 'fil' ? 'selected' : '' }}>Filipino</option>
                    </select>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-blue-600/20">SAVE CHANGES</button>
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
                    <button type="submit" class="w-full py-3 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-xl text-xs font-bold hover:bg-gray-800 dark:hover:bg-white transition-all shadow-md">UPDATE PASSWORD</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const yearSelect = document.getElementById('student-year-level');
        const courseSelect = document.getElementById('student-course');
        const strandSelect = document.getElementById('student-strand');

        if (!yearSelect || !courseSelect || !strandSelect) {
            return;
        }

        const isGradeLevel = value => value.startsWith('Grade ');
        const isCollegeYear = value => value.endsWith('Year');

        const updateProfileFields = () => {
            const selectedYear = yearSelect.value;

            if (isGradeLevel(selectedYear)) {
                courseSelect.value = '';
                courseSelect.disabled = true;
                courseSelect.style.opacity = '0.5';
                courseSelect.style.cursor = 'not-allowed';

                strandSelect.disabled = false;
                strandSelect.style.opacity = '';
                strandSelect.style.cursor = '';
            } else if (isCollegeYear(selectedYear)) {
                strandSelect.value = '';
                strandSelect.disabled = true;
                strandSelect.style.opacity = '0.5';
                strandSelect.style.cursor = 'not-allowed';

                courseSelect.disabled = false;
                courseSelect.style.opacity = '';
                courseSelect.style.cursor = '';
            } else {
                courseSelect.disabled = false;
                strandSelect.disabled = false;
                courseSelect.style.opacity = '';
                courseSelect.style.cursor = '';
                strandSelect.style.opacity = '';
                strandSelect.style.cursor = '';
            }
        };

        yearSelect.addEventListener('change', updateProfileFields);
        updateProfileFields();
    });
</script>