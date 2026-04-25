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
                const topbarAvatar = document.querySelector('.topbar-btn.user .user-avatar');
                const sidebarAvatar = document.getElementById('sidebar-avatar');
                const sidebarV2Avatar = document.getElementById('sidebar-v2-avatar');
                
                let originalTopbarContent = topbarAvatar ? topbarAvatar.innerHTML : '';
                let originalSidebarContent = sidebarAvatar ? sidebarAvatar.innerHTML : '';
                let originalSidebarV2Content = sidebarV2Avatar ? sidebarV2Avatar.innerHTML : '';

                // File input change - preview before upload
                photoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    // Validate file is an image
                    if (!file.type.startsWith('image/')) {
                        showAlert('Error', 'Please select a valid image file', 'error');
                        photoInput.value = '';
                        return;
                    }

                    // Show Preview in all locations immediately
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const avatarHtml = `<img src="${event.target.result}" class="w-full h-full object-cover">`;
                        
                        // 1. Update Preview in form
                        previewContainer.innerHTML = avatarHtml;
                        
                        // 2. Update Topbar
                        if (topbarAvatar) topbarAvatar.innerHTML = avatarHtml;
                        
                        // 3. Update Sidebars
                        if (sidebarAvatar) sidebarAvatar.innerHTML = avatarHtml;
                        if (sidebarV2Avatar) sidebarV2Avatar.innerHTML = avatarHtml;

                        photoActions.classList.remove('hidden');
                        statusIndicator.classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                });

                // Cancel button - reset everything
                if (cancelBtn) {
                    cancelBtn.addEventListener('click', function() {
                        // Reset file input
                        photoInput.value = '';
                        // Restore originals
                        previewContainer.innerHTML = originalPreviewContent;
                        if (topbarAvatar) topbarAvatar.innerHTML = originalTopbarContent;
                        if (sidebarAvatar) sidebarAvatar.innerHTML = originalSidebarContent;
                        if (sidebarV2Avatar) sidebarV2Avatar.innerHTML = originalSidebarV2Content;

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
                            showAlert('Warning', 'No file selected', 'warning');
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
                        formData.append('_method', 'PATCH'); // Use POST with _method for reliable file upload

                        // UI Updates
                        saveBtn.disabled = true;
                        saveBtn.textContent = 'SAVING...';
                        statusIndicator.textContent = 'UPLOADING...';
                        statusIndicator.classList.remove('hidden');
                        statusIndicator.style.color = 'var(--accent)';

                        try {
                            const response = await fetch('{{ route("tenant.profile.update") }}', {
                                method: 'POST', // Use POST for FormData with files
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
                                
                                // Update all avatars immediately
                                if (data.profile_photo_url) {
                                    const cacheUrl = data.profile_photo_url + (data.profile_photo_url.includes('?') ? '&' : '?') + 't=' + Date.now();
                                    const avatarHtml = `<img src="${cacheUrl}" class="w-full h-full object-cover">`;
                                    
                                    // 1. Update Preview
                                    previewContainer.innerHTML = avatarHtml;
                                    
                                    // 2. Update Topbar
                                    const topbarAvatar = document.querySelector('.topbar-btn.user .user-avatar');
                                    if (topbarAvatar) {
                                        topbarAvatar.innerHTML = avatarHtml;
                                    }
                                    
                                    // 3. Update Sidebar Bottom (V1 & V2)
                                    const sidebarAvatar = document.getElementById('sidebar-avatar');
                                    if (sidebarAvatar) {
                                        sidebarAvatar.innerHTML = avatarHtml;
                                    }
                                    const sidebarV2Avatar = document.getElementById('sidebar-v2-avatar');
                                    if (sidebarV2Avatar) {
                                        sidebarV2Avatar.innerHTML = avatarHtml;
                                    }

                                    // Update original contents for next preview/cancel cycle
                                    originalTopbarContent = avatarHtml;
                                    originalSidebarContent = avatarHtml;
                                    originalSidebarV2Content = avatarHtml;
                                }

                                // Delay before resetting to let user see success message
                                setTimeout(() => {
                                    // Reset input and hide actions
                                    photoInput.value = '';
                                    photoActions.classList.add('hidden');
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

                // AJAX Form Submission for Profile/Password
                const profileForms = document.querySelectorAll('form');
                profileForms.forEach(form => {
                    // Only apply to the profile-related forms
                    const action = form.getAttribute('action');
                    if (!action || (!action.includes('profile') && !action.includes('password'))) return;

                    form.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        // 1. Client-side Name Validation (No numbers)
                        const nameInput = form.querySelector('input[name="name"]');
                        if (nameInput && /\d/.test(nameInput.value)) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: 'Numbers are not allowed in your name.',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                            nameInput.focus();
                            nameInput.style.borderColor = '#ef4444';
                            return;
                        }

                        // 2. AJAX Submission
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
                        
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin me-2"></i> Saving...';
                        }

                        try {
                            const formData = new FormData(form);
                            const response = await fetch(action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                }
                            });

                            const data = await response.json();

                            if (response.ok && data.success) {
                                // Success Toast
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'success',
                                        title: data.message || 'Updated successfully!',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                }

                                // Clear password fields if it was a password update
                                if (action.includes('password')) {
                                    form.reset();
                                }
                            } else {
                                // Error handling
                                let errorMsg = data.message || 'An error occurred while saving.';
                                if (data.errors) {
                                    // Take the first validation error if available
                                    errorMsg = Object.values(data.errors)[0][0];
                                }

                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'error',
                                        title: errorMsg,
                                        showConfirmButton: false,
                                        timer: 4000
                                    });
                                }
                            }
                        } catch (error) {
                            console.error('Submission error:', error);
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: 'Connection error. Please try again.',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        } finally {
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalBtnText;
                            }
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
