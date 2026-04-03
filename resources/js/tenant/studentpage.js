document.addEventListener('DOMContentLoaded', function() {
    // --- Filter Logic ---
    const filterPills = document.querySelectorAll('.ann-filter-pill');
    const annCards = document.querySelectorAll('.ann-card');

    window.addEventListener('filter-tab', function(e) {
        const tab = e.detail;
        annCards.forEach(card => {
            if (tab === 'general') {
                card.style.display = 'block';
            } else {
                // For You: Only show cards the user has interacted with (reacted or commented)
                // We check if the reaction buttons have a custom border or class indicating activity
                const hasReactions = card.querySelector('.border-heart-200, .border-like-200, .border-fire-200, .border-gray-200');
                const commentCount = parseInt(card.querySelector('.px-2.py-0.5.bg-white')?.textContent || '0');
                if (hasReactions || commentCount > 0) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            }
        });
    });

    filterPills.forEach(pill => {
        pill.addEventListener('click', function() {
            const category = this.dataset.category;

            // Update active pill
            filterPills.forEach(p => p.classList.remove('active', 'bg-[var(--accent)]', 'text-white'));
            this.classList.add('active', 'bg-[var(--accent)]', 'text-white');

            // Filter cards
            annCards.forEach(card => {
                const cardCategory = card.dataset.category;
                if (category === 'all' || cardCategory === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // --- Date Filter Logic ---
    const applyBtn = document.getElementById('applyDateFilter');
    const clearBtn = document.getElementById('clearDateFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');

    applyBtn.addEventListener('click', function() {
        const from = dateFrom.value;
        const to = dateTo.value;

        if (!from && !to) return;

        annCards.forEach(card => {
            const dateSpan = card.querySelector('.text-\\[11px\\].font-bold.text-gray-500');
            if (!dateSpan) return;
            
            const cardDateText = dateSpan.textContent.split(' • ')[0].trim();
            const cardDate = new Date(cardDateText);
            
            let show = true;
            if (from && cardDate < new Date(from)) show = false;
            if (to && cardDate > new Date(to)) show = false;

            card.style.display = show ? 'block' : 'none';
        });
    });

    clearBtn.addEventListener('click', function() {
        dateFrom.value = '';
        dateTo.value = '';
        annCards.forEach(card => card.style.display = 'block');
    });
});
