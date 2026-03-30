document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // --- Reaction Logic ---
    document.querySelectorAll('.reaction-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const announcementId = this.dataset.id;
            const type = this.dataset.type;
            const countSpan = this.querySelector('.count');

            try {
                const response = await fetch(`/student/announcements/${announcementId}/react`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ type })
                });

                const data = await response.json();
                if (data.success) {
                    countSpan.textContent = data.count;
                    if (data.active) {
                        this.classList.add('bg-teal-50', 'dark:bg-teal-900/20', 'text-teal-600', 'dark:text-teal-400');
                        this.classList.remove('bg-gray-50', 'dark:bg-gray-700/50', 'text-gray-600', 'dark:text-gray-300');
                    } else {
                        this.classList.remove('bg-teal-50', 'dark:bg-teal-900/20', 'text-teal-600', 'dark:text-teal-400');
                        this.classList.add('bg-gray-50', 'dark:bg-gray-700/50', 'text-gray-600', 'dark:text-gray-300');
                    }
                }
            } catch (error) {
                console.error('Error toggling reaction:', error);
            }
        });
    });

    // --- Comment Logic ---
    let activeReplyTo = null;

    // Handle Reply buttons
    document.querySelectorAll('.reply-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const commentId = this.dataset.commentId;
            const userName = this.dataset.userName;
            const announcementCard = this.closest('.ann-card');
            const textarea = announcementCard.querySelector('.comment-textarea');
            const indicator = announcementCard.querySelector('.reply-indicator');
            const nameSpan = indicator.querySelector('.replying-to-name');

            activeReplyTo = commentId;
            nameSpan.textContent = userName;
            indicator.classList.remove('hidden');
            textarea.focus();
        });
    });

    // Handle Cancel Reply
    document.querySelectorAll('.cancel-reply').forEach(btn => {
        btn.addEventListener('click', function() {
            const announcementCard = this.closest('.ann-card');
            const indicator = announcementCard.querySelector('.reply-indicator');
            activeReplyTo = null;
            indicator.classList.add('hidden');
        });
    });

    // Handle Comment Submission
    document.querySelectorAll('.submit-comment').forEach(btn => {
        btn.addEventListener('click', function() {
            submitComment(this);
        });
    });

    // Handle Enter in textarea
    document.querySelectorAll('.comment-textarea').forEach(textarea => {
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                const btn = this.closest('.relative').querySelector('.submit-comment');
                submitComment(btn);
            }
        });

        // Auto-resize
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });

    async function submitComment(btn) {
        const announcementId = btn.dataset.announcementId;
        const announcementCard = btn.closest('.ann-card');
        const textarea = announcementCard.querySelector('.comment-textarea');
        const content = textarea.value.trim();
        const commentList = document.getElementById(`comments-${announcementId}`);
        const indicator = announcementCard.querySelector('.reply-indicator');

        if (!content) return;

        try {
            const response = await fetch(`/student/announcements/${announcementId}/comment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    content,
                    parent_id: activeReplyTo
                })
            });

            const data = await response.json();
            if (data.success) {
                // Clear input
                textarea.value = '';
                textarea.style.height = 'auto';
                
                // Reset reply state
                activeReplyTo = null;
                indicator.classList.add('hidden');

                // Append new comment or reload? For simplicity, let's prepend to the list if it's a top-level comment
                // For replies, it's better to just refresh the section or append to the specific parent.
                // For now, let's just refresh the page or prepend for better UX.
                location.reload(); // Simple approach for now to show nested structure correctly
            }
        } catch (error) {
            console.error('Error submitting comment:', error);
        }
    }

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
                const hasReactions = card.querySelector('.reaction-btn.bg-teal-50');
                const hasComments = card.querySelector(`.comment-item`); // Simple check: any comments for now
                if (hasReactions || hasComments) {
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
            filterPills.forEach(p => p.classList.remove('active', 'bg-teal-500', 'text-white'));
            filterPills.forEach(p => {
                if (!p.classList.contains('all')) {
                    // Restore original colors for non-active pills if needed
                }
            });
            this.classList.add('active', 'bg-teal-500', 'text-white');

            // Filter cards
            annCards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
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
            const cardDateText = card.querySelector('.text-xs.text-gray-500').textContent.split(' · ')[0];
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
