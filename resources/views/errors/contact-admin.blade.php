<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Unavailable</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,600,700,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="antialiased bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-lg p-8 bg-white rounded-3xl shadow-xl border border-gray-100 text-center">
        <div class="w-20 h-20 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center mx-auto mb-6 transform rotate-3 shadow-sm border border-red-100">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H8m4-6V4m0 0v5m0-5h2m-2 0H8"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        
        <h1 class="text-3xl font-black text-gray-900 mb-3 tracking-tight">Account Locked</h1>
        <p class="text-gray-500 font-medium mb-8 leading-relaxed">
            Your school's subscription has expired and is currently in a grace period. 
            Access is temporarily restricted. Please contact your school administrator for assistance.
        </p>
        
        @if(auth()->check())
        <form action="{{ route('tenant.logout') ?? '/logout' }}" method="POST">
            @csrf
            <button type="submit" class="w-full py-4 bg-gray-900 hover:bg-black text-white font-bold rounded-xl transition-all active:scale-95 shadow-lg shadow-gray-900/20">
                Sign Out
            </button>
        </form>
        @else
        <a href="/" class="block w-full py-4 bg-gray-900 hover:bg-black text-white font-bold rounded-xl transition-all active:scale-95 shadow-lg shadow-gray-900/20">
            Return to Homepage
        </a>
        @endif
    </div>
</body>
</html>
