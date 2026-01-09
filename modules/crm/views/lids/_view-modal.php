<?php
/**
 * Модальная карточка просмотра лида с табами
 */

use app\helpers\OrganizationUrl;
use app\models\Lids;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

// Funnel controller routes
$changeStatusUrl = OrganizationUrl::to(['lids-funnel/change-status']);
$getSalesScriptUrl = OrganizationUrl::to(['lids-funnel/get-sales-script']);

// Interaction controller routes
$toggleTagUrl = OrganizationUrl::to(['lids-interaction/toggle-tag']);
$updateFieldUrl = OrganizationUrl::to(['lids-interaction/update-field']);
$getWhatsappTemplatesUrl = OrganizationUrl::to(['lids-interaction/get-whatsapp-templates']);
$renderWhatsappMessageUrl = OrganizationUrl::to(['lids-interaction/render-whatsapp-message']);
$addInteractionUrl = OrganizationUrl::to(['lids-interaction/add-interaction']);

// Tag controller routes
$getTagsUrl = OrganizationUrl::to(['lid-tag/list']);
$toggleCustomTagUrl = OrganizationUrl::to(['lid-tag/toggle']);
$createTagUrl = OrganizationUrl::to(['lid-tag/create']);

// Получаем менеджеров для inline-select
$managers = \app\models\services\LidService::getManagersForDropdown();

// Причины отказа
$lostReasons = [
    'Дорого' => 'Дорого',
    'Нет времени' => 'Нет времени',
    'Выбрал конкурента' => 'Выбрал конкурента',
    'Передумал' => 'Передумал',
    'Не отвечает' => 'Не отвечает',
    'Другое' => 'Другое',
];
?>

