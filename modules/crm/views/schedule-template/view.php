<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use app\widgets\tailwind\Modal;
use yii\helpers\Html;
use yii\helpers\Json;

/** @var yii\web\View $this */
/** @var \app\models\ScheduleTemplate $model */
/** @var array $formData */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Шаблоны расписания', 'url' => OrganizationUrl::to(['schedule-template/index'])];
$this->params['breadcrumbs'][] = $this->title;

// URLs для API
$config = [
    'templateId' => $model->id,
    'urls' => [
        'events' => OrganizationUrl::to(['schedule-template/events', 'id' => $model->id]),
        'preview' => OrganizationUrl::to(['schedule-template/preview', 'id' => $model->id]),
        'generate' => OrganizationUrl::to(['schedule-template/generate', 'id' => $model->id]),
        'addLesson' => OrganizationUrl::to(['schedule-template/add-lesson', 'id' => $model->id]),
        'updateLesson' => OrganizationUrl::to(['schedule-template/update-lesson']),
        'deleteLesson' => OrganizationUrl::to(['schedule-template/delete-lesson']),
        'back' => OrganizationUrl::to(['schedule/index']),
        'list' => OrganizationUrl::to(['schedule-template/index']),
    ],
    'formData' => $formData,
    'csrfToken' => Yii::$app->request->csrfToken,
];
?>

