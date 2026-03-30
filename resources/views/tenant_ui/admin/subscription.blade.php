<x-app-layout>
    <x-slot name="title">Subscription - Buksu Eduboard</x-slot>

    <div class="admin-content" x-data="{ 
        upgradeModal: false, 
        step: 'select', 
        selectedPlan: 'Pro',
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
                <button @click="upgradeModal = true" class="px-5 py-2.5 bg-teal-500 text-white rounded-xl text-sm font-bold hover:bg-teal-600 transition-all shadow-lg shadow-teal-500/20 active:scale-95">
                    Upgrade Plan
                </button>
            </div>
        </div>

        @php
            $currentPlanName = tenant()->plan;
            $currentPlanData = \App\Models\Plan::where('name', $currentPlanName)->first();
            
            $hierarchy = ['Basic' => 0, 'Free' => 0, 'Pro' => 1, 'Premium' => 1, 'Ultimate' => 2];
            $currentRank = $hierarchy[$currentPlanName] ?? 0;
            
            $adminLimit = tenant()->getLimit('admins_count') ?? '1';
            $teacherLimit = tenant()->getLimit('teachers_count') ?? '5';
            $isUnlimitedAdmin = $adminLimit == -1;
            $isUnlimitedTeacher = $teacherLimit == -1;
        @endphp

        {{-- Main Plan Card --}}
        <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 md:p-12">
            {{-- Background Decoration --}}
            <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-96 h-96 bg-teal-500/5 rounded-full blur-3xl"></div>
            
            <div class="relative flex flex-col md:flex-row gap-12 items-center">
                <div class="flex-1 space-y-6">
                    <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 text-xs font-black uppercase tracking-widest border border-teal-100 dark:border-teal-900/50">
                        Current Active Plan
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-5xl font-black text-gray-900 dark:text-white">{{ $currentPlanName }} Plan</h2>
                        @if(tenant()->expires_at)
                            <p class="text-gray-500 font-medium">Your subscription renews on <span class="text-gray-900 dark:text-gray-100 font-bold">{{ \Carbon\Carbon::parse(tenant()->expires_at)->format('F d, Y') }}</span></p>
                        @else
                            <p class="text-gray-500 font-medium">Your subscription is <span class="text-teal-600 font-bold">Active</span> indefinitely.</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4">
                        <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                            <div class="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 text-teal-600 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Admins</p>
                                <p class="text-lg font-black text-gray-900 dark:text-white">{{ $isUnlimitedAdmin ? 'Unlimited' : $adminLimit . ' Slots' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 text-blue-600 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Teachers</p>
                                <p class="text-lg font-black text-gray-900 dark:text-white">{{ $isUnlimitedTeacher ? 'Unlimited' : $teacherLimit . ' Slots' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-80 space-y-4">
                    <div class="p-6 bg-gray-900 rounded-3xl text-white space-y-6 shadow-xl">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold opacity-60">Monthly Total</span>
                            <span class="px-2 py-1 bg-white/10 rounded-lg text-[10px] font-black uppercase tracking-tighter">{{ $currentPlanData->period ?? 'Monthly' }}</span>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black">{{ $currentPlanData->price ?? 'Free' }}</span>
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
                <div class="p-6 {{ $plan->name === tenant()->plan ? 'bg-teal-500 rounded-3xl border-4 border-teal-500 shadow-xl shadow-teal-500/20 transform -translate-y-2' : 'bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 hover:border-teal-500/30 transition-all group' }}">
                    <div class="flex justify-between items-start">
                        <h4 class="text-sm font-black {{ $plan->name === tenant()->plan ? 'text-teal-100' : 'text-gray-400' }} uppercase tracking-widest">{{ $plan->name }}</h4>
                        @if($plan->name === tenant()->plan)
                            <span class="px-2 py-0.5 bg-white text-teal-500 text-[10px] font-black rounded-full">ACTIVE</span>
                        @endif
                    </div>
                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-2xl font-black {{ $plan->name === tenant()->plan ? 'text-white' : 'text-gray-900 dark:text-white' }}">{{ $plan->price }}</span>
                        @if($plan->period)
                            <span class="text-xs font-bold {{ $plan->name === tenant()->plan ? 'text-teal-100' : 'text-gray-500' }}">{{ $plan->period }}</span>
                        @endif
                    </div>
                    <ul class="mt-6 space-y-3 text-sm {{ $plan->name === tenant()->plan ? 'text-teal-50' : 'text-gray-500' }} font-medium">
                        @foreach($plan->features as $feature)
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 {{ $plan->name === tenant()->plan ? 'text-teal-200' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                    
                    @php
                        $planRank = $hierarchy[$plan->name] ?? 0;
                    @endphp
                    @if($plan->name === tenant()->plan)
                        <button class="mt-8 w-full py-3 bg-white text-teal-500 rounded-xl text-xs font-black">CURRENT PLAN</button>
                    @elseif($planRank < $currentRank)
                        <button class="mt-8 w-full py-3 bg-gray-50 dark:bg-gray-900 text-gray-400 rounded-xl text-xs font-black cursor-not-allowed">UNAVAILABLE</button>
                    @else
                        <button @click="upgradeModal = true; selectedPlan = '{{ $plan->name }}'; step = 'select';" class="mt-8 w-full py-3 bg-gray-900 text-white rounded-xl text-xs font-black hover:bg-black transition-all shadow-lg active:scale-95">UPGRADE NOW</button>
                    @endif
                </div>
                @endforeach
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
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Upgrade Your Experience</h3>
                        <button @click="upgradeModal = false; step = 'select';" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900/30 rounded-2xl flex gap-3">
                        <div class="text-amber-500 shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                        </div>
                        <p class="text-sm text-amber-700 dark:text-amber-500 font-medium">Upgrading will take effect immediately. Your next billing statement will be prorated.</p>
                    </div>
                    <div class="space-y-4">
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">Select Plan</p>
                        <div class="space-y-2">
                            <button @click="selectedPlan = 'Pro'" :class="{'border-teal-500 bg-teal-50/50 dark:bg-teal-900/20': selectedPlan === 'Pro', 'border-gray-100 dark:border-gray-700 hover:border-teal-500/50': selectedPlan !== 'Pro'}" class="w-full p-4 rounded-2xl border-2 transition-all flex items-center justify-between text-left">
                                <div>
                                    <p class="font-black text-gray-900 dark:text-white">Pro Plan</p>
                                    <p class="text-xs text-gray-500">Perfect for medium schools</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-black" :class="{'text-teal-600': selectedPlan === 'Pro', 'text-gray-900 dark:text-white': selectedPlan !== 'Pro'}">₱2,499</p>
                                </div>
                            </button>
                            <button @click="selectedPlan = 'Ultimate'" :class="{'border-teal-500 bg-teal-50/50 dark:bg-teal-900/20': selectedPlan === 'Ultimate', 'border-gray-100 dark:border-gray-700 hover:border-teal-500/50': selectedPlan !== 'Ultimate'}" class="w-full p-4 rounded-2xl border-2 transition-all flex items-center justify-between text-left">
                                <div>
                                    <p class="font-black text-gray-900 dark:text-white">Ultimate Plan</p>
                                    <p class="text-xs text-gray-500">For large universities</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-black" :class="{'text-teal-600': selectedPlan === 'Ultimate', 'text-gray-900 dark:text-white': selectedPlan !== 'Ultimate'}">₱4,999</p>
                                </div>
                            </button>
                        </div>
                    </div>
                    <button @click="processUpgrade()" class="w-full py-4 bg-teal-500 text-white rounded-2xl font-black shadow-xl shadow-teal-500/20 active:scale-95 transition-all">PROCEED TO CHECKOUT</button>
                </div>

                {{-- Step 2: Confirm --}}
                <div x-show="step === 'confirm'" class="space-y-6 text-center py-4" style="display: none;">
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white">Are you sure?</h3>
                    <p class="text-gray-500">Please confirm your payment details to upgrade to the <span class="font-bold text-gray-900 dark:text-white" x-text="selectedPlan + ' Plan'"></span>.</p>
                    
                    <div class="flex gap-4 mt-8">
                        <button @click="step = 'select'" class="flex-1 py-4 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-2xl font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">Cancel</button>
                        <button @click="confirmPayment()" class="flex-1 py-4 bg-teal-500 text-white rounded-2xl font-black shadow-xl shadow-teal-500/20 active:scale-95 transition-all w-full">Pay & Upgrade</button>
                    </div>
                </div>

                {{-- Step 3: Processing --}}
                <div x-show="step === 'processing'" class="space-y-6 text-center py-12" style="display: none;">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-teal-500 border-t-transparent"></div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white">Processing Payment...</h3>
                    <p class="text-gray-500 text-sm">Please securely wait and do not close this window.</p>
                </div>

                {{-- Step 4: Success --}}
                <div x-show="step === 'success'" class="space-y-6 text-center py-6" style="display: none;">
                    <div class="w-20 h-20 bg-teal-100 text-teal-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white">Payment Successful!</h3>
                    <p class="text-gray-500">Thank you for subscribing to the <span class="font-bold text-gray-900 dark:text-white" x-text="selectedPlan + ' Plan'"></span>. Your new features are unlocked!</p>
                    <button @click="finishUpgrade()" class="w-full mt-8 py-4 bg-teal-500 text-white rounded-2xl font-black shadow-xl shadow-teal-500/20 active:scale-95 transition-all">RETURN TO DASHBOARD</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>