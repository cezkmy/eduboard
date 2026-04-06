<x-app-layout>
    <x-slot name="title">Users - EduBoard Admin</x-slot>

    <div class="admin-content" x-data='{ 
        activeTab: new URLSearchParams(window.location.search).get("tab") || "teachers",
        searchQuery: "",
        deptFilter: "all",
        userModal: false,
        deleteModal: false,
        successModal: false,
        limitModal: false,
        limitMessage: "",
        modalTitle: "Add User",
        successMessage: "",
        selectedUsers: [],
        selectAll: false,
        bulkModal: false,
        bulkField: "course",
        bulkValue: "",
        lockModal: false,
        lockDays: "0",

        currentUser: {
            id: null,
            name: "",
            email: "",
            role: "teacher",
            status: "active",
            department: "",
            employee_id: "",
            course: "",
            year_level: "",
            section: "",
            password: ""
        },

        openAddModal() {
            this.modalTitle = "Add User";
            this.currentUser = { id: null, name: "", email: "", role: "teacher", status: "active", department: "", employee_id: "", course: "", year_level: "", section: "", password: "" };
            this.userModal = true;
        },

        openEditModal(user) {
            this.modalTitle = "Edit User";
            this.currentUser = { ...user, password: "" };
            this.userModal = true;
        },
        
        unlockEdit(id) {
            fetch(`/admin/users/${id}/edit-unlock`, { method: "POST", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" } });
        },

        openDeleteModal(id) {
            this.currentUser.id = id;
            this.deleteModal = true;
        },

        confirmRestore(id) {
            fetch(`/admin/users/${id}/restore`, { method: "POST", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }})
            .then(res => res.json()).then(data => { if (data.success) { this.showSuccess(data.message); setTimeout(()=>window.location.reload(), 1000); } });
        },

        confirmForceDelete(id) {
            if (confirm("Are you sure you want to permanently delete this user? This cannot be undone.")) {
                fetch(`/admin/users/${id}/force`, { method: "DELETE", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }})
                .then(res => res.json()).then(data => { if (data.success) { this.showSuccess(data.message); setTimeout(()=>window.location.reload(), 1000); } });
            }
        },

        openLockModal(user) {
            this.currentUser = { ...user };
            this.lockDays = "0";
            this.lockModal = true;
        },

        submitLock() {
            fetch(`/admin/users/${this.currentUser.id}/lock-account`, {
                method: "POST", 
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ days: parseInt(this.lockDays) })
            }).then(res => res.json()).then(data => { if (data.success) { this.lockModal = false; this.showSuccess(data.message); setTimeout(()=>window.location.reload(), 1000); } });
        },

        confirmDelete() {
            fetch(`/admin/users/${this.currentUser.id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.deleteModal = false;
                    this.showSuccess(data.message);
                    setTimeout(() => window.location.reload(), 1000);
                }
            });
        },

        showSuccess(msg) {
            this.successMessage = msg;
            this.successModal = true;
            setTimeout(() => this.successModal = false, 3000);
        },

        checkLimit(role) {
            const limits = {
                admin: {{ tenant()->getLimit('admins') }},
                teacher: {{ tenant()->getLimit('teachers') }}
            };
            const counts = {
                admin: {{ $admins->count() }},
                teacher: {{ $teachers->count() }}
            };
            
            if (limits[role] !== -1 && counts[role] >= limits[role]) {
                this.limitMessage = `You have reached the limit of ${limits[role]} ${role}s for your current plan.`;
                this.limitModal = true;
                return true;
            }
            return false;
        },

        saveUser() {
            const isEdit = !!this.currentUser.id;
            const url = isEdit ? `{{ url('admin/users') }}/${this.currentUser.id}` : "{{ route('tenant.admin.users.store') }}";
            const method = isEdit ? "PUT" : "POST";

            fetch(url, {
                method: method,
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(this.currentUser)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.userModal = false;
                    this.showSuccess(data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    alert(data.message || "An error occurred");
                }
            });
        },

        toggleAll() {
            const table = document.getElementById("usersTableBody");
            const rows = Array.from(table.querySelectorAll("tr"))
                .filter(tr => {
                    const role = tr.dataset.role;
                    const name = tr.dataset.name;
                    const dept = tr.dataset.dept;
                    return this.isUserVisible(role, name, dept);
                });
            const visibleIds = rows.map(tr => tr.dataset.id);

            if (this.selectAll) {
                this.selectedUsers = Array.from(new Set([...this.selectedUsers, ...visibleIds]));
            } else {
                this.selectedUsers = this.selectedUsers.filter(id => !visibleIds.includes(id));
            }
        },

        submitBulk() {
            if (this.selectedUsers.length === 0 || !this.bulkValue) return;
            
            fetch("{{ route('tenant.admin.users.bulk_update') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    user_ids: this.selectedUsers,
                    field: this.bulkField,
                    value: this.bulkValue
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.bulkModal = false;
                    this.selectedUsers = [];
                    this.selectAll = false;
                    this.showSuccess(data.message);
                    setTimeout(() => window.location.reload(), 1000);
                }
            });
        },

        isUserVisible(role, name, dept) {
            const roleMatch = this.activeTab === role;
            const searchMatch = !this.searchQuery || name.toLowerCase().includes(this.searchQuery.toLowerCase());
            const deptMatch = this.deptFilter === "all" || dept === this.deptFilter;
            return roleMatch && searchMatch && deptMatch;
        }
    }'>

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">User Management</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Manage teachers and students of {{ tenant('school_name') ?? 'your school' }}</p>
            </div>
            <button class="px-5 py-2.5 bg-[var(--accent)] text-white rounded-xl text-sm font-bold hover:bg-[var(--accent-dark)] transition-all flex items-center gap-2 shadow-lg active:scale-95" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);"
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
                            :class="activeTab === 'teachers' ? 'bg-white dark:bg-gray-800 text-[var(--accent)] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            @click="activeTab = 'teachers'">
                        Teachers <span class="ml-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-medium">{{ $teachers->count() }}</span>
                    </button>
                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                            :class="activeTab === 'students' ? 'bg-white dark:bg-gray-800 text-[var(--accent)] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            @click="activeTab = 'students'">
                        Students <span class="ml-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-medium">{{ $students->count() }}</span>
                    </button>
                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                            :class="activeTab === 'admins' ? 'bg-white dark:bg-gray-800 text-[var(--accent)] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            @click="activeTab = 'admins'">
                        Admins <span class="ml-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-medium">{{ $admins->count() }}</span>
                    </button>
                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all relative"
                            :class="activeTab === 'pending' ? 'bg-white dark:bg-gray-800 text-[var(--accent)] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            @click="activeTab = 'pending'">
                        Pending Approval
                        <span class="ml-1 px-1.5 py-0.5 bg-amber-100 text-amber-600 dark:bg-amber-900/30 rounded text-[10px] font-black">{{ $pendingUsers->count() }}</span>
                    </button>
                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all relative"
                            :class="activeTab === 'archived' ? 'bg-white dark:bg-gray-800 text-[var(--accent)] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            @click="activeTab = 'archived'">
                        Archived
                        <span class="ml-1 px-1.5 py-0.5 bg-gray-100 text-gray-600 dark:bg-gray-700 rounded text-[10px] font-black">{{ $archivedUsers->count() }}</span>
                    </button>
                </div>

                {{-- Quick Filters --}}
                <div class="flex items-center gap-3 px-2">
                    <div class="relative flex-1 md:w-64">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" x-model="searchQuery" placeholder="Search users..." 
                               class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                    </div>
                    <select x-model="deptFilter" class="pl-4 pr-10 py-2 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                        <option value="all">All Departments/Colleges</option>
                        @foreach($colleges as $c)
                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden" style="background: var(--bg-card); border-color: var(--border-color);">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-gray-900/20 border-b border-gray-100 dark:border-gray-700" style="background: var(--bg-card); border-color: var(--border-color);">
                        <th class="w-12 px-6 py-4">
                            <div class="flex items-center justify-center">
                                <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded border-gray-300 text-[var(--accent)] focus:ring-[var(--accent)]">
                            </div>
                        </th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 dark:text-white uppercase tracking-wider">User Profile</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 dark:text-white uppercase tracking-wider">Organization</th>
                        <th x-show="activeTab === 'students'" class="px-6 py-4 text-[11px] font-bold text-gray-400 dark:text-white uppercase tracking-wider">Academic Info</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-400 dark:text-white uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody id="usersTableBody" class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @foreach($teachers as $user)
                        <tr x-show="isUserVisible('teachers', {{ json_encode($user->name) }}, {{ json_encode($user->department) }})" 
                            data-role="teachers" data-id="{{ $user->id }}" data-dept="{{ $user->department }}" data-name="{{ $user->name }}"
                            class="hover:bg-[rgba(var(--accent-rgb),0.06)] dark:hover:bg-[rgba(var(--accent-rgb),0.14)] transition-all group">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" :value="{{ $user->id }}" x-model="selectedUsers" class="rounded border-gray-300 text-[var(--accent)] focus:ring-[var(--accent)]">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[rgba(var(--accent-rgb),0.16)] flex items-center justify-center text-[var(--accent)] font-bold text-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500 font-medium">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-500 font-bold">{{ $user->school_name ?? (tenant('school_name') ?? 'N/A') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <button class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider {{ strtolower($user->status ?? 'active') === 'active' ? 'bg-[rgba(var(--accent-rgb),0.12)] text-[var(--accent)] border border-[rgba(var(--accent-rgb),0.28)]' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                    {{ $user->status ?? 'active' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openLockModal({{ json_encode(['id' => $user->id, 'name' => $user->name]) }})" class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all" title="Lock Account">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </button>
                                    <button @click='openEditModal({{ json_encode(["id" => $user->id, "name" => $user->name, "email" => $user->email, "role" => $user->role, "status" => $user->status ?? "active", "department" => $user->department, "employee_id" => $user->employee_id]) }})' class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-[var(--accent)] hover:bg-[rgba(var(--accent-rgb),0.10)] rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button @click="openDeleteModal({{ $user->id }})" class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @foreach($admins as $user)
                        <tr x-show="isUserVisible('admins', {{ json_encode($user->name) }}, 'all')" 
                            data-role="admins" data-id="{{ $user->id }}" data-dept="all" data-name="{{ $user->name }}"
                            class="hover:bg-[rgba(var(--accent-rgb),0.06)] dark:hover:bg-[rgba(var(--accent-rgb),0.14)] transition-all group">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" :value="{{ $user->id }}" x-model="selectedUsers" class="rounded border-gray-300 text-[var(--accent)] focus:ring-[var(--accent)]">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-600 dark:bg-blue-900/40 flex items-center justify-center text-white font-bold text-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500 font-medium">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-500 font-bold">{{ $user->school_name ?? (tenant('school_name') ?? 'N/A') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <button class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider {{ strtolower($user->status ?? 'active') === 'active' ? 'bg-[rgba(var(--accent-rgb),0.12)] text-[var(--accent)] border border-[rgba(var(--accent-rgb),0.28)]' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                    {{ $user->status ?? 'active' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click='openEditModal({{ json_encode(["id" => $user->id, "name" => $user->name, "email" => $user->email, "role" => $user->role, "status" => $user->status ?? "active", "course" => $user->course, "year_level" => $user->year_level, "section" => $user->section]) }})' class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-[var(--accent)] hover:bg-[rgba(var(--accent-rgb),0.10)] rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button @click="openDeleteModal({{ $user->id }})" class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @foreach($students as $user)
                        <tr x-show="isUserVisible('students', {{ json_encode($user->name) }}, {{ json_encode($user->course) }})" 
                            data-role="students" data-id="{{ $user->id }}" data-dept="{{ $user->course }}" data-name="{{ $user->name }}"
                            class="hover:bg-[rgba(var(--accent-rgb),0.06)] dark:hover:bg-[rgba(var(--accent-rgb),0.14)] transition-all group">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" :value="{{ $user->id }}" x-model="selectedUsers" class="rounded border-gray-300 text-[var(--accent)] focus:ring-[var(--accent)]">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold text-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500 font-medium">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-500 font-bold">{{ $user->school_name ?? (tenant('school_name') ?? 'N/A') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Member Since</span>
                                    <span class="text-[10px] font-semibold text-gray-500">{{ optional($user->created_at)->format('M Y') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <button class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider {{ strtolower($user->status ?? 'active') === 'active' ? 'bg-[rgba(var(--accent-rgb),0.12)] text-[var(--accent)] border border-[rgba(var(--accent-rgb),0.28)]' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                    {{ $user->status ?? 'active' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openLockModal({{ json_encode(['id' => $user->id, 'name' => $user->name]) }})" class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all" title="Lock Account">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </button>
                                    <button @click='openEditModal({{ json_encode(["id" => $user->id, "name" => $user->name, "email" => $user->email, "role" => $user->role, "status" => $user->status ?? "active", "course" => $user->course, "year_level" => $user->year_level, "section" => $user->section]) }})' class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-[var(--accent)] hover:bg-[rgba(var(--accent-rgb),0.10)] rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button @click="openDeleteModal({{ $user->id }})" class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @foreach($pendingUsers as $user)
                        <tr x-show="activeTab === 'pending'" data-role="pending" data-id="{{ $user->id }}" class="hover:bg-[rgba(var(--accent-rgb),0.06)] dark:hover:bg-[rgba(var(--accent-rgb),0.14)] transition-all group">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" :value="{{ $user->id }}" x-model="selectedUsers" class="rounded border-gray-300 text-[var(--accent)] focus:ring-[var(--accent)]">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300 flex items-center justify-center font-bold text-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500 font-medium">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-500 font-bold">{{ $user->school_name ?? (tenant('school_name') ?? 'N/A') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40 uppercase tracking-wider">Pending Review</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('tenant.admin.users.approve', $user->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-[var(--accent)] text-white text-[10px] font-black rounded-lg hover:bg-[var(--accent-dark)] transition-all flex items-center gap-1">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            APPROVE
                                        </button>
                                    </form>
                                    <form action="{{ route('tenant.admin.users.reject', $user->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-gray-50 dark:bg-gray-700 text-red-500 dark:text-red-400 text-[10px] font-black rounded-lg hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white transition-all flex items-center gap-1">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            REJECT
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @foreach($archivedUsers as $user)
                        <tr x-show="activeTab === 'archived'" data-role="archived" data-id="{{ $user->id }}" class="hover:bg-[rgba(var(--accent-rgb),0.06)] dark:hover:bg-[rgba(var(--accent-rgb),0.14)] transition-all group opacity-75">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" :value="{{ $user->id }}" x-model="selectedUsers" class="rounded border-gray-300 text-[var(--accent)] focus:ring-[var(--accent)]">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gray-200 dark:bg-gray-800 text-gray-500 dark:text-gray-400 flex items-center justify-center font-bold text-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900 dark:text-white line-through">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500 font-medium">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-500 font-bold">{{ $user->school_name ?? (tenant('school_name') ?? 'N/A') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold bg-gray-100 dark:bg-gray-900/30 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 uppercase tracking-wider">Archived</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="confirmRestore({{ $user->id }})" class="px-3 py-1.5 bg-green-500 text-white text-[10px] font-black rounded-lg hover:bg-green-600 transition-all flex items-center gap-1">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                                        RESTORE
                                    </button>
                                    <button @click="confirmForceDelete({{ $user->id }})" class="px-3 py-1.5 bg-red-500 text-white text-[10px] font-black rounded-lg hover:bg-red-600 transition-all">DELETE FOREVER</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($teachers->isEmpty() && $admins->isEmpty() && $students->isEmpty() && $pendingUsers->isEmpty() && $archivedUsers->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                    <div class="w-14 h-14 bg-gray-50 dark:bg-gray-900/50 rounded-2xl flex items-center justify-center text-gray-300 mb-4">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" class="w-7 h-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">No users found</h3>
                    <p class="text-xs text-gray-500 mt-1">Seed tenant users to populate this table.</p>
                </div>
            @endif
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
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white tracking-tight" x-text="modalTitle"></h3>
                        <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider">User Details</p>
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
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white text-xl font-bold shadow-lg transition-all duration-300" 
                             :class="currentUser.role === 'admin' ? 'bg-blue-600 shadow-blue-500/20' : ''"
                             :style="currentUser.role === 'admin' ? '' : 'background: var(--accent); box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.22);'"
                             x-text="currentUser.name ? currentUser.name.charAt(0) : 'U'"></div>
                        <div>
                            <p class="text-base font-bold text-gray-900 dark:text-white tracking-tight" x-text="currentUser.name || 'New User'"></p>
                            <p class="text-[10px] font-bold uppercase tracking-widest" 
                               :class="currentUser.role === 'admin' ? 'text-blue-600' : ''"
                               :style="currentUser.role === 'admin' ? '' : 'color: var(--accent);'"
                               x-text="currentUser.role"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-gray-500 dark:text-gray-400 ml-1">Full Name</label>
                            <input type="text" x-model="currentUser.name" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-semibold focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);" placeholder="Juan Dela Cruz">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-gray-500 dark:text-gray-400 ml-1">Email Address</label>
                            <input type="email" x-model="currentUser.email" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-semibold focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);" placeholder="juan@school.edu">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-gray-500 dark:text-gray-400 ml-1">Role</label>
                            <select x-model="currentUser.role" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-semibold focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                                <option value="admin" :disabled="!currentUser.id && checkLimit('admin')">Administrator (Limit: {{ tenant()->getLimit('admins') }})</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-gray-500 dark:text-gray-400 ml-1">Status</label>
                            <select x-model="currentUser.status" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-semibold focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{{ $schoolType === 'college' ? 'College' : 'Department' }}</label>
                            <select x-model="currentUser.department" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                                <option value="">Select {{ $schoolType === 'college' ? 'College' : 'Department' }}</option>
                                @foreach($colleges as $c)
                                    <option value="{{ $c->name }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="currentUser.role === 'teacher'" class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Employee ID</label>
                            <input type="text" x-model="currentUser.employee_id" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                        </div>
                        <div x-show="currentUser.role === 'student'" class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{{ $schoolType === 'college' ? 'Program' : 'Strand' }}</label>
                            <select x-model="currentUser.course" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                                <option value="">Select {{ $schoolType === 'college' ? 'Program' : 'Strand' }}</option>
                                @foreach($programs as $p)
                                    <option value="{{ $p->name }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div x-show="currentUser.role === 'student'" class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{{ $schoolType === 'college' ? 'Year Level' : 'Grade Level' }}</label>
                            <select x-model="currentUser.year_level" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                                <option value="">Select {{ $schoolType === 'college' ? 'Year' : 'Grade' }}</option>
                                @foreach($levels as $l)
                                    <option value="{{ $l->name }}">{{ $l->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Section</label>
                            <select x-model="currentUser.section" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                                <option value="">Select Section</option>
                                @foreach($sections as $s)
                                    <option value="{{ $s->name }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div x-show="!currentUser.id" class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Password</label>
                        <input type="password" x-model="currentUser.password" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);" placeholder="••••••••">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 bg-[var(--accent)] text-white rounded-2xl text-sm font-bold hover:bg-[var(--accent-dark)] transition-all shadow-lg active:scale-95" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);">Save User Information</button>
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
                            <button @click="confirmDelete()" class="py-4 bg-red-500 text-white rounded-[1.25rem] text-sm font-black hover:bg-red-600 transition-all shadow-xl shadow-red-500/20">DELETE</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template x-teleport="body">
            <div x-show="lockModal" 
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="lockModal = false"></div>
                <div class="relative w-full max-w-sm bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl overflow-hidden animate-modal-enter border border-gray-100 dark:border-gray-700">
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 bg-amber-50 dark:bg-amber-900/20 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold dark:text-white tracking-tight mb-2">Lock Account</h3>
                        <p class="text-sm text-gray-500 font-medium mb-6">Temporarily suspend access for <span class="font-bold text-gray-900 dark:text-white" x-text="currentUser.name"></span>.</p>
                        
                        <select x-model="lockDays" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 mb-6 focus:ring-[var(--accent)] text-sm font-bold text-gray-700 dark:text-gray-200">
                            <option value="0">Unlock (Active)</option>
                            <option value="1">Lock for 1 Day</option>
                            <option value="3">Lock for 3 Days</option>
                            <option value="7">Lock for 7 Days</option>
                            <option value="9999">Lock Permanently</option>
                        </select>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <button @click="lockModal = false" class="py-3 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">Cancel</button>
                            <button @click="submitLock()" class="py-3 bg-amber-500 text-white font-bold rounded-xl hover:bg-amber-600 shadow-xl shadow-amber-500/20 transition-all flex items-center justify-center gap-2">
                                Apply
                            </button>
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

        {{-- Bulk Action Bar --}}
        <div x-show="selectedUsers.length > 0" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[90] w-full max-w-2xl px-4" x-cloak>
            <div class="bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-[2rem] shadow-2xl p-4 flex items-center justify-between border border-white/10 dark:border-gray-200">
                <div class="flex items-center gap-4 pl-4">
                    <div class="w-10 h-10 rounded-xl bg-white/10 dark:bg-gray-100 flex items-center justify-center font-black text-sm">
                        <span x-text="selectedUsers.length"></span>
                    </div>
                    <div>
                        <p class="text-sm font-black uppercase tracking-widest text-white dark:text-gray-900">Users Selected</p>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase">Ready for bulk action</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 pr-2">
                    <button @click="selectedUsers = []; selectAll = false" class="px-6 py-3 rounded-xl text-xs font-bold hover:bg-white/10 dark:hover:bg-gray-100 transition-all">Cancel</button>
                    <button @click="bulkModal = true" class="px-8 py-3 bg-[var(--accent)] text-white rounded-xl text-xs font-bold hover:opacity-90 shadow-lg shadow-[var(--accent)]/20 transition-all">Bulk Assign</button>
                </div>
            </div>
        </div>

        {{-- Bulk Assignment Modal --}}
        <template x-teleport="body">
            <div x-show="bulkModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto" x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="bulkModal = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-modal-enter">
                    <div class="p-8">
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Bulk Assignment</h3>
                            <p class="text-xs text-gray-500 font-semibold mt-1">Updating <span x-text="selectedUsers.length" class="text-[var(--accent)]"></span> selected users</p>
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Attribute to Update</label>
                                <select x-model="bulkField" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-4 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.10);">
                                    <option value="course">{{ $schoolType === 'college' ? 'Program' : 'Strand' }}</option>
                                    <option value="year_level">{{ $schoolType === 'college' ? 'Year Level' : 'Grade Level' }}</option>
                                    <option value="section">Section</option>
                                    <option value="department">Department / College</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Select New Value</label>
                                <select x-model="bulkValue" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-4 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.10);">
                                    <option value="">Choose Value...</option>
                                    
                                    <template x-if="bulkField === 'course'">
                                        @foreach($programs as $p) <option value="{{ $p->name }}">{{ $p->name }}</option> @endforeach
                                    </template>

                                    <template x-if="bulkField === 'year_level'">
                                        @foreach($levels as $l) <option value="{{ $l->name }}">{{ $l->name }}</option> @endforeach
                                    </template>

                                    <template x-if="bulkField === 'section'">
                                        @foreach($sections as $s) <option value="{{ $s->name }}">{{ $s->name }}</option> @endforeach
                                    </template>

                                    <template x-if="bulkField === 'department'">
                                        @foreach($colleges as $c) <option value="{{ $c->name }}">{{ $c->name }}</option> @endforeach
                                    </template>
                                </select>
                            </div>

                            <div class="pt-4">
                                <button @click="submitBulk()" class="w-full py-4 bg-[var(--accent)] text-white rounded-2xl text-sm font-bold hover:opacity-90 transition-all shadow-xl shadow-[var(--accent)]/20">Update All Selected</button>
                            </div>
                        </div>
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
                 ... (remaining success modal code)

    </div>

    <style>
    /* remove extra bottom divider under the last users row */
    #usersTableBody > tr:last-of-type > td {
        border-bottom: none !important;
    }
    </style>
</x-app-layout>