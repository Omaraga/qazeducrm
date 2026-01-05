<?php

use yii\helpers\Html;
use yii\helpers\Json;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Modal;
use app\widgets\tailwind\CollapsibleFilter;
use app\widgets\tailwind\Icon;

/** @var yii\web\View $this */

$this->title = 'Расписание';
$this->params['breadcrumbs'][] = $this->title;

// URLs для JavaScript компонента
$config = [
    'urls' => [
        'events' => OrganizationUrl::to(['schedule/events']),
        'filters' => OrganizationUrl::to(['schedule/filters']),
        'create' => OrganizationUrl::to(['schedule/ajax-create']),
        'update' => OrganizationUrl::to(['schedule/ajax-update']),
        'delete' => OrganizationUrl::to(['schedule/ajax-delete']),
        'move' => OrganizationUrl::to(['schedule/move']),
        'details' => OrganizationUrl::to(['schedule/details']),
        'teachers' => OrganizationUrl::to(['schedule/teachers']),
    ],
];
?>

<div x-data="scheduleCalendar(<?= Html::encode(Json::encode($config)) ?>)" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Управление занятиями</p>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['schedule/typical-schedule']) ?>" class="btn btn-outline">
                <?= Icon::show('refresh') ?>
                Типовое расписание
            </a>
            <button type="button" @click="openCreateModal(formatDate(currentDate), 9)" class="btn btn-primary">
                <?= Icon::show('plus') ?>
                Добавить занятие
            </button>
        </div>
    </div>

    <!-- Filters -->
    <?php CollapsibleFilter::begin(['title' => 'Фильтры']) ?>
    <div class="space-y-4">
        <!-- Groups filter -->
        <div>
            <label class="form-label mb-2">Группы</label>
            <div class="flex flex-wrap gap-2">
                <template x-for="group in filterOptions.groups" :key="group.id">
                    <button type="button"
                            class="filter-chip"
                            :class="{ 'active': isGroupSelected(group.id) }"
                            @click="toggleGroupFilter(group.id)">
                        <span class="filter-chip-color" :style="{ backgroundColor: group.color }"></span>
                        <span x-text="group.code"></span>
                    </button>
                </template>
                <template x-if="filterOptions.groups.length === 0">
                    <span class="text-sm text-gray-500">Нет групп</span>
                </template>
            </div>
        </div>

        <!-- Teachers filter -->
        <div>
            <label class="form-label mb-2">Преподаватели</label>
            <div class="flex flex-wrap gap-2">
                <template x-for="teacher in filterOptions.teachers" :key="teacher.id">
                    <button type="button"
                            class="filter-chip"
                            :class="{ 'active': isTeacherSelected(teacher.id) }"
                            @click="toggleTeacherFilter(teacher.id)">
                        <span x-text="teacher.fio"></span>
                    </button>
                </template>
                <template x-if="filterOptions.teachers.length === 0">
                    <span class="text-sm text-gray-500">Нет преподавателей</span>
                </template>
            </div>
        </div>

        <!-- Rooms filter -->
        <div x-show="filterOptions.rooms.length > 0">
            <label class="form-label mb-2">Кабинеты</label>
            <div class="flex flex-wrap gap-2">
                <template x-for="room in filterOptions.rooms" :key="room.id">
                    <button type="button"
                            class="filter-chip"
                            :class="{ 'active': isRoomSelected(room.id) }"
                            @click="toggleRoomFilter(room.id)">
                        <span class="filter-chip-color" :style="{ backgroundColor: room.color }"></span>
                        <span x-text="room.code ? room.code + ' - ' + room.name : room.name"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Clear filters -->
        <div x-show="activeFiltersCount > 0">
            <button type="button" @click="clearFilters()" class="btn btn-sm btn-secondary">
                Сбросить фильтры
            </button>
        </div>
    </div>
    <?php CollapsibleFilter::end() ?>

    <!-- Calendar Card -->
    <div class="card">
        <!-- Calendar Header -->
        <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Navigation -->
            <div class="flex items-center gap-2">
                <button type="button" @click="goToPrev()" class="btn btn-icon btn-sm btn-outline" title="Назад">
                    <?= Icon::show('chevron-left') ?>
                </button>
                <button type="button" @click="goToNext()" class="btn btn-icon btn-sm btn-outline" title="Вперед">
                    <?= Icon::show('chevron-right') ?>
                </button>
                <button type="button" @click="goToToday()" class="btn btn-sm btn-secondary ml-2">
                    Сегодня
                </button>
            </div>

            <!-- Title -->
            <h2 class="text-lg font-semibold text-gray-900" x-text="title"></h2>

            <!-- View Mode Toggle -->
            <div class="flex items-center gap-4">
                <!-- Day view mode toggle (timeline/rooms) -->
                <div x-show="viewMode === 'day' && filterOptions.rooms.length > 0" class="view-mode-toggle">
                    <button type="button"
                            class="view-mode-btn"
                            :class="{ 'active': dayViewMode === 'timeline' }"
                            @click="setDayViewMode('timeline')"
                            title="По времени">
                        <?= Icon::show('clock', 'w-4 h-4') ?>
                    </button>
                    <button type="button"
                            class="view-mode-btn"
                            :class="{ 'active': dayViewMode === 'rooms' }"
                            @click="setDayViewMode('rooms')"
                            title="По кабинетам">
                        <?= Icon::show('building-office', 'w-4 h-4') ?>
                    </button>
                </div>

                <div class="view-mode-toggle">
                    <button type="button"
                            class="view-mode-btn"
                            :class="{ 'active': viewMode === 'day' }"
                            @click="setViewMode('day')">
                        День
                    </button>
                    <button type="button"
                            class="view-mode-btn"
                            :class="{ 'active': viewMode === 'week' }"
                            @click="setViewMode('week')">
                        Неделя
                    </button>
                    <button type="button"
                            class="view-mode-btn"
                            :class="{ 'active': viewMode === 'month' }"
                            @click="setViewMode('month')">
                        Месяц
                    </button>
                </div>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="border-t border-gray-200 relative">
            <!-- Loading overlay -->
            <div x-show="loading" class="calendar-loading">
                <div class="spinner spinner-lg"></div>
            </div>

            <!-- Day View - Timeline mode -->
            <template x-if="viewMode === 'day' && dayViewMode === 'timeline'">
                <div class="calendar-grid calendar-grid-day">
                    <!-- Header -->
                    <div class="calendar-time-col"></div>
                    <div class="calendar-header-day" :class="{ 'today': formatDate(currentDate) === formatDate(new Date()) }">
                        <div class="calendar-header-day-name" x-text="daysOfWeek[currentDate.getDay() === 0 ? 6 : currentDate.getDay() - 1]"></div>
                        <div class="calendar-header-day-num" :class="{ 'today': formatDate(currentDate) === formatDate(new Date()) }" x-text="currentDate.getDate()"></div>
                    </div>

                    <!-- Time slots -->
                    <template x-for="hour in hoursRange" :key="hour">
                        <div class="contents">
                            <div class="calendar-time-col" x-text="hour + ':00'"></div>
                            <div class="calendar-time-slot calendar-time-slot-clickable"
                                 :class="{
                                     'calendar-today': formatDate(currentDate) === formatDate(new Date()),
                                     'calendar-drop-target': isDropTarget(formatDate(currentDate), hour)
                                 }"
                                 @click="openCreateModal(formatDate(currentDate), hour)"
                                 @dragover.prevent="onDragOver($event, formatDate(currentDate), hour)"
                                 @dragleave="onDragLeave()"
                                 @drop="onDrop($event, formatDate(currentDate), hour)">
                                <template x-for="event in getEventsForDateHour(formatDate(currentDate), hour)" :key="event.id">
                                    <div class="calendar-day-event"
                                         :style="{ backgroundColor: event.color }"
                                         :title="event.title + ' - ' + event.teacher"
                                         draggable="true"
                                         :class="{ 'calendar-event-dragging': isDragging(event.id) }"
                                         @click.stop="openViewModal(event.id)"
                                         @dragstart="onDragStart($event, event.id)"
                                         @dragend="onDragEnd()">
                                        <div class="calendar-day-event-title" x-text="event.title"></div>
                                        <div class="calendar-day-event-time" x-text="event.start_time + ' - ' + event.end_time"></div>
                                        <div class="calendar-day-event-teacher" x-text="event.teacher"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Day View - Rooms mode -->
            <template x-if="viewMode === 'day' && dayViewMode === 'rooms'">
                <div>
                    <!-- Header row with rooms -->
                    <div class="calendar-grid" :style="'grid-template-columns: 60px repeat(' + (filterOptions.rooms.length + 1) + ', 1fr)'">
                        <div class="calendar-time-col"></div>
                        <template x-for="room in filterOptions.rooms" :key="room.id">
                            <div class="calendar-header-day">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: room.color }"></span>
                                    <span class="calendar-header-day-name text-xs" x-text="room.code || room.name"></span>
                                </div>
                            </div>
                        </template>
                        <!-- Column for events without room -->
                        <div class="calendar-header-day">
                            <div class="calendar-header-day-name text-xs text-gray-400">Без кабинета</div>
                        </div>
                    </div>

                    <!-- Time slots with rooms -->
                    <div class="overflow-y-auto" style="max-height: 600px;">
                        <template x-for="hour in hoursRange" :key="hour">
                            <div class="calendar-grid" :style="'grid-template-columns: 60px repeat(' + (filterOptions.rooms.length + 1) + ', 1fr)'">
                                <div class="calendar-time-col" x-text="hour + ':00'"></div>
                                <template x-for="room in filterOptions.rooms" :key="room.id + '-' + hour">
                                    <div class="calendar-time-slot calendar-time-slot-clickable min-h-[60px]"
                                         :class="{
                                             'calendar-drop-target': isDropTarget(formatDate(currentDate) + '-' + room.id, hour)
                                         }"
                                         @click="openCreateModal(formatDate(currentDate), hour)">
                                        <template x-for="event in getEventsForRoomHour(formatDate(currentDate), room.id, hour)" :key="event.id">
                                            <div class="calendar-event"
                                                 :style="{ backgroundColor: event.color }"
                                                 :title="event.title + '\n' + event.teacher + '\n' + event.start_time + ' - ' + event.end_time"
                                                 draggable="true"
                                                 :class="{ 'calendar-event-dragging': isDragging(event.id) }"
                                                 @click.stop="openViewModal(event.id)"
                                                 @dragstart="onDragStart($event, event.id)"
                                                 @dragend="onDragEnd()">
                                                <div class="calendar-event-title" x-text="event.title"></div>
                                                <div class="calendar-event-time" x-text="event.start_time + ' - ' + event.end_time"></div>
                                                <div class="calendar-event-teacher text-[10px] opacity-80" x-text="event.teacher"></div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <!-- Column for events without room -->
                                <div class="calendar-time-slot calendar-time-slot-clickable min-h-[60px] bg-gray-50/50"
                                     @click="openCreateModal(formatDate(currentDate), hour)">
                                    <template x-for="event in getEventsWithoutRoomHour(formatDate(currentDate), hour)" :key="event.id">
                                        <div class="calendar-event"
                                             :style="{ backgroundColor: event.color }"
                                             :title="event.title + '\n' + event.teacher + '\n' + event.start_time + ' - ' + event.end_time"
                                             draggable="true"
                                             :class="{ 'calendar-event-dragging': isDragging(event.id) }"
                                             @click.stop="openViewModal(event.id)"
                                             @dragstart="onDragStart($event, event.id)"
                                             @dragend="onDragEnd()">
                                            <div class="calendar-event-title" x-text="event.title"></div>
                                            <div class="calendar-event-time" x-text="event.start_time + ' - ' + event.end_time"></div>
                                            <div class="calendar-event-teacher text-[10px] opacity-80" x-text="event.teacher"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Week View -->
            <template x-if="viewMode === 'week'">
                <div>
                    <!-- Header row -->
                    <div class="calendar-grid calendar-grid-week">
                        <div class="calendar-time-col"></div>
                        <template x-for="day in weekDays" :key="day.dateStr">
                            <div class="calendar-header-day cursor-pointer hover:bg-primary-50 transition-colors"
                                 :class="{ 'today': day.isToday }"
                                 @click="goToDay(day.date)"
                                 title="Показать расписание за этот день">
                                <div class="calendar-header-day-name" x-text="day.dayName"></div>
                                <div class="calendar-header-day-num" :class="{ 'today': day.isToday }" x-text="day.dayNum"></div>
                            </div>
                        </template>
                    </div>

                    <!-- Time slots -->
                    <div class="overflow-y-auto" style="max-height: 600px;">
                        <template x-for="hour in hoursRange" :key="hour">
                            <div class="calendar-grid calendar-grid-week">
                                <div class="calendar-time-col" x-text="hour + ':00'"></div>
                                <template x-for="day in weekDays" :key="day.dateStr + '-' + hour">
                                    <div class="calendar-time-slot calendar-time-slot-clickable"
                                         :class="{
                                             'calendar-today': day.isToday,
                                             'calendar-drop-target': isDropTarget(day.dateStr, hour)
                                         }"
                                         @click="openCreateModal(day.dateStr, hour)"
                                         @dragover.prevent="onDragOver($event, day.dateStr, hour)"
                                         @dragleave="onDragLeave()"
                                         @drop="onDrop($event, day.dateStr, hour)">
                                        <template x-for="event in getEventsForDateHour(day.dateStr, hour)" :key="event.id">
                                            <div class="calendar-event"
                                                 :style="{ backgroundColor: event.color }"
                                                 :title="event.title + '\n' + event.teacher + '\n' + event.start_time + ' - ' + event.end_time"
                                                 draggable="true"
                                                 :class="{ 'calendar-event-dragging': isDragging(event.id) }"
                                                 @click.stop="openViewModal(event.id)"
                                                 @dragstart="onDragStart($event, event.id)"
                                                 @dragend="onDragEnd()">
                                                <div class="calendar-event-title" x-text="event.title"></div>
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

            <!-- Month View -->
            <template x-if="viewMode === 'month'">
                <div>
                    <!-- Header row -->
                    <div class="calendar-grid calendar-grid-month">
                        <template x-for="dayName in daysOfWeek" :key="dayName">
                            <div class="calendar-header-day">
                                <div class="calendar-header-day-name" x-text="dayName"></div>
                            </div>
                        </template>
                    </div>

                    <!-- Month grid -->
                    <template x-for="week in monthWeeks" :key="week[0].dateStr">
                        <div class="calendar-grid calendar-grid-month">
                            <template x-for="day in week" :key="day.dateStr">
                                <div class="calendar-month-day"
                                     :class="{
                                         'calendar-month-day-other': !day.isCurrentMonth,
                                         'calendar-today': day.isToday
                                     }">
                                    <div class="calendar-month-day-num cursor-pointer hover:bg-primary-100 hover:text-primary-700 rounded-full transition-colors"
                                         :class="{ 'other': !day.isCurrentMonth, 'today': day.isToday }"
                                         @click="goToDay(day.date)"
                                         x-text="day.dayNum"
                                         title="Показать расписание за этот день"></div>
                                    <template x-for="(event, index) in getEventsForDate(day.dateStr).slice(0, 3)" :key="event.id">
                                        <div class="calendar-month-event"
                                             :style="{ backgroundColor: event.color }"
                                             x-text="event.title"
                                             @click.stop="openViewModal(event.id)">
                                        </div>
                                    </template>
                                    <template x-if="getEventsForDate(day.dateStr).length > 3">
                                        <div class="calendar-month-more" x-text="'+' + (getEventsForDate(day.dateStr).length - 3) + ' ещё'"></div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- Legend -->
    <div class="card">
        <div class="card-body py-3">
            <div class="flex flex-wrap items-center gap-6 text-sm">
                <div class="flex items-center gap-2">
                    <?= Icon::show('check-circle', 'w-5 h-5 text-success-500') ?>
                    <span class="text-gray-600">Посещение проставлено</span>
                </div>
                <div class="flex items-center gap-2">
                    <?= Icon::show('clock', 'w-5 h-5 text-warning-500') ?>
                    <span class="text-gray-600">Ожидает заполнения</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Lesson Modal -->
    <?php Modal::begin(['id' => 'create-lesson-modal', 'title' => 'Новое занятие', 'size' => 'lg']); ?>
    <?= $this->render('_modal-form', ['isEdit' => false]) ?>
    <?php Modal::end(); ?>

    <!-- View Lesson Modal -->
    <?php Modal::begin(['id' => 'view-lesson-modal', 'title' => 'Детали занятия', 'size' => 'lg']); ?>
    <?= $this->render('_modal-view') ?>
    <?php Modal::end(); ?>

    <!-- Edit Lesson Modal -->
    <?php Modal::begin(['id' => 'edit-lesson-modal', 'title' => 'Редактировать занятие', 'size' => 'lg']); ?>
    <?= $this->render('_modal-form', ['isEdit' => true]) ?>
    <?php Modal::end(); ?>

    <!-- Delete Confirmation Modal -->
    <?php Modal::begin(['id' => 'delete-lesson-modal', 'title' => 'Удаление занятия']); ?>
    <div class="text-center py-4">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-danger-100 flex items-center justify-center">
            <?= Icon::show('trash', 'w-8 h-8 text-danger-600') ?>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Удалить занятие?</h3>
        <p class="text-gray-500 mb-6">Это действие нельзя отменить.</p>
        <div class="flex justify-center gap-3">
            <button type="button" @click="$dispatch('close-modal', 'delete-lesson-modal')" class="btn btn-secondary">
                Отмена
            </button>
            <button type="button" @click="deleteEvent(selectedEvent?.id)" class="btn btn-danger">
                Удалить
            </button>
        </div>
    </div>
    <?php Modal::end(); ?>
</div>
