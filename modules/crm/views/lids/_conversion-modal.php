<?php
/**
 * Модальное окно конверсии лида в ученика
 * Используется в kanban.php через Alpine.js
 *
 * @var yii\web\View $this
 */

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;

$convertUrl = OrganizationUrl::to(['lids-funnel/convert-ajax']);
$getConversionDataUrl = OrganizationUrl::to(['lids-funnel/get-conversion-data']);
$getGroupsUrl = OrganizationUrl::to(['lids-funnel/get-groups-by-tariff']);
?>

<!-- Модалка конверсии -->
<div x-show="showConversionModal"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="flex min-h-full items-center justify-center p-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/50" @click="closeConversionModal()"></div>

        <!-- Modal content -->
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg transform transition-all"
             @click.away="closeConversionModal()">

            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Конверсия в ученика</h3>
                    <p class="text-sm text-gray-500 mt-0.5" x-text="conversionLid?.fio"></p>
                </div>
                <button type="button" @click="closeConversionModal()"
                        class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                    <?= Icon::show('x-mark', 'md') ?>
                </button>
            </div>

            <!-- Body -->
            <div class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">

                <!-- Информация из лида -->
                <div class="bg-gray-50 rounded-lg p-3 space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Телефон:</span>
                        <span class="font-medium" x-text="conversionLid?.contact_phone || conversionLid?.phone || '-'"></span>
                    </div>
                    <div class="flex justify-between" x-show="conversionLid?.parent_fio">
                        <span class="text-gray-500">Родитель:</span>
                        <span class="font-medium" x-text="conversionLid?.parent_fio"></span>
                    </div>
                </div>

                <!-- Форма -->
                <form @submit.prevent="submitConversion()">

                    <!-- ИИН -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            ИИН <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               x-model="conversionForm.iin"
                               maxlength="12"
                               pattern="\d{12}"
                               placeholder="12 цифр"
                               class="form-input w-full"
                               :class="{'border-red-500': conversionErrors.iin}">
                        <p class="text-red-500 text-xs mt-1" x-show="conversionErrors.iin" x-text="conversionErrors.iin"></p>
                    </div>

                    <!-- Пол и дата рождения -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Пол <span class="text-red-500">*</span>
                            </label>
                            <select x-model="conversionForm.sex" class="form-select w-full">
                                <option value="">Выберите</option>
                                <option value="1">Мужской</option>
                                <option value="2">Женский</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Дата рождения</label>
                            <input type="date" x-model="conversionForm.birth_date" class="form-input w-full">
                        </div>
                    </div>

                    <!-- Чекбокс: добавить в группу -->
                    <div class="border border-gray-200 rounded-lg p-3 mb-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="conversionForm.add_to_group" class="form-checkbox">
                            <span class="font-medium text-gray-700">Добавить в группу</span>
                        </label>

                        <div x-show="conversionForm.add_to_group" x-collapse class="mt-3 space-y-3">
                            <!-- Тариф -->
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Тариф</label>
                                <select x-model="conversionForm.tariff_id"
                                        @change="loadGroups()"
                                        class="form-select w-full text-sm">
                                    <option value="">Выберите тариф</option>
                                    <template x-for="(name, id) in tariffs" :key="id">
                                        <option :value="id" x-text="name"></option>
                                    </template>
                                </select>
                            </div>
                            <!-- Группа -->
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Группа</label>
                                <select x-model="conversionForm.group_id" class="form-select w-full text-sm">
                                    <option value="">Выберите группу</option>
                                    <template x-for="(name, id) in groups" :key="id">
                                        <option :value="id" x-text="name"></option>
                                    </template>
                                </select>
                            </div>
                            <!-- Скидка -->
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600">Скидка:</label>
                                <input type="number" x-model="conversionForm.sale"
                                       min="0" max="100"
                                       class="form-input w-20 text-sm text-center">
                                <span class="text-gray-500">%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Чекбокс: принять оплату -->
                    <div class="border border-gray-200 rounded-lg p-3 mb-4"
                         :class="{'border-green-300 bg-green-50': conversionForm.create_payment}">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="conversionForm.create_payment" class="form-checkbox text-green-600">
                            <span class="font-medium text-gray-700">Принять оплату</span>
                        </label>

                        <div x-show="conversionForm.create_payment" x-collapse class="mt-3 space-y-3">
                            <!-- Сумма -->
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Сумма</label>
                                <div class="relative">
                                    <input type="number" x-model="conversionForm.payment_amount"
                                           min="0" step="100"
                                           class="form-input w-full pr-12">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">тг</span>
                                </div>
                            </div>
                            <!-- Метод оплаты -->
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Метод оплаты</label>
                                <select x-model="conversionForm.payment_method_id" class="form-select w-full text-sm">
                                    <option value="">Выберите</option>
                                    <template x-for="(name, id) in payMethods" :key="id">
                                        <option :value="id" x-text="name"></option>
                                    </template>
                                </select>
                            </div>
                            <!-- Дата и квитанция -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Дата</label>
                                    <input type="date" x-model="conversionForm.payment_date" class="form-input w-full text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">№ квитанции</label>
                                    <input type="text" x-model="conversionForm.payment_number"
                                           class="form-input w-full text-sm" placeholder="Опционально">
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3 p-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                <button type="button" @click="closeConversionModal()"
                        class="btn btn-secondary">
                    Отмена
                </button>
                <button type="button" @click="submitConversion()"
                        :disabled="conversionLoading"
                        class="btn btn-primary">
                    <span x-show="conversionLoading" class="animate-spin mr-2">
                        <?= Icon::show('arrow-path', 'sm') ?>
                    </span>
                    <span x-text="conversionLoading ? 'Создание...' : 'Создать ученика'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Модалка успешного создания ученика -->
