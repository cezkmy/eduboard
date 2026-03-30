<x-app-layout>
    <div class="content-header">
        <h1 class="content-title">Announcements</h1>
        <p class="content-subtitle">View all announcements across the school</p>
    </div>

    <div class="mt-8">
        {{-- Category Filters --}}
        @if(tenant() && tenant()->hasFeature('categories'))
        <div class="categories">
            @php
                $currentCategory = request('category', 'All');
                $categories = ['All', 'General', 'Academic', 'Events', 'Administrative', 'Emergency'];
            @endphp
            @foreach($categories as $cat)
                <a href="{{ route('tenant.teacher.announcements', ['category' => $cat]) }}" 
                   class="ann-filter-pill {{ $currentCategory === $cat ? 'active' : '' }}">
                    {{ $cat === 'All' ? 'All Categories' : $cat }}
                </a>
            @endforeach
        </div>
        @else
            @php $currentCategory = 'All'; @endphp
        @endif

        <div class="announcements space-y-8">
            @php
                $query = \App\Models\Announcement::with('postedBy');
                if(tenant() && tenant()->hasFeature('categories') && $currentCategory !== 'All') {
                    $query->where('category', $currentCategory);
                }
                $announcements = $query->orderBy('is_pinned', 'desc')->orderBy('pinned_at', 'desc')->latest()->paginate(10);
            @endphp

            @forelse($announcements as $announcement)
                <x-announcement-card :announcement="$announcement" :show-reactions="true" />
            @empty
                <div class="card">
                    <div class="empty-state-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 32px; height: 32px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h2 class="empty-state-title">No announcements found</h2>
                    <p class="empty-state-desc">We couldn't find any announcements in this category. Try checking a different one or browse all categories.</p>
                </div>
            @endforelse

            <div class="mt-12">
                {{ $announcements->links() }}
            </div>
        </div>
    </div>
</x-app-layout>










