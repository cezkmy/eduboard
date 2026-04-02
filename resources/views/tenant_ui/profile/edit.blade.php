<x-app-layout>
    <x-slot name="title">Profile Settings</x-slot>

    <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
        {{-- Back Button & Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                @if(auth()->user()->role === 'student')
                    <a href="{{ route('tenant.student.page') }}" class="w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 border-2 border-gray-100 dark:border-gray-700 flex items-center justify-center text-gray-400 hover:text-[var(--accent)] hover:border-[var(--accent)] transition-all group" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.10);">
                        <svg class="w-6 h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                @endif
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">Account Settings</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage your profile, security, and preferences</p>
                </div>
            </div>
        </div>

        <div class="profile-content w-full overflow-hidden">
            @if(auth()->user()->role === 'student')
                @include('tenant_ui.profile.partials.student-profile', ['user' => $user])
            @elseif(auth()->user()->role === 'teacher')
                @include('tenant_ui.profile.partials.teacher-profile', ['user' => $user])
            @else
                @include('tenant_ui.profile.partials.admin-profile', ['user' => $user])
            @endif
        </div>
    </div>
    @push('scripts')
        <script>
            const photoInput = document.getElementById('profile_photo_input');
            const previewContainer = document.getElementById('profile-photo-preview');
            const photoActions = document.getElementById('photo-actions');
            const saveBtn = document.getElementById('save-photo-btn');
            const cancelBtn = document.getElementById('cancel-photo-btn');
            const statusIndicator = document.getElementById('upload-status');
            
            // Store original content for cancel
            const originalPreviewContent = previewContainer.innerHTML;

            // Extra insurance: ensure label click triggers input
            document.querySelector('label[for="profile_photo_input"]').addEventListener('click', function() {
                photoInput.click();
            });

            photoInput.addEventListener('change', function(e) {
                console.log('File input changed');
                const file = e.target.files[0];
                if (!file) {
                    console.log('No file selected');
                    return;
                }

                console.log('File selected:', file.name, file.size, file.type);

                // Show Preview
                const reader = new FileReader();
                reader.onload = function(event) {
                    console.log('FileReader loaded');
                    previewContainer.innerHTML = `<img src="${event.target.result}" class="w-full h-full object-cover">`;
                    photoActions.classList.remove('hidden');
                    statusIndicator.classList.add('hidden');
                };
                reader.onerror = function(e) {
                    console.error('FileReader error:', e);
                };
                reader.readAsDataURL(file);
            });

            cancelBtn.addEventListener('click', function() {
                photoInput.value = '';
                previewContainer.innerHTML = originalPreviewContent;
                photoActions.classList.add('hidden');
                statusIndicator.classList.add('hidden');
            });

            saveBtn.addEventListener('click', async function() {
                const file = photoInput.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('profile_photo', file);
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PATCH');

                // UI Updates
                saveBtn.disabled = true;
                saveBtn.textContent = 'SAVING...';
                statusIndicator.textContent = 'UPLOADING...';
                statusIndicator.classList.remove('hidden', 'text-green-500', 'text-red-500');
                statusIndicator.style.color = 'var(--accent)';

                try {
                    const response = await fetch('{{ route("tenant.profile.update") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || `Server error: ${response.status}`);
                    }

                    const data = await response.json();
                    
                    if (data.success) {
                        statusIndicator.textContent = 'SAVED SUCCESSFULLY';
                        statusIndicator.style.color = '#22c55e';
                        photoActions.classList.add('hidden');
                        
                        // Update Topbar Photo
                        const topbarAvatar = document.querySelector('.topbar-btn.user .user-avatar');
                        if (topbarAvatar) {
                            topbarAvatar.innerHTML = `<img src="${data.profile_photo_url}" class="w-full h-full object-cover">`;
                        }

                        // Update local "original" content
                        previewContainer.innerHTML = `<img src="${data.profile_photo_url}" class="w-full h-full object-cover">`;
                        
                        setTimeout(() => statusIndicator.classList.add('hidden'), 3000);
                    } else {
                        throw new Error(data.message || 'Upload failed');
                    }
                } catch (error) {
                    statusIndicator.textContent = 'UPLOAD FAILED';
                    statusIndicator.style.color = '#ef4444';
                    console.error('Error:', error);
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'SAVE PHOTO';
                }
            });
        </script>
    @endpush
</x-app-layout>
