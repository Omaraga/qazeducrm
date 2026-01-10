<?php
/**
 * Модальное окно просмотра деталей занятия
 */

use app\widgets\tailwind\Icon;
use app\helpers\OrganizationUrl;
?>

<div x-show="selectedEvent" class="space-y-4">
    <!-- Информация о занятии -->
    <div class="bg-gray-50 rounded-xl p-4">
        <!-- Шапка: группа + кнопка редактирования -->
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: selectedEvent?.group?.color }"></span>
                <span class="text-sm font-semibold text-gray-900" x-text="selectedEvent?.group?.code + ' - ' + selectedEvent?.group?.name"></span>
            </div>
            <button type="button"
                    @click="$dispatch('close-modal', 'view-lesson-modal'); openEditModal(selectedEvent?.id)"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                </svg>
                Изменить
            </button>
        </div>

        <!-- Детали -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <!-- Преподаватель -->
            <div class="col-span-2">
                <div class="text-xs text-gray-500 mb-0.5">Преподаватель</div>
                <div class="text-sm text-gray-800" x-text="selectedEvent?.teacher?.fio || 'Не назначен'"></div>
            </div>

            <!-- Дата -->
            <div>
                <div class="text-xs text-gray-500 mb-0.5">Дата</div>
                <div class="text-sm text-gray-800" x-text="selectedEvent?.date"></div>
            </div>

            <!-- Время -->
            <div>
                <div class="text-xs text-gray-500 mb-0.5">Время</div>
                <div class="text-sm text-gray-800" x-text="selectedEvent?.start_time + ' - ' + selectedEvent?.end_time"></div>
            </div>

            <!-- Кабинет -->
            <div x-show="selectedEvent?.room" class="col-span-2">
                <div class="text-xs text-gray-500 mb-0.5">Кабинет</div>
                <div class="text-sm text-gray-800" x-text="selectedEvent?.room?.code ? (selectedEvent?.room?.code + ' - ' + selectedEvent?.room?.name) : selectedEvent?.room?.name"></div>
            </div>
        </div>
    </div>

    <!-- Посещаемость -->
    <div x-show="selectedEvent?.pupils?.length > 0">
        <!-- Заголовок -->
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-700">
                    Посещаемость
                    <span class="text-gray-400 font-normal">(<span x-text="selectedEvent?.pupils_count"></span>)</span>
                </span>
                <a :href="'<?= OrganizationUrl::to(['attendance/lesson']) ?>?id=' + selectedEvent?.id"
                   target="_blank"
                   class="text-blue-500 hover:text-blue-600 transition-colors cursor-pointer"
                   title="Открыть в новой вкладке">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                    </svg>
                </a>
            </div>
            <div class="flex items-center gap-1">
                <button type="button"
                        @click="setAllPupilsStatus(1)"
                        :disabled="savingAttendance"
                        class="text-xs font-medium px-2 py-1 rounded bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors disabled:opacity-50 cursor-pointer">
                    Все +
                </button>
                <button type="button"
                        @click="setAllPupilsStatus(3)"
                        :disabled="savingAttendance"
                        class="text-xs font-medium px-2 py-1 rounded bg-red-50 text-red-600 hover:bg-red-100 transition-colors disabled:opacity-50 cursor-pointer">
                    Все −
                </button>
            </div>
        </div>

        <!-- Список учеников -->
        <div class="border border-gray-200 rounded-lg overflow-hidden divide-y divide-gray-100">
            <template x-for="(pupil, idx) in selectedEvent?.pupils" :key="pupil.id">
                <div class="flex items-center justify-between px-3 py-2 bg-white hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <span class="text-xs text-gray-400 w-4 text-right flex-shrink-0" x-text="idx + 1"></span>
                        <a :href="'<?= OrganizationUrl::to(['pupil/view']) ?>?id=' + pupil.id"
                           target="_blank"
                           class="text-sm text-gray-800 hover:text-blue-600 truncate transition-colors cursor-pointer"
                           x-text="pupil.fio"></a>
                    </div>

                    <div class="flex items-center gap-1 flex-shrink-0">
                        <!-- Был -->
                        <button type="button"
                                @click="savePupilStatus(pupil.id, 1)"
                                :disabled="savingAttendance == pupil.id"
                                class="w-7 h-7 rounded-md flex items-center justify-center transition-all cursor-pointer"
                                :style="parseInt(pupil.status) === 1
                                    ? 'background-color: #10b981; color: white; box-shadow: 0 1px 2px rgba(0,0,0,0.1)'
                                    : 'background-color: #f3f4f6; color: #9ca3af'"
                                title="Был">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>

                        <!-- Оплачено -->
                        <button type="button"
                                @click="savePupilStatus(pupil.id, 2)"
                                :disabled="savingAttendance == pupil.id"
                                class="w-7 h-7 rounded-md flex items-center justify-center transition-all cursor-pointer"
                                :style="parseInt(pupil.status) === 2
                                    ? 'background-color: #3b82f6; color: white; box-shadow: 0 1px 2px rgba(0,0,0,0.1)'
                                    : 'background-color: #f3f4f6; color: #9ca3af'"
                                title="Оплачено">
                            <span class="text-xs font-bold">₸</span>
                        </button>

                        <!-- Не был -->
                        <button type="button"
                                @click="savePupilStatus(pupil.id, 3)"
                                :disabled="savingAttendance == pupil.id"
                                class="w-7 h-7 rounded-md flex items-center justify-center transition-all cursor-pointer"
                                :style="parseInt(pupil.status) === 3
                                    ? 'background-color: #ef4444; color: white; box-shadow: 0 1px 2px rgba(0,0,0,0.1)'
                                    : 'background-color: #f3f4f6; color: #9ca3af'"
                                title="Не был">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <!-- Уважительная -->
                        <button type="button"
                                @click="savePupilStatus(pupil.id, 4)"
                                :disabled="savingAttendance == pupil.id"
                                class="w-7 h-7 rounded-md flex items-center justify-center transition-all cursor-pointer"
                                :style="parseInt(pupil.status) === 4
                                    ? 'background-color: #f59e0b; color: white; box-shadow: 0 1px 2px rgba(0,0,0,0.1)'
                                    : 'background-color: #f3f4f6; color: #9ca3af'"
                                title="Уважительная причина">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                        </button>

                        <!-- Спиннер -->
                        <div class="w-4 flex items-center justify-center">
                            <svg x-show="savingAttendance == pupil.id"
                                 class="w-4 h-4 animate-spin text-blue-500"
                                 fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Легенда -->
        <div class="grid grid-cols-2 gap-x-4 gap-y-1 mt-3 text-[10px] text-gray-500">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-sm flex-shrink-0" style="background-color: #10b981"></span>
                <span><b class="text-gray-600">Был</b> — присутствовал на уроке</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-sm flex-shrink-0" style="background-color: #3b82f6"></span>
                <span><b class="text-gray-600">₸</b> — не был, но оплата учителю</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-sm flex-shrink-0" style="background-color: #ef4444"></span>
                <span><b class="text-gray-600">Нет</b> — не был, без оплаты</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-sm flex-shrink-0" style="background-color: #f59e0b"></span>
                <span><b class="text-gray-600">УП</b> — урок переносится</span>
            </span>
        </div>
    </div>

    <!-- Пустой список -->
    <div x-show="!selectedEvent?.pupils?.length" class="text-center py-8 text-gray-400">
        <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <p class="text-sm">Нет учеников в группе</p>
    </div>

    <!-- Футер -->
    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
        <button type="button"
                @click="$dispatch('close-modal', 'view-lesson-modal'); openDeleteModal(selectedEvent?.id)"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-red-600 hover:text-red-700 transition-colors cursor-pointer">
            <?= Icon::show('trash', 'w-4 h-4') ?>
            Удалить
        </button>
        <button type="button"
                @click="$dispatch('close-modal', 'view-lesson-modal')"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors cursor-pointer">
            Закрыть
        </button>
    </div>
</div>

<!-- Загрузка -->
<div x-show="!selectedEvent" class="flex items-center justify-center py-12">
    <div class="spinner spinner-lg"></div>
</div>
