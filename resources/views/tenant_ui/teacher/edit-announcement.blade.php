@php
    $categories = ['General', 'Academic', 'Events', 'Administrative', 'Emergency'];
    $programs = ['BSIT', 'BSEMC'];
    $yearLevels = [1, 2, 3, 4];
    $sections = ['A', 'B', 'C', 'D', 'E', 'F'];
    $mediaPaths = is_array($announcement->media_paths) ? $announcement->media_paths : json_decode($announcement->media_paths ?? '[]', true) ?? [];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Announcement
        </h2>
    </x-slot>

    <div class="space-y-6" x-data="{ 
        showingSuccess: false,
        successMessage: '',
        showSuccess(msg) {
            this.successMessage = msg;
            this.showingSuccess = true;
        }
    }">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <form id="editAnnouncementForm" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-1.5">Title</label>
                    <input
                        id="editAnnTitle"
                        name="title"
                        value="Midterm Examination Schedule"
                        required
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--teal)]"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-1.5">Category</label>
                    <select
                        id="editAnnCategory"
                        name="category"
                        required
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--teal)]"
                    >
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $cat === 'Academic' ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-1.5">Content</label>
                    <textarea
                        id="editAnnContent"
                        name="content"
                        rows="4"
                        required
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--teal)] resize-none"
                    >The midterm exams will start next week. Please check your portals for the specific schedule and room assignments.</textarea>
                </div>

                {{-- New Media Upload --}}
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-1.5">Upload New Media</label>
                    <div class="relative group">
                        <input
                            type="file"
                            name="media[]"
                            multiple
                            accept="image/*,video/*"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            onchange="previewNewMedia(this)"
                        />
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center group-hover:border-[var(--teal)] group-hover:bg-[var(--teal-bg)] dark:group-hover:bg-teal-900/10 transition-all duration-200">
                            <div class="w-12 h-12 bg-teal-50 dark:bg-teal-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6 text-[var(--teal)] dark:text-teal-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Click to add more images/videos</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">PNG, JPG, MP4 or MOV up to 10MB</p>
                        </div>
                    </div>
                    <div id="new_media_preview" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4"></div>
                </div>

                <div class="flex items-center gap-2 py-2">
                    <input type="checkbox" name="is_pinned" id="is_pinned" value="1" class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-[var(--teal)] focus:ring-[var(--teal)] bg-white dark:bg-gray-900">
                    <label for="is_pinned" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">Pin this announcement to top</label>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('tenant.teacher.my-announcements') }}" class="flex-1 sm:flex-none px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-bold hover:bg-gray-200 dark:hover:bg-gray-600 text-center transition-all">
                        Cancel
                    </a>
                    <button type="submit" class="flex-1 sm:flex-none px-8 py-2.5 bg-[var(--teal)] text-white rounded-lg text-sm font-bold hover:opacity-90 transition-all shadow-md">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Success Modal (Simplified) --}}
    <template x-teleport="body">
        <div x-show="showingSuccess" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="window.location.href = '{{ route('tenant.teacher.my-announcements') }}'"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Success</h3>
                    <button @click="window.location.href = '{{ route('tenant.teacher.my-announcements') }}'" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-8 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-lg font-medium">Announcement updated successfully!</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button @click="window.location.href = '{{ route('tenant.teacher.my-announcements') }}'" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-all">
                        Back to My Announcements
                    </button>
                </div>
            </div>
        </div>
    </template>
    </div>

    <script>
        document.getElementById('editAnnouncementForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const alpineData = document.querySelector('[x-data]').__x.$data;
            alpineData.showSuccess('Announcement updated successfully');
        });

        function removeMedia(index, button) {
            button.closest('.flex').style.display = 'none';
        }

        function previewNewMedia(input) {
            const preview = document.getElementById('new_media_preview');
            preview.innerHTML = '';
            
            if (input.files) {
                Array.from(input.files).forEach((file) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'relative aspect-video rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 shadow-sm';
                        
                        if (file.type.startsWith('image/')) {
                            div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                        } else if (file.type.startsWith('video/')) {
                            div.innerHTML = `
                                <video class="w-full h-full object-cover">
                                    <source src="${e.target.result}" type="${file.type}">
                                </video>
                                <div class="absolute inset-0 flex items-center justify-center bg-black/20">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.333-5.89a1.5 1.5 0 000-2.538L6.3 2.841z" />
                                    </svg>
                                </div>
                            `;
                        }
                        preview.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                });
            }
        }
    </script>
</x-app-layout>










