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
            document.addEventListener('DOMContentLoaded', function() {
                const photoInput = document.getElementById('profile_photo_input');
                const previewContainer = document.getElementById('profile-photo-preview');
                const photoActions = document.getElementById('photo-actions');
                const saveBtn = document.getElementById('save-photo-btn');
                const cancelBtn = document.getElementById('cancel-photo-btn');
                const statusIndicator = document.getElementById('upload-status');
                
                if (!photoInput || !previewContainer) return;

                // Store original content for cancel
                const originalPreviewContent = previewContainer.innerHTML;

                // File input change - preview before upload
                photoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    // Validate file is an image
                    if (!file.type.startsWith('image/')) {
                        alert('Please select a valid image file');
                        photoInput.value = '';
                        return;
                    }

                    // Show Preview
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const img = new Image();
                        img.onload = function() {
                            previewContainer.innerHTML = `<img src="${event.target.result}" class="w-full h-full object-cover">`;
                            photoActions.classList.remove('hidden');
                            statusIndicator.classList.add('hidden');
                        };
                        img.onerror = function() {
                            alert('Invalid image file');
                            photoInput.value = '';
                        };
                        img.src = event.target.result;
                    };
                    reader.onerror = function(e) {
                        console.error('FileReader error:', e);
                        alert('Error reading file');
                    };
                    reader.readAsDataURL(file);
                });

                // Cancel button - reset everything
                if (cancelBtn) {
                    cancelBtn.addEventListener('click', function() {
                        // Reset file input
                        photoInput.value = '';
                        // Restore original preview
                        previewContainer.innerHTML = originalPreviewContent;
                        // Hide action buttons
                        photoActions.classList.add('hidden');
                        statusIndicator.classList.add('hidden');
                    });
                }

                // Save button - upload file
                if (saveBtn) {
                    saveBtn.addEventListener('click', async function() {
                        const file = photoInput.files[0];
                        if (!file) {
                            alert('No file selected');
                            return;
                        }

                        // Get current user data from the form
                        const nameInput = document.querySelector('input[name="name"]');
                        const emailInput = document.querySelector('input[name="email"]');
                        
                        if (!nameInput || !emailInput) {
                            alert('Form data not found');
                            return;
                        }

                        // Prepare form data with photo + required fields
                        const formData = new FormData();
                        formData.append('profile_photo', file);
                        formData.append('name', nameInput.value);
                        formData.append('email', emailInput.value);

                        // UI Updates
                        saveBtn.disabled = true;
                        saveBtn.textContent = 'SAVING...';
                        statusIndicator.textContent = 'UPLOADING...';
                        statusIndicator.classList.remove('hidden');
                        statusIndicator.style.color = 'var(--accent)';

                        try {
                            const response = await fetch('{{ route("tenant.profile.update") }}', {
                                method: 'PATCH',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: formData
                            });

                            const data = await response.json();
                            
                            if (response.ok && data.success) {
                                statusIndicator.textContent = 'PHOTO SAVED SUCCESSFULLY';
                                statusIndicator.style.color = '#22c55e';
                                
                                // Delay before resetting to let user see success message
                                setTimeout(() => {
                                    // Reset input and hide actions
                                    photoInput.value = '';
                                    photoActions.classList.add('hidden');
                                    
                                    // Update preview with new photo URL (add cache buster)
                                    if (data.profile_photo_url) {
                                        const cacheUrl = data.profile_photo_url + (data.profile_photo_url.includes('?') ? '&' : '?') + 't=' + Date.now();
                                        previewContainer.innerHTML = `<img src="${cacheUrl}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML = '<div style=\\"color: var(--accent); font-weight: bold;\\">' + String.fromCharCode(65) + '</div>'">`;
                                        
                                        // Update topbar if it exists
                                        const topbarAvatar = document.querySelector('.topbar-btn.user .user-avatar');
                                        if (topbarAvatar) {
                                            topbarAvatar.innerHTML = `<img src="${cacheUrl}" class="w-full h-full object-cover">`;
                                        }
                                    }
                                    
                                    statusIndicator.classList.add('hidden');
                                }, 1500);
                            } else {
                                throw new Error(data.message || 'Upload failed');
                            }
                        } catch (error) {
                            statusIndicator.textContent = 'UPLOAD FAILED: ' + (error.message || 'Unknown error');
                            statusIndicator.style.color = '#ef4444';
                            console.error('Error:', error);
                        } finally {
                            saveBtn.disabled = false;
                            saveBtn.textContent = 'SAVE PHOTO';
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
