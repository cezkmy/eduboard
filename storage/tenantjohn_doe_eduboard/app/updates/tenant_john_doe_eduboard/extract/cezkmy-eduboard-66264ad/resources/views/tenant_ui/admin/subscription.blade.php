<x-app-layout>
    <x-slot name="title">Subscription - Buksu Eduboard</x-slot>

    <div class="admin-content" x-data="{ 
        upgradeModal: false, 
        storageModal: false,
        step: 'select', 
        selectedPlan: 'Pro',
        selectedStorage: 0,
        selectedStoragePrice: 0,
        isProcessing: false,
        processUpgrade() {
            this.step = 'confirm';
        },
        confirmPayment() {
            this.step = 'processing';
            this.isProcessing = true;
            
            setTimeout(() => {
                fetch('{{ route('tenant.admin.subscription.upgrade') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ plan: this.selectedPlan })
                }).then(res => res.json())
                .then(data => {
                    this.step = 'success';
                    this.isProcessing = false;
                });
            }, 2500);
        },
        confirmStoragePayment() {
            this.step = 'processing';
            this.isProcessing = true;
            
            setTimeout(() => {
                fetch('{{ route('tenant.admin.storage.purchase') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ gb: this.selectedStorage, price: this.selectedStoragePrice })
                }).then(res => res.json())
                .then(data => {
                    this.step = 'success';
                    this.isProcessing = false;
                });
            }, 2500);
        },

        finishUpgrade() {
            window.location.reload();
        }
    }">
        
        @php
            $tenant = tenant();
            $isDeactivated = $tenant->status === 'Deactivated';
            $expiresAt = $tenant->expires_at ? \Carbon\Carbon::parse($tenant->expires_at) : null;
            $isExpired = $expiresAt && $expiresAt->isPast();
            
            $daysLeft = 3;
            if ($isExpired) {
                // Countdown logic (e.g. 3 days max)
                $gracePeriodEnd = $expiresAt->copy()->addDays(3);
                $daysLeft = max(0, ceil(now()->diffInDays($gracePeriodEnd, false)));
            }

            // Moved from below to fix "Undefined variable $currentRank"
            $currentPlanName = $tenant->plan;
            $currentPlanData = \App\Models\Plan::where('name', $currentPlanName)->first();
            
            $hierarchy = ['Basic' => 0, 'Free' => 0, 'Pro' => 1, 'Premium' => 1, 'Ultimate' => 2];
            $currentRank = $hierarchy[$currentPlanName] ?? 0;
            
            $adminLimit = $tenant->getLimit('admins') ?? 1;
            $teacherLimit = $tenant->getLimit('teachers') ?? 5;
            $isUnlimitedAdmin = $adminLimit == -1;
            $isUnlimitedTeacher = $teacherLimit == -1;

            // Usage calculation
            $currentAdmins = \App\Models\User::where('role', 'admin')->count();
            $currentTeachers = \App\Models\User::where('role', 'teacher')->count();
            
            $adminsLeft = $isUnlimitedAdmin ? 'Unlimited' : max(0, $adminLimit - $currentAdmins);
            $teachersLeft = $isUnlimitedTeacher ? 'Unlimited' : max(0, $teacherLimit - $currentTeachers);

            // Plan limits for modal display
            $allPlanLimits = [
                'Basic' => ['admins' => 1, 'teachers' => 5],
                'Pro' => ['admins' => 5, 'teachers' => 15],
                'Ultimate' => ['admins' => 10, 'teachers' => 'Unlimited'],
            ];
        @endphp

        @if($isDeactivated)
            <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl mb-8 flex items-center justify-between gap-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 text-red-500 rounded-xl shrink-0">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    </div>
                    <div>
                        <p class="font-black text-lg leading-none mb-1">Domain Manually Deactivated</p>
                        <p class="text-sm font-medium text-red-600">Your school's access has been manually disabled by an administrator. Please contact central support for assistance.</p>
                    </div>
                </div>
            </div>
        @elseif($isExpired)
            @php 
                $isBasic = strtolower($tenant->plan) === 'basic' || strtolower($tenant->plan) === 'free';
            @endphp
            <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl mb-8 flex items-center justify-between gap-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 text-red-500 rounded-xl shrink-0">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    </div>
                    <div>
                        @if($isBasic)
                            <p class="font-black text-lg leading-none mb-1">Free Trial Concluded!</p>
                            <p class="text-sm font-medium text-red-600 mt-2">
                                Your 15-day Free Basic Plan trial period has officially ended, and administrative access has been temporarily restricted. 
                                To maintain uninterrupted access to EduBoard's management tools, student portals, and teacher dashboards, you must upgrade your school to a <strong>Pro</strong> or <strong>Ultimate</strong> subscription. <br><br>
                                Failure to upgrade within the grace period will result in your school's domain going completely offline, locking out all enrolled students and faculty members. <strong>Your domain will completely deactivate in <span class="font-black">{{ $daysLeft }} day(s)!</span></strong>
                            </p>
                        @else
                            <p class="font-black text-lg leading-none mb-1">Subscription Expired!</p>
                            <p class="text-sm font-medium text-red-600 mt-2">
                                Your school's {{ $tenant->plan ?? 'current' }} billing cycle has concluded, and your subscription is currently expired. 
                                All administrative features, teacher workflows, and student portals have been temporarily restricted to protect your account. <br><br>
                                To restore full operational access and prevent any disruption to your academic activities, please renew your subscription immediately. <strong>Your entire school domain will completely deactivate and go offline in <span class="font-black">{{ $daysLeft }} day(s)!</span></strong>
                            </p>
                        @endif
                    </div>
                </div>
                <button @click="upgradeModal = true" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-bold shadow-md hover:bg-red-700 active:scale-95 transition-all whitespace-nowrap">
                    {{ $isBasic ? 'Upgrade Plan' : 'Renew Plan' }}
                </button>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">Subscription Plan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Manage your school's current plan and billing details</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="px-5 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-bold hover:bg-gray-200 transition-all active:scale-95">
                    Billing History
                </button>
                <button @click="upgradeModal = true" class="px-5 py-2.5 bg-[var(--accent)] text-white rounded-xl text-sm font-bold hover:bg-[var(--accent-dark)] transition-all shadow-lg active:scale-95" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);">
                    {{ $currentRank >= 2 ? 'Manage Plan' : 'Upgrade Plan' }}
                </button>
            </div>
        </div>

        {{-- Main Plan Card --}}
        <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 md:p-12">
            {{-- Background Decoration --}}
            <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-96 h-96 rounded-full blur-3xl" style="background: rgba(var(--accent-rgb), 0.05);"></div>
            
            <div class="relative flex flex-col md:flex-row gap-12 items-center">
                <div class="flex-1 space-y-6">
                    <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-[rgba(var(--accent-rgb),0.10)] text-[var(--accent)] text-xs font-black uppercase tracking-widest" style="border: 1px solid rgba(var(--accent-rgb), 0.25);">
                        Current Active Plan
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-5xl font-black text-gray-900 dark:text-white">{{ $currentPlanName }} Plan</h2>
                        @if(tenant()->expires_at)
                            <p class="text-gray-500 font-medium">Your subscription renews on <span class="text-gray-900 dark:text-gray-100 font-bold">{{ \Carbon\Carbon::parse(tenant()->expires_at)->format('F d, Y') }}</span></p>
                        @else
                            <p class="text-gray-500 font-medium">Your subscription is <span class="text-[var(--accent)] font-bold">Active</span> indefinitely.</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4">
                        <div class="flex items-center gap-4 p-5 bg-gray-50 dark:bg-gray-900/50 rounded-[2rem] border border-gray-100 dark:border-gray-800 transition-all hover:shadow-md">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-[var(--accent)] shadow-inner" style="background: rgba(var(--accent-rgb), 0.16);">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Admin Slots Available</p>
                                <div class="flex items-baseline gap-2">
                                    <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $adminsLeft }}</p>
                                    <p class="text-[10px] font-bold text-gray-500">Available</p>
                                </div>
                                <div class="w-24 bg-gray-200 dark:bg-gray-700 h-1 rounded-full mt-2 overflow-hidden">
                                    <div class="bg-[var(--accent)] h-full rounded-full" style="width: {{ $isUnlimitedAdmin ? 0 : ($currentAdmins / $adminLimit) * 100 }}%"></div>
                                </div>
                                <p class="text-[9px] font-medium text-gray-400 mt-1">{{ $currentAdmins }} used of {{ $isUnlimitedAdmin ? '∞' : $adminLimit }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 p-5 bg-gray-50 dark:bg-gray-900/50 rounded-[2rem] border border-gray-100 dark:border-gray-800 transition-all hover:shadow-md">
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 rounded-2xl flex items-center justify-center shadow-inner">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Teacher Slots Available</p>
                                <div class="flex items-baseline gap-2">
                                    <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $teachersLeft }}</p>
                                    <p class="text-[10px] font-bold text-gray-500">Available</p>
                                </div>
                                <div class="w-24 bg-gray-200 dark:bg-gray-700 h-1 rounded-full mt-2 overflow-hidden">
                                    <div class="bg-blue-500 h-full rounded-full" style="width: {{ $isUnlimitedTeacher ? 0 : ($currentTeachers / $teacherLimit) * 100 }}%"></div>
                                </div>
                                <p class="text-[9px] font-medium text-gray-400 mt-1">{{ $currentTeachers }} used of {{ $isUnlimitedTeacher ? '∞' : $teacherLimit }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Server Storage Monitor --}}
                    @php
                        $usedGB = tenant()->updateStorageUsage();
                        $limitGB = tenant()->storage_limit_gb ?? 5.0;
                        $percent = $limitGB > 0 ? min(100, ($usedGB / $limitGB) * 100) : 0;
                        $progressColor = $percent > 90 ? 'bg-red-500' : ($percent > 75 ? 'bg-yellow-400' : 'bg-[var(--accent)]');
                    @endphp
                    
                    <div class="mt-6">
                        <div class="p-5 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800 flex flex-col justify-between h-full">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm14 1a1 1 0 11-2 0 1 1 0 012 0zM2 13a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2zm14 1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" /></svg>
                                        Server Storage
                                    </h4>
                                    <span class="text-[10px] font-bold text-gray-500">{{ number_format($percent, 1) }}% Consumed</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-2 overflow-hidden">
                                    <div class="{{ $progressColor }} h-2 rounded-full transition-all duration-1000" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                            <p class="text-xs font-bold text-gray-500 mt-1"><span class="text-gray-900 dark:text-white">{{ number_format($usedGB, 2) }} GB</span> / {{ number_format($limitGB, 2) }} GB Limit</p>
                        </div>

                    </div>
                </div>

                <div class="w-full md:w-80 space-y-4">
                    <div class="p-6 bg-white dark:bg-gray-900 rounded-3xl space-y-6 shadow-xl border border-gray-100 dark:border-gray-800 transition-colors">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-gray-500 dark:text-gray-400">Monthly Total</span>
                            <span class="px-2 py-1 bg-gray-100 dark:bg-white/10 rounded-lg text-[10px] font-black text-gray-900 dark:text-white uppercase tracking-tighter">{{ $currentPlanData->period ?? 'Monthly' }}</span>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-gray-900 dark:text-white">{{ $currentPlanData->price ?? 'Free' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Plan Comparison (Quick View) --}}
        <div class="mt-12">
            <h3 class="text-lg font-black text-gray-900 dark:text-white mb-6">Explore Other Plans</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($plans as $plan)
                <div class="p-6 {{ $plan->name === tenant()->plan ? 'rounded-3xl border-4 shadow-xl transform -translate-y-2' : 'bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 transition-all group' }}"
                     style="{{ $plan->name === tenant()->plan ? 'background: var(--accent); border-color: var(--accent); box-shadow: 0 20px 45px rgba(var(--accent-rgb), 0.25);' : '' }}">
                    <div class="flex justify-between items-start">
                        <h4 class="text-sm font-black {{ $plan->name === tenant()->plan ? 'text-white/80' : 'text-gray-400' }} uppercase tracking-widest">{{ $plan->name }}</h4>
                        @if($plan->name === tenant()->plan)
                            <span class="px-2 py-0.5 bg-white text-[var(--accent)] text-[10px] font-black rounded-full">ACTIVE</span>
                        @endif
                    </div>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-2xl font-black {{ $plan->name === tenant()->plan ? 'text-white' : 'text-gray-900 dark:text-white' }}">{{ $plan->price }}</span>
                        @if($plan->period)
                            <span class="text-xs font-bold {{ $plan->name === tenant()->plan ? 'text-white/80' : 'text-gray-500' }}">{{ $plan->period }}</span>
                        @endif
                    </div>
                    <ul class="mt-6 space-y-3 text-sm {{ $plan->name === tenant()->plan ? 'text-white/90' : 'text-gray-500' }} font-medium">
                        @foreach($plan->features as $feature)
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 {{ $plan->name === tenant()->plan ? 'text-white/90' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                    
                    @php
                        $planRank = $hierarchy[$plan->name] ?? 0;
                    @endphp
                    @if($plan->name === tenant()->plan)
                        <button class="mt-8 w-full py-3 bg-white text-[var(--accent)] rounded-xl text-xs font-black">CURRENT PLAN</button>
                    @elseif($planRank < $currentRank)
                        <button class="mt-8 w-full py-3 bg-gray-50 dark:bg-gray-900 text-gray-400 rounded-xl text-xs font-black cursor-not-allowed">UNAVAILABLE</button>
                    @else
                        <button @click="upgradeModal = true; selectedPlan = '{{ $plan->name }}'; step = 'select';" class="mt-8 w-full py-3 bg-gray-900 text-white rounded-xl text-xs font-black hover:bg-black transition-all shadow-lg active:scale-95">UPGRADE NOW</button>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Storage Add-ons --}}
        <div class="mt-16 mb-8">
            <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2">Storage Add-ons</h3>
            <p class="text-sm text-gray-500 mb-6">Need more space for your announcements and media? Get instant expansion.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- +3GB -->
                <div class="p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6h16v10a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" /><path fill-rule="evenodd" d="M10 2a2 2 0 00-2 2h4a2 2 0 00-2-2z" clip-rule="evenodd" /></svg>
                        </div>
                        <h4 class="text-xl font-black text-gray-900 dark:text-white">+3GB Storage</h4>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-2xl font-black text-gray-900 dark:text-white">₱49</span>
                            <span class="text-xs font-bold text-gray-500">/one-time</span>
                        </div>
                    </div>
                    <button @click="storageModal = true; selectedStorage = 3; selectedStoragePrice = 49; step = 'confirm';" class="mt-8 w-full py-3 bg-[rgba(var(--accent-rgb),0.1)] text-[var(--accent)] font-black hover:bg-[var(--accent)] hover:text-white rounded-xl text-xs transition-all active:scale-95">BUY +3GB NOW</button>
                </div>
                <!-- +6GB -->
                <div class="p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-yellow-400 text-yellow-900 text-[10px] font-black px-3 py-1 rounded-bl-xl">POPULAR</div>
                    <div>
                        <div class="w-12 h-12 bg-[rgba(var(--accent-rgb),0.1)] text-[var(--accent)] border border-[rgba(var(--accent-rgb),0.2)] rounded-2xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6h16v10a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" /><path fill-rule="evenodd" d="M10 2a2 2 0 00-2 2h4a2 2 0 00-2-2z" clip-rule="evenodd" /></svg>
                        </div>
                        <h4 class="text-xl font-black text-gray-900 dark:text-white">+6GB Storage</h4>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-2xl font-black text-gray-900 dark:text-white">₱99</span>
                            <span class="text-xs font-bold text-gray-500">/one-time</span>
                        </div>
                    </div>
                    <button @click="storageModal = true; selectedStorage = 6; selectedStoragePrice = 99; step = 'confirm';" class="mt-8 w-full py-3 bg-[var(--accent)] text-white hover:bg-[var(--accent-dark)] font-black shadow-lg rounded-xl text-xs transition-all active:scale-95" style="box-shadow: 0 10px 20px rgba(var(--accent-rgb), 0.2);">BUY +6GB NOW</button>
                </div>
                <!-- +9GB -->
                <div class="p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6h16v10a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" /><path fill-rule="evenodd" d="M10 2a2 2 0 00-2 2h4a2 2 0 00-2-2z" clip-rule="evenodd" /></svg>
                        </div>
                        <h4 class="text-xl font-black text-gray-900 dark:text-white">+9GB Storage</h4>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-2xl font-black text-gray-900 dark:text-white">₱149</span>
                            <span class="text-xs font-bold text-gray-500">/one-time</span>
                        </div>
                    </div>
                    <button @click="storageModal = true; selectedStorage = 9; selectedStoragePrice = 149; step = 'confirm';" class="mt-8 w-full py-3 bg-[rgba(var(--accent-rgb),0.1)] text-[var(--accent)] font-black hover:bg-[var(--accent)] hover:text-white rounded-xl text-xs transition-all active:scale-95">BUY +9GB NOW</button>
                </div>
            </div>
        </div>



        {{-- Upgrade Modal --}}
        <div x-show="upgradeModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="upgradeModal = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-8 max-w-lg w-full space-y-6 transform transition-all"
                 x-show="upgradeModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                 
                {{-- Step 1: Select Plan --}}
                <div x-show="step === 'select'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white">Manage Subscription</h3>
                            <p class="text-xs text-gray-500 mt-1">You are currently on the <span class="font-black text-[var(--accent)]">{{ $currentPlanName }} Plan</span></p>
                        </div>
                        <button @click="upgradeModal = false; step = 'select';" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    
                    @if($currentRank < 2)
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900/30 rounded-2xl flex gap-3">
                        <div class="text-amber-500 shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                        </div>
                        <p class="text-sm text-amber-700 dark:text-amber-500 font-medium">Upgrading will take effect immediately. Your next billing statement will be adjusted to reflect your new capabilities.</p>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">Select Your New Plan</p>
                        <div class="space-y-2">
                            @foreach($plans as $plan)
                                @php $planRank = $hierarchy[$plan->name] ?? 0; @endphp
                                @if($planRank > $currentRank)
                                    <button @click="selectedPlan = '{{ $plan->name }}'" 
                                            :class="{'border-[var(--accent)] bg-[rgba(var(--accent-rgb),0.10)]': selectedPlan === '{{ $plan->name }}', 'border-gray-100 dark:border-gray-700': selectedPlan !== '{{ $plan->name }}'}" 
                                            class="w-full p-4 rounded-2xl border-2 transition-all flex items-center justify-between text-left">
                                        <div>
                                            <p class="font-black text-gray-900 dark:text-white">{{ $plan->name }} Plan</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded-md font-bold text-gray-600 dark:text-gray-400">
                                                    {{ $allPlanLimits[$plan->name]['admins'] }} Admins
                                                </span>
                                                <span class="text-[10px] px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded-md font-bold text-gray-600 dark:text-gray-400">
                                                    {{ $allPlanLimits[$plan->name]['teachers'] }} Teachers
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-black" :class="{'text-[var(--accent)]': selectedPlan === '{{ $plan->name }}', 'text-gray-900 dark:text-white': selectedPlan !== '{{ $plan->name }}'}">{{ $plan->price }}</p>
                                        </div>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <button @click="processUpgrade()" class="w-full py-4 bg-[var(--accent)] text-white rounded-2xl font-black shadow-xl active:scale-95 transition-all hover:bg-[var(--accent-dark)]" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.25);">CONFIRM SELECTION</button>
                    @else
                    <div class="py-8 text-center space-y-4">
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white">You're on the Top Tier!</h4>
                        <p class="text-sm text-gray-500">You are already subscribed to our Ultimate plan. You have access to all features and maximum limits.</p>
                        <button @click="upgradeModal = false" class="w-full py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-xl font-bold">Close</button>
                    </div>
                    @endif
                </div>

                {{-- Step 2: Confirm --}}
                <div x-show="step === 'confirm'" class="space-y-6 text-center py-4" style="display: none;">
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white">Are you sure?</h3>
                    <p class="text-gray-500">Please confirm your payment details to upgrade to the <span class="font-bold text-gray-900 dark:text-white" x-text="selectedPlan + ' Plan'"></span>.</p>
                    
                    <div class="flex gap-4 mt-8">
                        <button @click="step = 'select'" class="flex-1 py-4 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-2xl font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">Cancel</button>
                        <button @click="confirmPayment()" class="flex-1 py-4 bg-[var(--accent)] text-white rounded-2xl font-black shadow-xl active:scale-95 transition-all w-full hover:bg-[var(--accent-dark)]" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.25);">Pay & Upgrade</button>
                    </div>
                </div>

                {{-- Step 3: Processing --}}
                <div x-show="step === 'processing'" class="space-y-6 text-center py-12" style="display: none;">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-t-transparent" style="border-color: var(--accent); border-top-color: transparent;"></div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white">Processing Payment...</h3>
                    <p class="text-gray-500 text-sm">Please securely wait and do not close this window.</p>
                </div>

                {{-- Step 4: Success --}}
                <div x-show="step === 'success'" class="space-y-6 text-center py-6" style="display: none;">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-[var(--accent)]" style="background: rgba(var(--accent-rgb), 0.16);">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white">Payment Successful!</h3>
                    <p class="text-gray-500">Thank you for subscribing to the <span class="font-bold text-gray-900 dark:text-white" x-text="selectedPlan + ' Plan'"></span>. Your new features are unlocked!</p>
                    <button @click="finishUpgrade()" class="w-full mt-8 py-4 bg-[var(--accent)] text-white rounded-2xl font-black shadow-xl active:scale-95 transition-all hover:bg-[var(--accent-dark)]" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.25);">RETURN TO DASHBOARD</button>
                </div>
            </div>
        </div>

        {{-- Storage Modal --}}
        <div x-show="storageModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="if(!isProcessing) storageModal = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-8 max-w-lg w-full space-y-6 transform transition-all"
                 x-show="storageModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                 
                {{-- Step 2: Confirm --}}
                <div x-show="step === 'confirm'" class="space-y-6 text-center py-4">
                    <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 text-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                         <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6h16v10a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" /><path fill-rule="evenodd" d="M10 2a2 2 0 00-2 2h4a2 2 0 00-2-2z" clip-rule="evenodd" /></svg>
                    </div>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white">+<span x-text="selectedStorage"></span>GB Storage Expansion</h3>
                    <p class="text-gray-500">Please confirm your payment of <strong class="text-gray-900 dark:text-white">₱<span x-text="selectedStoragePrice"></span></strong> to permanently upgrade your school storage capacity.</p>
                    
                    <div class="flex gap-4 mt-8">
                        <button @click="storageModal = false" class="flex-1 py-4 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-2xl font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">Cancel</button>
                        <button @click="confirmStoragePayment()" class="flex-1 py-4 bg-[var(--accent)] text-white rounded-2xl font-black shadow-xl active:scale-95 transition-all w-full hover:bg-[var(--accent-dark)]" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.25);">Pay & Expand</button>
                    </div>
                </div>

                {{-- Step 3: Processing --}}
                <div x-show="step === 'processing'" class="space-y-6 text-center py-12" style="display: none;">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-t-transparent" style="border-color: var(--accent); border-top-color: transparent;"></div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white">Processing Secure Payment...</h3>
                    <p class="text-gray-500 text-sm">Please do not refresh this page.</p>
                </div>

                {{-- Step 4: Success --}}
                <div x-show="step === 'success'" class="space-y-6 text-center py-6" style="display: none;">
                    <div class="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white">Storage Expanded!</h3>
                    <p class="text-gray-500">Success! You have added <strong class="text-gray-900 dark:text-white">+<span x-text="selectedStorage"></span>GB</strong> to your total capacity. Your invoice has been saved.</p>
                    <button @click="finishUpgrade()" class="w-full mt-8 py-4 bg-[var(--accent)] text-white rounded-2xl font-black shadow-xl active:scale-95 transition-all hover:bg-[var(--accent-dark)]" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.25);">RETURN TO DASHBOARD</button>
                </div>
            </div>
        </div>


    </div>
</x-app-layout>