<div x-data="scheduleTemplateView(<?= Html::encode(Json::encode($config)) ?>)" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="<?= OrganizationUrl::to(['schedule-template/index']) ?>" class="text-gray-400 hover:text-gray-600">
                    <?= Icon::svg('arrow-left', ['class' => 'w-5 h-5']) ?>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <?php if ($model->is_default): ?>
                            <span class="text-yellow-500" title="Шаблон по умолчанию">
                                <?= Icon::svg('star-filled', ['class' => 'w-5 h-5']) ?>
                            </span>
                        <?php endif; ?>
                        <?= Html::encode($model->name) ?>
                    </h1>
                    <?php if ($model->description): ?>
                        <p class="text-gray-500 mt-1"><?= Html::encode($model->description) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="flex gap-3">
            <button type="button" @click="$dispatch('open-modal', 'edit-template-modal')" class="btn btn-secondary">
                <?= Icon::svg('pencil', ['class' => 'w-4 h-4 mr-2']) ?>
                Редактировать
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button type="button"
                        @click="activeTab = 'calendar'"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors"
                        :class="activeTab === 'calendar' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <?= Icon::svg('calendar', ['class' => 'inline-block w-4 h-4 mr-2']) ?>
                    Типовое расписание
                </button>
                <button type="button"
                        @click="activeTab = 'generate'"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors"
                        :class="activeTab === 'generate' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <?= Icon::svg('clipboard-document-list', ['class' => 'inline-block w-4 h-4 mr-2']) ?>
                    Создать расписание
                </button>
            </nav>
        </div>

        <!-- Tab: Typical Schedule Calendar -->
        <div x-show="activeTab === 'calendar'" class="p-0">
            <!-- Calendar Controls -->
            <div class="flex flex-wrap items-center justify-between gap-4 p-4 border-b border-gray-200 bg-gray-50">
                <!-- View Mode Switcher -->
                <div class="flex items-center gap-4">
                    <div class="view-mode-toggle">
                        <button type="button"
                                class="view-mode-btn"
                                :class="{ 'active': calendarView === 'day' }"
                                @click="calendarView = 'day'">
                            День
                        </button>
                        <button type="button"
                                class="view-mode-btn"
                                :class="{ 'active': calendarView === 'week' }"
                                @click="calendarView = 'week'">
                            Неделя
                        </button>
                    </div>

                    <button type="button"
                            @click="openAddLessonModal()"
                            class="btn btn-primary btn-sm">
                        <?= Icon::svg('plus', ['class' => 'w-4 h-4 mr-1']) ?>
                        Добавить занятие
                    </button>
                </div>

                <!-- Day Navigation (only in day view) -->
                <div x-show="calendarView === 'day'" class="flex items-center gap-2">
                    <button type="button" @click="prevDay()" class="btn btn-sm btn-icon btn-secondary">
                        <?= Icon::svg('chevron-left', ['class' => 'w-4 h-4']) ?>
                    </button>
                    <div class="px-4 py-2 bg-white border border-gray-200 rounded-lg min-w-[180px] text-center">
                        <span class="font-medium text-gray-900" x-text="daysOfWeekFull[selectedDay - 1]"></span>
                    </div>
                    <button type="button" @click="nextDay()" class="btn btn-sm btn-icon btn-secondary">
                        <?= Icon::svg('chevron-right', ['class' => 'w-4 h-4']) ?>
                    </button>
                </div>
            </div>

            <!-- Calendar Content -->
            <div class="border-t border-gray-200 relative">
                <!-- Loading overlay -->
                <div x-show="loading" class="calendar-loading">
                    <div class="spinner spinner-lg"></div>
                </div>

                <!-- Week View -->
                <template x-if="calendarView === 'week'">
                    <div>
                        <!-- Header row -->
                        <div class="calendar-grid calendar-grid-week">
                            <div class="calendar-time-col"></div>
                            <template x-for="(day, index) in daysOfWeek" :key="index">
                                <div class="calendar-header-day">
                                    <div class="calendar-header-day-name" x-text="day"></div>
                                </div>
                            </template>
                        </div>

                        <!-- Time slots -->
                        <div class="overflow-y-auto" style="max-height: 600px;">
                            <template x-for="hour in hoursRange" :key="hour">
                                <div class="calendar-grid calendar-grid-week">
                                    <div class="calendar-time-col" x-text="hour + ':00'"></div>
                                    <template x-for="(day, dayIndex) in daysOfWeek" :key="'cell-' + hour + '-' + dayIndex">
                                        <div class="calendar-time-slot cursor-pointer hover:bg-gray-50"
                                             @click="openAddLessonModal(dayIndex + 1, hour)">
                                            <template x-for="event in getEventsForDayHour(dayIndex + 1, hour)" :key="event.id">
                                                <div class="calendar-event cursor-pointer"
                                                     :style="{ backgroundColor: event.color }"
                                                     :title="event.group_code + '\n' + event.teacher_fio + '\n' + event.start_time + ' - ' + event.end_time"
                                                     @click.stop="openEditLessonModal(event)">
                                                    <div class="calendar-event-title" x-text="event.group_code"></div>
                                                    <div class="calendar-event-time" x-text="event.start_time"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Day View -->
                <template x-if="calendarView === 'day'">
                    <div class="calendar-grid calendar-grid-day">
                        <!-- Header -->
                        <div class="calendar-time-col"></div>
                        <div class="calendar-header-day">
                            <div class="calendar-header-day-name" x-text="daysOfWeekFull[selectedDay - 1]"></div>
                        </div>

                        <!-- Time slots -->
                        <template x-for="hour in hoursRange" :key="hour">
                            <div class="contents">
                                <div class="calendar-time-col" x-text="hour + ':00'"></div>
                                <div class="calendar-time-slot cursor-pointer hover:bg-gray-50"
                                     @click="openAddLessonModal(selectedDay, hour)">
                                    <template x-for="event in getEventsForDayHour(selectedDay, hour)" :key="event.id">
                                        <div class="calendar-day-event cursor-pointer"
                                             :style="{ backgroundColor: event.color }"
                                             :title="event.group_code + ' - ' + event.teacher_fio"
                                             @click.stop="openEditLessonModal(event)">
                                            <div class="calendar-day-event-title" x-text="event.group_code"></div>
                                            <div class="calendar-day-event-time" x-text="event.start_time + ' - ' + event.end_time"></div>
                                            <div class="calendar-day-event-teacher" x-text="event.teacher_fio"></div>
                                            <template x-if="event.room_name">
                                                <div class="calendar-day-event-teacher" x-text="event.room_name"></div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Empty state for no events -->
                <div x-show="!loading && events.length === 0" class="p-12 text-center">
                    <?= Icon::svg('calendar', ['class' => 'mx-auto h-12 w-12 text-gray-300']) ?>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Шаблон пустой</h3>
                    <p class="mt-2 text-gray-500">Нажмите на ячейку календаря или кнопку "Добавить занятие"</p>
                    <button type="button" @click="openAddLessonModal()" class="btn btn-primary mt-4">
                        <?= Icon::svg('plus', ['class' => 'w-4 h-4 mr-2']) ?>
                        Добавить занятие
                    </button>
                </div>
            </div>

            <!-- Day Summary (only in day view) -->
            <div x-show="calendarView === 'day' && getEventsForDay(selectedDay).length > 0" class="p-4 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center gap-6 text-sm text-gray-600">
                    <span>
                        <?= Icon::svg('academic-cap', ['class' => 'inline-block w-4 h-4 mr-1']) ?>
                        Занятий: <strong x-text="getEventsForDay(selectedDay).length"></strong>
                    </span>
                    <span>
                        <?= Icon::svg('clock', ['class' => 'inline-block w-4 h-4 mr-1']) ?>
                        Первое: <strong x-text="getFirstEventTime(selectedDay)"></strong>
                    </span>
                    <span>
                        <?= Icon::svg('clock', ['class' => 'inline-block w-4 h-4 mr-1']) ?>
                        Последнее: <strong x-text="getLastEventTime(selectedDay)"></strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- Tab: Generate Schedule - Wizard Interface -->
        <div x-show="activeTab === 'generate'" class="p-0">
            <!-- Wizard Steps Header -->
            <div class="flex border-b border-gray-200 bg-gray-50">
                <button type="button" @click="wizardStep = 1"
                        class="flex-1 px-4 py-3 text-sm font-medium text-center transition-colors relative"
                        :class="wizardStep === 1 ? 'text-primary-600 bg-white border-b-2 border-primary-500 -mb-px' : 'text-gray-500 hover:text-gray-700'">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full mr-2 text-xs font-bold"
                          :class="wizardStep === 1 ? 'bg-primary-100 text-primary-600' : (wizardStep > 1 ? 'bg-success-100 text-success-600' : 'bg-gray-200 text-gray-600')">
                        <span x-show="wizardStep > 1"><?= Icon::svg('check', ['class' => 'w-3.5 h-3.5']) ?></span>
                        <span x-show="wizardStep <= 1">1</span>
                    </span>
                    Период и дни
                </button>
                <button type="button" @click="wizardStep > 1 && (wizardStep = 2)"
                        class="flex-1 px-4 py-3 text-sm font-medium text-center transition-colors relative"
                        :class="wizardStep === 2 ? 'text-primary-600 bg-white border-b-2 border-primary-500 -mb-px' : (wizardStep > 2 ? 'text-gray-700' : 'text-gray-400')"
                        :disabled="wizardStep < 2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full mr-2 text-xs font-bold"
                          :class="wizardStep === 2 ? 'bg-primary-100 text-primary-600' : (wizardStep > 2 ? 'bg-success-100 text-success-600' : 'bg-gray-200 text-gray-400')">
                        <span x-show="wizardStep > 2"><?= Icon::svg('check', ['class' => 'w-3.5 h-3.5']) ?></span>
                        <span x-show="wizardStep <= 2">2</span>
                    </span>
                    Редактирование
                </button>
                <button type="button"
                        class="flex-1 px-4 py-3 text-sm font-medium text-center transition-colors relative"
                        :class="wizardStep === 3 ? 'text-primary-600 bg-white border-b-2 border-primary-500 -mb-px' : 'text-gray-400'"
                        disabled>
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full mr-2 text-xs font-bold"
                          :class="wizardStep === 3 ? 'bg-primary-100 text-primary-600' : 'bg-gray-200 text-gray-400'">3</span>
                    Создание
                </button>
            </div>

            <!-- STEP 1: Period & Days Configuration -->
            <div x-show="wizardStep === 1" class="p-6 space-y-6">
                <!-- Period Selection -->
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <h3 class="text-lg font-semibold text-gray-900">Период</h3>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">Обязательно</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">Выберите даты, на которые будет создано расписание</p>

                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="movePeriod(-7)" class="btn btn-sm btn-icon btn-secondary" title="На неделю назад">
                                <?= Icon::svg('chevron-left', ['class' => 'w-4 h-4']) ?>
                            </button>
                            <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-1">
                                <div class="relative">
                                    <input type="date" x-model="dateStart" class="form-input w-auto pr-8">
                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-400">с</span>
                                </div>
                                <span class="text-gray-400">→</span>
                                <div class="relative">
                                    <input type="date" x-model="dateEnd" class="form-input w-auto pr-8">
                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-400">по</span>
                                </div>
                            </div>
                            <button type="button" @click="movePeriod(7)" class="btn btn-sm btn-icon btn-secondary" title="На неделю вперед">
                                <?= Icon::svg('chevron-right', ['class' => 'w-4 h-4']) ?>
                            </button>
                        </div>

                        <div class="flex gap-2">
                            <button type="button" @click="setPeriodWeek()" class="btn btn-sm btn-secondary">
                                <?= Icon::svg('calendar', ['class' => 'w-4 h-4 mr-1']) ?>
                                Эта неделя
                            </button>
                            <button type="button" @click="setPeriodNextWeek()" class="btn btn-sm btn-secondary">
                                След. неделя
                            </button>
                            <button type="button" @click="setPeriodMonth()" class="btn btn-sm btn-secondary">
                                Этот месяц
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Day Mapping -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center gap-2 mb-3">
                        <h3 class="text-lg font-semibold text-gray-900">Настройка дней</h3>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">Опционально</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">
                        Укажите, на какой день недели перенести занятия из шаблона.
                        Например, занятия из понедельника можно создать на вторник.
                    </p>

                    <!-- Template days summary -->
                    <div class="bg-primary-50 border border-primary-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <?= Icon::svg('information-circle', ['class' => 'w-5 h-5 text-primary-600']) ?>
                            <span class="font-medium text-primary-900">Занятия в шаблоне по дням:</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(dayName, index) in daysOfWeekFull" :key="index">
                                <span x-show="getTemplateDayCount(index + 1) > 0"
                                      class="inline-flex items-center gap-1 px-2 py-1 bg-white rounded text-sm">
                                    <span class="font-medium" x-text="daysOfWeek[index]"></span>
                                    <span class="text-primary-600" x-text="'(' + getTemplateDayCount(index + 1) + ')'"></span>
                                </span>
                            </template>
                            <span x-show="events.length === 0" class="text-primary-700 text-sm">
                                Шаблон пустой. Добавьте занятия на вкладке "Типовое расписание".
                            </span>
                        </div>
                    </div>

                    <!-- Day mapping table -->
                    <div class="overflow-hidden border border-gray-200 rounded-lg" x-show="events.length > 0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">День в шаблоне</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Занятий</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Создать на</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Вкл/Выкл</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(dayName, index) in daysOfWeekFull" :key="'map-' + index">
                                    <tr x-show="getTemplateDayCount(index + 1) > 0"
                                        :class="!dayMapping[index + 1].enabled ? 'bg-gray-50 opacity-60' : ''">
                                        <td class="px-4 py-3">
                                            <span class="font-medium text-gray-900" x-text="dayName"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
                                                  x-text="getTemplateDayCount(index + 1) + ' зан.'"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <select x-model="dayMapping[index + 1].targetDay"
                                                    :disabled="!dayMapping[index + 1].enabled"
                                                    class="form-select text-sm py-1.5 w-40">
                                                <template x-for="(tgtDay, tgtIndex) in daysOfWeekFull" :key="'tgt-' + tgtIndex">
                                                    <option :value="tgtIndex + 1" x-text="tgtDay"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" x-model="dayMapping[index + 1].enabled" class="sr-only peer">
                                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-600"></div>
                                            </label>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Quick actions for mapping -->
                    <div class="flex gap-2 mt-3" x-show="events.length > 0">
                        <button type="button" @click="resetDayMapping()" class="text-xs text-gray-500 hover:text-gray-700">
                            <?= Icon::svg('arrow-path', ['class' => 'w-3.5 h-3.5 inline mr-1']) ?>
                            Сбросить настройки
                        </button>
                        <span class="text-gray-300">|</span>
                        <button type="button" @click="enableAllDays()" class="text-xs text-gray-500 hover:text-gray-700">
                            Включить все
                        </button>
                        <span class="text-gray-300">|</span>
                        <button type="button" @click="disableWeekendDays()" class="text-xs text-gray-500 hover:text-gray-700">
                            Выключить выходные
                        </button>
                    </div>
                </div>

                <!-- Next Step Button -->
                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button type="button" @click="goToStep2()"
                            :disabled="!dateStart || !dateEnd || events.length === 0"
                            class="btn btn-primary">
                        Далее: Предпросмотр
                        <?= Icon::svg('arrow-right', ['class' => 'w-4 h-4 ml-2']) ?>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Preview & Edit -->
            <div x-show="wizardStep === 2" class="p-6 space-y-6">
                <!-- Loading state -->
                <div x-show="previewLoading" class="flex flex-col items-center justify-center py-12">
                    <span class="spinner spinner-lg mb-4"></span>
                    <p class="text-gray-500">Загрузка предпросмотра...</p>
                </div>

                <!-- Preview content -->
                <div x-show="!previewLoading && preview">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-gray-900" x-text="getActivePreviewCount()"></div>
                            <div class="text-sm text-gray-500">Будет создано</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-gray-900" x-text="getExcludedCount()"></div>
                            <div class="text-sm text-gray-500">Исключено</div>
                        </div>
                        <div class="bg-warning-50 rounded-lg p-4 text-center cursor-pointer hover:bg-warning-100 transition-colors"
                             x-show="preview?.total_conflicts > 0"
                             @click="filterPreview = filterPreview === 'conflicts' ? 'all' : 'conflicts'">
                            <div class="text-2xl font-bold text-warning-600" x-text="preview?.total_conflicts || 0"></div>
                            <div class="text-sm text-warning-600">Конфликтов</div>
                        </div>
                        <div class="bg-success-50 rounded-lg p-4 text-center" x-show="preview?.total_conflicts === 0">
                            <?= Icon::svg('check-circle', ['class' => 'w-6 h-6 mx-auto text-success-600']) ?>
                            <div class="text-sm text-success-600 mt-1">Нет конфликтов</div>
                        </div>
                    </div>

                    <!-- Toolbar -->
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4 p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">Показать:</span>
                            <div class="inline-flex rounded-lg border border-gray-200 bg-white">
                                <button type="button" @click="filterPreview = 'all'"
                                        class="px-3 py-1.5 text-sm rounded-l-lg transition-colors"
                                        :class="filterPreview === 'all' ? 'bg-primary-100 text-primary-700' : 'text-gray-600 hover:bg-gray-50'">
                                    Все
                                </button>
                                <button type="button" @click="filterPreview = 'active'"
                                        class="px-3 py-1.5 text-sm border-l border-gray-200 transition-colors"
                                        :class="filterPreview === 'active' ? 'bg-primary-100 text-primary-700' : 'text-gray-600 hover:bg-gray-50'">
                                    Активные
                                </button>
                                <button type="button" @click="filterPreview = 'conflicts'"
                                        x-show="preview?.total_conflicts > 0"
                                        class="px-3 py-1.5 text-sm border-l border-gray-200 rounded-r-lg transition-colors"
                                        :class="filterPreview === 'conflicts' ? 'bg-warning-100 text-warning-700' : 'text-gray-600 hover:bg-gray-50'">
                                    Конфликты
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="selectAllLessons()" class="btn btn-sm btn-secondary">
                                <?= Icon::svg('check-circle', ['class' => 'w-4 h-4 mr-1']) ?>
                                Выбрать все
                            </button>
                            <button type="button" @click="deselectAllLessons()" class="btn btn-sm btn-secondary">
                                <?= Icon::svg('x-circle', ['class' => 'w-4 h-4 mr-1']) ?>
                                Снять все
                            </button>
                            <button type="button" @click="excludeConflicts()" x-show="preview?.total_conflicts > 0" class="btn btn-sm btn-warning">
                                <?= Icon::svg('exclamation-triangle', ['class' => 'w-4 h-4 mr-1']) ?>
                                Исключить конфликты
                            </button>
                        </div>
                    </div>

                    <!-- Lessons List by Day -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="max-h-[450px] overflow-y-auto">
                            <template x-for="day in getFilteredPreviewDays()" :key="day.date">
                                <div class="border-b border-gray-200 last:border-b-0">
                                    <!-- Day Header -->
                                    <div class="bg-gray-50 px-4 py-2 flex items-center justify-between sticky top-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-900" x-text="day.day_name_full"></span>
                                            <span class="text-gray-500" x-text="day.date_formatted"></span>
                                            <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full"
                                                  x-text="getDayActiveCount(day) + ' из ' + day.lessons.length"></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="toggleDayLessons(day, true)"
                                                    class="text-xs text-primary-600 hover:text-primary-700">Выбрать день</button>
                                            <span class="text-gray-300">|</span>
                                            <button type="button" @click="toggleDayLessons(day, false)"
                                                    class="text-xs text-gray-500 hover:text-gray-700">Снять день</button>
                                        </div>
                                    </div>

                                    <!-- Lessons -->
                                    <div class="divide-y divide-gray-100">
                                        <template x-for="lesson in getFilteredLessons(day.lessons)" :key="lesson.uid">
                                            <div class="px-4 py-3 flex items-center gap-4 transition-colors"
                                                 :class="{
                                                     'bg-warning-50': lesson.has_conflict && lessonSelection[lesson.uid],
                                                     'bg-gray-50 opacity-50': !lessonSelection[lesson.uid],
                                                     'hover:bg-gray-50': lessonSelection[lesson.uid] && !lesson.has_conflict
                                                 }">
                                                <!-- Checkbox -->
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           x-model="lessonSelection[lesson.uid]"
                                                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                                </label>

                                                <!-- Color indicator -->
                                                <div class="w-1 h-10 rounded-full" :style="{ backgroundColor: lesson.color }"></div>

                                                <!-- Time -->
                                                <div class="w-24 flex-shrink-0">
                                                    <div class="font-medium text-gray-900" x-text="lesson.start_time + ' - ' + lesson.end_time"></div>
                                                </div>

                                                <!-- Group & Teacher -->
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-medium text-gray-900" x-text="lesson.group_code"></div>
                                                    <div class="text-sm text-gray-500 truncate" x-text="lesson.teacher_fio"></div>
                                                </div>

                                                <!-- Room -->
                                                <div class="w-24 text-sm text-gray-500" x-show="lesson.room_name" x-text="lesson.room_name"></div>

                                                <!-- Conflict indicator -->
                                                <div x-show="lesson.has_conflict" class="flex items-center gap-1 text-warning-600">
                                                    <?= Icon::svg('exclamation-triangle', ['class' => 'w-5 h-5']) ?>
                                                    <span class="text-xs hidden sm:inline" x-text="lesson.conflicts?.[0]?.message || 'Конфликт'"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Empty filtered state -->
                    <div x-show="getFilteredPreviewDays().length === 0" class="text-center py-12 text-gray-500">
                        <?= Icon::svg('funnel', ['class' => 'mx-auto h-12 w-12 text-gray-300 mb-4']) ?>
                        <p>Нет занятий для отображения с текущим фильтром.</p>
                    </div>
                </div>

                <!-- Empty state - no lessons -->
                <div x-show="!previewLoading && preview?.total === 0" class="text-center py-12">
                    <?= Icon::svg('calendar', ['class' => 'mx-auto h-16 w-16 text-gray-300 mb-4']) ?>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Нет занятий для создания</h3>
                    <p class="text-gray-500 mb-4">В выбранный период не попадает ни одно занятие из шаблона.</p>
                    <button type="button" @click="wizardStep = 1" class="btn btn-secondary">
                        <?= Icon::svg('arrow-left', ['class' => 'w-4 h-4 mr-2']) ?>
                        Изменить настройки
                    </button>
                </div>

                <!-- Navigation -->
                <div class="flex justify-between pt-4 border-t border-gray-200">
                    <button type="button" @click="wizardStep = 1" class="btn btn-secondary">
                        <?= Icon::svg('arrow-left', ['class' => 'w-4 h-4 mr-2']) ?>
                        Назад
                    </button>
                    <button type="button" @click="goToStep3()"
                            :disabled="getActivePreviewCount() === 0"
                            class="btn btn-primary">
                        Далее: Создать расписание
                        <?= Icon::svg('arrow-right', ['class' => 'w-4 h-4 ml-2']) ?>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Confirmation & Generate -->
            <div x-show="wizardStep === 3" class="p-6">
                <div class="max-w-lg mx-auto text-center">
                    <!-- Summary Icon -->
                    <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <?= Icon::svg('clipboard-document-check', ['class' => 'w-8 h-8 text-primary-600']) ?>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Готово к созданию</h3>
                    <p class="text-gray-500 mb-6">Проверьте итоговую информацию перед созданием расписания</p>

                    <!-- Summary Card -->
                    <div class="bg-gray-50 rounded-xl p-6 mb-6 text-left">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Период:</span>
                                <span class="font-medium text-gray-900" x-text="formatDateRange()"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Занятий будет создано:</span>
                                <span class="font-bold text-primary-600 text-lg" x-text="getActivePreviewCount()"></span>
                            </div>
                            <div class="flex justify-between" x-show="getExcludedCount() > 0">
                                <span class="text-gray-600">Исключено:</span>
                                <span class="text-gray-500" x-text="getExcludedCount()"></span>
                            </div>
                            <div class="flex justify-between" x-show="preview?.total_conflicts > 0">
                                <span class="text-gray-600">Конфликтов:</span>
                                <span class="text-warning-600" x-text="getConflictsInSelection()"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Warning about conflicts -->
                    <div x-show="getConflictsInSelection() > 0" class="bg-warning-50 border border-warning-200 rounded-lg p-4 mb-6 text-left">
                        <div class="flex gap-3">
                            <?= Icon::svg('exclamation-triangle', ['class' => 'w-5 h-5 text-warning-600 flex-shrink-0']) ?>
                            <div>
                                <p class="text-sm text-warning-800 font-medium">Внимание: будут созданы занятия с конфликтами</p>
                                <p class="text-sm text-warning-700 mt-1">Вы можете вернуться и исключить их на предыдущем шаге.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <button type="button" @click="wizardStep = 2" class="btn btn-secondary">
                            <?= Icon::svg('arrow-left', ['class' => 'w-4 h-4 mr-2']) ?>
                            Назад к редактированию
                        </button>
                        <button type="button" @click="generateSchedule()"
                                :disabled="generating"
                                class="btn btn-primary btn-lg">
                            <span x-show="generating" class="spinner spinner-sm mr-2"></span>
                            <span x-show="!generating"><?= Icon::svg('check', ['class' => 'w-5 h-5 mr-2']) ?></span>
                            <span x-text="generating ? 'Создание...' : 'Создать расписание'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Lesson Modal -->
    <?php Modal::begin([
        'id' => 'lesson-modal',
        'title' => 'Занятие',
        'size' => 'md',
    ]); ?>
    <form @submit.prevent="saveLesson">
        <div class="space-y-4">
            <div>
                <label class="form-label">День недели</label>
                <select x-model="lessonForm.week" class="form-select" required>
                    <option value="">Выберите день</option>
                    <template x-for="(name, index) in daysOfWeekFull" :key="index">
                        <option :value="index + 1" x-text="name"></option>
                    </template>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Время начала</label>
                    <input type="time" x-model="lessonForm.start_time"
                           @change="validateTime()"
                           class="form-input"
                           :class="timeError ? 'border-danger-500 focus:border-danger-500 focus:ring-danger-500' : ''"
                           required>
                </div>
                <div>
                    <label class="form-label">Время окончания</label>
                    <input type="time" x-model="lessonForm.end_time"
                           @change="validateTime()"
                           class="form-input"
                           :class="timeError ? 'border-danger-500 focus:border-danger-500 focus:ring-danger-500' : ''"
                           required>
                </div>
            </div>
            <div x-show="timeError" x-cloak class="text-sm text-danger-600 flex items-center gap-1.5">
                <?= Icon::svg('exclamation-circle', ['class' => 'w-4 h-4']) ?>
                <span x-text="timeError"></span>
            </div>
            <div>
                <label class="form-label">Группа</label>
                <select x-model="lessonForm.group_id" class="form-select" required @change="updateTeachersForGroup()">
                    <option value="">Выберите группу</option>
                    <template x-for="group in formData.groups" :key="group.id">
                        <option :value="group.id" x-text="group.code + ' - ' + group.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="form-label">Преподаватель</label>
                <select x-model="lessonForm.teacher_id" class="form-select" required>
                    <option value="">Выберите преподавателя</option>
                    <template x-for="teacher in formData.teachers" :key="teacher.id">
                        <option :value="teacher.id" x-text="teacher.fio"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="form-label">Кабинет (опционально)</label>
                <select x-model="lessonForm.room_id" class="form-select">
                    <option value="">Без кабинета</option>
                    <template x-for="room in formData.rooms" :key="room.id">
                        <option :value="room.id" x-text="room.code ? room.code + ' - ' + room.name : room.name"></option>
                    </template>
                </select>
            </div>
        </div>
        <div class="modal-footer mt-6 flex justify-between">
            <div>
                <button type="button"
                        x-show="lessonForm.id"
                        @click="deleteLesson()"
                        class="btn btn-danger">
                    <?= Icon::svg('trash', ['class' => 'w-4 h-4 mr-2']) ?>
                    Удалить
                </button>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="$dispatch('close-modal', 'lesson-modal')" class="btn btn-secondary">
                    Отмена
                </button>
                <button type="submit" class="btn btn-primary" :disabled="saving">
                    <span x-show="saving" class="spinner spinner-sm mr-2"></span>
                    <span x-text="lessonForm.id ? 'Сохранить' : 'Добавить'"></span>
                </button>
            </div>
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
                <input type="text" x-model="templateForm.name" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Описание</label>
                <textarea x-model="templateForm.description" class="form-input" rows="2"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" x-model="templateForm.is_default" id="template_is_default" class="form-checkbox">
                <label for="template_is_default" class="text-sm text-gray-700">Шаблон по умолчанию</label>
            </div>
        </div>
        <div class="modal-footer mt-6">
            <button type="button" @click="$dispatch('close-modal', 'edit-template-modal')" class="btn btn-secondary">
                Отмена
            </button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
                <span x-show="saving" class="spinner spinner-sm mr-2"></span>
                Сохранить
            </button>
        </div>
    </form>
    <?php Modal::end(); ?>
