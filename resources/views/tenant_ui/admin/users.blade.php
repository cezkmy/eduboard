<x-app-layout>
    <x-slot name="title">Users - EduBoard Admin</x-slot>

    <div class="admin-content" x-data="{ 
        activeTab: 'teachers',
        searchQuery: '',
        deptFilter: 'all',
        userModal: false,
        deleteModal: false,
        successModal: false,
        limitModal: false,
        limitMessage: '',
        modalTitle: 'Add User',
        successMessage: '',
        currentUser: { id: null, name: '', email: '', role: 'teacher', status: 'active', department: '', course: '', employee_id: '', year_level: '', section: '' },
        
        plan: '{{ tenant('plan') ?? 'Basic' }}',
        adminLimit: {{ tenant()->getLimit('admins') }},
        teacherLimit: {{ tenant()->getLimit('teachers') }},
        currentAdmins: {{ $adminCount ?? 1 }},
        currentTeachers: {{ $teacherCount ?? 5 }},

        checkLimit(role) {
            if (role === 'admin' && this.adminLimit !== -1 && this.currentAdmins >= this.adminLimit) {
                return true;
            }
            if (role === 'teacher' && this.teacherLimit !== -1 && this.currentTeachers >= this.teacherLimit) {
                return true;
            }
            return false;
        },

        saveUser() {
            if (!this.currentUser.id) { // Only check for new users
                if (this.currentUser.role === 'admin' && this.checkLimit('admin')) {
                    this.limitMessage = `Sorry, you can only add ${this.adminLimit} Admin(s). Please upgrade plan to add more.`;
                    this.limitModal = true;
                    return;
                }
                if (this.currentUser.role === 'teacher' && this.checkLimit('teacher')) {
                    this.limitMessage = `Sorry, you can only add ${this.teacherLimit} Teacher(s). Please upgrade plan to add more.`;
                    this.limitModal = true;
                    return;
                }
            }
            
            this.userModal = false;
            this.showSuccess('User information updated!');
        },
        
        showSuccess(msg) {
            this.successMessage = msg;
            this.successModal = true;
            setTimeout(() => { if(this.successModal) this.successModal = false; }, 3000);
        },
        
        openAddModal() {
            this.modalTitle = 'Add User';
            this.currentUser = { id: null, name: '', email: '', role: 'teacher', status: 'active', department: '', course: '', employee_id: '', year_level: '', section: '' };
            this.userModal = true;
        },
        
        openEditModal(user) {
            this.modalTitle = 'Edit User';
            this.currentUser = { ...user };
            this.userModal = true;
        },

        get filteredUsers() {
            // This is a simplified frontend filter for the 'skeleton' fix
            // In a real app, this would be handled by Laravel collections/pagination
            return Array.from(document.querySelectorAll('#usersTableBody tr')).filter(tr => {
                const roleMatch = tr.dataset.role === this.activeTab;
                const searchMatch = !this.searchQuery || tr.innerText.toLowerCase().includes(this.searchQuery.toLowerCase());
                const deptMatch = this.deptFilter === 'all' || tr.dataset.dept === this.deptFilter;
                return roleMatch && searchMatch && deptMatch;
            });
        }
    }">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">User Management</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Manage teachers and students of {{ tenant('school_name') ?? 'your school' }}</p>
            </div>
            <button class="px-5 py-2.5 bg-teal-500 text-white rounded-xl text-sm font-bold hover:bg-teal-600 transition-all flex items-center gap-2 shadow-lg shadow-teal-500/20 active:scale-95" 
                    @click="openAddModal()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add New User
            </button>
        </div>

        {{-- Tabs & Filters Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-2 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Tabs --}}
                <div class="flex items-center p-1 bg-gray-50 dark:bg-gray-900/50 rounded-xl w-fit">
                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                            :class="activeTab === 'teachers' ? 'bg-white dark:bg-gray-800 text-teal-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            @click="activeTab = 'teachers'">
                        Teachers <span class="ml-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px]">5</span>
                    </button>
                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                            :class="activeTab === 'students' ? 'bg-white dark:bg-gray-800 text-teal-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            @click="activeTab = 'students'">
                        Students <span class="ml-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px]">5</span>
                    </button>
                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                            :class="activeTab === 'admins' ? 'bg-white dark:bg-gray-800 text-teal-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            @click="activeTab = 'admins'">
                        Admins <span class="ml-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px]">1</span>
                    </button>
                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all relative"
                            :class="activeTab === 'pending' ? 'bg-white dark:bg-gray-800 text-teal-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            @click="activeTab = 'pending'">
                        Pending Approval
                        <span class="ml-1 px-1.5 py-0.5 bg-amber-100 text-amber-600 dark:bg-amber-900/30 rounded text-[10px] font-black">2</span>
                    </button>
                </div>

                {{-- Quick Filters --}}
                <div class="flex items-center gap-3 px-2">
                    <div class="relative flex-1 md:w-64">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" x-model="searchQuery" placeholder="Search users..." 
                               class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 transition-all">
                    </div>
                    <select x-model="deptFilter" class="pl-4 pr-10 py-2 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-teal-500/20 transition-all appearance-none">
                        <option value="all">All Departments</option>
                        <option value="COT">COT</option>
                        <option value="COB">COB</option>
                        <option value="CON">CON</option>
                        <option value="COE">COE</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-gray-900/20 border-b border-gray-100 dark:border-gray-700">
                        <th class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-[0.1em]">User Profile</th>
                        <th class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-[0.1em]">Department / Course</th>
                        <th x-show="activeTab === 'students'" class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-[0.1em]">Academic Info</th>
                        <th class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-[0.1em]">Status</th>
                        <th class="px-8 py-5"></th>
                    </tr>
                </thead>
                <tbody id="usersTableBody" class="divide-y divide-gray-50 dark:divide-gray-700/50">

                    {{-- ── Teachers ── --}}
                    <tr x-show="activeTab === 'teachers'" data-role="teachers" data-dept="COT" data-course="BSIT" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-all group">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center text-teal-600 dark:text-teal-400 font-bold text-lg overflow-hidden shadow-sm ring-2 ring-white dark:ring-gray-800">
                                    <img src="{{ asset('images/download.jpg') }}" alt="Prof. Reyes" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-white">Prof. Reyes</p>
                                    <p class="text-xs text-gray-500 font-medium">reyes@school.edu</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="space-y-1">
                                <div class="inline-flex px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-900 text-[10px] font-black text-gray-600 dark:text-gray-400 uppercase tracking-tighter">COT</div>
                                <p class="text-xs text-gray-500 font-bold">BS Information Technology</p>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <button @click="showSuccess('Account status toggled')" class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400 border border-green-100 dark:border-green-900/30 uppercase tracking-widest hover:bg-green-100 transition-colors">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-2"></span>
                                Active
                            </button>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                <button @click="openEditModal({id:1, name:'Prof. Reyes', email:'reyes@school.edu', role:'teacher', department:'COT', course:'BSIT', status:'active'})" class="w-9 h-9 flex items-center justify-center text-gray-400 hover:text-teal-600 hover:bg-teal-50 dark:hover:bg-teal-900/30 rounded-xl transition-all">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <button @click="deleteModal = true" class="w-9 h-9 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl transition-all">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr x-show="activeTab === 'teachers'" data-role="teachers" data-dept="COT" data-course="BSCS" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-all group">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center text-teal-600 dark:text-teal-400 font-bold text-lg overflow-hidden shadow-sm ring-2 ring-white dark:ring-gray-800">
                                    <img src="{{ asset('images/download.jpg') }}" alt="Prof. Garcia" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-white">Prof. Garcia</p>
                                    <p class="text-xs text-gray-500 font-medium">garcia@school.edu</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="space-y-1">
                                <div class="inline-flex px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-900 text-[10px] font-black text-gray-600 dark:text-gray-400 uppercase tracking-tighter">COT</div>
                                <p class="text-xs text-gray-500 font-bold">BS Computer Science</p>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <button @click="showSuccess('Account status toggled')" class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400 border border-green-100 dark:border-green-900/30 uppercase tracking-widest hover:bg-green-100 transition-colors">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-2"></span>
                                Active
                            </button>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                <button class="w-9 h-9 flex items-center justify-center text-gray-400 hover:text-teal-600 hover:bg-teal-50 rounded-xl transition-all"><svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button>
                                <button class="w-9 h-9 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all"><svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg></button>
                            </div>
                        </td>
                    </tr>

                    {{-- ── Admins ── --}}
                    <tr x-show="activeTab === 'admins'" data-role="admins" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-all group">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-600 dark:bg-blue-900/30 flex items-center justify-center text-white font-black text-lg overflow-hidden shadow-sm ring-2 ring-white dark:ring-gray-800">
                                    SA
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-white">Second Admin</p>
                                    <p class="text-xs text-gray-500 font-medium">admin2@school.edu</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="space-y-1">
                                <div class="inline-flex px-2 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-tighter">System</div>
                                <p class="text-xs text-gray-500 font-bold italic">Second Administrator</p>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <button @click="showSuccess('Account status toggled')" class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400 border border-green-100 dark:border-green-900/30 uppercase tracking-widest hover:bg-green-100 transition-colors">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-2"></span>
                                Active
                            </button>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                <button class="w-9 h-9 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all"><svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button>
                                <button class="w-9 h-9 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all"><svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg></button>
                            </div>
                        </td>
                    </tr>

                    {{-- ── Students ── --}}
                    <tr x-show="activeTab === 'students'" data-role="students" data-dept="COT" data-course="BSIT" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-all group">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-lg overflow-hidden shadow-sm ring-2 ring-white dark:ring-gray-800">
                                    <img src="{{ asset('images/download.jpg') }}" alt="Juan Dela Cruz" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-white">Juan Dela Cruz</p>
                                    <p class="text-xs text-gray-500 font-medium">juan@school.edu</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="space-y-1">
                                <div class="inline-flex px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-900 text-[10px] font-black text-gray-600 dark:text-gray-400 uppercase tracking-tighter">COT</div>
                                <p class="text-xs text-gray-500 font-bold">BS Information Technology</p>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Section A</span>
                                <span class="text-[10px] font-bold text-gray-500">3rd Year</span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <button @click="showSuccess('Account status toggled')" class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400 border border-green-100 dark:border-green-900/30 uppercase tracking-widest hover:bg-green-100 transition-colors">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-2"></span>
                                Active
                            </button>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                <button class="w-9 h-9 flex items-center justify-center text-gray-400 hover:text-teal-600 hover:bg-teal-50 rounded-xl transition-all"><svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button>
                                <button class="w-9 h-9 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all"><svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg></button>
                            </div>
                        </td>
                    </tr>

                    {{-- ── Pending Approvals ── --}}
                    <tr x-show="activeTab === 'pending'" data-role="pending" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-all group">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 font-black text-lg overflow-hidden shadow-sm ring-2 ring-white dark:ring-gray-800">
                                    KP
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-white">Kevin Park</p>
                                    <p class="text-xs text-gray-500 font-medium">kevin.park@example.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="space-y-1">
                                <div class="inline-flex px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-900 text-[10px] font-black text-gray-600 dark:text-gray-400 uppercase tracking-tighter">COT</div>
                                <p class="text-xs text-gray-500 font-bold">BS Information Technology</p>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black bg-amber-50 text-amber-600 border border-amber-100 uppercase tracking-widest">
                                Pending Review
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                <button @click="showSuccess('User approved!')" class="px-4 py-2 bg-teal-500 text-white text-[10px] font-black rounded-xl hover:bg-teal-600 transition-all shadow-lg shadow-teal-500/20 active:scale-95">APPROVE</button>
                                <button @click="deleteModal = true" class="px-4 py-2 bg-gray-100 text-gray-600 text-[10px] font-black rounded-xl hover:bg-gray-200 transition-all active:scale-95">REJECT</button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>

            {{-- Empty State Logic --}}
            <div x-show="false" class="flex flex-col items-center justify-center py-20 px-6 text-center">
                <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900/50 rounded-2xl flex items-center justify-center text-gray-300 mb-4">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">No users found</h3>
                <p class="text-xs text-gray-500 mt-1">Try adjusting your filters or search terms.</p>
            </div>
        </div>

        {{-- Add/Edit User Modal --}}
        <template x-teleport="body">
        <div x-show="userModal" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="userModal = false"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-modal-enter"
                 @click.stop>
                <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700/50 flex items-center justify-between bg-white dark:bg-gray-800 sticky top-0 z-10">
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight" x-text="modalTitle"></h3>
                        <p class="text-[10px] text-gray-500 font-medium">Manage user access details</p>
                    </div>
                    <button @click="userModal = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-all flex items-center justify-center">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form class="p-6 space-y-4" @submit.prevent="saveUser()">
                    @csrf
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800/50">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white text-xl font-black shadow-lg transition-all duration-300" 
                             :class="currentUser.role === 'admin' ? 'bg-blue-600 shadow-blue-500/20' : 'bg-teal-500 shadow-teal-500/20'"
                             x-text="currentUser.name ? currentUser.name.charAt(0) : 'U'"></div>
                        <div>
                            <p class="text-base font-black text-gray-900 dark:text-white tracking-tight" x-text="currentUser.name || 'New User'"></p>
                            <p class="text-[10px] font-black uppercase tracking-widest" 
                               :class="currentUser.role === 'admin' ? 'text-blue-600' : 'text-teal-600'"
                               x-text="currentUser.role"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Full Name</label>
                            <input type="text" x-model="currentUser.name" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all" placeholder="Juan Dela Cruz">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Email Address</label>
                            <input type="email" x-model="currentUser.email" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all" placeholder="juan@school.edu">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Role</label>
                            <select x-model="currentUser.role" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all appearance-none">
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                                <option value="admin" :disabled="!currentUser.id && checkLimit('admin')">Administrator (Limit: {{ tenant()->getLimit('admins') }})</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Status</label>
                            <select x-model="currentUser.status" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all appearance-none">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Department</label>
                            <select x-model="currentUser.department" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all appearance-none">
                                <option value="">Select Dept</option>
                                <option value="COT">COT</option>
                                <option value="COB">COB</option>
                                <option value="CON">CON</option>
                                <option value="COE">COE</option>
                            </select>
                        </div>
                        <div x-show="currentUser.role === 'teacher'" class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Employee ID</label>
                            <input type="text" x-model="currentUser.employee_id" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                        </div>
                        <div x-show="currentUser.role === 'student'" class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Course</label>
                            <input type="text" x-model="currentUser.course" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                        </div>
                    </div>

                    <div x-show="currentUser.role === 'student'" class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Year Level</label>
                            <select x-model="currentUser.year_level" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all appearance-none">
                                <option value="">Select Year</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Section</label>
                            <input type="text" x-model="currentUser.section" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                        </div>
                    </div>

                    <div x-show="!currentUser.id" class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Password</label>
                        <input type="password" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all" placeholder="••••••••">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 bg-teal-500 text-white rounded-2xl text-sm font-black hover:bg-teal-600 transition-all shadow-lg shadow-teal-500/20 active:scale-95">SAVE USER</button>
                    </div>
                </form>
            </div>
        </div>
        </template>

        {{-- Delete Modal --}}
        <template x-teleport="body">
            <div x-show="deleteModal" 
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="deleteModal = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-modal-enter">
                    <div class="p-8 text-center">
                        <div class="w-20 h-20 bg-red-50 dark:bg-red-900/20 rounded-full flex items-center justify-center text-red-500 mx-auto mb-6">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight mb-2">Delete User?</h3>
                        <p class="text-sm text-gray-500 font-medium mb-8">Are you sure you want to remove this user? This action cannot be undone.</p>
                        <div class="grid grid-cols-2 gap-4">
                            <button @click="deleteModal = false" class="py-4 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-[1.25rem] text-sm font-black hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">CANCEL</button>
                            <button @click="deleteModal = false; showSuccess('User deleted successfully')" class="py-4 bg-red-500 text-white rounded-[1.25rem] text-sm font-black hover:bg-red-600 transition-all shadow-xl shadow-red-500/20">DELETE</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Limit Warning Modal --}}
        <template x-teleport="body">
            <div x-show="limitModal" 
                 class="fixed inset-0 z-[110] flex items-center justify-center p-4 overflow-y-auto" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="limitModal = false"></div>
                <div class="relative w-full max-w-sm bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-modal-enter">
                    <div class="p-8 text-center text-red-500">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight mb-2">Limit Reached</h3>
                        <p class="text-sm text-gray-500 font-medium mb-6" x-text="limitMessage"></p>
                        <button @click="limitModal = false" class="w-full py-4 bg-gray-900 dark:bg-white dark:text-gray-900 text-white rounded-[1.25rem] text-sm font-black hover:opacity-90 transition-all">DISMISS</button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Success Modal --}}
        <template x-teleport="body">
            <div x-show="successModal"  
                 class="fixed inset-0 z-[110] flex items-center justify-center p-4 overflow-y-auto" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="successModal = false"></div>
                <div class="relative w-full max-w-sm bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-modal-enter">
                    <div class="p-8 text-center">
                        <div class="mb-6">
                            <svg class="animated-check mx-auto" viewBox="0 0 52 52" style="width: 80px; height: 80px;">
                                <circle class="animated-check-circle" cx="26" cy="26" r="25" fill="none" stroke="#10b981" stroke-width="2"/>
                                <path class="animated-check-path" fill="none" stroke="#10b981" stroke-width="4" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight mb-2">Success!</h3>
                        <p class="text-sm text-gray-500 font-medium mb-8" x-text="successMessage"></p>
                        <button @click="successModal = false" class="w-full py-4 bg-gray-900 dark:bg-white dark:text-gray-900 text-white rounded-[1.25rem] text-sm font-black hover:opacity-90 transition-all">CONTINUE</button>
                    </div>
                </div>
            </div>
        </template>
    </div>

</x-app-layout>