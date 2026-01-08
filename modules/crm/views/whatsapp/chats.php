<?php

use app\models\WhatsappChat;
use app\models\WhatsappSession;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var WhatsappSession $session */
/** @var WhatsappChat[] $chats */
/** @var string|null $search */

$this->title = 'WhatsApp';
$this->params['breadcrumbs'][] = ['label' => 'CRM', 'url' => ['/crm']];
$this->params['breadcrumbs'][] = 'WhatsApp';

// URLs для AJAX (с полным путём модуля)
$getChatContentUrl = Url::to(['/crm/whatsapp/get-chat-content']);
$getMessagesUrl = Url::to(['/crm/whatsapp/get-messages']);
$sendMessageUrl = Url::to(['/crm/whatsapp/send-message']);
$createLidUrl = Url::to(['/crm/whatsapp/create-lid-from-chat']);
$csrfToken = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
?>

<style>
    /* WhatsApp Web color scheme */
    :root {
        --wa-green-primary: #00a884;
        --wa-green-dark: #008069;
        --wa-green-light: #d9fdd3;
        --wa-bg-chat: #efeae2;
        --wa-bg-panel: #f0f2f5;
        --wa-text-primary: #111b21;
        --wa-text-secondary: #667781;
        --wa-blue-check: #53bdeb;
        --wa-border: #e9edef;
    }

    .wa-chat-bg {
        background-color: var(--wa-bg-chat);
        background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M14 16H9v-2h5V9h2v5h5v2h-5v5h-2v-5zM64 16h-5v-2h5V9h2v5h5v2h-5v5h-2v-5zM14 66h-5v-2h5v-5h2v5h5v2h-5v5h-2v-5zM64 66h-5v-2h5v-5h2v5h5v2h-5v5h-2v-5z' fill='%23d1d5db' fill-opacity='0.2' fill-rule='evenodd'/%3E%3C/svg%3E");
    }

    .wa-message-out {
        background-color: var(--wa-green-light);
        border-radius: 7.5px 0 7.5px 7.5px;
    }

    .wa-message-in {
        background-color: white;
        border-radius: 0 7.5px 7.5px 7.5px;
    }

    .wa-message-tail-out::before {
        content: '';
        position: absolute;
        top: 0;
        right: -8px;
        width: 8px;
        height: 13px;
        background: var(--wa-green-light);
        clip-path: polygon(0 0, 0% 100%, 100% 0);
    }

    .wa-message-tail-in::before {
        content: '';
        position: absolute;
        top: 0;
        left: -8px;
        width: 8px;
        height: 13px;
        background: white;
        clip-path: polygon(100% 0, 0 0, 100% 100%);
    }

    .wa-check {
        color: var(--wa-text-secondary);
    }

    .wa-check-read {
        color: var(--wa-blue-check);
    }

    /* Emoji picker positioning */
    emoji-picker {
        --num-columns: 8;
        --emoji-padding: 0.5rem;
        height: 300px;
    }

    /* Scrollbar styling */
    .wa-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .wa-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .wa-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(0,0,0,0.2);
        border-radius: 3px;
    }
</style>

