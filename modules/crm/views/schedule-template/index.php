<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\Modal;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $templates */

$this->title = Yii::t('main', 'Шаблоны расписания');
?>

<div class="schedule-template-index" x-data="scheduleTemplateIndex()">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
        <button type="button"
                @click="$dispatch('open-modal', 'create-template-modal')"
                class="btn btn-primary">
            <?= Icon::svg('plus', ['class' => 'w-5 h-5 mr-2']) ?>
            Создать шаблон
        </button>
    </div>

    <!-- Templates Grid -->
    <?php if (empty($templates)): ?>
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <?= Icon::svg('calendar', ['class' => 'w-16 h-16 mx-auto']) ?>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Нет шаблонов</h3>
            <p class="text-gray-500 mb-4">Создайте первый шаблон расписания для организации работы</p>
            <button type="button"
                    @click="$dispatch('open-modal', 'create-template-modal')"
                    class="btn btn-primary">
                <?= Icon::svg('plus', ['class' => 'w-5 h-5 mr-2']) ?>
                Создать шаблон
            </button>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($templates as $template): ?>
                <div class="card hover:shadow-lg transition-shadow duration-200">
                    <div class="card-body">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <?php if ($template['is_default']): ?>
                                    <span class="text-yellow-500" title="Шаблон по умолчанию">
                                        <?= Icon::svg('star-filled', ['class' => 'w-5 h-5']) ?>
                                    </span>
                                <?php endif; ?>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?= Html::encode($template['name']) ?>
                                </h3>
                            </div>
                            <div class="relative" x-data="{ open: false }">
                                <button type="button"
                                        @click="open = !open"
                                        class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                    <?= Icon::svg('dots-vertical', ['class' => 'w-5 h-5']) ?>
                                </button>
                                <div x-show="open"
                                     @click.away="open = false"
                                     x-transition
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border z-10">
                                    <a href="<?= OrganizationUrl::to(['schedule-template/view', 'id' => $template['id']]) ?>"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <?= Icon::svg('eye', ['class' => 'w-4 h-4 inline mr-2']) ?>
                                        Открыть
                                    </a>
                                    <button type="button"
                                            @click="duplicateTemplate(<?= $template['id'] ?>, '<?= Html::encode($template['name']) ?>')"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <?= Icon::svg('duplicate', ['class' => 'w-4 h-4 inline mr-2']) ?>
                                        Дублировать
                                    </button>
                                    <button type="button"
                                            @click="editTemplate(<?= $template['id'] ?>, '<?= Html::encode($template['name']) ?>', '<?= Html::encode($template['description'] ?? '') ?>')"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <?= Icon::svg('pencil', ['class' => 'w-4 h-4 inline mr-2']) ?>
                                        Редактировать
                                    </button>
                                    <?php if (!$template['is_default']): ?>
                                        <button type="button"
                                                @click="deleteTemplate(<?= $template['id'] ?>, '<?= Html::encode($template['name']) ?>')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                            <?= Icon::svg('trash', ['class' => 'w-4 h-4 inline mr-2']) ?>
                                            Удалить
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <?php if (!empty($template['description'])): ?>
                            <p class="text-sm text-gray-500 mb-3"><?= Html::encode($template['description']) ?></p>
                        <?php endif; ?>

                        <!-- Stats -->
                        <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                            <div class="flex items-center gap-1">
                                <?= Icon::svg('academic-cap', ['class' => 'w-4 h-4']) ?>
                                <span><?= $template['lessons_count'] ?> занятий</span>
                            </div>
                            <?php if (!$template['is_active']): ?>
                                <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs">
                                    Неактивен
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <a href="<?= OrganizationUrl::to(['schedule-template/view', 'id' => $template['id']]) ?>"
                               class="btn btn-primary flex-1 text-center">
                                Открыть
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Create Template Modal -->
    <?php Modal::begin([
        'id' => 'create-template-modal',
        'title' => 'Создать шаблон',
        'size' => 'md',
    ]); ?>
    <form @submit.prevent="createTemplate">
        <div class="space-y-4">
            <div>
                <label class="form-label">Название</label>
                <input type="text" x-model="newTemplate.name" class="form-input" required placeholder="Например: Летнее расписание">
            </div>
            <div>
                <label class="form-label">Описание</label>
                <textarea x-model="newTemplate.description" class="form-input" rows="2" placeholder="Необязательно"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" x-model="newTemplate.is_default" id="is_default" class="form-checkbox">
                <label for="is_default" class="text-sm text-gray-700">Сделать шаблоном по умолчанию</label>
            </div>
        </div>
        <div class="modal-footer mt-6">
            <button type="button" @click="$dispatch('close-modal', 'create-template-modal')" class="btn btn-secondary">
                Отмена
            </button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
                <span x-show="loading" class="loading loading-sm mr-2"></span>
                Создать
            </button>
        </div>
    </form>
    <?php Modal::end(); ?>

    <!-- Edit Template Modal -->
    <?php Modal::begin([
        'id' => 'edit-template-modal',
        'title' => 'Редактировать шаблон',
        'size' => 'md',
    ]); ?>
    <form @submit.prevent="updateTemplate">
        <div class="space-y-4">
            <div>
                <label class="form-label">Название</label>
                <input type="text" x-model="editingTemplate.name" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Описание</label>
                <textarea x-model="editingTemplate.description" class="form-input" rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer mt-6">
            <button type="button" @click="$dispatch('close-modal', 'edit-template-modal')" class="btn btn-secondary">
                Отмена
            </button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
                <span x-show="loading" class="loading loading-sm mr-2"></span>
                Сохранить
            </button>
        </div>
    </form>
    <?php Modal::end(); ?>