<div x-show="showSuccessModal"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="flex min-h-full items-center justify-center p-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/50"></div>

        <!-- Modal content -->
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all">
            <!-- Success Icon -->
            <div class="pt-8 pb-4 text-center">
                <div class="mx-auto w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900">Ученик создан!</h3>
                <p class="text-gray-500 mt-2" x-text="createdPupilFio"></p>
            </div>

            <!-- Actions -->
            <div class="p-6 pt-2 space-y-3">
                <a :href="'<?= OrganizationUrl::to(['pupil/update']) ?>?id=' + createdPupilId"
                   class="btn btn-primary w-full justify-center">
                    <?= Icon::show('pencil', 'sm') ?>
                    Заполнить данные ученика
                </a>
                <button type="button"
                        @click="showSuccessModal = false; location.reload();"
                        class="btn btn-secondary w-full justify-center">
                    Остаться на канбане
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Расширяем kanbanBoard для поддержки конверсии
const conversionMixin = {
    // State для модалки конверсии
    showConversionModal: false,
    conversionLid: null,
    conversionLoading: false,
    conversionErrors: {},

    // State для модалки успеха
    showSuccessModal: false,
    createdPupilId: null,
    createdPupilFio: '',

    // Данные формы
    conversionForm: {
        iin: '',
        sex: '',
        birth_date: '',
        add_to_group: false,
        tariff_id: '',
        group_id: '',
        sale: 0,
        create_payment: true,
        payment_amount: '',
        payment_method_id: '',
        payment_date: new Date().toISOString().split('T')[0],
        payment_number: ''
    },

    // Справочники
    tariffs: {},
    groups: {},
    payMethods: {},

    // Открыть модалку конверсии
    async openConversionModal(lid) {
        this.conversionLid = lid;
        this.conversionErrors = {};
        this.conversionLoading = false;

        // Сбрасываем форму
        this.conversionForm = {
            iin: '',
            sex: '',
            birth_date: '',
            add_to_group: false,
            tariff_id: '',
            group_id: '',
            sale: lid.sale || 0,
            create_payment: true,
            payment_amount: lid.total_sum || '',
            payment_method_id: '',
            payment_date: new Date().toISOString().split('T')[0],
            payment_number: ''
        };

        // Загружаем справочники
        try {
            const response = await fetch('<?= $getConversionDataUrl ?>?id=' + lid.id);
            const data = await response.json();
            if (data.success) {
                this.tariffs = data.tariffs || {};
                this.payMethods = data.payMethods || {};
            }
        } catch (e) {
            console.error('Error loading conversion data:', e);
        }

        this.showConversionModal = true;
    },

    // Закрыть модалку
    closeConversionModal() {
        this.showConversionModal = false;
        this.conversionLid = null;
    },

    // Загрузить группы по тарифу
    async loadGroups() {
        if (!this.conversionForm.tariff_id) {
            this.groups = {};
            return;
        }

        try {
            const response = await fetch('<?= $getGroupsUrl ?>?tariff_id=' + this.conversionForm.tariff_id);
            const data = await response.json();
            if (data.success) {
                this.groups = data.groups || {};
            }
        } catch (e) {
            console.error('Error loading groups:', e);
        }
    },

    // Отправить форму конверсии
    async submitConversion() {
        this.conversionErrors = {};

        // Простая валидация
        if (!this.conversionForm.iin || this.conversionForm.iin.length !== 12) {
            this.conversionErrors.iin = 'ИИН должен содержать 12 цифр';
            return;
        }
        if (!this.conversionForm.sex) {
            this.conversionErrors.sex = 'Выберите пол';
            return;
        }

        this.conversionLoading = true;

        try {
            const formData = new URLSearchParams();
            formData.append('lid_id', this.conversionLid.id);
            formData.append('iin', this.conversionForm.iin);
            formData.append('sex', this.conversionForm.sex);
            if (this.conversionForm.birth_date) {
                // Конвертируем дату в формат d.m.Y
                const date = new Date(this.conversionForm.birth_date);
                formData.append('birth_date', date.toLocaleDateString('ru-RU'));
            }

            if (this.conversionForm.add_to_group) {
                formData.append('add_to_group', '1');
                if (this.conversionForm.tariff_id) formData.append('tariff_id', this.conversionForm.tariff_id);
                if (this.conversionForm.group_id) formData.append('group_id', this.conversionForm.group_id);
                if (this.conversionForm.sale) formData.append('sale', this.conversionForm.sale);
            }

            if (this.conversionForm.create_payment && this.conversionForm.payment_amount) {
                formData.append('create_payment', '1');
                formData.append('payment_amount', this.conversionForm.payment_amount);
                if (this.conversionForm.payment_method_id) formData.append('payment_method_id', this.conversionForm.payment_method_id);
                if (this.conversionForm.payment_date) {
                    const pdate = new Date(this.conversionForm.payment_date);
                    formData.append('payment_date', pdate.toLocaleDateString('ru-RU'));
                }
                if (this.conversionForm.payment_number) formData.append('payment_number', this.conversionForm.payment_number);
            }

            const response = await fetch('<?= $convertUrl ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.closeConversionModal();

                // Показываем модалку успеха
                if (data.pupil_id) {
                    this.createdPupilId = data.pupil_id;
                    this.createdPupilFio = data.pupil_fio || 'Ученик';
                    this.showSuccessModal = true;
                } else {
                    if (Alpine.store('toast')) {
                        Alpine.store('toast').success(data.message || 'Ученик создан');
                    }
                    location.reload();
                }
            } else {
                if (data.errors) {
                    this.conversionErrors = data.errors;
                }
                if (Alpine.store('toast')) {
                    Alpine.store('toast').error(data.message || 'Ошибка создания ученика');
                }
            }
        } catch (e) {
            console.error('Error submitting conversion:', e);
            if (Alpine.store('toast')) {
                Alpine.store('toast').error('Ошибка сети');
            }
        } finally {
            this.conversionLoading = false;
        }
    }
};
</script>