<div class="whatsapp-split-view h-[calc(100vh-180px)] min-h-[500px]"
     x-data="whatsappSplitView()"
     x-init="init()">

    <div class="flex h-full bg-white border rounded-lg overflow-hidden shadow-sm">
        <!-- Left Panel: Chat List -->
        <div class="w-[350px] flex-shrink-0 border-r flex flex-col bg-white">
            <!-- Header -->
            <div class="px-4 py-3 bg-[var(--wa-bg-panel)] border-b flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[var(--wa-green-primary)] flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-[var(--wa-text-primary)]">WhatsApp</div>
                        <?php if ($session->phone_number): ?>
                            <div class="text-xs text-[var(--wa-text-secondary)]">+<?= Html::encode($session->phone_number) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>
                    Online
                </span>
            </div>

            <!-- Search -->
            <div class="p-2 bg-[var(--wa-bg-panel)]">
                <form method="get" action="<?= Url::to(['chats']) ?>">
                    <div class="relative">
                        <input type="text" name="search" value="<?= Html::encode($search) ?>"
                               placeholder="Поиск или новый чат"
                               class="w-full pl-10 pr-4 py-2 bg-white border-0 rounded-lg text-sm focus:ring-1 focus:ring-[var(--wa-green-primary)]">
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-[var(--wa-text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>
            </div>

            <!-- Chat List -->
            <div class="flex-1 overflow-y-auto wa-scrollbar">
                <?php if (empty($chats)): ?>
                    <div class="p-8 text-center text-[var(--wa-text-secondary)]">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="font-medium">Нет чатов</p>
                        <p class="text-sm mt-1">Чаты появятся когда клиенты напишут вам</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($chats as $chat): ?>
                        <div @click="selectChat(<?= $chat->id ?>)"
                             :class="{ 'bg-[var(--wa-bg-panel)]': selectedChatId === <?= $chat->id ?> }"
                             class="flex items-center gap-3 px-3 py-3 cursor-pointer hover:bg-[var(--wa-bg-panel)] border-b border-[var(--wa-border)] transition-colors">
                            <!-- Avatar -->
                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 <?= $chat->lid_id ? 'bg-blue-100' : 'bg-gray-200' ?>">
                                <?php if ($chat->lid): ?>
                                    <span class="text-blue-600 font-semibold text-lg">
                                        <?= mb_strtoupper(mb_substr($chat->getDisplayName(), 0, 1)) ?>
                                    </span>
                                <?php else: ?>
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                <?php endif; ?>
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="font-medium text-[var(--wa-text-primary)] truncate">
                                        <?= Html::encode($chat->getDisplayName()) ?>
                                    </span>
                                    <span class="text-xs text-[var(--wa-text-secondary)] flex-shrink-0 ml-2">
                                        <?= $chat->getLastMessageTime() ?>
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-[var(--wa-text-secondary)] truncate">
                                        <?php if ($chat->lastMessage): ?>
                                            <?php if ($chat->lastMessage->is_from_me): ?>
                                                <svg class="w-4 h-4 inline <?= $chat->lastMessage->status === 'read' ? 'wa-check-read' : 'wa-check' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            <?php endif; ?>
                                            <?= Html::encode($chat->lastMessage->getPreview()) ?>
                                        <?php else: ?>
                                            <span class="italic">Нет сообщений</span>
                                        <?php endif; ?>
                                    </p>

                                    <?php if ($chat->unread_count > 0): ?>
                                        <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold text-white bg-[var(--wa-green-primary)] rounded-full flex-shrink-0 ml-2">
                                            <?= $chat->unread_count > 99 ? '99+' : $chat->unread_count ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($chat->lid): ?>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <?= Html::encode($chat->lid->fio ?: $chat->lid->parent_fio ?: 'Лид') ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Panel: Chat Content -->
        <div class="flex-1 flex flex-col bg-[var(--wa-bg-panel)]">
            <!-- Empty State -->
            <template x-if="!selectedChatId">
                <div class="flex-1 flex items-center justify-center wa-chat-bg">
                    <div class="text-center">
                        <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-[var(--wa-green-primary)]/10 flex items-center justify-center">
                            <svg class="w-12 h-12 text-[var(--wa-green-primary)]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-light text-[var(--wa-text-primary)] mb-2">WhatsApp CRM</h3>
                        <p class="text-[var(--wa-text-secondary)]">Выберите чат для начала общения</p>
                    </div>
                </div>
            </template>

            <!-- Chat Content -->
            <template x-if="selectedChatId">
                <div class="flex-1 flex flex-col">
                    <!-- Chat Header -->
                    <div class="px-4 py-2 bg-[var(--wa-bg-panel)] border-b border-[var(--wa-border)] flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                 :class="chatInfo.lid_id ? 'bg-blue-100' : 'bg-gray-200'">
                                <span class="font-semibold" :class="chatInfo.lid_id ? 'text-blue-600' : 'text-gray-500'"
                                      x-text="chatInfo.name ? chatInfo.name.charAt(0).toUpperCase() : '?'"></span>
                            </div>
                            <div>
                                <div class="font-medium text-[var(--wa-text-primary)]" x-text="chatInfo.name"></div>
                                <div class="text-xs text-[var(--wa-text-secondary)]" x-text="chatInfo.phone"></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Link to Lead -->
                            <template x-if="chatInfo.lid_id">
                                <a :href="'/<?= Yii::$app->params['organizationId'] ?? 2 ?>/lids/view?id=' + chatInfo.lid_id"
                                   class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span x-text="chatInfo.lid_name || 'Открыть лид'"></span>
                                </a>
                            </template>

                            <!-- Create Lead Button -->
                            <template x-if="!chatInfo.lid_id">
                                <button @click="createLid()"
                                        :disabled="creatingLid"
                                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-[var(--wa-green-dark)] bg-green-50 rounded-lg hover:bg-green-100 transition-colors disabled:opacity-50">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span x-text="creatingLid ? 'Создание...' : 'Создать лид'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div class="flex-1 overflow-y-auto wa-chat-bg wa-scrollbar p-4"
                         x-ref="messagesContainer"
                         id="messages-container">
                        <div x-html="messagesHtml"></div>
                    </div>

                    <!-- Input Area -->
                    <div class="px-4 py-3 bg-[var(--wa-bg-panel)] border-t border-[var(--wa-border)]">
                        <!-- Emoji Picker -->
                        <div x-show="showEmojiPicker"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform translate-y-2"
                             @click.outside="showEmojiPicker = false"
                             class="absolute bottom-20 left-4 z-10 bg-white rounded-lg shadow-lg border">
                            <emoji-picker @emoji-click="insertEmoji($event)"></emoji-picker>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- Emoji Button -->
                            <button @click="showEmojiPicker = !showEmojiPicker"
                                    type="button"
                                    class="flex-shrink-0 w-10 h-10 flex items-center justify-center text-[var(--wa-text-secondary)] hover:text-[var(--wa-green-primary)] hover:bg-gray-100 rounded-full transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>

                            <!-- Text Input -->
                            <div class="flex-1">
                                <textarea x-model="messageText"
                                          x-ref="messageInput"
                                          @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                                          @input="adjustTextarea()"
                                          placeholder="Введите сообщение"
                                          rows="1"
                                          class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-3xl text-sm resize-none focus:outline-none focus:border-[var(--wa-green-primary)] focus:ring-1 focus:ring-[var(--wa-green-primary)] max-h-32 overflow-y-auto"
                                          style="min-height: 42px; line-height: 1.4;"></textarea>
                            </div>

                            <!-- Send Button -->
                            <button @click="sendMessage()"
                                    :disabled="!messageText.trim() || sending"
                                    class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    style="background-color: #00a884; color: white;"
                                    onmouseover="this.style.backgroundColor='#008069'"
                                    onmouseout="this.style.backgroundColor='#00a884'">
                                <svg x-show="!sending" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                </svg>
                                <svg x-show="sending" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<!-- Emoji Picker Script -->
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('whatsappSplitView', () => ({
        selectedChatId: null,
        chatInfo: {},
        messagesHtml: '',
        lastMessageId: 0,
        messageText: '',
        showEmojiPicker: false,
        sending: false,
        creatingLid: false,
        pollInterval: null,

        init() {
            // Start polling for new messages
            this.pollInterval = setInterval(() => {
                if (this.selectedChatId) {
                    this.checkNewMessages();
                }
            }, 3000);
        },

        async selectChat(chatId, forceReload = false) {
            if (this.selectedChatId === chatId && !forceReload) return;

            this.selectedChatId = chatId;
            this.messagesHtml = '<div class="text-center py-8 text-gray-400">Загрузка...</div>';

            try {
                const response = await fetch(`<?= $getChatContentUrl ?>?chat_id=${chatId}`);
                const data = await response.json();

                if (data.success) {
                    this.chatInfo = data.chat;
                    this.messagesHtml = data.messages_html;
                    this.lastMessageId = data.last_message_id;

                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                }
            } catch (error) {
                console.error('Error loading chat:', error);
                this.messagesHtml = '<div class="text-center py-8 text-red-500">Ошибка загрузки чата</div>';
            }
        },

        async sendMessage() {
            if (!this.messageText.trim() || this.sending) return;

            this.sending = true;
            const text = this.messageText;
            this.messageText = '';
            this.showEmojiPicker = false;

            // Reset textarea height
            this.$refs.messageInput.style.height = '42px';

            try {
                const formData = new FormData();
                formData.append('chat_id', this.selectedChatId);
                formData.append('text', text);
                formData.append('<?= $csrfParam ?>', '<?= $csrfToken ?>');

                const response = await fetch('<?= $sendMessageUrl ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Reload messages (force reload since same chat)
                    await this.selectChat(this.selectedChatId, true);
                } else {
                    alert(data.message || 'Ошибка отправки');
                    this.messageText = text;
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Ошибка отправки сообщения');
                this.messageText = text;
            } finally {
                this.sending = false;
            }
        },

        async checkNewMessages() {
            if (!this.selectedChatId || !this.lastMessageId) return;

            try {
                const response = await fetch(`<?= $getMessagesUrl ?>?chat_id=${this.selectedChatId}&after_id=${this.lastMessageId}`);
                const data = await response.json();

                if (data.success && data.messages.length > 0) {
                    // Reload the whole chat to get new messages
                    await this.selectChat(this.selectedChatId);
                }
            } catch (error) {
                console.error('Error checking messages:', error);
            }
        },

        async createLid() {
            if (this.creatingLid) return;

            this.creatingLid = true;

            try {
                const response = await fetch(`<?= $createLidUrl ?>?chat_id=${this.selectedChatId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': '<?= $csrfToken ?>'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.chatInfo.lid_id = data.lid_id;
                    this.chatInfo.lid_name = 'Лид';
                    if (window.$store && window.$store.toast) {
                        window.$store.toast.show('Лид создан', 'success');
                    }
                } else {
                    alert(data.message || 'Ошибка создания лида');
                }
            } catch (error) {
                console.error('Error creating lid:', error);
                alert('Ошибка создания лида');
            } finally {
                this.creatingLid = false;
            }
        },

        insertEmoji(event) {
            const emoji = event.detail.unicode;
            const input = this.$refs.messageInput;
            const start = input.selectionStart;
            const end = input.selectionEnd;

            this.messageText = this.messageText.substring(0, start) + emoji + this.messageText.substring(end);

            this.$nextTick(() => {
                input.focus();
                input.setSelectionRange(start + emoji.length, start + emoji.length);
            });
        },

        adjustTextarea() {
            const textarea = this.$refs.messageInput;
            textarea.style.height = '42px';
            textarea.style.height = Math.min(textarea.scrollHeight, 128) + 'px';
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
