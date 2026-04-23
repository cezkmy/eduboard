@php
    $me = auth()->user();
    $isAdmin = $me && $me->role === 'admin';
    $csrfToken = csrf_token();
@endphp

@auth
<div
    x-data="supportChat()"
    x-init="init()"
    class="fixed bottom-6 right-6 z-[9999] flex flex-col items-end gap-3 select-none"
    style="font-family: 'Sora', sans-serif;"
>
    {{-- ━━━━━ Chat Panel ━━━━━ --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="w-80 rounded-3xl shadow-2xl border border-gray-200 dark:border-white/20 overflow-hidden flex flex-col bg-white/95 dark:bg-[#0f121e]/90 backdrop-blur-xl"
        style="height: 480px;"
        x-cloak
    >
        {{-- Header --}}
        <div class="px-5 pt-5 pb-4 flex items-center justify-between border-b border-gray-100 dark:border-white/10 shrink-0 relative">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl flex items-center justify-center text-white shadow-lg"
                     style="background: linear-gradient(135deg, var(--accent), var(--accent-dark));">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
                    </svg>
                </div>
                <div>
                    {{-- Dynamic Title --}}
                    <p class="text-gray-800 dark:text-white text-xs font-black uppercase tracking-wider"
                       x-text="view === 'conversations' ? (adminMode === 'central' ? 'Central Support' : 'Support Inbox') : (view === 'new_conversation' ? 'New Message' : (activeConv ? activeConv.subject : 'Support'))"></p>
                    <p class="text-gray-500 dark:text-white/40 text-[10px] font-semibold"
                       x-text="view === 'conversations' ? conversations.length + ' active' : (view === 'new_conversation' ? 'Select a concern' : (activeConv ? activeConv.from_role + ': ' + activeConv.from_name : ''))"></p>
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" x-show="view !== 'conversations'" @click="backToConversations()"
                    class="w-7 h-7 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-800 hover:bg-gray-100 dark:text-white/60 dark:hover:text-white dark:hover:bg-white/10 transition-all text-[10px] font-black cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                </button>
                <button type="button" @click="open = false"
                    class="w-7 h-7 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-800 hover:bg-gray-100 dark:text-white/60 dark:hover:text-white dark:hover:bg-white/10 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
            </div>
        </div>

        @if($isAdmin)
        {{-- Admin Toggle: Inbox vs Central Support --}}
        <div x-show="view === 'conversations'" class="flex px-3 pt-3 gap-2 shrink-0" x-cloak>
            <button type="button" @click="switchAdminMode('tenant')"
                    class="cursor-pointer flex-1 py-1.5 rounded-lg text-[10px] font-bold transition-all shadow-sm"
                    :class="adminMode === 'tenant' ? 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-white' : 'bg-transparent text-gray-500 hover:text-gray-800 dark:text-white/40 dark:hover:text-white/70'">
                School Inbox
            </button>
            <button type="button" @click="switchAdminMode('central')"
                    class="cursor-pointer flex-1 py-1.5 rounded-lg text-[10px] font-bold transition-all shadow-sm"
                    :class="adminMode === 'central' ? 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-white' : 'bg-transparent text-gray-500 hover:text-gray-800 dark:text-white/40 dark:hover:text-white/70'">
                Central Support
            </button>
        </div>
        @endif

        {{-- Conversations List View --}}
        <div x-show="view === 'conversations'" class="flex-1 flex flex-col overflow-hidden" x-cloak>
            <div class="flex-1 overflow-y-auto p-3 space-y-1">
                <template x-if="conversations.length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-center pb-8">
                        <div class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-white/5 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-gray-400 dark:text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>
                            </svg>
                        </div>
                        <p class="text-gray-400 dark:text-white/30 text-xs font-bold" x-text="adminMode === 'central' ? 'No messages with Central' : 'No messages yet'"></p>
                    </div>
                </template>
                <template x-for="conv in conversations" :key="conv.id">
                    <button type="button" @click="openConversation(conv)"
                        class="cursor-pointer bg-transparent w-full flex items-center gap-3 p-3 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/10 transition-all text-left group">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-600 dark:text-white text-[12px] font-black shrink-0 shadow-sm"
                             :style="conv.unread_count > 0 ? 'background: linear-gradient(135deg, var(--accent), var(--accent-dark)); color: white;' : 'background: rgba(128,128,128,0.1);'">
                             <span x-text="conv.from_name ? conv.from_name.charAt(0).toUpperCase() : '?'"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-gray-800 dark:text-white text-xs font-black truncate group-hover:text-[var(--accent)] transition-colors" x-text="conv.subject"></p>
                                <span class="text-gray-400 dark:text-white/30 text-[9px] font-bold" x-text="conv.time"></span>
                            </div>
                            <p class="text-gray-500 dark:text-white/40 text-[10px] font-semibold truncate mt-0.5" 
                               x-text="(IS_ADMIN && adminMode === 'tenant') ? conv.from_name + ' ('+conv.from_role+')' : (conv.status === 'open' ? 'Ongoing' : 'Closed')"></p>
                        </div>
                        <span x-show="conv.unread_count > 0"
                              class="min-w-[18px] h-[18px] rounded-full bg-red-500 text-white text-[9px] font-black flex items-center justify-center px-1"
                              x-text="conv.unread_count"></span>
                    </button>
                </template>
            </div>
            
            <div class="p-3 border-t border-gray-100 dark:border-white/10 shrink-0" x-show="!IS_ADMIN || adminMode === 'central'">
                <button type="button" @click="view = 'new_conversation'"
                    class="cursor-pointer w-full flex justify-center items-center gap-2 py-3 rounded-xl text-white text-xs font-bold shadow-lg transition-all hover:scale-[1.01]"
                    style="background: linear-gradient(135deg, var(--accent), var(--accent-dark));">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    New Message
                </button>
            </div>
        </div>

        {{-- New Conversation Form --}}
        <div x-show="view === 'new_conversation'" class="flex-1 overflow-y-auto p-4 space-y-4" x-cloak>
            <div>
                <label class="block text-gray-500 dark:text-white/60 text-[10px] font-black uppercase tracking-wider mb-2" 
                       x-text="IS_ADMIN ? 'What do you need help with from Central Admin?' : 'What is your concern?'"></label>
                <select x-model="newSubject" class="w-full bg-gray-50 dark:bg-white/10 text-gray-800 dark:text-white border-transparent focus:border-[var(--accent)] focus:ring-2 focus:ring-[var(--accent)]/30 rounded-xl px-3 py-2.5 text-xs font-bold appearance-none transition-all">
                    <option value="" class="text-gray-900">Select a concern...</option>
                    @if($isAdmin)
                        <option value="Billing / Payment" class="text-gray-900">Billing / Payment</option>
                        <option value="Bug / System Error" class="text-gray-900">Bug / System Error</option>
                        <option value="Feature Request" class="text-gray-900">Feature Request</option>
                        <option value="General Inquiry" class="text-gray-900">General Inquiry</option>
                    @else
                        <option value="Update Password" class="text-gray-900">Update Password</option>
                        <option value="Technical Issue" class="text-gray-900">Technical Issue</option>
                        <option value="Account Settings" class="text-gray-900">Account Settings</option>
                        <option value="General Inquiry" class="text-gray-900">General Inquiry</option>
                    @endif
                </select>
            </div>
            <div>
                <label class="block text-gray-500 dark:text-white/60 text-[10px] font-black uppercase tracking-wider mb-2">Message</label>
                <textarea x-model="newMessageContent" rows="5" placeholder="Describe your concern in detail..."
                          class="w-full bg-gray-50 dark:bg-white/10 text-gray-800 dark:text-white dark:placeholder-white/30 border-transparent focus:border-[var(--accent)] focus:ring-2 focus:ring-[var(--accent)]/30 rounded-xl px-3 py-2.5 text-xs font-semibold resize-none transition-all"></textarea>
            </div>
            <button type="button" @click="submitNewConversation()" :disabled="!newSubject || !newMessageContent.trim() || submitting"
                class="cursor-pointer w-full flex justify-center items-center gap-2 py-3 rounded-xl text-white text-xs font-bold shadow-lg transition-all disabled:opacity-50"
                style="background: linear-gradient(135deg, var(--accent), var(--accent-dark));">
                <span x-show="!submitting">Send Message</span>
                <span x-show="submitting">Sending...</span>
            </button>
        </div>

        {{-- Active Chat Thread --}}
        <div x-show="view === 'chat'" class="flex-1 flex flex-col overflow-hidden" x-cloak>
            <div class="flex-1 overflow-y-auto p-3 space-y-2" id="chatThread">
                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex" :class="msg.mine ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[85%] px-3.5 py-2.5 rounded-2xl text-xs font-semibold shadow leading-relaxed"
                             :class="msg.mine ? 'rounded-br-md text-white' : 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-white/90 rounded-bl-md'"
                             :style="msg.mine ? 'background: var(--accent);' : ''">
                            <p x-text="msg.message" style="white-space: pre-wrap; word-break: break-word;"></p>
                            <p class="text-[9px] mt-1 opacity-60 flex justify-end" x-text="msg.time"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Reply input --}}
            <div class="p-3 border-t border-gray-100 dark:border-white/10 shrink-0 flex gap-2">
                <input x-model="newMessage" @keydown.enter.prevent="sendMessage()"
                    type="text" placeholder="Type your reply..."
                    class="flex-1 bg-gray-50 dark:bg-white/10 text-gray-800 dark:text-white dark:placeholder-white/30 rounded-xl px-3.5 py-2.5 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[var(--accent)]/30 border-transparent transition-all">
                <button type="button" @click="sendMessage()" :disabled="!newMessage.trim() || submitting"
                    class="cursor-pointer w-9 h-9 rounded-xl flex items-center justify-center text-white transition-all disabled:opacity-30 shrink-0 shadow-sm"
                    style="background: var(--accent);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ━━━━━ Floating Bubble Button ━━━━━ --}}
    <button type="button" @click="toggleChat()"
        class="cursor-pointer w-14 h-14 rounded-2xl flex items-center justify-center shadow-2xl transition-all hover:scale-105 active:scale-95 relative"
        style="background: linear-gradient(135deg, var(--accent), var(--accent-dark)); box-shadow: 0 8px 32px rgba(var(--accent-rgb),0.45);">
        {{-- Chat icon --}}
        <svg x-show="!open" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
        </svg>
        {{-- X icon when open --}}
        <svg x-show="open" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        {{-- Unread badge --}}
        <span x-show="unreadCount > 0" x-cloak
              class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 rounded-full bg-red-500 text-white text-[10px] font-black flex items-center justify-center ring-2 ring-white/20 px-1"
              x-text="unreadCount"></span>
    </button>
