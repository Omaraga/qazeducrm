<?php

use app\models\WhatsappChat;
use app\models\WhatsappSession;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var WhatsappSession $session */
/** @var WhatsappChat[] $chats */
/** @var int $unreadCount */
/** @var array $urls */

// Register WhatsApp CSS
$this->registerCssFile('@web/css/whatsapp.css');

$csrfToken = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
?>

<!-- WhatsApp Widget -->
<div id="wa-widget"
     x-data="waWidget()"
     x-init="init()"
     class="wa-widget-container">

    <!-- Floating Button -->
    <button type="button"
            @click="toggle()"
            class="wa-widget-btn"
            :class="{ 'wa-widget-btn-active': isOpen }">
        <!-- WhatsApp Icon -->
        <svg x-show="!isOpen" class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
        <!-- Close Icon -->
        <svg x-show="isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <!-- Unread Badge -->
        <span x-show="totalUnread > 0 && !isOpen"
              x-text="totalUnread > 99 ? '99+' : totalUnread"
              class="wa-widget-badge"></span>
    </button>

    <!-- Slide Panel -->
    <div x-show="isOpen"
         x-transition:enter="transform transition-transform duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition-transform duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="wa-widget-panel"
         @click.outside="if(isOpen && !selectedChatId) close()">

        <!-- Panel Header -->
        <div class="wa-widget-header">
            <div class="flex items-center gap-3">
                <button x-show="selectedChatId"
                        @click="goBack()"
                        class="p-1 -ml-1 rounded hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h3 class="font-semibold" x-text="selectedChatId ? chatInfo.name : 'WhatsApp'"></h3>
                    <p class="text-xs text-white/70" x-text="selectedChatId ? chatInfo.phone : '+<?= $session->phone_number ?>'"></p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <a href="<?= $urls['chatsPage'] ?>" class="p-2 rounded hover:bg-white/20 transition-colors" title="Открыть полную версию">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
                <button @click="close()" class="p-2 rounded hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat List View -->
        <div x-show="!selectedChatId" class="wa-widget-body">
            <!-- Search -->
            <div class="p-3 border-b border-gray-100">
                <div class="relative">
                    <input type="text"
                           x-model="searchQuery"
                           @input.debounce.300ms="filterChats()"
                           placeholder="Поиск..."
                           class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-green-500">
                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Chats List -->
            <div class="flex-1 overflow-y-auto">
                <template x-if="filteredChats.length === 0">
                    <div class="p-8 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="text-sm">Нет чатов</p>
                    </div>
                </template>
                <template x-for="chat in filteredChats" :key="chat.id">
                    <div @click="selectChat(chat.id)"
                         class="flex items-center gap-3 px-3 py-3 cursor-pointer hover:bg-gray-50 transition-colors border-b border-gray-50">
                        <!-- Avatar -->
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden"
                             :class="chat.profile_picture_url ? '' : (chat.lid_id ? 'bg-blue-100' : 'bg-gray-200')">
                            <img x-show="chat.profile_picture_url"
                                 :src="chat.profile_picture_url"
                                 :alt="chat.name"
                                 class="w-full h-full object-cover"
                                 @error="chat.profile_picture_url = null">
                            <span x-show="!chat.profile_picture_url"
                                  class="font-semibold text-sm"
                                  :class="chat.lid_id ? 'text-blue-600' : 'text-gray-500'"
                                  x-text="chat.name ? chat.name.charAt(0).toUpperCase() : '?'"></span>
                        </div>
                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="font-medium text-gray-900 text-sm truncate" x-text="chat.name"></span>
                                <span class="text-xs text-gray-400 flex-shrink-0 ml-2" x-text="chat.time"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-gray-500 truncate" x-text="chat.preview"></p>
                                <span x-show="chat.unread > 0"
                                      x-text="chat.unread > 99 ? '99+' : chat.unread"
                                      class="ml-2 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-green-500 rounded-full flex-shrink-0"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Messages View -->
        <div x-show="selectedChatId" x-cloak class="wa-widget-body flex flex-col">
            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-3 bg-[#e5ddd5]" x-ref="messagesContainer">
                <div x-show="loadingMessages" class="text-center py-8 text-gray-500">
                    <svg class="animate-spin h-6 w-6 mx-auto text-green-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
                <div x-show="!loadingMessages" x-html="messagesHtml"></div>
            </div>

            <!-- Input -->
            <div class="p-3 bg-gray-100 border-t border-gray-200">
                <div class="flex items-center gap-2">
                    <input type="text"
                           x-model="messageText"
                           @keydown.enter="sendMessage()"
                           placeholder="Введите сообщение..."
                           class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-full focus:outline-none focus:border-green-500">
                    <button @click="sendMessage()"
                            :disabled="!messageText.trim() || sending"
                            class="w-10 h-10 flex items-center justify-center bg-green-500 text-white rounded-full hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="!sending" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                        <svg x-show="sending" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('waWidget', () => ({
        isOpen: false,
        selectedChatId: null,
        chatInfo: {},
        chats: <?= json_encode(array_map(function($chat) {
            return [
                'id' => $chat->id,
                'name' => $chat->getDisplayName(),
                'phone' => $chat->remote_phone,
                'preview' => $chat->lastMessage ? $chat->lastMessage->getPreview() : '',
                'time' => $chat->getLastMessageTime(),
                'unread' => $chat->unread_count,
                'lid_id' => $chat->lid_id,
                'profile_picture_url' => $chat->profile_picture_url,
            ];
        }, $chats)) ?>,
        filteredChats: [],
        searchQuery: '',
        messagesHtml: '',
        messageText: '',
        loadingMessages: false,
        sending: false,
        lastMessageId: 0,
        pollInterval: null,
        totalUnread: <?= $unreadCount ?>,

        init() {
            this.filteredChats = this.chats;
            // Update unread count periodically
            setInterval(() => this.updateUnreadCount(), 30000);
        },

        toggle() {
            this.isOpen = !this.isOpen;
            if (this.isOpen && !this.selectedChatId) {
                this.refreshChats();
            }
        },

        close() {
            this.isOpen = false;
            this.selectedChatId = null;
            this.stopPolling();
        },

        goBack() {
            this.selectedChatId = null;
            this.chatInfo = {};
            this.messagesHtml = '';
            this.stopPolling();
            this.refreshChats();
        },

        filterChats() {
            const q = this.searchQuery.toLowerCase();
            if (!q) {
                this.filteredChats = this.chats;
                return;
            }
            this.filteredChats = this.chats.filter(c =>
                c.name.toLowerCase().includes(q) ||
                (c.phone && c.phone.includes(q))
            );
        },

        async selectChat(chatId) {
            this.selectedChatId = chatId;
            this.loadingMessages = true;
            this.messagesHtml = '';

            try {
                const response = await fetch(`<?= $urls['getChatContent'] ?>?chat_id=${chatId}`);
                const data = await response.json();

                if (data.success) {
                    this.chatInfo = {
                        name: data.chat.name,
                        phone: data.chat.phone,
                        lid_id: data.chat.lid_id,
                        profile_picture_url: data.chat.profile_picture_url
                    };
                    this.messagesHtml = data.messages_html;
                    this.lastMessageId = data.last_message_id;

                    this.$nextTick(() => this.scrollToBottom());
                    this.startPolling();

                    // Update unread in list
                    const chat = this.chats.find(c => c.id === chatId);
                    if (chat) {
                        this.totalUnread -= chat.unread;
                        chat.unread = 0;
                    }
                }
            } catch (error) {
                console.error('Error loading chat:', error);
                this.messagesHtml = '<div class="text-center py-4 text-red-500">Ошибка загрузки</div>';
            } finally {
                this.loadingMessages = false;
            }
        },

        async sendMessage() {
            if (!this.messageText.trim() || this.sending) return;

            this.sending = true;
            const text = this.messageText;
            this.messageText = '';

            try {
                const formData = new FormData();
                formData.append('chat_id', this.selectedChatId);
                formData.append('text', text);
                formData.append('<?= $csrfParam ?>', '<?= $csrfToken ?>');

                const response = await fetch('<?= $urls['sendMessage'] ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    await this.reloadMessages();
                } else {
                    alert(data.message || 'Ошибка отправки');
                    this.messageText = text;
                }
            } catch (error) {
                console.error('Error sending:', error);
                this.messageText = text;
            } finally {
                this.sending = false;
            }
        },

        startPolling() {
            this.stopPolling();
            this.pollInterval = setInterval(() => this.checkNewMessages(), 3000);
        },

        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
        },

        async checkNewMessages() {
            if (!this.selectedChatId || !this.lastMessageId) return;

            try {
                const response = await fetch(`<?= $urls['getMessages'] ?>?chat_id=${this.selectedChatId}&after_id=${this.lastMessageId}`);
                const data = await response.json();

                if (data.success && data.messages && data.messages.length > 0) {
                    await this.reloadMessages();
                }
            } catch (error) {
                console.error('Polling error:', error);
            }
        },

        async reloadMessages() {
            try {
                const response = await fetch(`<?= $urls['getChatContent'] ?>?chat_id=${this.selectedChatId}`);
                const data = await response.json();

                if (data.success) {
                    const wasAtBottom = this.isScrolledToBottom();
                    this.messagesHtml = data.messages_html;
                    this.lastMessageId = data.last_message_id;

                    if (wasAtBottom) {
                        this.$nextTick(() => this.scrollToBottom());
                    }
                }
            } catch (error) {
                console.error('Reload error:', error);
            }
        },

        async refreshChats() {
            try {
                const response = await fetch('<?= $urls['getChats'] ?>');
                const data = await response.json();
                if (data.success) {
                    this.chats = data.chats;
                    this.filterChats();
                }
            } catch (error) {
                console.error('Error refreshing chats:', error);
            }
        },

        async updateUnreadCount() {
            try {
                const response = await fetch('<?= $urls['getChats'] ?>');
                const data = await response.json();
                if (data.success) {
                    this.totalUnread = data.chats.reduce((sum, c) => sum + (c.unread || 0), 0);
                    this.chats = data.chats;
                    this.filterChats();
                }
            } catch (error) {}
        },

        isScrolledToBottom() {
            const container = this.$refs.messagesContainer;
            if (!container) return true;
            return container.scrollHeight - container.scrollTop - container.clientHeight < 50;
        },

        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
    }));
});
</script>
