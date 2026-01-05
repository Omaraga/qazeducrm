<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;
use yii\helpers\Json;

/** @var yii\web\View $this */
/** @var \app\models\forms\TypicalLessonForm $model */

$this->title = Yii::t('main', 'Типовое расписание');
$this->params['breadcrumbs'][] = ['label' => 'Расписание', 'url' => OrganizationUrl::to(['schedule/index'])];
$this->params['breadcrumbs'][] = $this->title;

// URLs для API
$config = [
    'urls' => [
        'events' => OrganizationUrl::to(['schedule/typical-events']),
        'preview' => OrganizationUrl::to(['schedule/typical-preview']),
        'generate' => OrganizationUrl::to(['schedule/typical-generate']),
        'back' => OrganizationUrl::to(['schedule/index']),
    ],
];
?>

<div x-data="typicalSchedule(<?= Html::encode(Json::encode($config)) ?>)" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Шаблон расписания по дням недели</p>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['schedule/index']) ?>" class="btn btn-secondary">
                <?= Icon::show('arrow-left') ?>
                Назад к расписанию
            </a>
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
                    <?= Icon::show('calendar', 'inline-block mr-2') ?>
                    Типовое расписание
                </button>
                <button type="button"
                        @click="activeTab = 'generate'"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors"
                        :class="activeTab === 'generate' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <?= Icon::show('clipboard-document-list', 'inline-block mr-2') ?>
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
                </div>

                <!-- Day Navigation (only in day view) -->
                <div x-show="calendarView === 'day'" class="flex items-center gap-2">
                    <button type="button" @click="prevDay()" class="btn btn-sm btn-icon btn-secondary">
                        <?= Icon::show('chevron-left') ?>
                    </button>
                    <div class="px-4 py-2 bg-white border border-gray-200 rounded-lg min-w-[180px] text-center">
                        <span class="font-medium text-gray-900" x-text="daysOfWeekFull[selectedDay - 1]"></span>
                    </div>
                    <button type="button" @click="nextDay()" class="btn btn-sm btn-icon btn-secondary">
                        <?= Icon::show('chevron-right') ?>
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
                                        <div class="calendar-time-slot">
                                            <template x-for="event in getEventsForDayHour(dayIndex + 1, hour)" :key="event.id">
                                                <div class="calendar-event"
                                                     :style="{ backgroundColor: event.color }"
                                                     :title="event.group_code + '\n' + event.teacher_fio + '\n' + event.start_time + ' - ' + event.end_time">
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
                                <div class="calendar-time-slot">
                                    <template x-for="event in getEventsForDayHour(selectedDay, hour)" :key="event.id">
                                        <div class="calendar-day-event"
                                             :style="{ backgroundColor: event.color }"
                                             :title="event.group_code + ' - ' + event.teacher_fio">
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
                    <?= Icon::show('calendar-days', 'mx-auto h-12 w-12 text-gray-300') ?>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Типовое расписание пустое</h3>
                    <p class="mt-2 text-gray-500">Добавьте занятия в типовое расписание через раздел "Типовое расписание" в настройках групп.</p>
                </div>
            </div>

            <!-- Day Summary (only in day view) -->
            <div x-show="calendarView === 'day' && getEventsForDay(selectedDay).length > 0" class="p-4 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center gap-6 text-sm text-gray-600">
                    <span>
                        <?= Icon::show('academic-cap', 'inline-block w-4 h-4 mr-1') ?>
                        Занятий: <strong x-text="getEventsForDay(selectedDay).length"></strong>
                    </span>
                    <span>
                        <?= Icon::show('clock', 'inline-block w-4 h-4 mr-1') ?>
                        Первое: <strong x-text="getFirstEventTime(selectedDay)"></strong>
                    </span>
                    <span>
                        <?= Icon::show('clock', 'inline-block w-4 h-4 mr-1') ?>
                        Последнее: <strong x-text="getLastEventTime(selectedDay)"></strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- Tab: Generate Schedule -->
        <div x-show="activeTab === 'generate'" class="p-6">
            <!-- Period Selection -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Период генерации</h3>
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="movePeriod(-7)" class="btn btn-sm btn-icon btn-secondary">
                            <?= Icon::show('chevron-left') ?>
                        </button>
                        <input type="date" x-model="dateStart" @change="loadPreview()" class="form-input w-auto">
                        <span class="text-gray-500">—</span>
                        <input type="date" x-model="dateEnd" @change="loadPreview()" class="form-input w-auto">
                        <button type="button" @click="movePeriod(7)" class="btn btn-sm btn-icon btn-secondary">
                            <?= Icon::show('chevron-right') ?>
                        </button>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" @click="setPeriodWeek()" class="btn btn-sm btn-secondary">
                            Эта неделя
                        </button>
                        <button type="button" @click="setPeriodMonth()" class="btn btn-sm btn-secondary">
                            Этот месяц
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Предпросмотр</h3>
                    <div x-show="previewLoading" class="flex items-center gap-2 text-gray-500 text-sm">
                        <span class="spinner spinner-sm"></span>
                        Загрузка...
                    </div>
                </div>

                <!-- Stats -->
                <div x-show="preview" class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900" x-text="preview?.total || 0"></div>
                        <div class="text-sm text-gray-500">Занятий будет создано</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900" x-text="preview?.by_day?.length || 0"></div>
                        <div class="text-sm text-gray-500">Дней</div>
                    </div>
                    <div class="bg-warning-50 rounded-lg p-4 text-center" x-show="preview?.total_conflicts > 0">
                        <div class="text-2xl font-bold text-warning-600" x-text="preview?.total_conflicts || 0"></div>
                        <div class="text-sm text-warning-600">Конфликтов</div>
                    </div>
                    <div class="bg-success-50 rounded-lg p-4 text-center" x-show="preview?.total_conflicts === 0">
                        <div class="text-2xl font-bold text-success-600"><?= Icon::show('check-circle', 'inline-block w-6 h-6') ?></div>
                        <div class="text-sm text-success-600">Нет конфликтов</div>
                    </div>
                </div>

                <!-- Conflicts Warning -->
                <div x-show="preview?.total_conflicts > 0" class="mb-6 p-4 bg-warning-50 border border-warning-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <?= Icon::show('exclamation-triangle', 'w-5 h-5 text-warning-600 flex-shrink-0 mt-0.5') ?>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-warning-800">Найдены пересечения</h4>
                            <ul class="mt-2 text-sm text-warning-700 space-y-1 max-h-40 overflow-y-auto">
                                <template x-for="conflictItem in preview?.conflicts || []" :key="conflictItem.lesson.date + conflictItem.lesson.start_time">
                                    <li class="flex items-start gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-warning-500 mt-1.5 flex-shrink-0"></span>
                                        <span>
                                            <span x-text="conflictItem.lesson.date_formatted"></span>
                                            <span x-text="conflictItem.lesson.start_time"></span>
                                            <span class="font-medium" x-text="conflictItem.lesson.group_code"></span>:
                                            <span x-text="conflictItem.conflicts[0]?.message"></span>
                                        </span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Preview List -->
                <div x-show="preview?.by_day?.length > 0" class="border border-gray-200 rounded-lg overflow-hidden max-h-[400px] overflow-y-auto mb-6">
                    <template x-for="day in preview?.by_day || []" :key="day.date">
                        <div class="border-b border-gray-200 last:border-b-0">
                            <div class="bg-gray-50 px-4 py-2 font-medium text-gray-900">
                                <span x-text="day.day_name"></span>, <span x-text="day.date_formatted"></span>
                            </div>
                            <div class="p-4 flex flex-wrap gap-2">
                                <template x-for="lesson in day.lessons" :key="lesson.date + lesson.start_time + lesson.group_id">
                                    <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm"
                                         :class="lesson.has_conflict ? 'bg-warning-100 border border-warning-300' : 'bg-gray-100'"
                                         :style="!lesson.has_conflict ? { borderLeft: '3px solid ' + lesson.color } : {}">
                                        <span class="font-medium" x-text="lesson.start_time + '-' + lesson.end_time"></span>
                                        <span x-text="lesson.group_code"></span>
                                        <span class="text-gray-500" x-text="'(' + lesson.teacher_fio + ')'"></span>
                                        <template x-if="lesson.room_name">
                                            <span class="text-gray-400" x-text="lesson.room_name"></span>
                                        </template>
                                        <template x-if="lesson.has_conflict">
                                            <?= Icon::show('exclamation-triangle', 'w-4 h-4 text-warning-600') ?>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty Preview -->
                <div x-show="!previewLoading && preview?.total === 0" class="text-center py-12 text-gray-500">
                    <?= Icon::show('calendar-days', 'mx-auto h-12 w-12 text-gray-300 mb-4') ?>
                    <p>В выбранный период не будет создано занятий.</p>
                    <p class="text-sm mt-1">Убедитесь, что типовое расписание настроено.</p>
                </div>

                <!-- Actions -->
                <div x-show="preview?.total > 0" class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-gray-200">
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer" x-show="preview?.total_conflicts > 0">
                            <input type="checkbox" x-model="skipConflicts" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-gray-700">Пропустить занятия с конфликтами</span>
                        </label>
                    </div>
                    <button type="button"
                            @click="generateSchedule()"
                            :disabled="generating"
                            class="btn btn-primary">
                        <template x-if="generating">
                            <span class="spinner spinner-sm mr-2"></span>
                        </template>
                        <?= Icon::show('check') ?>
                        <span x-text="'Создать ' + (skipConflicts && preview?.total_conflicts > 0 ? (preview.total - preview.total_conflicts) : preview?.total) + ' занятий'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function typicalSchedule(config) {
    return {
        activeTab: 'calendar',
        loading: false,
        events: [],

        // Calendar view
        calendarView: 'week', // 'week' | 'day'
        selectedDay: 1, // 1-7 (Monday-Sunday)

        daysOfWeek: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
        daysOfWeekFull: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
        hoursRange: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21],

        // Generate tab
        dateStart: '',
        dateEnd: '',
        preview: null,
        previewLoading: false,
        skipConflicts: false,
        generating: false,

        init() {
            this.loadEvents();
            this.setPeriodWeek();
        },

        async loadEvents() {
            this.loading = true;
            try {
                const response = await fetch(config.urls.events, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                this.events = await response.json();
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
            this.loadPreview();
        },

        setPeriodMonth() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            this.dateStart = this.formatDateInput(firstDay);
            this.dateEnd = this.formatDateInput(lastDay);
            this.loadPreview();
        },

        movePeriod(days) {
            const start = new Date(this.dateStart);
            const end = new Date(this.dateEnd);
            start.setDate(start.getDate() + days);
            end.setDate(end.getDate() + days);
            this.dateStart = this.formatDateInput(start);
            this.dateEnd = this.formatDateInput(end);
            this.loadPreview();
        },

        formatDateInput(date) {
            return date.toISOString().split('T')[0];
        },

        async loadPreview() {
            if (!this.dateStart || !this.dateEnd) return;

            this.previewLoading = true;
            this.preview = null;

            try {
                const response = await fetch(config.urls.preview, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || ''
                    },
                    body: new URLSearchParams({
                        date_start: this.dateStart,
                        date_end: this.dateEnd
                    })
                });
                this.preview = await response.json();
            } catch (e) {
                console.error('Error loading preview:', e);
            } finally {
                this.previewLoading = false;
            }
        },

        async generateSchedule() {
            if (this.generating) return;

            this.generating = true;

            try {
                const response = await fetch(config.urls.generate, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || ''
                    },
                    body: new URLSearchParams({
                        date_start: this.dateStart,
                        date_end: this.dateEnd,
                        skip_conflicts: this.skipConflicts ? '1' : '0'
                    })
                });
                const result = await response.json();

                if (result.success) {
                    if (window.$store?.toast) {
                        window.$store.toast.show(result.message, 'success');
                    }
                    // Redirect to schedule
                    setTimeout(() => {
                        window.location.href = config.urls.back;
                    }, 1000);
                } else {
                    if (window.$store?.toast) {
                        window.$store.toast.show(result.message || 'Ошибка', 'error');
                    }
                }
            } catch (e) {
                console.error('Error generating:', e);
                if (window.$store?.toast) {
                    window.$store.toast.show('Ошибка при создании расписания', 'error');
                }
            } finally {
                this.generating = false;
            }
        }
    };
}
</script>