<div x-data="{
        activeTab: 'info',
        editingField: null,
        editValue: '',

        // WhatsApp templates
        showWaTemplates: false,
        waTemplates: [],
        waTemplatesLoaded: false,
        selectedWaTemplate: null,
        waMessage: '',

        // Lost reason
        showLostModal: false,
        lostReason: '',
        customLostReason: '',

        // Sales script
        salesScript: null,
        salesScriptLoading: false,
        salesScriptStatus: null,
        expandedObjection: null,

        // Custom tags
        availableTags: [],
        tagsLoaded: false,
        showNewTagForm: false,
        newTagName: '',
        newTagColor: 'gray',

        // Quick note
        quickNoteText: '',
        quickNoteSaving: false,

        async loadTags() {
            if (this.tagsLoaded) return;
            try {
                const response = await fetch('<?= $getTagsUrl ?>');
                const data = await response.json();
                if (data.success) {
                    this.availableTags = data.tags;
                    this.tagsLoaded = true;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async toggleCustomTag(tagId) {
            const lid = $store.lids.viewingLid;
            if (!lid) return;

            try {
                const response = await fetch('<?= $toggleCustomTagUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || ''
                    },
                    body: `lid_id=${lid.id}&tag_id=${tagId}`
                });
                const data = await response.json();
                if (data.success) {
                    // Update local state
                    if (data.hasTag) {
                        const tag = this.availableTags.find(t => t.id === tagId);
                        if (tag && !lid.custom_tags.some(t => t.id === tagId)) {
                            lid.custom_tags.push(tag);
                        }
                    } else {
                        lid.custom_tags = lid.custom_tags.filter(t => t.id !== tagId);
                    }
                    if (Alpine.store('toast')) Alpine.store('toast').success(data.message);
                }
            } catch (e) {
                console.error(e);
            }
        },

        hasCustomTag(tagId) {
            return ($store.lids.viewingLid?.custom_tags || []).some(t => t.id === tagId);
        },

        async createTag() {
            if (!this.newTagName.trim()) return;

            try {
                const response = await fetch('<?= $createTagUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || ''
                    },
                    body: `name=${encodeURIComponent(this.newTagName)}&color=${this.newTagColor}`
                });
                const data = await response.json();
                if (data.success) {
                    this.availableTags.push(data.tag);
                    this.newTagName = '';
                    this.showNewTagForm = false;
                    if (Alpine.store('toast')) Alpine.store('toast').success(data.message);
                } else {
                    if (Alpine.store('toast')) Alpine.store('toast').error(data.message);
                }
            } catch (e) {
                console.error(e);
            }
        },

        async saveQuickNote() {
            const lid = $store.lids.viewingLid;
            if (!lid || !this.quickNoteText.trim() || this.quickNoteSaving) return;

            this.quickNoteSaving = true;
            try {
                const response = await fetch('<?= $addInteractionUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || ''
                    },
                    body: `lid_id=${lid.id}&type=note&comment=${encodeURIComponent(this.quickNoteText)}`
                });
                const data = await response.json();
                if (data.success) {
                    // Add new note to history
                    const newNote = {
                        id: Date.now(),
                        type: 'note',
                        type_label: 'Заметка',
                        type_color: 'blue',
                        comment: this.quickNoteText,
                        user_name: '<?= Html::encode(Yii::$app->user->identity->fio ?? 'Менеджер') ?>',
                        created_at: 'Только что'
                    };
                    if (!lid.history) lid.history = [];
                    lid.history.unshift(newNote);
                    this.quickNoteText = '';
                    if (Alpine.store('toast')) Alpine.store('toast').success('Заметка добавлена');
                } else {
                    if (Alpine.store('toast')) Alpine.store('toast').error(data.message || 'Ошибка');
                }
            } catch (e) {
                console.error(e);
                if (Alpine.store('toast')) Alpine.store('toast').error('Ошибка сохранения');
            } finally {
                this.quickNoteSaving = false;
            }
        },

        async toggleTag(tag) {
            const lid = $store.lids.viewingLid;
            if (!lid) return;

            try {
                const response = await fetch('<?= $toggleTagUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || ''
                    },
                    body: `id=${lid.id}&tag=${tag}`
                });
                const data = await response.json();
                if (data.success) {
                    $store.lids.viewingLid.tags = data.tags;
                    if (Alpine.store('toast')) Alpine.store('toast').success(data.message);
                }
            } catch (e) {
                console.error(e);
            }
        },

        // Show confirmation for PAID status
        showPaidConfirm: false,

        async confirmPaid() {
            await this.updateField('status', <?= Lids::STATUS_PAID ?>, true);
            this.showPaidConfirm = false;
        },

        async updateField(field, value, skipConfirm = false) {
            const lid = $store.lids.viewingLid;
            if (!lid) return;

            // Confirmation for PAID status
            if (field === 'status' && value == <?= Lids::STATUS_PAID ?> && !skipConfirm) {
                this.showPaidConfirm = true;
                return;
            }

            try {
                const response = await fetch('<?= $updateFieldUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || ''
                    },
                    body: `id=${lid.id}&field=${field}&value=${encodeURIComponent(value)}`
                });
                const data = await response.json();
                if (data.success) {
                    if (field === 'next_contact_date') {
                        $store.lids.viewingLid.next_contact_date = value;
                        $store.lids.viewingLid.next_contact_date_formatted = data.value;
                    } else if (field === 'manager_id') {
                        $store.lids.viewingLid.manager_id = value;
                        $store.lids.viewingLid.manager_name = data.value;
                    } else if (field === 'comment') {
                        $store.lids.viewingLid.comment = value;
                    } else if (field === 'status') {
                        // Update store without reload
                        $store.lids.viewingLid.status = parseInt(value);
                        $store.lids.viewingLid.status_label = data.status_label || '';
                        $store.lids.viewingLid.status_color = data.status_color || 'gray';

                        // Show toast
                        if (Alpine.store('toast')) {
                            Alpine.store('toast').success('Статус изменён');
                        }

                        // Update kanban board if visible (move card to new column)
                        this.updateKanbanCard(lid.id, parseInt(value));

                        // If PAID - reload to show conversion dialog
                        if (value == <?= Lids::STATUS_PAID ?>) {
                            setTimeout(() => location.reload(), 1500);
                        }
                    }
                    if (Alpine.store('toast')) Alpine.store('toast').success(data.message);
                } else {
                    if (Alpine.store('toast')) Alpine.store('toast').error(data.message);
                }
            } catch (e) {
                console.error(e);
            }
            this.editingField = null;
        },

        // Update kanban card position after status change
        updateKanbanCard(lidId, newStatus) {
            const card = document.querySelector('.kanban-card[data-id=' + lidId + ']');
            if (!card) return;

            const targetColumn = document.querySelector('.kanban-column[data-status=' + newStatus + '] .kanban-cards');
            if (targetColumn) {
                // Move card to new column
                card.classList.add('opacity-50', 'scale-95');
                setTimeout(() => {
                    targetColumn.prepend(card);
                    card.classList.remove('opacity-50', 'scale-95');
                    card.classList.add('ring-2', 'ring-primary-400');
                    setTimeout(() => card.classList.remove('ring-2', 'ring-primary-400'), 2000);
                }, 200);

                // Update column counts
                this.updateColumnCounts();
            }
        },

        updateColumnCounts() {
            document.querySelectorAll('.kanban-column').forEach(col => {
                const count = col.querySelectorAll('.kanban-card').length;
                const countEl = col.querySelector('.kanban-count');
                if (countEl) countEl.textContent = count;
            });
        },

        hasTag(tag) {
            return ($store.lids.viewingLid?.tags || []).includes(tag);
        },

        // WhatsApp шаблоны
        async loadWaTemplates() {
            if (this.waTemplatesLoaded) return;

            try {
                const response = await fetch('<?= $getWhatsappTemplatesUrl ?>');
                const data = await response.json();
                if (data.success) {
                    this.waTemplates = data.templates;
                    this.waTemplatesLoaded = true;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async selectWaTemplate(template) {
            this.selectedWaTemplate = template;
            const lid = $store.lids.viewingLid;
            if (!lid) return;

            try {
                const response = await fetch(`<?= $renderWhatsappMessageUrl ?>?lid_id=${lid.id}&template_id=${template.id}`);
                const data = await response.json();
                if (data.success) {
                    this.waMessage = data.message;
                    window.open(data.whatsapp_url, '_blank');
                    this.showWaTemplates = false;
                }
            } catch (e) {
                console.error(e);
            }
        },

        openWhatsAppDirect() {
            const lid = $store.lids.viewingLid;
            if (!lid) return;
            const phone = (lid.contact_phone || lid.phone || lid.parent_phone || '').replace(/\D/g, '');
            if (phone) {
                window.open('https://wa.me/' + phone, '_blank');
            }
        },

        // Lost reason handling
        openLostModal() {
            this.lostReason = '';
            this.customLostReason = '';
            this.showLostModal = true;
        },

        async confirmLost() {
            const lid = $store.lids.viewingLid;
            if (!lid) return;

            const reason = this.lostReason === 'Другое' ? this.customLostReason : this.lostReason;
            if (!reason) {
                if (Alpine.store('toast')) Alpine.store('toast').error('Укажите причину отказа');
                return;
            }

            try {
                const response = await fetch('<?= $changeStatusUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || ''
                    },
                    body: `id=${lid.id}&status=<?= Lids::STATUS_LOST ?>&comment=${encodeURIComponent('Причина: ' + reason)}`
                });
                const data = await response.json();
                if (data.success) {
                    if (Alpine.store('toast')) Alpine.store('toast').success('Лид отмечен как потерянный');
                    this.showLostModal = false;
                    location.reload();
                } else {
                    if (Alpine.store('toast')) Alpine.store('toast').error(data.message);
                }
            } catch (e) {
                console.error(e);
            }
        },

        async loadSalesScript(status) {
            if (this.salesScriptStatus === status && this.salesScript) return;
            if (this.salesScriptLoading) return;

            this.salesScriptLoading = true;
            this.salesScript = null;

            try {
                const response = await fetch(`<?= $getSalesScriptUrl ?>?status=${status}`);
                const data = await response.json();
                if (data.success && data.scripts.length > 0) {
                    this.salesScript = data.scripts[0];
                    this.salesScriptStatus = status;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.salesScriptLoading = false;
            }
        },

        copyScriptToClipboard() {
            if (!this.salesScript?.content) return;
            const lid = $store.lids.viewingLid;
            let text = this.salesScript.content
                .replace('[ИМЯ]', '<?= Yii::$app->user->identity->name ?? 'менеджер' ?>')
                .replace('[ЦЕНТР]', '<?= Yii::$app->user->identity->organization->name ?? 'наш центр' ?>')
                .replace('[ДАТА]', lid?.next_contact_date_formatted || '__')
                .replace('[ВРЕМЯ]', '__')
                .replace('[АДРЕС]', '<?= Yii::$app->user->identity->organization->address ?? '__' ?>')
                .replace('[СУММА]', '__');

            navigator.clipboard.writeText(text);
            if (Alpine.store('toast')) Alpine.store('toast').success('Скрипт скопирован');
        }
     }"
     x-show="$store.lids.viewingLid" class="space-y-4">

    <!-- Loading state -->
    <div x-show="$store.lids.isLoading" class="flex items-center justify-center py-8">
        <svg class="w-8 h-8 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    <!-- Content -->
    <template x-if="$store.lids.viewingLid && !$store.lids.isLoading">
        <div class="space-y-4">
            <!-- Header with status and tags -->
            <div class="flex items-start justify-between pb-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <!-- Аватар (WhatsApp или иконка) -->
                    <div class="w-12 h-12 rounded-full flex items-center justify-center overflow-hidden"
                         :class="!$store.lids.viewingLid?.whatsapp_profile_picture ? 'bg-primary-100' : ''">
                        <template x-if="$store.lids.viewingLid?.whatsapp_profile_picture">
                            <img :src="$store.lids.viewingLid.whatsapp_profile_picture"
                                 :alt="$store.lids.viewingLid?.fio"
                                 class="w-full h-full object-cover"
                                 @error="$store.lids.viewingLid.whatsapp_profile_picture = null">
                        </template>
                        <template x-if="!$store.lids.viewingLid?.whatsapp_profile_picture">
                            <span class="text-primary-600"><?= Icon::show('user', 'lg') ?></span>
                        </template>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900" x-text="$store.lids.viewingLid?.fio || 'Без имени'"></h3>
                        <p class="text-sm text-gray-500" x-text="$store.lids.viewingLid?.phone || $store.lids.viewingLid?.parent_phone || ''"></p>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <span class="badge badge-lg"
                          :class="{
                              'badge-primary': $store.lids.viewingLid?.status == <?= Lids::STATUS_NEW ?>,
                              'badge-info': $store.lids.viewingLid?.status == <?= Lids::STATUS_CONTACTED ?>,
                              'badge-warning': $store.lids.viewingLid?.status == <?= Lids::STATUS_TRIAL ?> || $store.lids.viewingLid?.status == <?= Lids::STATUS_THINKING ?>,
                              'badge-success': $store.lids.viewingLid?.status == <?= Lids::STATUS_PAID ?> || $store.lids.viewingLid?.status == <?= Lids::STATUS_ENROLLED ?>,
                              'badge-danger': $store.lids.viewingLid?.status == <?= Lids::STATUS_LOST ?>
                          }"
                          x-text="$store.lids.viewingLid?.status_label || ''"></span>
                    <!-- Days in status indicator -->
                    <span x-show="$store.lids.viewingLid?.days_in_status > 3"
                          class="text-xs"
                          :class="$store.lids.viewingLid?.is_stale ? 'text-orange-600' : 'text-gray-400'"
                          x-text="'В статусе ' + $store.lids.viewingLid?.days_in_status + ' дн.'"></span>
                </div>
            </div>

            <!-- Tags management -->
            <div class="pb-3 border-b border-gray-100" x-init="loadTags()">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-gray-500 mr-1">Метки:</span>

                    <!-- Custom tags from DB -->
                    <template x-for="tag in availableTags" :key="tag.id">
                        <button type="button"
                                @click="toggleCustomTag(tag.id)"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium transition-colors cursor-pointer"
                                :class="hasCustomTag(tag.id)
                                    ? `bg-${tag.color}-100 text-${tag.color}-700 ring-1 ring-${tag.color}-300`
                                    : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                            <span x-text="tag.name"></span>
                        </button>
                    </template>

                    <!-- Add new tag button -->
                    <button type="button"
                            @click="showNewTagForm = !showNewTagForm"
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors cursor-pointer border border-dashed border-gray-300">
                        <?= Icon::show('plus', 'xs') ?>
                        <span x-text="showNewTagForm ? 'Отмена' : 'Добавить'"></span>
                    </button>
                </div>

                <!-- New tag form -->
                <div x-show="showNewTagForm" x-collapse
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:leave="transition ease-in duration-100"
                     class="mt-3 flex items-center gap-2">
                    <input type="text" x-model="newTagName"
                           @keydown.enter="createTag()"
                           placeholder="Название тега..."
                           class="form-input text-sm py-1 px-2 w-32">
                    <select x-model="newTagColor" class="form-select text-sm py-1 px-2 w-24">
                        <option value="gray">Серый</option>
                        <option value="red">Красный</option>
                        <option value="orange">Оранжевый</option>
                        <option value="amber">Янтарный</option>
                        <option value="green">Зелёный</option>
                        <option value="blue">Синий</option>
                        <option value="purple">Фиолетовый</option>
                        <option value="pink">Розовый</option>
                    </select>
                    <button type="button" @click="createTag()"
                            class="btn btn-primary btn-sm py-1">
                        <?= Icon::show('check', 'xs') ?>
                        Создать
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="flex gap-4" aria-label="Tabs">
                    <button @click="activeTab = 'info'"
                            class="px-1 py-2 text-sm font-medium border-b-2 transition-colors"
                            :class="activeTab === 'info' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                        Информация
                    </button>
                    <button @click="activeTab = 'history'"
                            class="px-1 py-2 text-sm font-medium border-b-2 transition-colors"
                            :class="activeTab === 'history' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                        История
                    </button>
                    <button @click="activeTab = 'actions'"
                            class="px-1 py-2 text-sm font-medium border-b-2 transition-colors"
                            :class="activeTab === 'actions' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                        Действия
                    </button>
                    <button @click="activeTab = 'script'; loadSalesScript($store.lids.viewingLid?.status)"
                            class="px-1 py-2 text-sm font-medium border-b-2 transition-colors"
                            :class="activeTab === 'script' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                        <?= Icon::show('document-text', 'xs', 'inline') ?>
                        Скрипт
                    </button>
                </nav>
            </div>

            <!-- Tab: Info -->
            <div x-show="activeTab === 'info'" class="space-y-4">
                <!-- Contact info grid -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Child data -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Ребёнок</h4>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-sm">
                                <?= Icon::show('user', 'sm', 'text-gray-400') ?>
                                <span x-text="$store.lids.viewingLid?.fio || '—'"></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <?= Icon::show('phone', 'sm', 'text-gray-400') ?>
                                <a :href="'tel:' + $store.lids.viewingLid?.phone" class="text-primary-600 hover:underline" x-text="$store.lids.viewingLid?.phone || '—'"></a>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <?= Icon::show('building-office', 'sm', 'text-gray-400') ?>
                                <span x-text="$store.lids.viewingLid?.school || '—'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Parent data -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Родитель</h4>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-sm">
                                <?= Icon::show('user', 'sm', 'text-gray-400') ?>
                                <span x-text="$store.lids.viewingLid?.parent_fio || '—'"></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <?= Icon::show('phone', 'sm', 'text-gray-400') ?>
                                <a :href="'tel:' + $store.lids.viewingLid?.parent_phone" class="text-primary-600 hover:underline" x-text="$store.lids.viewingLid?.parent_phone || '—'"></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick actions -->
                <div class="flex flex-wrap gap-2 py-3 border-t border-b border-gray-100">
                    <a :href="'tel:' + ($store.lids.viewingLid?.contact_phone || $store.lids.viewingLid?.phone || $store.lids.viewingLid?.parent_phone)"
                       class="btn btn-sm btn-secondary">
                        <?= Icon::show('phone', 'sm', 'text-green-600') ?>
                        Позвонить
                    </a>

                    <!-- WhatsApp with template dropdown -->
                    <div class="relative" x-data="{ open: false }" @click.stop>
                        <div class="flex">
                            <button type="button"
                                    @click.stop="openWhatsAppDirect()"
                                    class="btn btn-sm btn-secondary rounded-r-none border-r-0">
                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                WhatsApp
                            </button>
                            <button type="button"
                                    @click.stop="open = !open; loadWaTemplates()"
                                    class="btn btn-sm btn-secondary rounded-l-none px-2">
                                <svg class="w-4 h-4 transition-transform duration-150" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Templates dropdown -->
                        <div x-show="open" @click.away="open = false" @click.stop
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 mt-1 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="p-2 border-b border-gray-100">
                                <span class="text-xs font-medium text-gray-500">Шаблоны сообщений</span>
                            </div>
                            <div class="max-h-64 overflow-y-auto py-1">
                                <template x-if="waTemplates.length === 0">
                                    <div class="px-3 py-2 text-sm text-gray-400">
                                        Шаблоны не найдены
                                    </div>
                                </template>
                                <template x-for="template in waTemplates" :key="template.id">
                                    <button type="button"
                                            @click="selectWaTemplate(template)"
                                            class="w-full px-3 py-2 text-left hover:bg-gray-50 transition-colors">
                                        <div class="text-sm font-medium text-gray-700" x-text="template.name"></div>
                                        <div class="text-xs text-gray-400 truncate" x-text="template.content.substring(0, 50) + '...'"></div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details with inline edit -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Источник:</span>
                        <span class="ml-2 font-medium" x-text="$store.lids.viewingLid?.source_label || '—'"></span>
                    </div>

                    <!-- Manager (inline editable) -->
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500">Менеджер:</span>
                        <template x-if="editingField !== 'manager_id'">
                            <span class="ml-2 font-medium cursor-pointer hover:text-primary-600 flex items-center gap-1"
                                  @click="editingField = 'manager_id'; editValue = $store.lids.viewingLid?.manager_id || ''">
                                <span x-text="$store.lids.viewingLid?.manager_name || '—'"></span>
                                <?= Icon::show('pencil', 'xs', 'text-gray-400') ?>
                            </span>
                        </template>
                        <template x-if="editingField === 'manager_id'">
                            <select x-model="editValue"
                                    @change="updateField('manager_id', editValue)"
                                    @blur="editingField = null"
                                    class="form-select text-sm py-1 px-2 w-36">
                                <option value="">—</option>
                                <?php foreach ($managers as $id => $name): ?>
                                    <option value="<?= $id ?>"><?= Html::encode($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </template>
                    </div>

                    <!-- Next contact (inline editable) -->
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500">След. контакт:</span>
                        <template x-if="editingField !== 'next_contact_date'">
                            <span class="ml-2 font-medium cursor-pointer hover:text-primary-600 flex items-center gap-1"
                                  :class="$store.lids.viewingLid?.is_overdue ? 'text-danger-600' : ''"
                                  @click="editingField = 'next_contact_date'; editValue = $store.lids.viewingLid?.next_contact_date || ''">
                                <span x-text="$store.lids.viewingLid?.next_contact_date_formatted || '—'"></span>
                                <?= Icon::show('pencil', 'xs', 'text-gray-400') ?>
                            </span>
                        </template>
                        <template x-if="editingField === 'next_contact_date'">
                            <input type="date" x-model="editValue"
                                   @change="updateField('next_contact_date', editValue)"
                                   @blur="editingField = null"
                                   class="form-input text-sm py-1 px-2 w-36">
                        </template>
                    </div>

                    <div>
                        <span class="text-gray-500">Создан:</span>
                        <span class="ml-2 font-medium" x-text="$store.lids.viewingLid?.created_at || '—'"></span>
                    </div>
                </div>

                <!-- Comment (inline editable) -->
                <div class="pt-3 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-500">Комментарий:</span>
                        <button @click="editingField = 'comment'; editValue = $store.lids.viewingLid?.comment || ''"
                                x-show="editingField !== 'comment'"
                                class="text-xs text-primary-600 hover:underline cursor-pointer">
                            Редактировать
                        </button>
                    </div>
                    <template x-if="editingField !== 'comment'">
                        <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3"
                           x-text="$store.lids.viewingLid?.comment || 'Нет комментария'"></p>
                    </template>
                    <template x-if="editingField === 'comment'">
                        <div class="space-y-2">
                            <textarea x-model="editValue" rows="3" class="form-input text-sm w-full"></textarea>
                            <div class="flex gap-2">
                                <button @click="updateField('comment', editValue)" class="btn btn-sm btn-primary">Сохранить</button>
                                <button @click="editingField = null" class="btn btn-sm btn-secondary">Отмена</button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Last contacts mini-section -->
                <div class="pt-3 border-t border-gray-100" x-show="$store.lids.viewingLid?.history?.length > 0">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-500">Последние контакты:</span>
                        <button @click="activeTab = 'history'" class="text-xs text-primary-600 hover:underline cursor-pointer">
                            Вся история
                        </button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="item in ($store.lids.viewingLid?.history || []).slice(0, 3)" :key="item.id">
                            <div class="flex items-center gap-2 text-sm p-2 bg-gray-50 rounded-lg">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0"
                                     :class="`bg-${item.type_color}-100 text-${item.type_color}-600`">
                                    <span x-show="item.type === 'call'"><?= Icon::show('phone', 'xs') ?></span>
                                    <span x-show="item.type === 'message' || item.type === 'whatsapp'"><?= Icon::show('chat-bubble-left', 'xs') ?></span>
                                    <span x-show="item.type === 'status_change'"><?= Icon::show('arrow-path', 'xs') ?></span>
                                    <span x-show="item.type === 'note'"><?= Icon::show('document-text', 'xs') ?></span>
                                    <span x-show="item.type === 'created'"><?= Icon::show('plus-circle', 'xs') ?></span>
                                    <span x-show="item.type === 'meeting'"><?= Icon::show('user-group', 'xs') ?></span>
                                    <span x-show="item.type === 'converted'"><?= Icon::show('check-circle', 'xs') ?></span>
                                </div>
                                <span class="text-gray-700 truncate flex-1" x-text="item.type_label"></span>
                                <span class="text-xs text-gray-400 flex-shrink-0" x-text="item.created_at"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Tab: History -->
            <div x-show="activeTab === 'history'" class="space-y-3">
                <!-- Quick note form -->
                <div class="flex items-start gap-2 p-3 bg-blue-50 rounded-lg border border-blue-100">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 text-blue-600">
                        <?= Icon::show('pencil-square', 'sm') ?>
                    </div>
                    <div class="flex-1">
                        <textarea x-model="quickNoteText"
                                  @keydown.ctrl.enter="saveQuickNote()"
                                  @keydown.meta.enter="saveQuickNote()"
                                  placeholder="Добавить заметку... (Ctrl+Enter для сохранения)"
                                  rows="2"
                                  class="form-input text-sm w-full resize-none"></textarea>
                        <div class="flex justify-end mt-2">
                            <button @click="saveQuickNote()"
                                    :disabled="!quickNoteText.trim() || quickNoteSaving"
                                    class="btn btn-sm btn-primary disabled:opacity-50">
                                <svg x-show="quickNoteSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-show="!quickNoteSaving"><?= Icon::show('check', 'sm') ?></span>
                                <span x-text="quickNoteSaving ? 'Сохранение...' : 'Добавить'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- История есть -->
                <template x-if="$store.lids.viewingLid?.history?.length > 0">
                    <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                        <template x-for="item in $store.lids.viewingLid.history" :key="item.id">
                            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <!-- Icon -->
                                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                                     :class="`bg-${item.type_color}-100 text-${item.type_color}-600`">
                                    <span x-show="item.type === 'call'"><?= Icon::show('phone', 'sm') ?></span>
                                    <span x-show="item.type === 'message' || item.type === 'whatsapp'"><?= Icon::show('chat-bubble-left', 'sm') ?></span>
                                    <span x-show="item.type === 'status_change'"><?= Icon::show('arrow-path', 'sm') ?></span>
                                    <span x-show="item.type === 'note'"><?= Icon::show('document-text', 'sm') ?></span>
                                    <span x-show="item.type === 'created'"><?= Icon::show('plus-circle', 'sm') ?></span>
                                    <span x-show="item.type === 'meeting'"><?= Icon::show('user-group', 'sm') ?></span>
                                    <span x-show="item.type === 'converted'"><?= Icon::show('check-circle', 'sm') ?></span>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-sm font-medium text-gray-900" x-text="item.type_label"></span>
                                        <span class="text-xs text-gray-400 flex-shrink-0" x-text="item.created_at"></span>
                                    </div>

                                    <!-- Status change description -->
                                    <p x-show="item.status_change" class="text-sm text-amber-600 mt-1" x-text="item.status_change"></p>

                                    <!-- Comment -->
                                    <p x-show="item.comment" class="text-sm text-gray-600 mt-1" x-text="item.comment"></p>

                                    <!-- Call duration -->
                                    <span x-show="item.call_duration" class="inline-flex items-center gap-1 text-xs text-gray-400 mt-1">
                                        <?= Icon::show('clock', 'xs') ?>
                                        <span x-text="item.call_duration"></span>
                                    </span>

                                    <!-- User -->
                                    <div class="text-xs text-gray-400 mt-1" x-text="item.user_name"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- История пуста -->
                <template x-if="!$store.lids.viewingLid?.history?.length">
                    <div class="text-center py-8">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <?= Icon::show('clock', 'w-6 h-6 text-gray-400') ?>
                        </div>
                        <p class="text-sm text-gray-500 mb-2">История взаимодействий пуста</p>
                        <a :href="'<?= OrganizationUrl::to(['lids/view']) ?>?id=' + $store.lids.viewingLid?.id"
                           class="text-primary-600 hover:underline text-xs">
                            Открыть полную карточку
                        </a>
                    </div>
                </template>
            </div>

            <!-- Tab: Actions -->
            <div x-show="activeTab === 'actions'" class="space-y-4">
                <!-- Funnel Progress Visualization (PHP-generated) -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Прогресс по воронке</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <?php
                        $funnelStatuses = [
                            Lids::STATUS_NEW => ['label' => 'Новый', 'color' => 'sky'],
                            Lids::STATUS_CONTACTED => ['label' => 'Связались', 'color' => 'blue'],
                            Lids::STATUS_TRIAL => ['label' => 'Пробное', 'color' => 'amber'],
                            Lids::STATUS_THINKING => ['label' => 'Думает', 'color' => 'gray'],
                            Lids::STATUS_ENROLLED => ['label' => 'Записан', 'color' => 'indigo'],
                            Lids::STATUS_PAID => ['label' => 'Оплатил', 'color' => 'green'],
                        ];
                        $idx = 0;
                        foreach ($funnelStatuses as $statusId => $info):
                            $idx++;
                        ?>
                            <div class="flex items-center">
                                <button type="button"
                                        @click="updateField('status', <?= $statusId ?>)"
                                        :disabled="$store.lids.viewingLid?.status === <?= $statusId ?>"
                                        class="relative group"
                                        title="<?= Html::encode($info['label']) ?>">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-200"
                                         :class="$store.lids.viewingLid?.status >= <?= $statusId ?>
                                            ? 'bg-<?= $info['color'] ?>-500 text-white shadow-sm'
                                            : 'bg-gray-200 text-gray-400 hover:bg-gray-300'">
                                        <?= $idx ?>
                                    </div>
                                    <div x-show="$store.lids.viewingLid?.status === <?= $statusId ?>"
                                         class="absolute -top-1 -right-1 w-3 h-3 bg-white rounded-full flex items-center justify-center shadow">
                                        <div class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></div>
                                    </div>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                                        <?= Html::encode($info['label']) ?>
                                    </div>
                                </button>
                                <?php if ($idx < count($funnelStatuses)): ?>
                                    <div class="w-4 h-1 rounded transition-colors duration-200"
                                         :class="$store.lids.viewingLid?.status > <?= $statusId ?> ? 'bg-green-400' : 'bg-gray-200'"></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Quick status buttons -->
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Быстрые действия</h4>
                    <div class="grid grid-cols-3 gap-2">
                        <?php foreach (Lids::getStatusList() as $status => $label): ?>
                            <?php if ($status == Lids::STATUS_LOST) continue; ?>
                            <button type="button"
                                    @click="updateField('status', <?= $status ?>)"
                                    :disabled="$store.lids.viewingLid?.status == <?= $status ?>"
                                    class="btn btn-sm btn-outline text-xs disabled:opacity-50 disabled:cursor-not-allowed
                                           <?php if ($status == Lids::STATUS_PAID): ?>
                                           bg-green-50 border-green-300 text-green-700 hover:bg-green-100
                                           <?php endif; ?>">
                                <?php if ($status == Lids::STATUS_PAID): ?>
                                    <?= Icon::show('check-circle', 'xs') ?>
                                <?php endif; ?>
                                <?= Html::encode($label) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <button type="button"
                            @click="openLostModal()"
                            :disabled="$store.lids.viewingLid?.status == <?= Lids::STATUS_LOST ?>"
                            class="btn btn-sm btn-outline-danger w-full disabled:opacity-50 disabled:cursor-not-allowed">
                        <?= Icon::show('x-circle', 'sm') ?>
                        Отметить как потерянный
                    </button>
                </div>
            </div>

            <!-- Tab: Script (Sales Script) -->
            <div x-show="activeTab === 'script'" class="space-y-4">
                <!-- Loading -->
                <div x-show="salesScriptLoading" class="flex items-center justify-center py-8">
                    <svg class="w-6 h-6 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>

                <!-- Script content -->
                <template x-if="salesScript && !salesScriptLoading">
                    <div class="space-y-4">
                        <!-- Script title -->
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-900" x-text="salesScript.title"></h4>
                            <button @click="copyScriptToClipboard()"
                                    class="text-xs text-primary-600 hover:underline flex items-center gap-1">
                                <?= Icon::show('clipboard-document', 'xs') ?>
                                Копировать
                            </button>
                        </div>

                        <!-- Script text -->
                        <div class="bg-gradient-to-br from-primary-50 to-primary-100/50 rounded-xl p-4 border border-primary-200">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed" x-text="salesScript.content"></p>
                        </div>

                        <!-- Tips -->
                        <template x-if="salesScript.tips && salesScript.tips.length > 0">
                            <div class="space-y-2">
                                <h5 class="text-xs font-semibold text-gray-500 uppercase tracking-wide flex items-center gap-1">
                                    <?= Icon::show('light-bulb', 'xs', 'text-amber-500') ?>
                                    Советы
                                </h5>
                                <ul class="space-y-1">
                                    <template x-for="(tip, index) in salesScript.tips" :key="index">
                                        <li class="flex items-start gap-2 text-sm text-gray-600">
                                            <span class="text-amber-500 mt-0.5">•</span>
                                            <span x-text="tip"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        <!-- Objections -->
                        <template x-if="salesScript.objections && salesScript.objections.length > 0">
                            <div class="space-y-2">
                                <h5 class="text-xs font-semibold text-gray-500 uppercase tracking-wide flex items-center gap-1">
                                    <?= Icon::show('chat-bubble-left-right', 'xs', 'text-red-500') ?>
                                    Работа с возражениями
                                </h5>
                                <div class="space-y-2">
                                    <template x-for="(obj, index) in salesScript.objections" :key="index">
                                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                                            <button @click="expandedObjection = expandedObjection === index ? null : index"
                                                    class="w-full px-3 py-2 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                                                <span class="text-sm font-medium text-red-600" x-text="'«' + obj.objection + '»'"></span>
                                                <svg class="w-4 h-4 text-gray-400 transition-transform"
                                                     :class="expandedObjection === index && 'rotate-180'"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                            <div x-show="expandedObjection === index" x-collapse
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:leave="transition ease-in duration-100">
                                                <div class="px-3 py-2 bg-green-50 border-t border-gray-100">
                                                    <p class="text-sm text-green-700" x-text="obj.response"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- No script -->
                <template x-if="!salesScript && !salesScriptLoading">
                    <div class="text-center py-8">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <?= Icon::show('document-text', 'w-6 h-6 text-gray-400') ?>
                        </div>
                        <p class="text-sm text-gray-500">Скрипт для этого статуса не найден</p>
                    </div>
                </template>
            </div>

            <!-- Lost Reason Modal (внутренняя модалка) -->
            <div x-show="showLostModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50"
                 @click.self="showLostModal = false">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-danger-100 flex items-center justify-center text-danger-600">
                            <?= Icon::show('x-circle', 'md') ?>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Причина отказа</h3>
                            <p class="text-sm text-gray-500">Укажите почему лид потерян</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <?php foreach ($lostReasons as $value => $label): ?>
                            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors"
                                   :class="lostReason === '<?= $value ?>' && 'border-danger-300 bg-danger-50'">
                                <input type="radio" name="lost_reason" value="<?= $value ?>"
                                       x-model="lostReason"
                                       class="form-radio text-danger-600">
                                <span class="text-sm text-gray-700"><?= Html::encode($label) ?></span>
                            </label>
                        <?php endforeach; ?>

                        <!-- Custom reason input -->
                        <div x-show="lostReason === 'Другое'" x-collapse
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:leave="transition ease-in duration-100">
                            <input type="text" x-model="customLostReason"
                                   placeholder="Укажите причину..."
                                   class="form-input w-full mt-2">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button"
                                @click="showLostModal = false"
                                class="btn btn-secondary flex-1">
                            Отмена
                        </button>
                        <button type="button"
                                @click="confirmLost()"
                                :disabled="!lostReason || (lostReason === 'Другое' && !customLostReason)"
                                class="btn btn-danger flex-1 disabled:opacity-50 disabled:cursor-not-allowed">
                            Подтвердить
                        </button>
                    </div>
                </div>
            </div>

            <!-- PAID Confirmation Modal -->
            <div x-show="showPaidConfirm"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50"
                 @click.self="showPaidConfirm = false">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                            <?= Icon::show('check-circle', 'w-7 h-7') ?>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Подтвердить оплату</h3>
                            <p class="text-sm text-gray-500">Лид будет отмечен как оплативший</p>
                        </div>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <?= Icon::show('information-circle', 'w-5 h-5 text-green-600 flex-shrink-0 mt-0.5') ?>
                            <div class="text-sm text-green-800">
                                <p class="font-medium">После подтверждения:</p>
                                <ul class="mt-1 space-y-1 text-green-700">
                                    <li>• Статус изменится на «Оплатил»</li>
                                    <li>• Лид будет конвертирован в ученика</li>
                                    <li>• Появится в разделе «Ученики»</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button"
                                @click="showPaidConfirm = false"
                                class="btn btn-secondary flex-1">
                            Отмена
                        </button>
                        <button type="button"
                                @click="confirmPaid()"
                                class="btn flex-1 bg-green-600 text-white hover:bg-green-700">
                            <?= Icon::show('check', 'sm') ?>
                            Подтвердить
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <a :href="'<?= OrganizationUrl::to(['lids/view']) ?>?id=' + $store.lids.viewingLid?.id"
                   class="btn btn-outline btn-sm">
                    <?= Icon::show('arrow-top-right-on-square', 'sm') ?>
                    Открыть полностью
                </a>
                <div class="flex gap-2">
                    <button type="button"
                            @click="$dispatch('close-modal', 'lids-view-modal')"
                            class="btn btn-secondary">
                        Закрыть
                    </button>
                    <button type="button"
                            @click="$store.lids.openEdit()"
                            class="btn btn-primary">
                        <?= Icon::show('pencil', 'sm') ?>
                        Редактировать
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
