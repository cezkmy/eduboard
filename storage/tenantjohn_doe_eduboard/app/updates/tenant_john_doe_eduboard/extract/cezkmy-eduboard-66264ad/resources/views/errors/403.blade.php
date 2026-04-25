<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access | 403</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    </style>
</head>
<body class="h-screen w-full flex items-center justify-center bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <div class="max-w-md w-full bg-white dark:bg-gray-800 shadow-xl rounded-3xl p-8 text-center border border-gray-100 dark:border-gray-700">
        
        <div class="mx-auto w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>

        <h1 class="text-4xl font-extrabold mb-2 text-gray-900 dark:text-white">403</h1>
        <h2 class="text-xl font-bold mb-3 text-gray-800 dark:text-gray-200">Access Denied</h2>
        
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8 leading-relaxed">
            Oops! It looks like you don't have the necessary permissions to view this page. If you believe this is a mistake, please contact your administrator.
        </p>

        <a href="{{ auth()->check() ? (auth()->user()->role === 'admin' ? '/admin/dashboard' : (auth()->user()->role === 'teacher' ? '/teacher/dashboard' : '/student/dashboard')) : '/' }}" 
           class="inline-flex items-center justify-center w-full py-3.5 px-4 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-xl text-sm font-bold shadow-md hover:bg-gray-800 dark:hover:bg-white transition-all uppercase tracking-wider gap-2">
            
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Return to Dashboard
        </a>

        @if(auth()->check())
            <form action="{{ route('tenant.logout') }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors underline">
                    Log out and try another account
                </button>
            </form>
        @endif
    </div>

</body>
</html>
