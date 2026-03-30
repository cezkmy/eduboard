<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center gap-6">
                    <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-blue-500 shadow-lg">
                        <img src="{{ asset('images/download.jpg') }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                    </div>
                    <div>
                        @if(auth()->user()->role === 'admin')
                            <h3 class="text-2xl font-bold">Welcome, {{ auth()->user()->name }}!</h3>
                            <p class="text-gray-600 dark:text-gray-400">You have full access to manage the system.</p>
                            <!-- Admin specific UI elements -->
                        @elseif(auth()->user()->role === 'teacher')
                            <h3 class="text-2xl font-bold">Welcome, {{ auth()->user()->name }}!</h3>
                            <p class="text-gray-600 dark:text-gray-400">Manage your classes and assignments.</p>
                            <!-- Teacher specific UI -->
                        @else
                            <h3 class="text-2xl font-bold">Welcome, {{ auth()->user()->name }}!</h3>
                            <p class="text-gray-600 dark:text-gray-400">View your courses and grades.</p>
                            <!-- Student specific UI -->
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>










