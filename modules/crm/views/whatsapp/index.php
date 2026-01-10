<?php

use app\models\WhatsappSession;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var WhatsappSession|null $session */
/** @var bool $apiAvailable */
/** @var array|null $webhookDiagnostic */
/** @var string|null $disconnectReason */

$this->title = 'WhatsApp';
$this->params['breadcrumbs'][] = ['label' => 'CRM', 'url' => ['/crm']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="whatsapp-index" x-data="whatsappConnection()" x-init="init()">
    <div class="max-w-2xl mx-auto">
        <!-- Заголовок -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">WhatsApp</h1>
                    <p class="text-sm text-gray-500">Интеграция с клиентами</p>
                </div>
            </div>

            <?php if ($session && $session->isConnected()): ?>
                <a href="<?= \yii\helpers\Url::to(['/crm/whatsapp/chats', 'oid' => \app\models\Organizations::getCurrentOrganizationId()]) ?>" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Открыть чаты
                </a>
            <?php endif; ?>
        </div>

        <!-- Предупреждение о рисках -->
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <svg class="w-5 h-5 text-amber-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-amber-800">Важная информация</h3>
                    <p class="text-sm text-amber-700 mt-1">
                        Используется неофициальный API WhatsApp. Это может привести к блокировке номера.
                        Рекомендуется использовать отдельный номер телефона для CRM.
                    </p>
                </div>
            </div>
        </div>

        <?php if (!$apiAvailable): ?>
            <!-- API недоступен -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-lg font-medium text-red-800 mb-2">WhatsApp API недоступен</h3>
                <p class="text-red-600 mb-4">
                    Убедитесь что Docker контейнер запущен.<br>
                    Команда: <code class="bg-red-100 px-2 py-1 rounded">cd whatsapp-service && docker compose up -d</code>
                </p>
                <button onclick="location.reload()" class="btn btn-outline-danger">
                    Проверить снова
                </button>
            </div>

        <?php elseif (!$session): ?>
            <!-- Нет сессии - показываем кнопку подключения -->
            <div class="bg-white border rounded-lg p-8 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">WhatsApp не подключен</h3>
                <p class="text-gray-500 mb-6">Подключите WhatsApp чтобы получать сообщения от клиентов и отвечать им прямо из CRM</p>
                <button @click="connect()" :disabled="loading" class="btn btn-success btn-lg">
                    <span x-show="!loading">Подключить WhatsApp</span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Подключение...
                    </span>
                </button>
            </div>

        <?php elseif ($session->status === WhatsappSession::STATUS_CONNECTING): ?>
            <!-- Сообщение о причине отключения -->
            <?php if (!empty($disconnectReason) && $disconnectReason === 'device_removed'): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-yellow-800">Устройство было отключено</h3>
                            <p class="text-sm text-yellow-700 mt-1">
                                Вы вышли из WhatsApp на телефоне или удалили связанное устройство.
                                Отсканируйте QR-код чтобы переподключиться. Ваши чаты сохранены.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Ожидание сканирования QR -->
            <div class="bg-white border rounded-lg p-8 text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Отсканируйте QR-код</h3>
                <p class="text-gray-500 mb-6">Откройте WhatsApp на телефоне → Настройки → Связанные устройства → Привязать устройство</p>

                <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-lg mb-6" id="qr-container">
                    <?php if ($session->qr_code): ?>
                        <img src="data:image/png;base64,<?= $session->qr_code ?>" alt="QR Code" class="w-64 h-64" x-ref="qrImage">
                    <?php else: ?>
                        <div class="w-64 h-64 flex items-center justify-center bg-gray-100">
                            <svg class="animate-spin h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex justify-center gap-4">
                    <button @click="refreshQr()" :disabled="loading" class="btn btn-outline-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Обновить QR
                    </button>
                    <button @click="checkStatus()" :disabled="checking" class="btn btn-primary">
                        <span x-show="!checking">Проверить статус</span>
                        <span x-show="checking" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Проверка...
                        </span>
                    </button>
                </div>

                <p class="text-sm text-gray-400 mt-4">
                    Статус обновляется автоматически каждые 5 секунд
                </p>
            </div>

        <?php elseif ($session->status === WhatsappSession::STATUS_DISCONNECTED): ?>
            <!-- Отключён - нужно переподключение -->
            <div class="bg-white border rounded-lg p-8 text-center">
                <?php if (!empty($disconnectReason) && $disconnectReason === 'device_removed'): ?>
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">WhatsApp отключен</h3>
                    <p class="text-gray-500 mb-2">Вы вышли из WhatsApp на телефоне или удалили связанное устройство.</p>
                    <p class="text-sm text-green-600 mb-6">Ваши чаты и история сообщений сохранены.</p>
                <?php else: ?>
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">WhatsApp отключен</h3>
                    <p class="text-gray-500 mb-6">Соединение было потеряно. Переподключитесь чтобы продолжить работу.</p>
                <?php endif; ?>

                <button @click="reconnect()" :disabled="loading" class="btn btn-success btn-lg">
                    <span x-show="!loading">
                        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Переподключить
                    </span>
                    <span x-show="loading" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Подключение...
                    </span>
                </button>
            </div>

        <?php else: ?>
            <!-- Подключено -->
            <div class="bg-white border rounded-lg overflow-hidden">
                <div class="bg-green-50 p-6 border-b border-green-100">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-green-800">WhatsApp подключен</h3>
                            <p class="text-green-600">
                                <?= Html::encode($session->phone_number ? '+' . $session->phone_number : 'Номер определяется...') ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-gray-900" x-text="unreadCount">0</div>
                            <div class="text-sm text-gray-500">Непрочитанных</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-gray-900">
                                <?= $session->connected_at ? Yii::$app->formatter->asRelativeTime($session->connected_at) : '-' ?>
                            </div>
                            <div class="text-sm text-gray-500">Подключен</div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <a href="<?= \yii\helpers\Url::to(['/crm/whatsapp/chats', 'oid' => \app\models\Organizations::getCurrentOrganizationId()]) ?>" class="btn btn-primary flex-1">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Открыть чаты
                        </a>
                        <button @click="disconnect()" class="btn btn-outline-danger">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Отключить
                        </button>
                    </div>

                    <!-- Дополнительные действия -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Не обновляются статусы доставки?</span>
                            <a href="<?= \yii\helpers\Url::to(['/crm/whatsapp/reconfigure-webhook', 'oid' => \app\models\Organizations::getCurrentOrganizationId()]) ?>"
                               class="text-sm text-blue-600 hover:text-blue-800">
                                Переконфигурировать webhook
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function whatsappConnection() {
    return {
        loading: false,
        checking: false,
        status: '<?= $session ? $session->status : 'not_created' ?>',
        unreadCount: 0,
        pollInterval: null,

        init() {
            // Запускаем polling если в статусе connecting
            if (this.status === 'connecting') {
                this.startPolling();
            } else if (this.status === 'connected') {
                this.checkStatus();
            }
        },

        async connect() {
            this.loading = true;
            try {
                const response = await fetch('<?= \yii\helpers\Url::to(['/crm/whatsapp/connect', 'oid' => \app\models\Organizations::getCurrentOrganizationId()]) ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>',
                        'Content-Type': 'application/json',
                    },
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Ошибка подключения');
                }
            } catch (e) {
                alert('Ошибка сети');
            } finally {
                this.loading = false;
            }
        },

        async reconnect() {
            // Переподключение - то же что и connect
            await this.connect();
        },

        async disconnect() {
            if (!confirm('Отключить WhatsApp? Все активные чаты сохранятся.')) return;

            this.loading = true;
            try {
                const response = await fetch('<?= \yii\helpers\Url::to(['/crm/whatsapp/disconnect', 'oid' => \app\models\Organizations::getCurrentOrganizationId()]) ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>',
                    },
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch (e) {
                alert('Ошибка сети');
            } finally {
                this.loading = false;
            }
        },

        async refreshQr() {
            this.loading = true;
            try {
                const response = await fetch('<?= \yii\helpers\Url::to(['/crm/whatsapp/get-qr-code', 'oid' => \app\models\Organizations::getCurrentOrganizationId()]) ?>');
                const data = await response.json();
                if (data.qr_code) {
                    const img = this.$refs.qrImage;
                    if (img) {
                        img.src = 'data:image/png;base64,' + data.qr_code;
                    }
                }
                if (data.status === 'connected') {
                    location.reload();
                }
            } catch (e) {
                console.error('Error refreshing QR:', e);
            } finally {
                this.loading = false;
            }
        },

        async checkStatus() {
            this.checking = true;
            try {
                const response = await fetch('<?= \yii\helpers\Url::to(['/crm/whatsapp/get-status', 'oid' => \app\models\Organizations::getCurrentOrganizationId()]) ?>');
                const data = await response.json();

                console.log('WhatsApp status response:', data);

                const oldStatus = this.status;
                this.status = data.status;
                this.unreadCount = data.unread_count || 0;

                // Если статус изменился на connected - перезагружаем страницу
                if (data.status === 'connected' && oldStatus !== 'connected') {
                    console.log('Status changed to connected, reloading...');
                    location.reload();
                    return;
                }

                // Обновляем QR-код если есть
                if (data.qr_code && this.$refs.qrImage) {
                    this.$refs.qrImage.src = 'data:image/png;base64,' + data.qr_code;
                }
            } catch (e) {
                console.error('Error checking status:', e);
            } finally {
                this.checking = false;
            }
        },

        startPolling() {
            this.pollInterval = setInterval(() => this.checkStatus(), 5000);
        },

        destroy() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
            }
        }
    };
}
</script>
