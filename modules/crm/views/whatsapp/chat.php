<?php

use app\models\WhatsappChat;
use app\models\WhatsappMessage;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var WhatsappChat $chat */
/** @var WhatsappMessage[] $messages */

$this->title = $chat->getDisplayName() . ' - WhatsApp';
$this->params['breadcrumbs'][] = ['label' => 'CRM', 'url' => ['/crm']];
$this->params['breadcrumbs'][] = ['label' => 'WhatsApp', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Чаты', 'url' => ['chats']];
$this->params['breadcrumbs'][] = $chat->getDisplayName();
?>

<div class="whatsapp-chat h-full" x-data="whatsappChat(<?= $chat->id ?>)">
    <div class="flex h-[calc(100vh-180px)]">
        <!-- Чат -->
        <div class="flex-1 flex flex-col bg-white border rounded-lg overflow-hidden">
            <!-- Заголовок чата -->
            <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50">
                <div class="flex items-center gap-3">
                    <a href="<?= Url::to(['chats']) ?>" class="text-gray-400 hover:text-gray-600 lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>

                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center <?= $chat->lid_id ? 'bg-blue-100' : '' ?>">
                        <?php if ($chat->lid): ?>
                            <span class="text-blue-600 font-medium"><?= mb_substr($chat->getDisplayName(), 0, 1) ?></span>
                        <?php else: ?>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h2 class="font-medium text-gray-900"><?= Html::encode($chat->getDisplayName()) ?></h2>
                        <p class="text-sm text-gray-500"><?= Html::encode($chat->getFormattedPhone()) ?></p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <?php if ($chat->lid): ?>
                        <a href="<?= Url::to(['/crm/lids/view', 'id' => $chat->lid_id]) ?>"
                           class="btn btn-sm btn-outline-primary">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Лид
                        </a>
                    <?php else: ?>
                        <button @click="createLid()" class="btn btn-sm btn-outline-success">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Создать лид
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Сообщения -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-100" id="messages-container" x-ref="messagesContainer">
                <?php foreach ($messages as $message): ?>
                    <?= $this->render('_message', ['message' => $message]) ?>
                <?php endforeach; ?>

                <?php if (empty($messages)): ?>
                    <div class="text-center text-gray-400 py-8">
                        <p>Начните переписку</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Форма отправки -->
            <div class="p-4 border-t bg-white">
                <form @submit.prevent="sendMessage()" class="flex gap-2">
                    <input type="text" x-model="newMessage" placeholder="Введите сообщение..."
                           class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           :disabled="sending">
                    <button type="submit" :disabled="!newMessage.trim() || sending"
                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="!sending" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <svg x-show="sending" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Боковая панель с информацией -->
        <div class="w-80 ml-4 bg-white border rounded-lg overflow-hidden hidden xl:block">
            <div class="p-4 border-b">
                <h3 class="font-medium text-gray-900">Информация</h3>
            </div>

            <div class="p-4 space-y-4">
                <!-- Контактная информация -->
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wider">Телефон</label>
                    <p class="font-medium"><?= Html::encode($chat->getFormattedPhone()) ?></p>
                </div>

                <?php if ($chat->remote_name): ?>
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wider">Имя в WhatsApp</label>
                        <p class="font-medium"><?= Html::encode($chat->remote_name) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($chat->lid): ?>
                    <div class="border-t pt-4">
                        <label class="text-xs text-gray-500 uppercase tracking-wider">Связанный лид</label>
                        <div class="mt-2 p-3 bg-blue-50 rounded-lg">
                            <p class="font-medium text-blue-900"><?= Html::encode($chat->lid->fio ?: $chat->lid->parent_fio) ?></p>
                            <p class="text-sm text-blue-700">Статус: <?= $chat->lid->getStatusLabel() ?></p>
                            <a href="<?= Url::to(['/crm/lids/view', 'id' => $chat->lid_id]) ?>"
                               class="text-sm text-blue-600 hover:underline mt-1 inline-block">
                                Открыть карточку →
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-500 mb-2">Чат не привязан к лиду</p>
                        <button @click="createLid()" class="btn btn-sm btn-success w-full">
                            Создать лид из чата
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function whatsappChat(chatId) {
    return {
        chatId: chatId,
        newMessage: '',
        sending: false,
        lastMessageId: <?= !empty($messages) ? end($messages)->id : 0 ?>,
        pollInterval: null,

        init() {
            this.scrollToBottom();
            this.startPolling();
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },

        async sendMessage() {
            if (!this.newMessage.trim() || this.sending) return;

            this.sending = true;
            const text = this.newMessage;
            this.newMessage = '';

            try {
                const response = await fetch('<?= Url::to(['send-message']) ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        chat_id: this.chatId,
                        text: text,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    // Добавляем сообщение в UI
                    this.appendMessage({
                        id: data.data.id,
                        content: text,
                        is_from_me: true,
                        created_at: data.data.created_at,
                    });
                    this.lastMessageId = data.data.id;
                } else {
                    alert(data.message || 'Ошибка отправки');
                    this.newMessage = text;
                }
            } catch (e) {
                alert('Ошибка сети');
                this.newMessage = text;
            } finally {
                this.sending = false;
            }
        },

        appendMessage(message) {
            const container = this.$refs.messagesContainer;
            const div = document.createElement('div');
            div.className = 'flex ' + (message.is_from_me ? 'justify-end' : 'justify-start');
            div.innerHTML = `
                <div class="max-w-[70%] ${message.is_from_me ? 'bg-green-500 text-white' : 'bg-white'} rounded-lg px-4 py-2 shadow">
                    <p class="whitespace-pre-wrap">${this.escapeHtml(message.content)}</p>
                    <p class="text-xs ${message.is_from_me ? 'text-green-100' : 'text-gray-400'} text-right mt-1">${message.created_at}</p>
                </div>
            `;
            container.appendChild(div);
            this.scrollToBottom();
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        async checkNewMessages() {
            try {
                const response = await fetch(`<?= Url::to(['get-messages']) ?>&chat_id=${this.chatId}&after_id=${this.lastMessageId}`);
                const data = await response.json();

                if (data.success && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        if (msg.id > this.lastMessageId) {
                            this.appendMessage(msg);
                            this.lastMessageId = msg.id;
                        }
                    });
                }
            } catch (e) {
                console.error('Error checking messages:', e);
            }
        },

        startPolling() {
            this.pollInterval = setInterval(() => this.checkNewMessages(), 3000);
        },

        async createLid() {
            if (!confirm('Создать лид из этого чата?')) return;

            try {
                const response = await fetch(`<?= Url::to(['create-lid-from-chat']) ?>&chat_id=${this.chatId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>',
                    },
                });
                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Ошибка создания');
                }
            } catch (e) {
                alert('Ошибка сети');
            }
        },

        destroy() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
            }
        }
    };
}
</script>