</div>

<script>
function scheduleTemplateView(config) {
    return {
        activeTab: 'calendar',
        loading: false,
        saving: false,
        events: [],

        // Calendar view
        calendarView: 'week',
        selectedDay: 1,

        daysOfWeek: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
        daysOfWeekFull: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
        hoursRange: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21],

        // Form data
        formData: config.formData || { groups: [], teachers: [], rooms: [] },

        // Lesson form
        lessonForm: {
            id: null,
            week: '',
            start_time: '',
            end_time: '',
            group_id: '',
            teacher_id: '',
            room_id: ''
        },

        // Template form
        templateForm: {
            name: '<?= Html::encode($model->name) ?>',
            description: '<?= Html::encode($model->description ?? '') ?>',
            is_default: <?= $model->is_default ? 'true' : 'false' ?>
        },

        // Generate tab - Wizard
        wizardStep: 1,
        dateStart: '',
        dateEnd: '',
        preview: null,
        previewLoading: false,
        generating: false,
        filterPreview: 'all', // all, active, conflicts

        // Day mapping: {1: {targetDay: 1, enabled: true}, ...}
        dayMapping: {
            1: { targetDay: 1, enabled: true },
            2: { targetDay: 2, enabled: true },
            3: { targetDay: 3, enabled: true },
            4: { targetDay: 4, enabled: true },
            5: { targetDay: 5, enabled: true },
            6: { targetDay: 6, enabled: true },
            7: { targetDay: 7, enabled: true }
        },

        // Lesson selection for preview (uid -> boolean)
        lessonSelection: {},

        // Validation
        timeError: '',

        init() {
            this.loadEvents();
            this.setPeriodWeek();
            this.initMobileView();
            this.initTouchEvents();
            this.initDayMapping();
        },

        // Initialize day mapping based on template events
        initDayMapping() {
            // Reset to defaults
            for (let i = 1; i <= 7; i++) {
                this.dayMapping[i] = { targetDay: i, enabled: true };
            }
        },

        // Mobile: автопереключение на day view
        initMobileView() {
            const checkMobile = () => {
                if (window.innerWidth < 640 && this.calendarView === 'week') {
                    this.calendarView = 'day';
                }
            };
            checkMobile();
            window.addEventListener('resize', checkMobile);
        },

        // Touch swipe для навигации между днями
        initTouchEvents() {
            let touchStartX = 0;
            let touchEndX = 0;
            const self = this;

            // Ждём пока DOM обновится
            this.$nextTick(() => {
                const calendarContainer = this.$el.querySelector('.calendar-grid');
                if (!calendarContainer) return;

                calendarContainer.addEventListener('touchstart', (e) => {
                    touchStartX = e.changedTouches[0].screenX;
                }, { passive: true });

                calendarContainer.addEventListener('touchend', (e) => {
                    touchEndX = e.changedTouches[0].screenX;
                    const diff = touchStartX - touchEndX;

                    // Минимальное расстояние для swipe
                    if (Math.abs(diff) > 60) {
                        if (diff > 0) {
                            self.nextDay(); // Swipe влево - следующий день
                        } else {
                            self.prevDay(); // Swipe вправо - предыдущий день
                        }
                    }
                }, { passive: true });
            });
        },

        validateTime() {
            if (this.lessonForm.start_time && this.lessonForm.end_time) {
                if (this.lessonForm.start_time >= this.lessonForm.end_time) {
                    this.timeError = 'Время окончания должно быть позже времени начала';
                    return false;
                }
            }
            this.timeError = '';
            return true;
        },

        async loadEvents() {
            this.loading = true;
            try {
                const response = await fetch(config.urls.events, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                this.events = result.events || [];
            } catch (e) {
                console.error('Error loading events:', e);
            } finally {
                this.loading = false;
            }
        },

        // Day navigation
        prevDay() {
            this.selectedDay = this.selectedDay > 1 ? this.selectedDay - 1 : 7;
        },

        nextDay() {
            this.selectedDay = this.selectedDay < 7 ? this.selectedDay + 1 : 1;
        },

        // Event helpers
        getEventsForDay(weekDay) {
            return this.events.filter(e => e.week === weekDay);
        },

        getEventsForDayHour(weekDay, hour) {
            return this.events.filter(e => {
                if (e.week !== weekDay) return false;
                const [h] = e.start_time.split(':').map(Number);
                return h === hour;
            });
        },

        getFirstEventTime(weekDay) {
            const events = this.getEventsForDay(weekDay);
            if (events.length === 0) return '—';
            const sorted = [...events].sort((a, b) => a.start_time.localeCompare(b.start_time));
            return sorted[0].start_time;
        },

        getLastEventTime(weekDay) {
            const events = this.getEventsForDay(weekDay);
            if (events.length === 0) return '—';
            const sorted = [...events].sort((a, b) => b.end_time.localeCompare(a.end_time));
            return sorted[0].end_time;
        },

        // Lesson modal
        openAddLessonModal(weekDay = null, hour = null) {
            this.timeError = '';
            this.lessonForm = {
                id: null,
                week: weekDay || '',
                start_time: hour ? (hour < 10 ? '0' + hour : hour) + ':00' : '',
                end_time: hour ? (hour < 9 ? '0' + (hour + 1) : hour + 1) + ':00' : '',
                group_id: '',
                teacher_id: '',
                room_id: ''
            };
            this.$dispatch('open-modal', 'lesson-modal');
        },

        openEditLessonModal(event) {
            this.timeError = '';
            this.lessonForm = {
                id: event.id,
                week: event.week,
                start_time: event.start_time,
                end_time: event.end_time,
                group_id: event.group_id,
                teacher_id: event.teacher_id,
                room_id: event.room_id || ''
            };
            this.$dispatch('open-modal', 'lesson-modal');
        },

        async saveLesson() {
            // Валидация времени
            if (!this.validateTime()) {
                return;
            }

            this.saving = true;
            try {
                const baseUrl = this.lessonForm.id ? config.urls.updateLesson : config.urls.addLesson;
                const separator = baseUrl.includes('?') ? '&' : '?';
                const url = this.lessonForm.id
                    ? baseUrl + separator + 'lessonId=' + this.lessonForm.id
                    : baseUrl;

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': config.csrfToken
                    },
                    body: new URLSearchParams(this.lessonForm)
                });
                const result = await response.json();

                if (result.success) {
                    this.$dispatch('close-modal', 'lesson-modal');
                    QazToast.success(this.lessonForm.id ? 'Занятие обновлено' : 'Занятие добавлено');
                    await this.loadEvents();
                    this.loadPreview();
                } else {
                    QazToast.error(result.message || 'Ошибка сохранения');
                }
            } catch (e) {
                console.error(e);
                QazToast.error('Ошибка сети');
            } finally {
                this.saving = false;
            }
        },

        deleteLesson() {
            const self = this;
            QazConfirm.show('Удалить это занятие из шаблона?', {
                title: 'Удаление занятия',
                type: 'danger',
                confirmText: 'Удалить',
                cancelText: 'Отмена',
                onConfirm: () => self.performDeleteLesson()
            });
        },

        async performDeleteLesson() {
            this.saving = true;
            try {
                const baseUrl = config.urls.deleteLesson;
                const separator = baseUrl.includes('?') ? '&' : '?';
                const response = await fetch(baseUrl + separator + 'lessonId=' + this.lessonForm.id, {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': config.csrfToken }
                });
                const result = await response.json();

                if (result.success) {
                    this.$dispatch('close-modal', 'lesson-modal');
                    QazToast.success('Занятие удалено');
                    await this.loadEvents();
                    this.loadPreview();
                } else {
                    QazToast.error(result.message || 'Ошибка удаления');
                }
            } catch (e) {
                console.error(e);
                QazToast.error('Ошибка сети');
            } finally {
                this.saving = false;
            }
        },

        async updateTemplate() {
            this.saving = true;
            try {
                const response = await fetch('<?= OrganizationUrl::to(['schedule-template/update', 'id' => $model->id]) ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': config.csrfToken
                    },
                    body: new URLSearchParams({
                        name: this.templateForm.name,
                        description: this.templateForm.description,
                        is_default: this.templateForm.is_default ? 1 : 0
                    })
                });
                const result = await response.json();

                if (result.success) {
                    this.$dispatch('close-modal', 'edit-template-modal');
                    QazToast.success('Шаблон обновлен');
                    // Обновить заголовок на странице
                    const titleEl = document.querySelector('h1');
                    if (titleEl) {
                        const starHtml = this.templateForm.is_default
                            ? '<span class="text-yellow-500" title="Шаблон по умолчанию"><svg class="w-5 h-5 inline-block" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></span> '
                            : '';
                        titleEl.innerHTML = starHtml + this.escapeHtml(this.templateForm.name);
                    }
                } else {
                    QazToast.error(result.message || 'Ошибка');
                }
            } catch (e) {
                console.error(e);
                QazToast.error('Ошибка сети');
            } finally {
                this.saving = false;
            }
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        // ========== Generate Tab - Wizard Methods ==========

        // Period selection
        setPeriodWeek() {
            const today = new Date();
            const dayOfWeek = today.getDay();
            const diff = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
            const monday = new Date(today);
            monday.setDate(today.getDate() + diff);
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);

            this.dateStart = this.formatDateInput(monday);
            this.dateEnd = this.formatDateInput(sunday);
        },

        setPeriodNextWeek() {
            const today = new Date();
            const dayOfWeek = today.getDay();
            const diff = dayOfWeek === 0 ? 1 : 8 - dayOfWeek;
            const monday = new Date(today);
            monday.setDate(today.getDate() + diff);
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);

            this.dateStart = this.formatDateInput(monday);
            this.dateEnd = this.formatDateInput(sunday);
        },

        setPeriodMonth() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            this.dateStart = this.formatDateInput(firstDay);
            this.dateEnd = this.formatDateInput(lastDay);
        },

        movePeriod(days) {
            const start = new Date(this.dateStart);
            const end = new Date(this.dateEnd);
            start.setDate(start.getDate() + days);
            end.setDate(end.getDate() + days);
            this.dateStart = this.formatDateInput(start);
            this.dateEnd = this.formatDateInput(end);
        },

        formatDateInput(date) {
            return date.toISOString().split('T')[0];
        },

        formatDateRange() {
            if (!this.dateStart || !this.dateEnd) return '';
            const start = new Date(this.dateStart);
            const end = new Date(this.dateEnd);
            const options = { day: 'numeric', month: 'short' };
            return start.toLocaleDateString('ru-RU', options) + ' — ' + end.toLocaleDateString('ru-RU', options);
        },

        // Day mapping helpers
        getTemplateDayCount(weekDay) {
            return this.events.filter(e => e.week === weekDay).length;
        },

        resetDayMapping() {
            for (let i = 1; i <= 7; i++) {
                this.dayMapping[i] = { targetDay: i, enabled: true };
            }
        },

        enableAllDays() {
            for (let i = 1; i <= 7; i++) {
                this.dayMapping[i].enabled = true;
            }
        },

        disableWeekendDays() {
            this.dayMapping[6].enabled = false; // Суббота
            this.dayMapping[7].enabled = false; // Воскресенье
        },

        // Wizard navigation
        async goToStep2() {
            this.wizardStep = 2;
            await this.loadPreviewWithMapping();
        },

        goToStep3() {
            if (this.getActivePreviewCount() > 0) {
                this.wizardStep = 3;
            }
        },

        // Load preview with day mapping applied
        async loadPreviewWithMapping() {
            if (!this.dateStart || !this.dateEnd) return;

            this.previewLoading = true;
            this.preview = null;
            this.lessonSelection = {};

            try {
                // Build day mapping for backend
                const dayMappingData = {};
                for (let i = 1; i <= 7; i++) {
                    if (this.dayMapping[i].enabled) {
                        dayMappingData[i] = this.dayMapping[i].targetDay;
                    }
                }

                const response = await fetch(config.urls.preview, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': config.csrfToken
                    },
                    body: new URLSearchParams({
                        date_start: this.dateStart,
                        date_end: this.dateEnd,
                        day_mapping: JSON.stringify(dayMappingData)
                    })
                });

                const result = await response.json();

                if (result.success !== false) {
                    // Add unique IDs and full day names to lessons
                    let lessonIndex = 0;
                    const dayNamesFull = {
                        'Пн': 'Понедельник', 'Вт': 'Вторник', 'Ср': 'Среда',
                        'Чт': 'Четверг', 'Пт': 'Пятница', 'Сб': 'Суббота', 'Вс': 'Воскресенье'
                    };

                    if (result.by_day) {
                        result.by_day.forEach(day => {
                            day.day_name_full = dayNamesFull[day.day_name] || day.day_name;
                            day.lessons.forEach(lesson => {
                                lesson.uid = 'lesson_' + (lessonIndex++);
                                // Initialize selection (all selected by default)
                                this.lessonSelection[lesson.uid] = true;
                            });
                        });
                    }

                    this.preview = result;
                } else {
                    QazToast.error(result.message || 'Ошибка загрузки предпросмотра');
                }
            } catch (e) {
                console.error('Error loading preview:', e);
                QazToast.error('Ошибка загрузки');
            } finally {
                this.previewLoading = false;
            }
        },

        // Keep old loadPreview for backward compatibility
        async loadPreview() {
            // Only load if on step 2
            if (this.wizardStep === 2) {
                await this.loadPreviewWithMapping();
            }
        },

        // Lesson selection helpers
        getActivePreviewCount() {
            if (!this.preview?.by_day) return 0;
            let count = 0;
            this.preview.by_day.forEach(day => {
                day.lessons.forEach(lesson => {
                    if (this.lessonSelection[lesson.uid]) count++;
                });
            });
            return count;
        },

        getExcludedCount() {
            if (!this.preview?.total) return 0;
            return this.preview.total - this.getActivePreviewCount();
        },

        getConflictsInSelection() {
            if (!this.preview?.by_day) return 0;
            let count = 0;
            this.preview.by_day.forEach(day => {
                day.lessons.forEach(lesson => {
                    if (this.lessonSelection[lesson.uid] && lesson.has_conflict) count++;
                });
            });
            return count;
        },

        getDayActiveCount(day) {
            let count = 0;
            day.lessons.forEach(lesson => {
                if (this.lessonSelection[lesson.uid]) count++;
            });
            return count;
        },

        selectAllLessons() {
            if (!this.preview?.by_day) return;
            this.preview.by_day.forEach(day => {
                day.lessons.forEach(lesson => {
                    this.lessonSelection[lesson.uid] = true;
                });
            });
        },

        deselectAllLessons() {
            if (!this.preview?.by_day) return;
            this.preview.by_day.forEach(day => {
                day.lessons.forEach(lesson => {
                    this.lessonSelection[lesson.uid] = false;
                });
            });
        },

        excludeConflicts() {
            if (!this.preview?.by_day) return;
            this.preview.by_day.forEach(day => {
                day.lessons.forEach(lesson => {
                    if (lesson.has_conflict) {
                        this.lessonSelection[lesson.uid] = false;
                    }
                });
            });
            QazToast.success('Занятия с конфликтами исключены');
        },

        toggleDayLessons(day, selected) {
            day.lessons.forEach(lesson => {
                this.lessonSelection[lesson.uid] = selected;
            });
        },

        // Preview filtering
        getFilteredPreviewDays() {
            if (!this.preview?.by_day) return [];

            return this.preview.by_day.filter(day => {
                const filtered = this.getFilteredLessons(day.lessons);
                return filtered.length > 0;
            });
        },

        getFilteredLessons(lessons) {
            if (this.filterPreview === 'all') return lessons;
            if (this.filterPreview === 'active') {
                return lessons.filter(l => this.lessonSelection[l.uid]);
            }
            if (this.filterPreview === 'conflicts') {
                return lessons.filter(l => l.has_conflict);
            }
            return lessons;
        },

        // Generate schedule with selection
        async generateSchedule() {
            if (this.generating) return;

            // Collect selected lesson UIDs
            const selectedLessons = [];
            if (this.preview?.by_day) {
                this.preview.by_day.forEach(day => {
                    day.lessons.forEach(lesson => {
                        if (this.lessonSelection[lesson.uid]) {
                            selectedLessons.push({
                                date: lesson.date,
                                start_time: lesson.start_time,
                                end_time: lesson.end_time,
                                group_id: lesson.group_id,
                                teacher_id: lesson.teacher_id,
                                room_id: lesson.room_id,
                                typical_schedule_id: lesson.typical_schedule_id
                            });
                        }
                    });
                });
            }

            if (selectedLessons.length === 0) {
                QazToast.error('Нет выбранных занятий для создания');
                return;
            }

            this.generating = true;

            try {
                const response = await fetch(config.urls.generate, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': config.csrfToken
                    },
                    body: new URLSearchParams({
                        lessons: JSON.stringify(selectedLessons)
                    })
                });
                const result = await response.json();

                if (result.success) {
                    QazToast.success(result.message || 'Расписание создано');
                    setTimeout(() => {
                        window.location.href = config.urls.back;
                    }, 1500);
                } else {
                    QazToast.error(result.message || 'Ошибка');
                }
            } catch (e) {
                console.error('Error generating:', e);
                QazToast.error('Ошибка при создании расписания');
            } finally {
                this.generating = false;
            }
        }
    };
}
</script>
