document.addEventListener('DOMContentLoaded', function() {
    // --- Filter Logic ---
    const filterPills = document.querySelectorAll('.ann-filter-pill');
    const intFilterPills = document.querySelectorAll('.int-filter-pill');
    const annCards = document.querySelectorAll('.ann-wrapper');

    let currentTab = 'general';
    let currentCategory = 'all';

    function applyAllFilters() {
        annCards.forEach(card => {
            let show = true;

            // 1. Tab Filter (General vs For You)
            if (currentTab === 'foryou') {
                if (card.dataset.isTargeted !== 'true') show = false;
            } else if (currentTab === 'general') {
                if (card.dataset.isTargeted === 'true') show = false;
            }

            // 2. Category Filter
            const cardCategory = card.dataset.category;
            if (currentCategory !== 'all' && cardCategory !== currentCategory) {
                show = false;
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
