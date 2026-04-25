<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    {{-- Profile Photo --}}
    <div class="mb-6">
        <x-input-label for="profile_photo" :value="__('Profile Photo')" />
        <div class="mt-2 flex items-center gap-6">
            <div class="relative group">
                <div class="w-24 h-24 rounded-2xl overflow-hidden bg-gray-100 flex items-center justify-center border-2 border-gray-200 shadow-sm" id="profile-preview-container">
                    @if($user->profile_photo)
                        <img id="profile-preview" src="{{ asset('storage/' . $user->profile_photo) }}" alt="Profile" class="w-full h-full object-cover">
                    @else
                        <div id="profile-initials" class="text-3xl font-bold text-indigo-400">{{ substr($user->name, 0, 1) }}</div>
                    @endif
                </div>
                <label for="profile_photo" class="absolute -bottom-2 -right-2 w-8 h-8 bg-indigo-600 text-white rounded-lg flex items-center justify-center cursor-pointer hover:bg-indigo-700 transition-colors shadow-lg border-2 border-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </label>
            </div>
            
            <div class="flex flex-col gap-2">
                <input type="file" id="profile_photo" name="profile_photo" class="hidden" accept="image/*" onchange="handlePhotoChange(this)">
                <div id="photo-actions" class="hidden flex items-center gap-2">
                    <button type="button" id="save-photo-btn" onclick="saveProfilePhoto()" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition-all uppercase tracking-widest shadow-md">Save Photo</button>
                    <button type="button" onclick="cancelPhotoChange()" class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-lg hover:bg-gray-200 transition-all uppercase tracking-widest border border-gray-200">Cancel</button>
                </div>
                <p id="photo-status" class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest hidden">Updating...</p>
                <p class="text-xs text-gray-500">Allowed formats: PNG, JPG, JPEG (Max 2MB)</p>
            </div>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
    </div>

    <script>
        let originalPreviewHtml = '';
        let originalTopbarHtml = '';
        let originalSidebarHtml = '';

        function handlePhotoChange(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate
                if (!file.type.startsWith('image/')) {
                    showAlert('Error', 'Please select a valid image file', 'error');
                    input.value = '';
                    return;
                }

                // Store originals if first time
                const container = document.getElementById('profile-preview-container');
                const topbarAvatar = document.getElementById('topbar-avatar');
                const sidebarAvatar = document.getElementById('sidebar-avatar');

                if (!originalPreviewHtml) originalPreviewHtml = container.innerHTML;
                if (!originalTopbarHtml && topbarAvatar) originalTopbarHtml = topbarAvatar.innerHTML;
                if (!originalSidebarHtml && sidebarAvatar) originalSidebarHtml = sidebarAvatar.innerHTML;

                // Show Preview in all locations immediately
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgHtml = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                    
                    // 1. Update Preview in form
                    container.innerHTML = `<img id="profile-preview" src="${e.target.result}" class="w-full h-full object-cover">`;
                    
                    // 2. Update Topbar Avatar
                    if (topbarAvatar) topbarAvatar.innerHTML = imgHtml;
                    
                    // 3. Update Sidebar Avatar
                    if (sidebarAvatar) sidebarAvatar.innerHTML = imgHtml;

                    document.getElementById('photo-actions').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        function cancelPhotoChange() {
            const input = document.getElementById('profile_photo');
            const container = document.getElementById('profile-preview-container');
            const topbarAvatar = document.getElementById('topbar-avatar');
            const sidebarAvatar = document.getElementById('sidebar-avatar');
            const actions = document.getElementById('photo-actions');
            
            input.value = '';
            container.innerHTML = originalPreviewHtml;
            if (topbarAvatar) topbarAvatar.innerHTML = originalTopbarHtml;
            if (sidebarAvatar) sidebarAvatar.innerHTML = originalSidebarHtml;
            
            actions.classList.add('hidden');
        }

        async function saveProfilePhoto() {
            const input = document.getElementById('profile_photo');
            const file = input.files[0];
            const saveBtn = document.getElementById('save-photo-btn');
            const status = document.getElementById('photo-status');
            
            if (!file) return;

            saveBtn.disabled = true;
            saveBtn.textContent = 'SAVING...';
            status.classList.remove('hidden');
            status.textContent = 'UPLOADING...';

            const formData = new FormData();
            formData.append('profile_photo', file);
            formData.append('name', document.getElementById('name').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('school_name', '{{ $user->school_name }}');
            formData.append('_method', 'PATCH');

            try {
                const response = await fetch('{{ route("profile.update") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json();
                
                if (response.ok && data.success) {
                    status.textContent = 'PHOTO SAVED SUCCESSFULLY';
                    status.style.color = '#10b981';
                    
                    if (data.profile_photo_url) {
                        const cacheUrl = data.profile_photo_url + '?t=' + Date.now();
                        const imgHtml = `<img src="${cacheUrl}" class="w-full h-full object-cover">`;
                        
                        // Update layout avatars
                        const topbarAvatar = document.getElementById('topbar-avatar');
                        if (topbarAvatar) topbarAvatar.innerHTML = imgHtml;
                        
                        const sidebarAvatar = document.getElementById('sidebar-avatar');
                        if (sidebarAvatar) sidebarAvatar.innerHTML = imgHtml;

                        originalPreviewHtml = `<img id="profile-preview" src="${cacheUrl}" class="w-full h-full object-cover">`;
                        originalTopbarHtml = imgHtml;
                        originalSidebarHtml = imgHtml;
                    }

                    setTimeout(() => {
                        document.getElementById('photo-actions').classList.add('hidden');
                        status.classList.add('hidden');
                        status.style.color = '';
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Upload failed');
                }
            } catch (error) {
                showAlert('Error', error.message, 'error');
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Photo';
            }
        }
    </script>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>




