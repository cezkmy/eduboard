<x-app-layout>
    <x-slot name="title">Users</x-slot>

    <script>
        window.permissionsSchema = @json($permissionsSchema ?? (object)[]);
        window.roleGroupMapping = @json($roleGroupMapping ?? (object)[]);
        window.tenantRoles = @json($tenantRoles ?? (object)[]);
        window.__adminLimit  = {{ tenant()->getLimit('admins') }};
        window.__teacherLimit = {{ tenant()->getLimit('teachers') }};
        window.__adminCount  = {{ $roleCounts->get('admin', 0) }};
        window.__teacherCount = {{ $roleCounts->get('teacher', 0) }};
        window.__csrfToken   = '{{ csrf_token() }}';
        window.__usersUrl    = '{{ url('admin/users') }}';
        window.__usersStore  = '{{ route('tenant.admin.users.store') }}';
        window.__usersBulk   = '{{ route('tenant.admin.users.bulk_update') }}';
        window.__usersBulkPermissions = '{{ route('tenant.admin.users.bulk_permissions') }}';
        window.__activeTab   = '{{ $activeTab }}';
        window.__search      = '{{ request('search') }}';
        window.__deptFilter  = '{{ request('dept', 'all') }}';
        window.__yearLevels  = @json($yearLevels ?? []);
        window.__gradeLevels = @json($gradeLevels ?? []);
        window.__programs    = @json($programs ?? []);
        window.__strands     = @json($strands ?? []);
        window.__colleges    = @json($colleges ?? []);

        function usersManager() {
            return {
                activeTab: window.__activeTab,
                searchQuery: window.__search,
                deptFilter: window.__deptFilter,
                userModal: false,
                deleteModal: false,
                forceDeleteModal: false,
                successModal: false,
                limitModal: false,
                limitMessage: '',
                generatedPasswordAlert: null,
                showGeneratedPassword: false,
                modalTitle: 'Add User',
                successMessage: '',
                selectedUsers: [],
                selectAll: false,
                bulkModal: false,
                bulkPermissionsModal: false,
                bulkPermissionsSelect: [],
                bulkField: 'course',
                bulkValue: '',
                lockModal: false,
                lockDays: '0',
                permissionSearchQuery: '',
                userModalStep: 1,
                permissionMode: 'role',
                permissionsSchema: window.permissionsSchema,
                roleGroupMapping: window.roleGroupMapping,
                tenantRoles: window.tenantRoles,
                schoolLevel: 'college',
                allYearLevels: window.__yearLevels,
                allGradeLevels: window.__gradeLevels,
                allPrograms: window.__programs,
                allStrands: window.__strands,
                allColleges: window.__colleges,
                allSections: @json($sections ?? []),
                currentUser: {
                    id: null, name: '', email: '', role: 'teacher',
                    status: 'active', department: '', employee_id: '',
                    course: '', year_level: '', section: '', password: '',
                    custom_permissions: { granted: [], denied: [] }
                },
                isSaving: false,

                get filteredPermissionsSchema() {
                    const filtered = {};
                    const role = String(this.currentUser.role).toLowerCase();
                    const allowedGroups = this.roleGroupMapping[role] || Object.keys(this.permissionsSchema);
                    const query = this.permissionSearchQuery ? this.permissionSearchQuery.toLowerCase() : '';
                    
                    for (const group in this.permissionsSchema) {
                        if (!allowedGroups.includes(group)) continue;
                        
                        const gf = {};
                        for (const code in this.permissionsSchema[group]) {
                            const label = this.permissionsSchema[group][code];
                            if (!query || label.toLowerCase().includes(query) || code.toLowerCase().includes(query)) {
                                gf[code] = label;
                            }
                        }
                        if (Object.keys(gf).length > 0) filtered[group] = gf;
                    }
                    return filtered;
                },

                get filteredColleges() {
                    const target = this.schoolLevel === 'college' ? 'college' : (this.schoolLevel === 'elementary' ? 'elementary' : (this.schoolLevel === 'jhs' ? 'junior_high' : 'senior_high'));
                    return this.allColleges.filter(c => c.educational_level === target);
                },

                get filteredPrograms() {
                    if (this.schoolLevel === 'shs') return this.allStrands.filter(s => s.educational_level === 'senior_high');
                    if (this.schoolLevel === 'college') return this.allPrograms.filter(p => p.educational_level === 'college');
                    return [];
                },

                get filteredLevels() {
                    if (this.schoolLevel === 'college') return this.allYearLevels.filter(l => l.educational_level === 'college');
                    
                    const target = this.schoolLevel === 'elementary' ? 'elementary' : (this.schoolLevel === 'jhs' ? 'junior_high' : 'senior_high');
                    return this.allGradeLevels.filter(l => {
                        if (l.educational_level !== target) return false;
                        
                        // Extract number from Grade name (e.g., "Grade 7" -> 7)
                        const gradeNum = parseInt(l.name.replace(/\D/g, ''));
                        if (isNaN(gradeNum)) return true; // Show if no number (flexible)

                        if (this.schoolLevel === 'elementary') return gradeNum <= 6;
                        if (this.schoolLevel === 'jhs') return gradeNum >= 7 && gradeNum <= 10;
                        if (this.schoolLevel === 'shs') return gradeNum >= 11;
                        
                        return true;
                    });
                },

                get filteredSections() {
                    const target = this.schoolLevel === 'college' ? 'college' : (this.schoolLevel === 'elementary' ? 'elementary' : (this.schoolLevel === 'jhs' ? 'junior_high' : 'senior_high'));
                    return this.allSections.filter(s => s.educational_level === target);
                },

                validateStep1() {
                    if (!this.currentUser.name || !this.currentUser.email) {
                        this.showError('Name and Email are required.');
                        return false;
                    }
                    if (this.currentUser.role === 'teacher' && !this.currentUser.employee_id) {
                        this.showError('Employee ID is required for Teachers.');
                        return false;
                    }
                    if (this.currentUser.role === 'student') {
                        if (!this.currentUser.year_level || !this.currentUser.section) {
                            this.showError('Level and Section are required for Students.');
                            return false;
                        }
                        if ((this.schoolLevel === 'college' || this.schoolLevel === 'shs') && !this.currentUser.course) {
                            this.showError(this.schoolLevel === 'college' ? 'Program / Course is required.' : 'Strand / Track is required.');
                            return false;
                        }
                    }
                    if ((this.schoolLevel === 'college' || this.currentUser.role !== 'student') && !this.currentUser.department) {
                        this.showError(this.schoolLevel === 'college' ? 'College / Faculty is required.' : 'Department / Office is required.');
                        return false;
                    }
                    return true;
                },

                goToStep(step) {
                    if (step === 2 && !this.validateStep1()) return;
                    this.userModalStep = step;
                },

                switchTab(tab) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tab);
                    url.searchParams.set('page', 1);
                    window.location.href = url.toString();
                },

                applyFilters() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('search', this.searchQuery);
                    url.searchParams.set('dept', this.deptFilter);
                    url.searchParams.set('page', 1);
                    window.location.href = url.toString();
                },

                openAddModal() {
                    this.modalTitle = 'Add New User';
                    this.userModalStep = 1;
                    this.permissionMode = 'role';
                    this.schoolLevel = 'college';
                    this.currentUser = { id: null, name: '', email: '', role: 'teacher', status: 'active', department: '', employee_id: '', course: '', year_level: '', section: '', password: '', custom_permissions: { granted: [], denied: [] } };
                    this.userModal = true;
                },

                openEditModal(user) {
                    this.modalTitle = 'Edit User Details';
                    this.userModalStep = 1;
                    this.currentUser = { ...user, password: '', custom_permissions: user.custom_permissions || { granted: [], denied: [] } };
                    
                    // Determine school level based on department/course/year_level
                    if (this.allColleges.some(c => c.name === user.department)) {
                        this.schoolLevel = 'college';
                    } else if (this.allStrands.some(s => s.name === user.course)) {
                        this.schoolLevel = 'shs';
                    } else if (this.allGradeLevels.some(g => g.name === user.year_level)) {
                        // Further logic to distinguish JHS vs Elementary if needed
                        const gradeNum = parseInt(user.year_level.replace(/\D/g, ''));
                        this.schoolLevel = (gradeNum >= 7 && gradeNum <= 10) ? 'jhs' : 'elementary';
                    } else {
                        this.schoolLevel = 'college'; // default
                    }

                    const g = this.currentUser.custom_permissions.granted || [];
                    const d = this.currentUser.custom_permissions.denied  || [];
                    this.permissionMode = (g.length > 0 || d.length > 0) ? 'custom' : 'role';
                    this.userModal = true;
                },

                isCustomAllowed(code) {
                    if (this.currentUser.custom_permissions?.denied?.includes(code))  return false;
                    if (this.currentUser.custom_permissions?.granted?.includes(code)) return true;
                    return this.roleHasPermission(code);
                },

                toggleCustomStatus(code, isGrant) {
                    const cp = this.currentUser.custom_permissions || { granted: [], denied: [] };
                    cp.granted = cp.granted || [];
                    cp.denied  = cp.denied  || [];
                    
                    const hasByRole = this.roleHasPermission(code);
                    
                    // Remove from both first
                    cp.granted = cp.granted.filter(c => c !== code);
                    cp.denied  = cp.denied.filter(c  => c !== code);
                    
                    if (isGrant) {
                        // If we want to ALLOW, and the role doesn't have it, we must GRANT it explicitly.
                        // If the role ALREADY has it, we don't need to do anything (removing from 'denied' was enough).
                        if (!hasByRole) {
                            cp.granted.push(code);
                        }
                    } else {
                        // If we want to DENY, and the role HAS it, we must DENY it explicitly.
                        // If the role DOESN'T have it, we don't need to do anything (removing from 'granted' was enough).
                        if (hasByRole) {
                            cp.denied.push(code);
                        }
                    }
                    this.currentUser.custom_permissions = { ...cp };
                },

                getPermissionState(code) {
                    if (this.currentUser.custom_permissions?.granted?.includes(code)) return 'granted';
                    if (this.currentUser.custom_permissions?.denied?.includes(code))  return 'denied';
                    return 'default';
                },

                roleHasPermission(code) {
                    const role = String(this.currentUser.role).toLowerCase();
                    if (!this.tenantRoles || !this.tenantRoles[role]) return false;
                    return (this.tenantRoles[role].permissions || []).includes(code);
                },

                unlockEdit(id) {
                    fetch('/admin/users/' + id + '/edit-unlock', { method: 'POST', headers: { 'X-CSRF-TOKEN': window.__csrfToken } });
                },

                openDeleteModal(id, name = '') {
                    this.currentUser = { ...this.currentUser, id: id, name: name };
                    this.deleteModal = true;
                },

                confirmRestore(id) {
                    fetch('/admin/users/' + id + '/restore', { method: 'POST', headers: { 'X-CSRF-TOKEN': window.__csrfToken } })
                        .then(r => r.json()).then(data => { if (data.success) { this.showSuccess(data.message); setTimeout(() => window.location.reload(), 1000); } });
                },

                confirmForceDelete(id, name = '') {
                    this.currentUser = { ...this.currentUser, id: id, name: name };
                    this.forceDeleteModal = true;
                },

                submitForceDelete() {
                    fetch('/admin/users/' + this.currentUser.id + '/force', { 
                        method: 'DELETE', 
                        headers: { 'X-CSRF-TOKEN': window.__csrfToken } 
                    })
                    .then(r => r.json())
                    .then(data => { 
                        if (data.success) { 
                            this.forceDeleteModal = false;
                            this.showSuccess(data.message); 
                            setTimeout(() => window.location.reload(), 1000); 
                        } 
                    });
                },

                openLockModal(user) { this.currentUser = { ...user }; this.lockDays = '0'; this.lockModal = true; },

                submitLock() {
                    fetch('/admin/users/' + this.currentUser.id + '/lock-account', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.__csrfToken },
                        body: JSON.stringify({ days: parseInt(this.lockDays) })
                    }).then(r => r.json()).then(data => { if (data.success) { this.lockModal = false; this.showSuccess(data.message); setTimeout(() => window.location.reload(), 1000); } });
                },

                confirmDelete() {
                    if (!this.currentUser.id) return;
                    fetch('/admin/users/' + this.currentUser.id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': window.__csrfToken } })
                        .then(r => r.json())
                        .then(data => { if (data.success) { this.deleteModal = false; this.showSuccess(data.message); setTimeout(() => window.location.reload(), 1000); } });
                },

                showSuccess(msg) { this.successMessage = msg; this.successModal = true; setTimeout(() => this.successModal = false, 3000); },

                checkLimit(role) {
                    const limits = { admin: window.__adminLimit, teacher: window.__teacherLimit };
                    const counts = { admin: window.__adminCount, teacher: window.__teacherCount };
                    return (limits[role] !== -1 && counts[role] >= limits[role]);
                },

                saveUser() {
                    if (!this.currentUser.name || !this.currentUser.email || !this.currentUser.role) {
                        this.showError('Name, Email, and Role are required.');
                        return;
                    }
                    if (this.currentUser.role === 'teacher' && !this.currentUser.employee_id) {
                        this.showError('Employee ID is required for Teachers.');
                        return;
                    }
                    if (this.currentUser.role === 'student') {
                        if (!this.currentUser.year_level || !this.currentUser.section) {
                            this.showError('Level and Section are required for Students.');
                            return;
                        }
                        if ((this.schoolLevel === 'college' || this.schoolLevel === 'shs') && !this.currentUser.course) {
                            this.showError(this.schoolLevel === 'college' ? 'Program / Course is required.' : 'Strand / Track is required.');
                            return;
                        }
                    }
                    if ((this.schoolLevel === 'college' || this.currentUser.role !== 'student') && !this.currentUser.department) {
                        this.showError(this.schoolLevel === 'college' ? 'College / Faculty is required.' : 'Department / Office is required.');
                        return;
                    }

                    if (this.permissionMode === 'role') this.currentUser.custom_permissions = { granted: [], denied: [] };
                    const isEdit = !!this.currentUser.id;
                    const url = isEdit ? window.__usersUrl + '/' + this.currentUser.id : window.__usersStore;
                    
                    this.isSaving = true;
                    fetch(url, {
                        method: isEdit ? 'PUT' : 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.__csrfToken },
                        body: JSON.stringify(this.currentUser)
                    }).then(r => r.json()).then(data => {
                        this.isSaving = false;
                        if (data.success) { 
                            this.userModal = false; 
                            if (data.password && !isEdit) {
                                this.generatedPasswordAlert = {
                                    email: this.currentUser.email,
                                    password: data.password
                                };
                            } else {
                                this.showSuccess(data.message); 
                                setTimeout(() => window.location.reload(), 1000); 
                            }
                        }
                        else { this.showError(data.message || 'An error occurred'); }
                    }).catch(error => {
                        this.isSaving = false;
                        this.showError('A network error occurred. Please try again.');
                    });
                },

                showError(msg) {
                    showAlert('Error', msg, 'error');
                },

                toggleAll() {
                    const rows = Array.from(document.getElementById('usersTableBody').querySelectorAll('tr'))
                        .filter(tr => this.isUserVisible(tr.dataset.role, tr.dataset.name, tr.dataset.dept));
                    const ids = rows.map(tr => tr.dataset.id);
                    if (this.selectAll) { this.selectedUsers = [...new Set([...this.selectedUsers, ...ids])]; }
                    else { this.selectedUsers = this.selectedUsers.filter(id => !ids.includes(id)); }
                },

                submitBulk() {
                    if (!this.selectedUsers.length || !this.bulkValue) return;
                    fetch(window.__usersBulk, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.__csrfToken },
                        body: JSON.stringify({ user_ids: this.selectedUsers, field: this.bulkField, value: this.bulkValue })
                    }).then(r => r.json()).then(data => {
                        if (data.success) { this.bulkModal = false; this.selectedUsers = []; this.selectAll = false; this.showSuccess(data.message); setTimeout(() => window.location.reload(), 1000); }
                    });
                },

                isUserVisible(role, name, dept) {
                    return this.activeTab === role
                        && (!this.searchQuery || name.toLowerCase().includes(this.searchQuery.toLowerCase()))
                        && (this.deptFilter === 'all' || dept === this.deptFilter);
                },

                isGroupAllAllowed(permissions) {
                    return Object.keys(permissions).every(code => this.isCustomAllowed(code));
                },

                toggleAllInGroup(permissions) {
                    const allOn = this.isGroupAllAllowed(permissions);
                    Object.keys(permissions).forEach(code => {
                        this.toggleCustomStatus(code, !allOn);
                    });
                }
            };
        }
    </script>

    <div class="admin-content" x-data="usersManager()">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">User Management</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Manage teachers and students of {{ tenant('school_name') ?? 'your school' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('tenant.admin.roles') }}" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all flex items-center gap-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Roles & Permissions
                </a>
                <button class="px-5 py-2.5 bg-[var(--accent)] text-white rounded-xl text-sm font-bold hover:bg-[var(--accent-dark)] transition-all flex items-center gap-2 shadow-lg active:scale-95" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);"
                        @click="openAddModal()">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add New User
                </button>
            </div>
        </div>

        {{-- Tabs & Filters Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-2 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Tabs --}}
                <div class="flex items-center p-1 bg-gray-50 dark:bg-gray-900/50 rounded-xl w-fit overflow-x-auto custom-scrollbar whitespace-nowrap">
                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                            :class="activeTab === 'teachers' ? 'bg-white dark:bg-gray-800 text-[var(--accent)] shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                            @click="switchTab('teachers')">
                        Teachers <span class="ml-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-medium">{{ $roleCounts->get('teacher', 0) }}</span>
                    </button>
                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                            :class="activeTab === 'students' ? 'bg-white dark:bg-gray-800 text-[var(--accent)] shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                            @click="switchTab('students')">
                        Students <span class="ml-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-medium">{{ $roleCounts->get('student', 0) }}</span>
                    </button>
                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                            :class="activeTab === 'admins' ? 'bg-white dark:bg-gray-800 text-[var(--accent)] shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                            @click="switchTab('admins')">
                        Admins <span class="ml-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-medium">{{ $roleCounts->get('admin', 0) }}</span>
                    </button>
                    
                    @foreach($roleCounts->except(['admin', 'teacher', 'student']) as $role => $count)
                        <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all"
                                :class="activeTab === '{{ $role }}s' ? 'bg-white dark:bg-gray-800 text-[var(--accent)] shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                                @click="switchTab('{{ $role }}s')">
                            {{ ucfirst($role) }}s <span class="ml-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px] font-medium">{{ $count }}</span>
                        </button>
                    @endforeach

                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all relative"
                            :class="activeTab === 'pending' ? 'bg-white dark:bg-gray-800 text-[var(--accent)] shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                            @click="switchTab('pending')">
                        Pending Review
                        <span class="ml-1 px-1.5 py-0.5 bg-amber-100 text-amber-600 dark:bg-amber-900/30 rounded text-[10px] font-black">{{ $pendingCount }}</span>
                    </button>
                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all relative"
                            :class="activeTab === 'locked' ? 'bg-white dark:bg-gray-800 text-[var(--accent)] shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                            @click="switchTab('locked')">
                        Locked
                        <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 dark:bg-red-900/30 rounded text-[10px] font-black">{{ $lockedCount }}</span>
                    </button>
                    <button class="px-4 py-2 rounded-lg text-sm font-bold transition-all relative"
                            :class="activeTab === 'archived' ? 'bg-white dark:bg-gray-800 text-[var(--accent)] shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                            @click="switchTab('archived')">
                        Archived
                        <span class="ml-1 px-1.5 py-0.5 bg-gray-100 text-gray-600 dark:bg-gray-700 rounded text-[10px] font-black">{{ $archivedCount }}</span>
                    </button>
                </div>

                {{-- Quick Filters --}}
                <div class="flex items-center gap-3 px-2">
                    <div class="relative flex-1 md:w-64">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" x-model="searchQuery" @keydown.enter="applyFilters()" placeholder="Search users..." 
                               class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                    </div>
                    <select x-model="deptFilter" @change="applyFilters()" class="pl-4 pr-10 py-2 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                        <option value="all">All Departments/Colleges</option>
                        @foreach($colleges as $c)
                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div x-show="activeTab !== 'roles'" class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-x-auto custom-scrollbar" style="background: var(--bg-card); border-color: var(--border-color);">
            <table class="w-full text-left border-collapse min-w-[1000px]">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-gray-900/20 border-b border-gray-100 dark:border-gray-700" style="background: var(--bg-card); border-color: var(--border-color);">
                        <th class="w-12 px-6 py-4">
                            <div class="flex items-center justify-center">
                                <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded border-gray-300 text-[var(--accent)] focus:ring-[var(--accent)]">
                            </div>
                        </th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">User Profile</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Organization</th>
                        <th x-show="activeTab === 'students'" class="px-6 py-4 text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Academic Info</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody id="usersTableBody" class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @foreach($users as $user)
                        @php
                            $roleTab = $user->role . 's';
                            if ($user->status === 'pending' && !($user->deleted_at)) $roleTab = 'pending';
                            if ($user->deleted_at) $roleTab = 'archived';
                        @endphp
                        <tr x-show="activeTab === '{{ $roleTab }}'" 
                            data-role="{{ $roleTab }}" data-id="{{ $user->id }}" data-dept="{{ $user->department ?? $user->course }}" data-name="{{ $user->name }}"
                            class="hover:bg-[rgba(var(--accent-rgb),0.06)] dark:hover:bg-[rgba(var(--accent-rgb),0.14)] transition-all group {{ $user->deleted_at ? 'opacity-75' : '' }}">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" :value="{{ $user->id }}" x-model="selectedUsers" class="rounded border-gray-300 text-[var(--accent)] focus:ring-[var(--accent)]">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm overflow-hidden
                                        {{ $user->role === 'admin' ? 'bg-blue-600 text-white' : '' }}
                                        {{ $user->role === 'teacher' ? 'bg-[var(--accent)] text-white' : '' }}
                                        {{ $user->role === 'student' ? 'bg-blue-100 text-blue-600' : '' }}
                                        {{ !in_array($user->role, ['admin', 'teacher', 'student']) ? 'bg-amber-600 text-white' : '' }}
                                        {{ $user->deleted_at ? 'bg-gray-200 text-gray-500' : '' }}
                                    ">
                                        @if($user->profile_photo)
                                            <img src="{{ (function_exists('tenant_asset') && tenant()) ? tenant_asset($user->profile_photo) : asset('storage/' . $user->profile_photo) }}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML = '{{ strtoupper(substr($user->name, 0, 1)) }}'">
                                        @else
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-950 dark:text-white {{ $user->deleted_at ? 'line-through' : '' }}">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400 font-medium">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-slate-700 dark:text-slate-400 font-bold">{{ $user->school_name ?? (tenant('school_name') ?? 'N/A') }}</p>
                            </td>
                            
                            @if($activeTab === 'students')
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Member Since</span>
                                    <span class="text-[10px] font-semibold text-slate-600 dark:text-slate-400">{{ optional($user->created_at)->format('M Y') }}</span>
                                </div>
                            </td>
                            @endif

                            <td class="px-6 py-4">
                                @if($user->deleted_at)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold bg-gray-100 dark:bg-gray-900/30 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 uppercase tracking-wider">Archived</span>
                                @elseif($user->locked_until && \Carbon\Carbon::parse($user->locked_until)->isFuture())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/40 uppercase tracking-wider" title="Locked until {{ \Carbon\Carbon::parse($user->locked_until)->format('M d, Y h:i A') }}">Locked</span>
                                @elseif($user->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40 uppercase tracking-wider">Pending Review</span>
                                @else
                                    <button class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider {{ strtolower($user->status ?? 'active') === 'active' ? 'bg-[rgba(var(--accent-rgb),0.12)] text-[var(--accent)] border border-[rgba(var(--accent-rgb),0.28)]' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                        {{ $user->status ?? 'active' }}
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($user->deleted_at)
                                        <button @click.stop="confirmRestore({{ $user->id }})" class="px-3 py-1.5 bg-green-500 text-white text-[10px] font-black rounded-lg hover:bg-green-600 transition-all flex items-center gap-1">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                                            RESTORE
                                        </button>
                                        <button @click.stop="confirmForceDelete({{ $user->id }}, '{{ addslashes($user->name) }}')" class="px-3 py-1.5 bg-red-500 text-white text-[10px] font-black rounded-lg hover:bg-red-600 transition-all">DELETE FOREVER</button>
                                    @elseif($user->status === 'pending')
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
                                    @else
                                        <button @click.stop="openLockModal({{ json_encode(['id' => $user->id, 'name' => $user->name]) }})" class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all" title="Lock Account">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        </button>
                                        <button @click.stop='openEditModal({{ json_encode($user) }})' class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-[var(--accent)] hover:bg-[rgba(var(--accent-rgb),0.10)] rounded-lg transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        <button @click.stop="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')" class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $users->links() }}
            </div>

            @if($users->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                    <div class="w-14 h-14 bg-gray-50 dark:bg-gray-900/50 rounded-2xl flex items-center justify-center text-gray-300 mb-4">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" class="w-7 h-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">No users found</h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Try adjusting your filters or search query.</p>
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
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-modal-enter flex flex-col max-h-[90vh]"
                 @click.stop>

                {{-- Modal Header (always visible) --}}
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/50 bg-white dark:bg-gray-800 shrink-0">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white tracking-tight" x-text="modalTitle"></h3>
                        <button type="button" @click="userModal = false" class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600 transition-all flex items-center justify-center">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    {{-- Clickable Step Tabs --}}
                    <div class="flex gap-2">
                        <button type="button" @click="goToStep(1)"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all"
                            :class="userModalStep === 1
                                ? 'bg-[var(--accent)] text-white shadow-md'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600'">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[9px] font-black shrink-0"
                                  :class="userModalStep === 1 ? 'bg-white/20' : 'bg-gray-300 dark:bg-gray-600 text-gray-600'">1</span>
                            Basic Info
                        </button>
                        <button type="button" @click="goToStep(2)"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all"
                            :class="userModalStep === 2
                                ? 'bg-amber-500 text-white shadow-md'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600'">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[9px] font-black shrink-0"
                                  :class="userModalStep === 2 ? 'bg-white/20' : 'bg-gray-300 dark:bg-gray-600 text-gray-600'">2</span>
                            Access Rules
                        </button>
                    </div>
                </div>

                {{-- Scrollable body --}}
                <form id="userForm" @submit.prevent="saveUser()" class="overflow-y-auto flex-1 p-6 space-y-4 custom-scrollbar">
                    @csrf

                    {{-- Step 1 fields --}}
                    <div x-show="userModalStep === 1" class="space-y-4">
                        {{-- School Level Selection --}}
                        <div class="p-4 bg-slate-50 dark:bg-gray-900/50 rounded-2xl border border-slate-100 dark:border-gray-800/50">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">Educational Level</label>
                            <div class="grid grid-cols-4 gap-2">
                                <button type="button" @click="schoolLevel = 'elementary'; currentUser.department = ''; currentUser.course = ''; currentUser.year_level = ''"
                                    class="py-2 px-1 rounded-xl text-[10px] font-black uppercase tracking-tight transition-all border-2"
                                    :class="schoolLevel === 'elementary' ? 'border-[var(--accent)] bg-[var(--accent)] text-white shadow-md' : 'border-slate-200 dark:border-gray-700 text-slate-500 hover:border-slate-300'">
                                    Elementary
                                </button>
                                <button type="button" @click="schoolLevel = 'jhs'; currentUser.department = ''; currentUser.course = ''; currentUser.year_level = ''"
                                    class="py-2 px-1 rounded-xl text-[10px] font-black uppercase tracking-tight transition-all border-2"
                                    :class="schoolLevel === 'jhs' ? 'border-[var(--accent)] bg-[var(--accent)] text-white shadow-md' : 'border-slate-200 dark:border-gray-700 text-slate-500 hover:border-slate-300'">
                                    J. High
                                </button>
                                <button type="button" @click="schoolLevel = 'shs'; currentUser.department = ''; currentUser.course = ''; currentUser.year_level = ''"
                                    class="py-2 px-1 rounded-xl text-[10px] font-black uppercase tracking-tight transition-all border-2"
                                    :class="schoolLevel === 'shs' ? 'border-[var(--accent)] bg-[var(--accent)] text-white shadow-md' : 'border-slate-200 dark:border-gray-700 text-slate-500 hover:border-slate-300'">
                                    S. High
                                </button>
                                <button type="button" @click="schoolLevel = 'college'; currentUser.department = ''; currentUser.course = ''; currentUser.year_level = ''"
                                    class="py-2 px-1 rounded-xl text-[10px] font-black uppercase tracking-tight transition-all border-2"
                                    :class="schoolLevel === 'college' ? 'border-[var(--accent)] bg-[var(--accent)] text-white shadow-md' : 'border-slate-200 dark:border-gray-700 text-slate-500 hover:border-slate-300'">
                                    College
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800/50">
                            <div class="w-14 h-14 rounded-xl flex items-center justify-center text-white text-xl font-bold shadow-lg transition-all duration-300"
                                 :class="currentUser.role === 'admin' ? 'bg-blue-600 shadow-blue-500/20' : 'bg-[var(--accent)] shadow-[var(--accent)]/20'"
                                 x-text="currentUser.name ? currentUser.name.charAt(0) : 'U'"></div>
                            <div>
                                <p class="text-base font-bold text-gray-900 dark:text-white tracking-tight" x-text="currentUser.name || 'New User'"></p>
                                <p class="text-[10px] font-black uppercase tracking-widest"
                                   :class="currentUser.role === 'admin' ? 'text-blue-600' : 'text-[var(--accent)]'"
                                   x-text="currentUser.role"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-gray-600 dark:text-gray-400 ml-1">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" x-model="currentUser.name" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-semibold focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);" placeholder="Juan Dela Cruz">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-gray-600 dark:text-gray-400 ml-1">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" x-model="currentUser.email" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-semibold focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);" placeholder="juan@school.edu">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-gray-600 dark:text-gray-400 ml-1">Role <span class="text-red-500">*</span></label>
                                <select x-model="currentUser.role" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-semibold focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                                    <option value="teacher">Teacher</option>
                                    <option value="student">Student</option>
                                    <option value="admin" :disabled="!currentUser.id && checkLimit('admin')">Administrator (Limit: {{ tenant()->getLimit('admins') }})</option>
                                    @php $customRoles = \App\Models\TenantRole::whereNotIn('name', ['admin','teacher','student'])->get(); @endphp
                                    @foreach($customRoles as $customRole)
                                        <option value="{{ $customRole->name }}">{{ $customRole->display_name ?? ucfirst($customRole->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-gray-600 dark:text-gray-400 ml-1">Status <span class="text-red-500">*</span></label>
                                <select x-model="currentUser.status" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-semibold focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5" x-show="schoolLevel === 'college' || currentUser.role !== 'student'">
                                <label class="text-[10px] font-black text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">
                                    <span x-text="schoolLevel === 'college' ? 'College / Faculty' : (schoolLevel === 'shs' ? 'Department / Office' : 'Grade / Level Group')"></span>
                                    <span class="text-red-500">*</span>
                                </label>
                                <select x-model="currentUser.department"
                                        class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                                    <option value="">Select Option</option>
                                    <template x-for="c in filteredColleges" :key="c.id">
                                        <option :value="c.name" x-text="c.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div x-show="currentUser.role === 'teacher'" class="space-y-1.5">
                                <label class="text-[10px] font-black text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">
                                    Employee ID <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="currentUser.employee_id"
                                       class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                            </div>
                            <div x-show="currentUser.role === 'student'" class="space-y-1.5">
                                <label class="text-[10px] font-black text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">
                                    <span x-text="schoolLevel === 'college' ? 'Program / Course' : (schoolLevel === 'shs' ? 'Strand / Track' : 'Academic Group')"></span>
                                    <span x-show="schoolLevel === 'college' || schoolLevel === 'shs'" class="text-red-500">*</span>
                                </label>
                                <select x-model="currentUser.course"
                                        class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                                    <option value="">Select Option</option>
                                    <template x-for="p in filteredPrograms" :key="p.id">
                                        <option :value="p.name" x-text="p.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div x-show="currentUser.role === 'student'" class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">
                                    <span x-text="schoolLevel === 'college' ? 'Year Level' : 'Grade Level'"></span>
                                    <span class="text-red-500">*</span>
                                </label>
                                <select x-model="currentUser.year_level"
                                        class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                                    <option value="">Select Level</option>
                                    <template x-for="l in filteredLevels" :key="l.id">
                                        <option :value="l.name" x-text="l.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">
                                    Section <span class="text-red-500">*</span>
                                </label>
                                <select x-model="currentUser.section"
                                        class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl p-3 text-sm font-bold focus:ring-2 transition-all appearance-none" style="--tw-ring-color: rgba(var(--accent-rgb), 0.20);">
                                    <option value="">Select Section</option>
                                    <template x-for="s in filteredSections" :key="s.id">
                                        <option :value="s.name" x-text="s.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div x-show="!currentUser.id" class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-2xl border border-amber-100 dark:border-amber-900/30">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <label class="text-[10px] font-black text-amber-700 dark:text-amber-400 uppercase tracking-widest">Automatic Password</label>
                            </div>
                            <p class="text-[11px] text-amber-600 dark:text-amber-500 font-medium">A secure password will be automatically generated and sent to the user's Gmail address.</p>
                        </div>

                        {{-- Password Reset (edit only) --}}
                        <div x-show="currentUser.id" class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-100 dark:border-blue-900/30 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                                    </svg>
                                    <label class="text-[10px] font-black text-blue-700 dark:text-blue-400 uppercase tracking-widest">Reset Password</label>
                                </div>
                                <button type="button" @click="showPasswordReset = !showPasswordReset"
                                    class="text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg transition-all"
                                    :class="showPasswordReset ? 'bg-blue-200 text-blue-800 dark:bg-blue-800/40 dark:text-blue-300' : 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400 hover:bg-blue-200'"
                                    x-text="showPasswordReset ? 'Cancel' : 'Change Password'">
                                </button>
                            </div>

                            <div x-show="showPasswordReset" x-transition.opacity class="space-y-2.5">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-blue-600 dark:text-blue-400 ml-1">New Password</label>
                                    <div class="relative">
                                        <input :type="showPwd ? 'text' : 'password'"
                                            x-model="currentUser.new_password"
                                            class="w-full bg-white dark:bg-gray-900 border border-blue-200 dark:border-blue-800/50 rounded-xl p-3 pr-10 text-sm font-semibold focus:ring-2 transition-all"
                                            style="--tw-ring-color: rgba(59,130,246,0.25);"
                                            placeholder="Enter new password">
                                        <button type="button" @click="showPwd = !showPwd"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                                            <svg x-show="!showPwd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <svg x-show="showPwd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-blue-600 dark:text-blue-400 ml-1">Confirm New Password</label>
                                    <input :type="showPwd ? 'text' : 'password'"
                                        x-model="currentUser.new_password_confirmation"
                                        class="w-full bg-white dark:bg-gray-900 border border-blue-200 dark:border-blue-800/50 rounded-xl p-3 text-sm font-semibold focus:ring-2 transition-all"
                                        style="--tw-ring-color: rgba(59,130,246,0.25);"
                                        placeholder="Confirm new password">
                                </div>
                                <p class="text-[10px] text-blue-500 dark:text-blue-400 font-semibold leading-relaxed">
                                    Password will be updated immediately. The user will need to use the new password on their next login.
                                </p>
                            </div>
                        </div>
                    </div>


                    {{-- Step 2: Access & Permissions --}}
                    <div x-show="userModalStep === 2" class="space-y-5">
                        <div class="grid grid-cols-2 gap-3">
                            <div @click="permissionMode = 'role'; currentUser.custom_permissions = {granted:[], denied:[]}"
                                 class="cursor-pointer border-2 rounded-2xl p-4 transition-all"
                                 :class="permissionMode === 'role' ? 'border-[var(--accent)] bg-[rgba(var(--accent-rgb),0.04)] ring-4 ring-[var(--accent)]/10' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" :class="permissionMode === 'role' ? 'bg-[var(--accent)]' : 'bg-gray-200 dark:bg-gray-600'">
                                        <svg x-show="permissionMode === 'role'" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <h5 class="text-[12px] font-black text-gray-900 dark:text-white uppercase tracking-wide">Standard Role</h5>
                                </div>
                                <p class="text-[10px] text-gray-500 font-semibold leading-relaxed ml-6">Inherits all permissions from the <span class="text-[var(--accent)] font-black" x-text="currentUser.role"></span> role.</p>
                            </div>

                            <div @click="permissionMode = 'custom'"
                                 class="cursor-pointer border-2 rounded-2xl p-4 transition-all"
                                 :class="permissionMode === 'custom' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/10 ring-4 ring-amber-500/10' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" :class="permissionMode === 'custom' ? 'bg-amber-500' : 'bg-gray-200 dark:bg-gray-600'">
                                        <svg x-show="permissionMode === 'custom'" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <h5 class="text-[12px] font-black text-gray-900 dark:text-white uppercase tracking-wide">Custom Access</h5>
                                </div>
                                <p class="text-[10px] text-gray-500 font-semibold leading-relaxed ml-6">Manually pick exactly what this user can access.</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <p x-show="permissionMode === 'role'" class="text-[11px] font-black text-[var(--accent)] bg-[rgba(var(--accent-rgb),0.05)] border border-[rgba(var(--accent-rgb),0.1)] rounded-xl px-4 py-3 leading-relaxed">
                                Viewing standard rules for <strong class="uppercase" x-text="currentUser.role"></strong>. Switch to Custom Access above to modify.
                            </p>
                            <p x-show="permissionMode === 'custom'" class="text-[11px] font-black text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-900/40 rounded-xl px-4 py-3 leading-relaxed">
                                Toggles pre-loaded from the <strong class="uppercase" x-text="currentUser.role"></strong> role. Flip to override.
                            </p>
                                <template x-for="(permissions, group) in filteredPermissionsSchema" :key="group">
                                    <div class="bg-gray-50 dark:bg-gray-900/60 rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm"
                                         :class="permissionMode === 'role' ? 'opacity-80' : ''">
                                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-200 dark:border-gray-700">
                                            <h4 class="text-[11px] font-black text-gray-900 dark:text-gray-400 uppercase tracking-widest flex items-center gap-2" x-text="group"></h4>
                                            <button type="button" x-show="permissionMode === 'custom'"
                                                @click="toggleAllInGroup(permissions)"
                                                class="text-[9px] font-black uppercase tracking-widest px-3 py-1.5 rounded-xl transition-all"
                                                :class="isGroupAllAllowed(permissions)
                                                    ? 'bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-200'
                                                    : 'bg-[var(--accent)]/10 text-[var(--accent)] hover:bg-[var(--accent)]/20'">
                                                <span x-text="isGroupAllAllowed(permissions) ? 'Deselect Section' : 'Select Section'"></span>
                                            </button>
                                        </div>
                                        <div class="space-y-3">
                                            <template x-for="(label, code) in permissions" :key="code">
                                                <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-900 rounded-2xl border transition-all group/item"
                                                     :class="isCustomAllowed(code) ? 'border-[var(--accent)]/30 ring-2 ring-[var(--accent)]/5' : 'border-gray-100 dark:border-gray-800'">
                                                    <div class="flex flex-col gap-0.5">
                                                        <span class="text-sm font-black text-gray-800 dark:text-gray-200" x-text="label"></span>
                                                        <span class="text-[9px] font-mono text-gray-400 uppercase tracking-tight" x-text="code"></span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <template x-if="permissionMode === 'custom'">
                                                            <div class="flex items-center gap-2">
                                                                <button type="button" @click="toggleCustomStatus(code, true)"
                                                                        class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase transition-all"
                                                                        :class="isCustomAllowed(code) 
                                                                            ? 'bg-green-600 text-white shadow-lg shadow-green-600/20' 
                                                                            : 'bg-gray-100 dark:bg-gray-800 text-gray-400 hover:bg-green-50 hover:text-green-600'">
                                                                    ALLOW
                                                                </button>
                                                                <button type="button" @click="toggleCustomStatus(code, false)"
                                                                        class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase transition-all"
                                                                        :class="!isCustomAllowed(code) 
                                                                            ? 'bg-red-600 text-white shadow-lg shadow-red-600/20' 
                                                                            : 'bg-gray-100 dark:bg-gray-800 text-gray-400 hover:bg-red-50 hover:text-red-600'">
                                                                    DENY
                                                                </button>
                                                            </div>
                                                        </template>
                                                        <template x-if="permissionMode === 'role'">
                                                            <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
                                                                <div class="w-2 h-2 rounded-full" :class="isCustomAllowed(code) ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]' : 'bg-red-400'"></div>
                                                                <span class="text-[9px] font-black uppercase tracking-widest" 
                                                                      :class="isCustomAllowed(code) ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400'"
                                                                      x-text="isCustomAllowed(code) ? 'ENABLED' : 'DISABLED'"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                        </div>
                    </div>
                </form>

                {{-- Modal Footer (ALWAYS VISIBLE, outside scrollable form) --}}
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700/50 bg-white dark:bg-gray-800 shrink-0">
                    <div x-show="userModalStep === 1">
                        <button type="button" @click="goToStep(2)"
                            class="w-full py-3.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-2xl text-xs font-black shadow-lg hover:opacity-90 transition-all flex items-center justify-center gap-2 uppercase tracking-wider">
                            Next: Configure Access Rules
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </button>
                    </div>
                    <div x-show="userModalStep === 2" class="flex gap-3">
                        <button type="button" @click="goToStep(1)"
                            class="px-6 py-3.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-2xl text-xs font-black hover:bg-gray-200 transition-all uppercase tracking-wider">
                            Back
                        </button>
                        <button type="button" @click="saveUser()" :disabled="isSaving"
                            class="flex-1 py-3.5 bg-[var(--accent)] text-white rounded-2xl text-xs font-black shadow-lg hover:bg-[var(--accent-dark)] disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2 transition-all uppercase tracking-wider relative">
                            <span x-show="isSaving" class="absolute left-4">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </span>
                            <span x-text="isSaving ? 'Saving...' : 'Finalize & Save User'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </template>

        {{-- Generated Password Modal --}}
        <template x-teleport="body">
            <div x-show="generatedPasswordAlert" 
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="generatedPasswordAlert = null; window.location.reload();"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl overflow-hidden p-8 text-center animate-modal-enter border border-gray-100 dark:border-gray-700">
                    <div class="w-16 h-16 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-white dark:border-gray-800 shadow-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-black mb-2 dark:text-white">User Created!</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 text-sm">The user was successfully created. Here is the auto-generated password. Make sure to copy it!</p>
                    
                    <div class="bg-indigo-50/50 dark:bg-indigo-900/20 p-5 rounded-2xl text-left border border-indigo-100 dark:border-indigo-900/30 mb-6 flex justify-between items-center break-all shadow-inner relative group">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black text-indigo-400 dark:text-indigo-500 uppercase tracking-widest mb-1">Generated Password</span>
                            <span class="font-mono text-lg font-bold text-indigo-600 dark:text-indigo-400" 
                                  x-text="showGeneratedPassword ? generatedPasswordAlert?.password : '••••••••••••'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="showGeneratedPassword = !showGeneratedPassword" 
                                    class="p-2.5 bg-white dark:bg-gray-800 rounded-xl shadow-sm text-gray-500 hover:text-indigo-600 transition-all border border-gray-200 dark:border-gray-700">
                                <svg x-show="!showGeneratedPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showGeneratedPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                            <button type="button" 
                                    @click="navigator.clipboard.writeText(generatedPasswordAlert?.password); showSuccess('Password copied!')" 
                                    class="p-2.5 bg-white dark:bg-gray-800 rounded-xl shadow-sm text-gray-500 hover:text-indigo-600 transition-all border border-gray-200 dark:border-gray-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                            </button>
                        </div>
                    </div>
                    <button type="button" @click="generatedPasswordAlert = null; showGeneratedPassword = false; window.location.reload();" class="w-full py-4 bg-[var(--accent)] text-white rounded-2xl font-black text-sm uppercase tracking-wider shadow-lg hover:bg-[var(--accent-dark)] transition-all flex justify-center items-center gap-2">
                        Done & Close
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>
            </div>
        </template>

        {{-- Delete Modal (Soft Delete) --}}
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
                        <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight mb-2 uppercase">Archive User?</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-8">Are you sure you want to archive <span class="font-bold text-gray-900 dark:text-white" x-text="currentUser.name"></span>? They will no longer be able to log in.</p>
                        <div class="grid grid-cols-2 gap-4">
                            <button @click="deleteModal = false" class="py-4 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-[1.25rem] text-sm font-black hover:bg-gray-200 dark:hover:bg-gray-600 transition-all uppercase tracking-widest">Cancel</button>
                            <button @click="confirmDelete()" class="py-4 bg-red-500 text-white rounded-[1.25rem] text-sm font-black hover:bg-red-600 transition-all shadow-xl shadow-red-500/20 uppercase tracking-widest">Archive</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Force Delete Modal --}}
        <template x-teleport="body">
            <div x-show="forceDeleteModal" 
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="forceDeleteModal = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-modal-enter">
                    <div class="p-8 text-center">
                        <div class="w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center text-red-600 mx-auto mb-6">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight mb-2 uppercase">Delete Forever?</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-8">Are you sure you want to permanently delete <span class="font-bold text-gray-900 dark:text-white" x-text="currentUser.name"></span>? This action cannot be reversed.</p>
                        <div class="grid grid-cols-2 gap-4">
                            <button @click="forceDeleteModal = false" class="py-4 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-[1.25rem] text-sm font-black hover:bg-gray-200 dark:hover:bg-gray-600 transition-all uppercase tracking-widest">Wait, Cancel</button>
                            <button @click="submitForceDelete()" class="py-4 bg-red-600 text-white rounded-[1.25rem] text-sm font-black hover:bg-red-700 transition-all shadow-xl shadow-red-500/20 uppercase tracking-widest">Yes, Delete</button>
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
                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mb-6">Temporarily suspend access for <span class="font-bold text-gray-900 dark:text-white" x-text="currentUser.name"></span>.</p>
                        
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
            <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-[2rem] shadow-2xl p-4 flex items-center justify-between border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-4 pl-4">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center font-black text-sm">
                        <span x-text="selectedUsers.length"></span>
                    </div>
                    <div>
                        <p class="text-sm font-black uppercase tracking-widest text-gray-900 dark:text-white">Users Selected</p>
                        <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase">Ready for bulk action</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 pr-2">
                    <button @click="selectedUsers = []; selectAll = false" class="px-6 py-3 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">Cancel</button>
                    <button @click="bulkPermissionsSelect = []; bulkPermissionsModal = true" class="px-6 py-3 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 transition-all">Add Permissions</button>
                    <button @click="bulkModal = true" class="px-8 py-3 bg-[var(--accent)] text-white rounded-xl text-xs font-bold hover:opacity-90 shadow-lg shadow-[var(--accent)]/20 transition-all">Bulk Assign</button>
                </div>
            </div>
        </div>

        {{-- Bulk Assignment Modal --}}
        <template x-teleport="body">
            <div x-show="bulkPermissionsModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto" x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="bulkPermissionsModal = false"></div>
                <div class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-modal-enter">
                    <div class="p-8">
                        <div class="mb-6 flex justify-between items-start">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Add Bulk Permissions</h3>
                                <p class="text-xs text-gray-500 font-semibold mt-1">Applying new custom permissions to <span x-text="selectedUsers.length" class="text-indigo-600"></span> users</p>
                            </div>
                            <button @click="bulkPermissionsModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="space-y-6 max-h-[50vh] overflow-y-auto px-2 custom-scrollbar">
                            <template x-for="(perms, group) in filteredPermissionsSchema" :key="group">
                                <div class="mb-6">
                                    <h4 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3" x-text="group"></h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <template x-for="(label, code) in perms" :key="code">
                                            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors border border-transparent hover:border-indigo-100 dark:hover:border-indigo-900/30">
                                                <input type="checkbox" x-model="bulkPermissionsSelect" :value="code" class="w-5 h-5 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-600 focus:ring-2 disabled:opacity-50">
                                                <div class="flex-1">
                                                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100" x-text="label"></div>
                                                    <div class="text-[10px] font-mono text-gray-400 uppercase" x-text="code"></div>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="pt-6 border-t border-gray-100 dark:border-gray-700 mt-6 flex gap-4">
                            <button @click="bulkPermissionsModal = false" class="flex-1 py-4 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-black text-xs rounded-2xl hover:bg-gray-200 uppercase tracking-widest transition-all">Cancel</button>
                            <button @click="applyBulkPermissions()" :disabled="bulkPermissionsSelect.length === 0" class="flex-1 py-4 bg-indigo-600 text-white font-black text-xs rounded-2xl hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-indigo-600/20 uppercase tracking-widest transition-all">Apply to Selected</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

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
                                        @foreach($programs->merge($strands) as $p) <option value="{{ $p->name }}">{{ $p->name }}</option> @endforeach
                                    </template>

                                    <template x-if="bulkField === 'year_level'">
                                        @foreach($yearLevels->merge($gradeLevels) as $l) <option value="{{ $l->name }}">{{ $l->name }}</option> @endforeach
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
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="successModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-2xl flex items-center gap-4 animate-modal-enter w-full max-w-sm">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 text-green-600 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Success!</h4>
                        <p class="text-xs text-gray-500 font-medium" x-text="successMessage"></p>
                    </div>
                </div>
            </div>
        </template>    </div>

    <style>
    /* remove extra bottom divider under the last users row */
    #usersTableBody > tr:last-of-type > td {
        border-bottom: none !important;
    }
    </style>
</x-app-layout>