</div>

<script>
function scheduleTemplateIndex() {
    return {
        loading: false,
        newTemplate: {
            name: '',
            description: '',
            is_default: false
        },
        editingTemplate: {
            id: null,
            name: '',
            description: ''
        },

        async createTemplate() {
            this.loading = true;
            try {
                const response = await fetch('<?= OrganizationUrl::to(['schedule-template/create']) ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
                    },
                    body: new URLSearchParams({
                        name: this.newTemplate.name,
                        description: this.newTemplate.description,
                        is_default: this.newTemplate.is_default ? 1 : 0
                    })
                });
                const result = await response.json();
                if (result.success && result.redirect) {
                    window.location.href = result.redirect;
                } else {
                    alert(result.message || 'Ошибка создания');
                }
            } catch (e) {
                console.error(e);
                alert('Ошибка сети');
            } finally {
                this.loading = false;
            }
        },

        editTemplate(id, name, description) {
            this.editingTemplate = { id, name, description };
            this.$dispatch('open-modal', 'edit-template-modal');
        },

        async updateTemplate() {
            this.loading = true;
            try {
                const response = await fetch('<?= OrganizationUrl::to(['schedule-template/update']) ?>?id=' + this.editingTemplate.id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
                    },
                    body: new URLSearchParams({
                        name: this.editingTemplate.name,
                        description: this.editingTemplate.description
                    })
                });
                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Ошибка');
                }
            } catch (e) {
                console.error(e);
                alert('Ошибка сети');
            } finally {
                this.loading = false;
            }
        },

        async duplicateTemplate(id, name) {
            const newName = prompt('Название копии:', name + ' (копия)');
            if (!newName) return;

            try {
                const response = await fetch('<?= OrganizationUrl::to(['schedule-template/duplicate']) ?>?id=' + id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
                    },
                    body: new URLSearchParams({ name: newName })
                });
                const result = await response.json();
                if (result.success && result.redirect) {
                    window.location.href = result.redirect;
                } else {
                    alert(result.message || 'Ошибка');
                }
            } catch (e) {
                console.error(e);
                alert('Ошибка сети');
            }
        },

        async deleteTemplate(id, name) {
            if (!confirm('Удалить шаблон "' + name + '" и все его занятия?')) return;

            try {
                const response = await fetch('<?= OrganizationUrl::to(['schedule-template/delete']) ?>?id=' + id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
                    }
                });
                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Ошибка');
                }
            } catch (e) {
                console.error(e);
                alert('Ошибка сети');
            }
        }
    };
}
</script>
