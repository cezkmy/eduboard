<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Pending Approval - {{ tenant('school_name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .dark .glass-card {
            background: rgba(17, 24, 39, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-slate-50 dark:bg-gray-950 min-h-screen flex items-center justify-center p-6 selection:bg-emerald-500/30">
    {{-- Animated Background Objects --}}
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[10%] left-[10%] w-64 h-64 bg-emerald-500/10 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[20%] right-[10%] w-96 h-96 bg-blue-500/10 rounded-full blur-[100px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="w-full max-w-lg">
        <div class="glass-card rounded-[2.5rem] p-8 md:p-12 shadow-2xl shadow-emerald-500/5 relative overflow-hidden text-center">
            {{-- Icon --}}
            <div class="relative inline-flex mb-8">
                <div class="absolute inset-0 bg-emerald-500 blur-2xl opacity-20 animate-pulse"></div>
                <div class="relative w-20 h-20 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-3xl flex items-center justify-center shadow-xl shadow-emerald-500/20 rotate-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>

            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-4 tracking-tight">Pending Approval</h1>
            
            <div class="space-y-6">
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed font-medium">
                    Thank you for joining <span class="text-emerald-500 font-bold">{{ tenant('school_name') }}</span>! Your account has been successfully created and is currently awaiting administrator review.
                </p>

                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-6 border border-gray-100 dark:border-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        You will receive an email confirmation once your account has been approved. This usually takes less than 24 hours.
                    </p>
                </div>

                <div class="pt-6 flex flex-col gap-3">
                    <a href="{{ route('tenant.login') }}" class="w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-bold transition-all shadow-lg shadow-emerald-500/20 active:scale-95">
                        Back to Login
                    </a>
                    <form action="{{ route('tenant.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-sm font-bold text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors uppercase tracking-widest">
                            Log out current session
                        </button>
                    </form>
                </div>
            </div>

            {{-- Decorative bottom bar --}}
            <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 to-blue-500"></div>
        </div>

        <p class="mt-8 text-center text-gray-400 dark:text-gray-600 text-sm font-bold uppercase tracking-widest">
            &copy; {{ date('Y') }} EduBoard SaaS • Excellence in Education
        </p>
    </div>
</body>
</html>