</div>

<script>
function supportChat() {
    const IS_ADMIN = {{ $isAdmin ? 'true' : 'false' }};
    const CSRF    = '{{ $csrfToken }}';

    return {
        IS_ADMIN: IS_ADMIN,
        open: false,
        view: 'conversations', // 'conversations', 'new_conversation', 'chat'
        adminMode: 'tenant', // 'tenant' or 'central' (tenant = local inbox, central = messaging central admin)
        
        conversations: [],
        messages: [],
        
        activeConv: null,
        
        newSubject: '',
        newMessageContent: '',
        newMessage: '',
        
        unreadCount: 0,
        submitting: false,
        pollTimer: null,

        init() {
            if (!IS_ADMIN) {
                this.adminMode = 'tenant';
            }
            this.fetchUnread();
            this.pollTimer = setInterval(() => {
                this.fetchUnread();
                if (this.open && this.view === 'conversations') {
                    this.loadConversations();
                } else if (this.open && this.view === 'chat' && this.activeConv) {
                    this.loadMessages(this.activeConv.id, true);
                }
            }, 10000);
        },

        switchAdminMode(mode) {
            this.adminMode = mode;
            this.conversations = [];
            this.loadConversations();
        },

        toggleChat() {
            this.open = !this.open;
            if (this.open && this.view === 'conversations') {
                this.loadConversations();
            }
            if (!this.open) {
                this.fetchUnread();
            }
        },

        fetchUnread() {
            fetch(`/support/unread?t=${Date.now()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(d => { this.unreadCount = parseInt(d.count); })
                .catch(() => {});
        },

        loadConversations() {
            const url = this.adminMode === 'central' ? '/support/central/inbox' : '/support/inbox';
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => { 
                    this.conversations = data; 
                })
                .catch(console.error);
        },

        openConversation(conv) {
            this.activeConv = conv;
            this.view = 'chat';
            this.loadMessages(conv.id, false);
            
            // Immediate UI feedback
            if (conv.unread_count > 0) {
                this.unreadCount = Math.max(0, this.unreadCount - conv.unread_count);
                conv.unread_count = 0;
            }
        },

        backToConversations() {
            this.activeConv = null;
            this.messages = [];
            this.view = 'conversations';
            this.newSubject = '';
            this.newMessageContent = '';
            this.loadConversations();
        },

        loadMessages(id, silent = false) {
            const url = this.adminMode === 'central' 
                ? `/support/central/messages?ticket_id=${id}` 
                : `/support/messages?ticket_id=${id}`;
                
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    const prevLen = this.messages.length;
                    this.messages = data;
                    if (!silent || this.messages.length > prevLen) {
                        this.$nextTick(() => this.scrollToBottom());
                    }
                    // Always refresh unread count after loading messages, as some may have been marked read
                    this.fetchUnread();
                });
        },

        submitNewConversation() {
            if (!this.newSubject || !this.newMessageContent.trim()) return;
            this.submitting = true;
            
            const url = this.adminMode === 'central' ? '/support/central/ticket' : '/support/ticket';
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    subject: this.newSubject,
                    message: this.newMessageContent
                })
            })
            .then(r => r.json())
            .then(d => {
                this.submitting = false;
                if (d.success) {
                    this.newSubject = '';
                    this.newMessageContent = '';
                    // Go to chat
                    this.openConversation(d.ticket);
                }
            })
            .catch(() => { this.submitting = false; });
        },

        sendMessage() {
            const msg = this.newMessage.trim();
            if (!msg || !this.activeConv) return;
            
            this.submitting = true;
            
            const url = this.adminMode === 'central' ? '/support/central/send' : '/support/send';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ticket_id: this.activeConv.id,
                    message: msg
                })
            })
            .then(r => r.json())
            .then(d => {
                this.submitting = false;
                if (d.success) {
                    this.newMessage = '';
                    this.loadMessages(this.activeConv.id);
                }
            })
            .catch(() => { this.submitting = false; });
        },

        scrollToBottom() {
            const el = document.getElementById('chatThread');
            if (el) el.scrollTop = el.scrollHeight;
        }
    };
}
</script>
@endauth
