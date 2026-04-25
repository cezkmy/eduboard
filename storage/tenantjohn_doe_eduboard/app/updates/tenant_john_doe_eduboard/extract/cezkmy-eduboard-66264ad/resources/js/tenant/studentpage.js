document.addEventListener('DOMContentLoaded', function() {
    // --- Filter Logic ---
    const filterPills = document.querySelectorAll('.ann-filter-pill');
    const intFilterPills = document.querySelectorAll('.int-filter-pill');
    const annCards = document.querySelectorAll('.ann-card');

    let currentTab = 'general';
    let currentCategory = 'all';
    let currentIntFilter = 'all';

    function applyAllFilters() {
        annCards.forEach(card => {
            let show = true;

            // 1. Tab Filter (General vs For You)
            if (currentTab === 'foryou') {
                const userInteracted = card.dataset.userInteracted === 'true';
                if (!userInteracted) show = false;
            }

            // 2. Category Filter
            const cardCategory = card.dataset.category;
            if (currentCategory !== 'all' && cardCategory !== currentCategory) {
                show = false;
            }

            // 3. Interaction Filter
            if (currentIntFilter === 'reacted') {
                const reactions = parseInt(card.dataset.reactions || '0');
                if (reactions === 0) show = false;
            } else if (currentIntFilter === 'commented') {
                const comments = parseInt(card.dataset.comments || '0');
                if (comments === 0) show = false;
            } else if (currentIntFilter === 'popular') {
                const engagement = parseInt(card.dataset.reactions || '0') + parseInt(card.dataset.comments || '0');
                if (engagement < 5) show = false; // Threshold for "popular"
            }

            // 4. Date Filter (Check if values exist)
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            if (dateFrom || dateTo) {
                const dateSpan = card.querySelector('.text-\\[11px\\].font-bold.text-gray-500');
                if (dateSpan) {
                    const cardDateText = dateSpan.textContent.split(' • ')[0].trim();
                    const cardDate = new Date(cardDateText);
                    if (dateFrom && cardDate < new Date(dateFrom)) show = false;
                    if (dateTo && cardDate > new Date(dateTo)) show = false;
                }
            }

            card.style.display = show ? 'block' : 'none';
        });

        // Check if list is empty after filtering
        const visibleCards = Array.from(annCards).filter(c => c.style.display !== 'none');
        const emptyState = document.querySelector('.empty-state');
        if (visibleCards.length === 0) {
            if (!emptyState) {
                // Create a temporary empty state if it doesn't exist
                const list = document.querySelector('.ann-list');
                const tempEmpty = document.createElement('div');
                tempEmpty.className = 'temp-empty-state bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm mt-6';
                tempEmpty.innerHTML = '<p class="text-gray-500 font-bold">No results match your current filters.</p>';
                if (list && !list.querySelector('.temp-empty-state')) list.appendChild(tempEmpty);
            }
        } else {
            const tempEmpty = document.querySelector('.temp-empty-state');
            if (tempEmpty) tempEmpty.remove();
        }
    }

    window.addEventListener('filter-tab', function(e) {
        currentTab = e.detail;
        applyAllFilters();
    });

    filterPills.forEach(pill => {
        pill.addEventListener('click', function() {
            currentCategory = this.dataset.category;

            // Update active pill UI
            filterPills.forEach(p => {
                p.classList.remove('active', 'bg-[var(--accent)]', 'text-white');
                p.classList.add('bg-white', 'dark:bg-gray-800', 'text-gray-600', 'dark:text-gray-400', 'border', 'border-gray-100', 'dark:border-gray-700');
            });
            this.classList.add('active', 'bg-[var(--accent)]', 'text-white');
            this.classList.remove('bg-white', 'dark:bg-gray-800', 'text-gray-600', 'dark:text-gray-400', 'border-gray-100', 'dark:border-gray-700');

            applyAllFilters();
        });
    });

    intFilterPills.forEach(pill => {
        pill.addEventListener('click', function() {
            currentIntFilter = this.dataset.filter;

            // Update active pill UI
            intFilterPills.forEach(p => {
                p.classList.remove('active', 'bg-gray-900', 'dark:bg-gray-100', 'text-white', 'dark:text-gray-900');
                p.classList.add('bg-white', 'dark:bg-gray-800', 'text-gray-600', 'dark:text-gray-400', 'border', 'border-gray-100', 'dark:border-gray-700');
            });
            this.classList.add('active', 'bg-gray-900', 'dark:bg-gray-100', 'text-white', 'dark:text-gray-900');
            this.classList.remove('bg-white', 'dark:bg-gray-800', 'text-gray-600', 'dark:text-gray-400', 'border-gray-100', 'dark:border-gray-700');

            applyAllFilters();
        });
    });

    // --- Date Filter Logic ---
    const applyBtn = document.getElementById('applyDateFilter');
    const clearBtn = document.getElementById('clearDateFilter');

    applyBtn.addEventListener('click', function() {
        applyAllFilters();
    });

    clearBtn.addEventListener('click', function() {
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        applyAllFilters();
    });
});
