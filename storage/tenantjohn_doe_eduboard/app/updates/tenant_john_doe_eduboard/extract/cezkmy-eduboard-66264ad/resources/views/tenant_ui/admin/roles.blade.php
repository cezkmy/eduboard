<x-app-layout>
    <x-slot name="title">Roles & Permissions</x-slot>

    <div class="admin-content" x-data="rolesManager()" x-cloak>

        {{-- Back Button --}}
        <div class="mb-6">
            <a href="{{ route('tenant.admin.users') }}" class="inline-flex items-center gap-2 text-xs font-black text-gray-500 hover:text-gray-800 dark:hover:text-white uppercase tracking-widest transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                Back to User Management
            </a>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 items-start">
            
            <!-- Sidebar: Role Tabs -->
            <div class="w-full lg:w-80 shrink-0 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-8 px-2">
                        <h3 class="text-[11px] font-black text-gray-400 uppercase tracking-widest">User Types</h3>
                        <button @click="openCustomRoleModal()" class="text-[11px] font-black text-blue-600 hover:text-blue-700 uppercase tracking-tighter flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Add New
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <template x-for="(roleData, roleName) in tenantRoles" :key="roleName">
                            <div class="relative group">
                                <button @click="selectRole(roleName)" 
                                        class="w-full text-left px-6 py-5 rounded-2xl transition-all border-2 group relative shadow-sm" 
                                        :class="activeTab === roleName ? 'border-blue-500 bg-blue-50/30 text-blue-600' : 'border-transparent bg-white text-gray-500 hover:bg-gray-50'">
                                    <div class="text-lg font-black tracking-tight" x-text="roleData.display_name"></div>
                                    <div class="text-[9px] font-bold opacity-60 uppercase mt-0.5 tracking-widest" x-text="roleName"></div>
                                </button>
                                
                                <button x-show="!['admin', 'teacher', 'student'].includes(roleName)" 
                                        @click="deleteRole(roleName)"
                                        class="absolute top-1/2 -translate-y-1/2 right-4 text-gray-300 hover:text-red-500 p-2 rounded-lg transition-colors z-10"
                                        title="Delete Role">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="bg-blue-50/50 dark:bg-blue-900/10 rounded-2xl p-5 border border-blue-100/50">
                    <h4 class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1.5">Tip</h4>
                    <p class="text-[11px] text-blue-900/60 font-medium leading-relaxed">Switch between tabs to manage specific permissions for each role.</p>
                </div>
            </div>

            <!-- Main Container: Permissions for Selected Role -->
            <div class="flex-1 bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col min-h-[700px]">
                
                <!-- Container Header -->
                <div class="p-6 border-b border-gray-50 dark:border-gray-700 bg-gray-50/30">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white font-black text-xl shadow-xl shadow-blue-500/20 shrink-0">
                                <span x-text="activeTab.charAt(0).toUpperCase()"></span>
                            </div>
                            
                            <div class="space-y-1">
                                <h2 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight" x-text="activeTab + ' Permissions' "></h2>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">List of permissions and capabilities assigned to this role</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 lg:ml-auto">
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-3.5 w-3.5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <input type="text" x-model="permissionSearchQuery" placeholder="Search capabilities..." 
                                       class="pl-10 pr-5 py-2.5 bg-gray-50 border-none rounded-xl text-xs font-bold w-full lg:w-64 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                            </div>

                            <button @click="openManagePermissionsModal()"
                                    class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-gray-200 shadow-sm active:scale-95 transition-all flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Add Custom Permission
                            </button>
                            <button @click="saveRolePermissions()"
                                    class="px-5 py-2.5 bg-blue-600 text-white font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-blue-700 shadow-xl shadow-blue-500/20 active:scale-95 transition-all">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Container Body: Scrollable Permissions -->
                <div class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-gray-50/10">
                    <div class="space-y-10">
                        <template x-for="(permissions, group) in filteredPermissionsSchema" :key="group">
                            <div class="animate-fade-in">
                                <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">
                                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-[var(--accent)]"></span>
                                        <span x-text="group"></span>
                                    </h3>
                                    <button type="button" @click="toggleGroup(permissions)" 
                                            class="text-[9px] font-black uppercase tracking-widest px-3 py-1.5 rounded-xl transition-all"
                                            :class="isGroupSelected(permissions) ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-blue-50 text-blue-600 hover:bg-blue-100'">
                                        <span x-text="isGroupSelected(permissions) ? 'Deselect All' : 'Select All'"></span>
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                     <template x-for="(label, code) in permissions" :key="code">
                                         <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-900 rounded-[1.5rem] border-2 transition-all cursor-pointer group/item shadow-sm"
                                              :class="rolesTabState.permissions.includes(code)
                                                 ? 'border-[var(--accent)] bg-[rgba(var(--accent-rgb),0.02)] ring-4 ring-[var(--accent)]/5'
                                                 : 'border-gray-100 dark:border-gray-800 hover:border-gray-200'"
                                              @click="rolesTabState.permissions.includes(code)
                                                 ? (rolesTabState.permissions = rolesTabState.permissions.filter(p => p !== code))
                                                 : rolesTabState.permissions.push(code)">
                                             <div class="flex flex-col gap-1 min-w-0 pr-4">
                                                 <span class="text-sm font-black text-gray-900 dark:text-white leading-tight flex items-center gap-2">
                                                     <div class="w-1.5 h-1.5 rounded-full" :class="rolesTabState.permissions.includes(code) ? 'bg-[var(--accent)]' : 'bg-gray-300'"></div>
                                                     <span x-text="label"></span>
                                                 </span>
                                                 <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest pl-3.5" x-text="rolesTabState.permissions.includes(code) ? 'Permission Enabled' : 'Permission Disabled'"></p>
                                             </div>
                                             <div class="shrink-0">
                                                 <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all"
                                                      :class="rolesTabState.permissions.includes(code) ? 'bg-[var(--accent)] text-white' : 'bg-gray-100 text-gray-300 dark:bg-gray-800'">
                                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                         <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                     </svg>
                                                 </div>
                                             </div>
                                         </div>
                                     </template>
                                 </div>
                            </div>
                        </template>

                        <div x-show="Object.keys(filteredPermissionsSchema).length === 0" class="flex flex-col items-center justify-center py-32 text-center">
                            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-3xl flex items-center justify-center text-gray-300 mb-6">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">No Permissions Found</h3>
                            <p class="text-sm text-gray-500 mt-2 font-medium uppercase tracking-widest">Try a different search term</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Role Modal -->
        <div x-show="customRoleModal" class="fixed inset-0 z-[1000]" x-cloak>
            <div x-show="customRoleModal" x-transition.opacity.duration.300ms class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeCustomRoleModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div x-show="customRoleModal" x-transition.scale.95.duration.300ms class="pointer-events-auto relative bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden border border-gray-100 dark:border-gray-700 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">New Role</h3>
                        </div>
                        <button @click="closeCustomRoleModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-5">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Unique Key</label>
                            <input type="text" x-model="newRole.name" placeholder="e.g. staff" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Display Name</label>
                            <input type="text" x-model="newRole.display_name" placeholder="e.g. School Staff" class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                        </div>
                    </div>
                    <div class="flex gap-4 mt-10">
                        <button type="button" @click="closeCustomRoleModal()" class="flex-1 py-4 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-black text-[11px] rounded-2xl hover:bg-gray-200 transition-all uppercase tracking-widest">Cancel</button>
                        <button type="button" @click="saveCustomRole()" class="flex-1 py-4 bg-blue-600 text-white font-black text-[11px] rounded-2xl hover:bg-blue-700 shadow-xl shadow-blue-500/20 transition-all uppercase tracking-widest">Create</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Custom Permissions Modal -->
        <div x-show="managePermissionsModal" class="fixed inset-0 z-[1000]" x-cloak>
            <div x-show="managePermissionsModal" x-transition.opacity.duration.300ms class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeManagePermissionsModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div x-show="managePermissionsModal" x-transition.scale.95.duration.300ms class="pointer-events-auto relative bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden border border-gray-100 dark:border-gray-700 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Custom Permissions</h3>
                        </div>
                        <button @click="closeManagePermissionsModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-4 mb-6">
                        <div class="flex gap-2">
                            <select x-model="newPermissionGroup" class="flex-1 bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                                <option value="">-- Choose Existing Section --</option>
                                <template x-for="group in Object.keys(permissionsSchema)">
                                    <option :value="group" x-text="group"></option>
                                </template>
                                <option value="NEW_GROUP">+ Create New Section...</option>
                            </select>
                        </div>
                        <div class="flex gap-2" x-show="newPermissionGroup === 'NEW_GROUP'" x-cloak>
                            <input type="text" x-model="newPermissionGroupName" placeholder="Enter new section name" class="flex-1 bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                        </div>
                        <div class="flex gap-2">
                            <input type="text" x-model="newPermissionLabel" @keydown.enter="addCustomPermission()" placeholder="e.g. Can manage cafeteria" class="flex-1 bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                            <button type="button" @click="addCustomPermission()" class="bg-blue-600 text-white px-6 rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all">Add</button>
                        </div>
                    </div>

                    <div class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar pr-2 mb-8">
                        <template x-if="Object.keys(customPermissionsList).length === 0">
                            <div class="py-10 text-center flex flex-col items-center">
                                <div class="w-12 h-12 bg-gray-50 dark:bg-gray-800 rounded-xl flex items-center justify-center text-gray-400 mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                </div>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">No Custom Permissions</p>
                            </div>
                        </template>
                        <template x-for="(perm, code) in customPermissionsList" :key="code">
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800/50">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="text-[13px] font-black text-gray-900 dark:text-gray-200" x-text="perm.label"></p>
                                        <span class="text-[8px] font-black text-blue-600 bg-blue-100 dark:bg-blue-900/30 px-2 py-0.5 rounded-md uppercase tracking-wider" x-text="perm.group"></span>
                                    </div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase font-mono tracking-tight" x-text="code"></p>
                                </div>
                                <button type="button" @click="removeCustomPermission(code)" class="text-gray-400 hover:text-red-500 p-2 transition-colors" title="Delete Permission">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Confirmation Modal -->
        <div x-show="confirmModal" class="fixed inset-0 z-[2000]" x-cloak>
            <div x-show="confirmModal" x-transition.opacity.duration.300ms class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="confirmModal = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div x-show="confirmModal" x-transition.scale.95.duration.300ms class="pointer-events-auto relative bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-700 p-8 text-center ring-1 ring-black/5">
                    <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-[17px] font-black text-gray-900 dark:text-white uppercase tracking-tight mb-2" x-text="confirmConfig.title"></h3>
                    <p class="text-[12px] text-gray-500 font-bold leading-relaxed mb-8" x-text="confirmConfig.message"></p>
                    <div class="flex gap-3">
                        <button type="button" @click="confirmModal = false" class="flex-1 py-3.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-black text-[11px] rounded-2xl hover:bg-gray-200 transition-all uppercase tracking-widest">Cancel</button>
                        <button type="button" @click="confirmConfig.onConfirm()" :class="confirmConfig.actionColor" class="flex-1 py-3.5 text-white font-black text-[11px] rounded-2xl shadow-xl transition-all uppercase tracking-widest" x-text="confirmConfig.actionLabel"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function rolesManager() {
            return {
                permissionsSchema: @json($permissionsSchema),
                customPermissionsList: @json($customPermissionsList ?? (object)[]),
                tenantRoles: @json($tenantRoles),
                roleGroupMapping: @json($roleGroupMapping),
                activeTab: 'admin',
                permissionSearchQuery: '',
                rolesTabState: {
                    selectedRole: 'admin',
                    permissions: []
                },

                init() {
                    this.selectRole('admin');
                },

                selectRole(role) {
                    this.activeTab = role;
                    this.rolesTabState.selectedRole = role;
                    if (this.tenantRoles[role]) {
                        const perms = this.tenantRoles[role].permissions || [];
                        this.rolesTabState.permissions = Array.isArray(perms) ? [...perms] : [];
                    } else {
                        this.rolesTabState.permissions = [];
                    }
                },

                get filteredPermissionsSchema() {
                    const query = this.permissionSearchQuery.toLowerCase();
                    const filtered = {};
                    const allowedGroups = this.roleGroupMapping[this.activeTab] || Object.keys(this.permissionsSchema);
                    
                    for (const group in this.permissionsSchema) {
                        // Skip if group is not allowed for this role
                        if (!allowedGroups.includes(group)) continue;
                        
                        const groupFiltered = {};
                        for (const code in this.permissionsSchema[group]) {
                            const label = this.permissionsSchema[group][code];
                            if (!query || label.toLowerCase().includes(query) || code.toLowerCase().includes(query)) {
                                groupFiltered[code] = label;
                            }
                        }
                        if (Object.keys(groupFiltered).length > 0) {
                            filtered[group] = groupFiltered;
                        }
                    }
                    return filtered;
                },

                isGroupSelected(permissions) {
                    return Object.keys(permissions).every(code => this.rolesTabState.permissions.includes(code));
                },

                toggleGroup(permissions) {
                    const allSelected = this.isGroupSelected(permissions);
                    const codes = Object.keys(permissions);
                    
                    if (allSelected) {
                        // Deselect all in this group
                        this.rolesTabState.permissions = this.rolesTabState.permissions.filter(p => !codes.includes(p));
                    } else {
                        // Select all in this group (avoid duplicates)
                        const toAdd = codes.filter(c => !this.rolesTabState.permissions.includes(c));
                        this.rolesTabState.permissions.push(...toAdd);
                    }
                },

                saveRolePermissions() {
                    fetch('{{ route('tenant.admin.roles.permissions.update') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            role_name: this.rolesTabState.selectedRole,
                            permissions: this.rolesTabState.permissions
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.showSuccess(data.message);
                            this.tenantRoles[this.rolesTabState.selectedRole].permissions = [...this.rolesTabState.permissions];
                        } else {
                            this.showError(data.message || 'An error occurred while saving.');
                        }
                    });
                },

                showSuccess(msg) {
                    const successDiv = document.createElement('div');
                    successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3.5 rounded-xl shadow-2xl z-[999] animate-slide-in flex items-center gap-3 font-bold text-sm';
                    successDiv.innerHTML = `<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M5 13l4 4L19 7'/></svg><span>${msg}</span>`;
                    document.body.appendChild(successDiv);
                    setTimeout(() => successDiv.remove(), 4000);
                },

                showError(msg) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3.5 rounded-xl shadow-2xl z-[999] animate-slide-in flex items-center gap-3 font-bold text-sm';
                    errorDiv.innerHTML = `<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M6 18L18 6M6 6l12 12'/></svg><span>${msg}</span>`;
                    document.body.appendChild(errorDiv);
                    setTimeout(() => errorDiv.remove(), 4000);
                },

                confirmModal: false,
                confirmConfig: { title: '', message: '', actionLabel: '', actionColor: '', onConfirm: null },
                openConfirm(title, message, actionLabel, actionColor, callback) {
                    this.confirmConfig = { title, message, actionLabel, actionColor, onConfirm: () => { callback(); this.confirmModal = false; } };
                    this.confirmModal = true;
                },

                customRoleModal: false,
                newRole: { name: '', display_name: '', description: '', permissions: [] },
                openCustomRoleModal() { this.customRoleModal = true; this.newRole = { name: '', display_name: '', description: '', permissions: [] }; },
                closeCustomRoleModal() { this.customRoleModal = false; },
                saveCustomRole() {
                    if (!this.newRole.name || !this.newRole.display_name) { 
                        this.showError('Please fill in all required fields.'); 
                        return; 
                    }
                    fetch('{{ route('tenant.admin.roles.store') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(this.newRole)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.closeCustomRoleModal();
                            this.showSuccess(data.message);
                            setTimeout(() => window.location.reload(), 1000);
                        } else { 
                            this.showError(data.message || 'An error occurred while creating role.'); 
                        }
                    });
                },
                deleteRole(roleName) {
                    this.openConfirm(
                        'Delete User Role',
                        `Are you sure you want to permanently delete the role "${roleName}"? All users strictly assigned to this role may lose access.`,
                        'Delete Role',
                        'bg-red-600 hover:bg-red-700 shadow-red-500/20',
                        () => {
                            fetch(`/admin/roles/${roleName}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    this.showSuccess(data.message);
                                    setTimeout(() => window.location.reload(), 1000);
                                } else {
                                    this.showError(data.message || 'An error occurred while deleting role.');
                                }
                            });
                        }
                    );
                },
                managePermissionsModal: false,
                newPermissionLabel: '',
                newPermissionGroup: '',
                newPermissionGroupName: '',
                openManagePermissionsModal() { this.managePermissionsModal = true; },
                closeManagePermissionsModal() { this.managePermissionsModal = false; },
                addCustomPermission() {
                    if (!this.newPermissionLabel) return;
                    let targetGroup = this.newPermissionGroup;
                    if (targetGroup === 'NEW_GROUP') targetGroup = this.newPermissionGroupName;
                    
                    fetch('{{ route('tenant.admin.roles.permissions.custom.store') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ label: this.newPermissionLabel, group: targetGroup })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.showSuccess(data.message);
                            let g = data.permission.group;
                            if (!this.permissionsSchema[g]) {
                                this.permissionsSchema[g] = {};
                                Object.values(this.roleGroupMapping).forEach(arr => {
                                    if (!arr.includes(g)) arr.push(g);
                                });
                            }
                            this.permissionsSchema[g][data.permission.code] = data.permission.label;
                            this.customPermissionsList[data.permission.code] = { label: data.permission.label, group: g };
                            
                            this.newPermissionLabel = '';
                            this.newPermissionGroup = '';
                            this.newPermissionGroupName = '';
                            this.permissionsSchema = { ...this.permissionsSchema };
                        } else {
                            this.showError(data.message || 'An error occurred while adding permission.');
                        }
                    });
                },
                removeCustomPermission(code) {
                    this.openConfirm(
                        'Delete Custom Permission',
                        `Are you sure you want to permanently eliminate the permission "${code}"? Any active role referencing this will be affected.`,
                        'Delete Permission',
                        'bg-red-600 hover:bg-red-700 shadow-red-500/20',
                        () => {
                            fetch(`/admin/custom-permissions/${code}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    this.showSuccess(data.message);
                                    let g = this.customPermissionsList[code]?.group || 'Custom Capabilities';
                                    if (this.permissionsSchema[g]) {
                                        delete this.permissionsSchema[g][code];
                                        // Remove group entirely if empty and not a default base group
                                        if (Object.keys(this.permissionsSchema[g]).length === 0 && !['Dashboard', 'Category', 'Users', 'Reports', 'Settings', 'Subscription', 'Profile', 'Teacher Portal', 'Student Portal'].includes(g)) {
                                            delete this.permissionsSchema[g];
                                        }
                                    }
                                    delete this.customPermissionsList[code];
                                    
                                    this.permissionsSchema = { ...this.permissionsSchema };
                                    this.rolesTabState.permissions = this.rolesTabState.permissions.filter(p => p !== code);
                                } else {
                                    this.showError(data.message || 'An error occurred while deleting permission.');
                                }
                            });
                        }
                    );
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 20px; border: 2px solid transparent; background-clip: content-box; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; border: 2px solid transparent; background-clip: content-box; }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</x-app-layout>