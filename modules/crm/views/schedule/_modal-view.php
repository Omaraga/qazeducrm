<?php
/**
 * Модальное окно просмотра деталей занятия
 */

use app\widgets\tailwind\Icon;
use app\helpers\OrganizationUrl;
?>

<div x-show="selectedEvent" class="space-y-6">
    <!-- Основная информация -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Группа -->
        <div>
            <label class="text-sm text-gray-500">Группа</label>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: selectedEvent?.group?.color }"></span>
                <span class="font-medium text-gray-900" x-text="selectedEvent?.group?.code + ' - ' + selectedEvent?.group?.name"></span>
            </div>
        </div>

        <!-- Преподаватель -->
        <div>
            <label class="text-sm text-gray-500">Преподаватель</label>
            <div class="font-medium text-gray-900 mt-1" x-text="selectedEvent?.teacher?.fio || 'Не назначен'"></div>
        </div>

        <!-- Дата -->
        <div>
            <label class="text-sm text-gray-500">Дата</label>
            <div class="font-medium text-gray-900 mt-1" x-text="selectedEvent?.date"></div>
        </div>

        <!-- Время -->
        <div>
            <label class="text-sm text-gray-500">Время</label>
            <div class="font-medium text-gray-900 mt-1" x-text="selectedEvent?.start_time + ' - ' + selectedEvent?.end_time"></div>
        </div>

        <!-- Кабинет -->
        <div x-show="selectedEvent?.room">
            <label class="text-sm text-gray-500">Кабинет</label>
            <div class="font-medium text-gray-900 mt-1" x-text="selectedEvent?.room?.code ? (selectedEvent?.room?.code + ' - ' + selectedEvent?.room?.name) : selectedEvent?.room?.name"></div>
        </div>
    </div>

    <!-- Ученики -->
    <div x-show="selectedEvent?.pupils?.length > 0">
        <label class="text-sm text-gray-500 mb-2 block">
            Ученики (<span x-text="selectedEvent?.pupils_count"></span>)
        </label>
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ФИО</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="pupil in selectedEvent?.pupils" :key="pupil.id">
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900" x-text="pupil.fio"></td>
                            <td class="px-4 py-2">
                                <span class="badge"
                                      :class="{
                                          'badge-success': pupil.status === 1,
                                          'badge-danger': pupil.status === 2 || pupil.status === 3,
                                          'badge-warning': pupil.status === 4,
                                          'badge-gray': !pupil.status
                                      }"
                                      x-text="pupil.status_label">
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Пустой список учеников -->
    <div x-show="!selectedEvent?.pupils?.length" class="text-center py-6 text-gray-500">
        <p>Нет учеников в этой группе на данную дату</p>
    </div>

    <!-- Footer с кнопками -->
    <div class="flex flex-wrap justify-between gap-3 pt-4 border-t border-gray-200">
        <div class="flex gap-2">
            <a :href="'<?= OrganizationUrl::to(['attendance/lesson']) ?>?id=' + selectedEvent?.id"
               class="btn btn-outline-primary"
               x-show="selectedEvent?.pupils_count > 0">
                <?= Icon::show('users') ?>
                Посещаемость
            </a>
        </div>
        <div class="flex gap-2">
            <button type="button"
                    @click="$dispatch('close-modal', 'view-lesson-modal'); openEditModal(selectedEvent?.id)"
                    class="btn btn-secondary">
                <?= Icon::show('pencil') ?>
                Редактировать
            </button>
            <button type="button"
                    @click="$dispatch('close-modal', 'view-lesson-modal'); openDeleteModal(selectedEvent?.id)"
                    class="btn btn-danger">
                <?= Icon::show('trash') ?>
                Удалить
            </button>
        </div>
    </div>
</div>

<!-- Загрузка -->
<div x-show="!selectedEvent" class="flex items-center justify-center py-12">
    <div class="spinner spinner-lg"></div>
</div>
