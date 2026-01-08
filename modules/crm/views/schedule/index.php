<?php

use yii\helpers\Html;
use yii\helpers\Json;
use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Modal;
use app\widgets\tailwind\CollapsibleFilter;
use app\widgets\tailwind\Icon;

/** @var yii\web\View $this */
/** @var array $initialData */

$this->title = 'Расписание';
$this->params['breadcrumbs'][] = $this->title;

// URLs и начальные данные для JavaScript компонента
// settings и filters передаются сразу, чтобы избежать отдельных AJAX запросов
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
        'settings' => OrganizationUrl::to(['schedule/settings']),
        'saveSettings' => OrganizationUrl::to(['schedule/save-settings']),
    ],
    'initialData' => $initialData ?? null,
];
?>

<div x-data="scheduleCalendar(<?= Html::encode(Json::encode($config)) ?>)" class="space-y-3">
    <!-- Filters + Mini Calendar Combined -->
    <?php CollapsibleFilter::begin(['title' => 'Фильтры', 'compact' => true]) ?>
    <div class="flex flex-col lg:flex-row gap-4">
        <!-- Mini Calendar (left column) -->
        <div class="lg:w-52 flex-shrink-0">
            <!-- Mini calendar header -->
            <div class="flex items-center justify-between mb-2">
                <button type="button" @click="miniCalendarPrev()" class="p-1 hover:bg-gray-100 rounded">
                    <?= Icon::show('chevron-left', 'sm') ?>
                </button>
                <span class="text-xs font-medium text-gray-600" x-text="miniCalendarTitle"></span>
                <button type="button" @click="miniCalendarNext()" class="p-1 hover:bg-gray-100 rounded">
                    <?= Icon::show('chevron-right', 'sm') ?>
                </button>
            </div>

            <!-- Mini calendar grid -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0; text-align: center; font-size: 11px;">
                <!-- Day headers -->
                <div class="py-0.5 text-gray-400 font-medium text-[10px]">Пн</div>
                <div class="py-0.5 text-gray-400 font-medium text-[10px]">Вт</div>
                <div class="py-0.5 text-gray-400 font-medium text-[10px]">Ср</div>
                <div class="py-0.5 text-gray-400 font-medium text-[10px]">Чт</div>
                <div class="py-0.5 text-gray-400 font-medium text-[10px]">Пт</div>
                <div class="py-0.5 text-gray-400 font-medium text-[10px]">Сб</div>
                <div class="py-0.5 text-gray-400 font-medium text-[10px]">Вс</div>

                <!-- Date cells -->
                <template x-for="day in miniCalendarDays" :key="day.dateStr">
                    <div class="relative" style="padding: 1px 0;">
                        <button type="button"
                                @click="goToDay(day.date)"
                                class="rounded-full transition-colors"
                                style="width: 24px; height: 24px; font-size: 11px;"
                                :class="{
                                    'bg-primary-500 text-white font-medium': day.dateStr === currentDateStr,
                                    'bg-primary-100 text-primary-700': day.isToday && day.dateStr !== currentDateStr,
                                    'text-gray-300': !day.isCurrentMonth,
                                    'text-gray-700 hover:bg-gray-100': day.isCurrentMonth && day.dateStr !== currentDateStr && !day.isToday
                                }"
                                x-text="day.dayNum">
                        </button>
                        <div x-show="miniCalendarHasEvents(day.dateStr)"
                             class="absolute rounded-full"
                             style="bottom: -1px; left: 50%; transform: translateX(-50%); width: 3px; height: 3px;"
                             :class="day.dateStr === currentDateStr ? 'bg-white' : 'bg-primary-400'"></div>
                    </div>
                </template>
            </div>

            <!-- Quick buttons -->
            <div class="mt-2 flex gap-1">
                <button type="button" @click="goToToday()" class="flex-1 rounded border border-gray-300 bg-gray-50 hover:bg-gray-100 text-gray-600" style="font-size: 10px; padding: 3px 6px;">
                    Сегодня
                </button>
                <button type="button" @click="goToWeekStart()" class="flex-1 rounded border border-gray-200 hover:bg-gray-50 text-gray-500" style="font-size: 10px; padding: 3px 6px;">
                    Нач. нед.
                </button>
            </div>
        </div>

        <!-- Divider -->
        <div class="hidden lg:block w-px bg-gray-200"></div>

        <!-- Filters (right column) -->
        <div class="flex-1 space-y-3">
            <!-- Groups filter -->
            <div>
                <label class="text-xs font-medium text-gray-500 mb-1 block">Группы</label>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="group in filteredGroups" :key="group.id">
                        <button type="button"
                                class="filter-chip text-xs py-1 px-2"
                                :class="{ 'active': isGroupSelected(group.id) }"
                                @click="toggleGroupFilter(group.id)">
                            <span class="filter-chip-color" style="width: 8px; height: 8px;" :style="{ backgroundColor: group.color }"></span>
                            <span x-text="group.code"></span>
                        </button>
                    </template>
                    <template x-if="filteredGroups.length === 0">
                        <span class="text-xs text-gray-400">Нет групп</span>
                    </template>
                </div>
            </div>

            <!-- Teachers filter -->
            <div>
                <label class="text-xs font-medium text-gray-500 mb-1 block">Преподаватели</label>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="teacher in filteredTeachers" :key="teacher.id">
                        <button type="button"
                                class="filter-chip text-xs py-1 px-2"
                                :class="{ 'active': isTeacherSelected(teacher.id) }"
                                @click="toggleTeacherFilter(teacher.id)">
                            <span x-text="teacher.fio"></span>
                        </button>
                    </template>
                    <template x-if="filteredTeachers.length === 0">
                        <span class="text-xs text-gray-400">Нет преподавателей</span>
                    </template>
                </div>
            </div>

            <!-- Rooms filter -->
            <div x-show="filterOptions.rooms.length > 0">
                <label class="text-xs font-medium text-gray-500 mb-1 block">Кабинеты</label>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="room in filterOptions.rooms" :key="room.id">
                        <button type="button"
                                class="filter-chip text-xs py-1 px-2"
                                :class="{ 'active': isRoomSelected(room.id) }"
                                @click="toggleRoomFilter(room.id)">
                            <span class="filter-chip-color" style="width: 8px; height: 8px;" :style="{ backgroundColor: room.color }"></span>
                            <span x-text="room.code || room.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Clear filters -->
            <div x-show="activeFiltersCount > 0" class="pt-1">
                <button type="button" @click="clearFilters()" class="text-xs text-gray-500 hover:text-gray-700">
                    ✕ Сбросить фильтры
                </button>
            </div>
        </div>
    </div>
    <?php CollapsibleFilter::end() ?>

    <!-- Calendar Card -->
    <div class="card">
        <!-- Calendar Header (compact, responsive) -->
        <div class="px-2 py-2 bg-white border-b border-gray-100">
            <!-- Mobile: 2 rows, Desktop: 1 row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">

                <!-- Row 1 on mobile / Left on desktop: Actions -->
                <div class="flex items-center justify-between sm:justify-start gap-1.5 sm:gap-1 order-2 sm:order-1">
                    <div class="flex items-center gap-1.5 sm:gap-1">
                        <button type="button" @click="goToToday()" class="px-3 py-2 sm:px-2 sm:py-1 text-xs rounded border border-gray-300 hover:bg-gray-50 cursor-pointer active:bg-gray-100">
                            Сегодня
                        </button>
                        <button type="button" @click="openCreateModal(currentDateStr, 9)" class="inline-flex items-center gap-1 px-3 py-2 sm:px-2 sm:py-1 text-xs rounded bg-primary-500 text-white hover:bg-primary-600 active:bg-primary-700 cursor-pointer" title="Создать занятие">
                            <?= Icon::show('plus', 'xs') ?>
                            Добавить
                        </button>
                        <a href="<?= OrganizationUrl::to(['/crm/schedule-template']) ?>" class="inline-flex items-center gap-1 px-3 py-2 sm:px-2 sm:py-1 text-xs rounded border border-gray-300 hover:bg-gray-50 active:bg-gray-100 cursor-pointer" title="Шаблоны">
                            <?= Icon::show('template', 'xs') ?>
                            <span class="hidden sm:inline">Шаблон</span>
                        </a>
                    </div>
                    <!-- View mode on mobile (right side of row 1) -->
                    <div class="flex items-center gap-1 sm:hidden">
                        <div class="view-mode-toggle">
                            <button type="button" class="view-mode-btn text-xs px-2.5 py-1.5" :class="{ 'active': viewMode === 'day' }" @click="setViewMode('day')" title="День">Д</button>
                            <button type="button" class="view-mode-btn text-xs px-2.5 py-1.5" :class="{ 'active': viewMode === 'week' }" @click="setViewMode('week')" title="Неделя">Н</button>
                            <button type="button" class="view-mode-btn text-xs px-2.5 py-1.5" :class="{ 'active': viewMode === 'month' }" @click="setViewMode('month')" title="Месяц">М</button>
                        </div>
                    </div>
                </div>

                <!-- Row 0 on mobile / Center on desktop: Navigation + Title -->
                <div class="flex items-center justify-center gap-1 order-1 sm:order-2">
                    <button type="button" @click="goToPrev()" class="p-2 sm:p-1 rounded hover:bg-gray-100 active:bg-gray-200 cursor-pointer" title="Назад">
                        <?= Icon::show('chevron-left', 'sm') ?>
                    </button>
                    <h2 class="text-sm font-medium text-gray-900 min-w-[130px] sm:min-w-[140px] text-center" x-text="title"></h2>
                    <button type="button" @click="goToNext()" class="p-2 sm:p-1 rounded hover:bg-gray-100 active:bg-gray-200 cursor-pointer" title="Вперед">
                        <?= Icon::show('chevron-right', 'sm') ?>
                    </button>
                </div>

                <!-- Desktop only: Right side controls -->
                <div class="hidden sm:flex items-center gap-2 order-3">
                    <!-- Grid interval selector -->
                    <div x-show="viewMode !== 'month'" class="flex items-center gap-1">
                        <select x-model.number="gridInterval" @change="saveGridInterval()" class="text-xs py-1 px-1.5 border border-gray-300 rounded cursor-pointer" title="Интервал сетки">
                            <option value="60">1ч</option>
                            <option value="30">30м</option>
                            <option value="15">15м</option>
                        </select>
                    </div>
                    <!-- Day view mode toggle -->
                    <div x-show="viewMode === 'day' && filterOptions.rooms.length > 0" class="view-mode-toggle">
                        <button type="button" class="view-mode-btn" :class="{ 'active': dayViewMode === 'timeline' }" @click="setDayViewMode('timeline')" title="По времени">
                            <?= Icon::show('clock', 'xs') ?>
                        </button>
                        <button type="button" class="view-mode-btn" :class="{ 'active': dayViewMode === 'rooms' }" @click="setDayViewMode('rooms')" title="По кабинетам">
                            <?= Icon::show('building-office', 'xs') ?>
                        </button>
                    </div>
                    <!-- View mode toggle -->
                    <div class="view-mode-toggle">
                        <button type="button" class="view-mode-btn text-xs" :class="{ 'active': viewMode === 'day' }" @click="setViewMode('day')" title="День">Д</button>
                        <button type="button" class="view-mode-btn text-xs" :class="{ 'active': viewMode === 'week' }" @click="setViewMode('week')" title="Неделя">Н</button>
                        <button type="button" class="view-mode-btn text-xs" :class="{ 'active': viewMode === 'month' }" @click="setViewMode('month')" title="Месяц">М</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="border-t border-gray-200 relative">
            <!-- Loading overlay -->
            <div x-show="loading" class="calendar-loading">
                <div class="spinner spinner-lg"></div>
            </div>

            <!-- Empty state -->
            <div x-show="!loading && events.length === 0 && filterOptions.groups.length > 0" class="text-center py-16">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <?= Icon::show('calendar', 'xl', 'text-gray-400') ?>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Нет занятий на выбранный период</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    На выбранную дату занятия не запланированы.
                    <span x-show="activeFiltersCount > 0">Попробуйте сбросить фильтры или выбрать другой период.</span>
                </p>
                <div class="flex justify-center gap-3">
                    <button type="button" @click="openCreateModal(currentDateStr, 9)" class="btn btn-primary">
                        <?= Icon::show('plus') ?>
                        Добавить занятие
                    </button>
                    <button type="button" x-show="activeFiltersCount > 0" @click="clearFilters()" class="btn btn-secondary">
                        Сбросить фильтры
                    </button>
                </div>
            </div>

            <!-- No groups state -->
            <div x-show="!loading && filterOptions.groups.length === 0" class="text-center py-16">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <?= Icon::show('users', 'xl', 'text-gray-400') ?>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Нет групп</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    Для создания расписания сначала добавьте группы учеников.
                </p>
                <a href="<?= OrganizationUrl::to(['/crm/group/create']) ?>" class="btn btn-primary">
                    <?= Icon::show('plus') ?>
                    Создать группу
                </a>
            </div>

            <!-- Day View - Timeline mode -->
            <template x-if="viewMode === 'day' && dayViewMode === 'timeline'">
                <div>
                    <!-- Scrollable container with sticky header -->
                    <div class="overflow-y-auto relative" style="max-height: 650px;">
                        <!-- Header (sticky) -->
                        <div class="calendar-grid calendar-grid-day sticky top-0 z-10 bg-white">
                            <div class="calendar-time-col"></div>
                            <div class="calendar-header-day" :class="{ 'today': currentDateStr === todayDateStr }">
                                <div class="calendar-header-day-name" x-text="daysOfWeek[currentDate.getDay() === 0 ? 6 : currentDate.getDay() - 1]"></div>
                                <div class="calendar-header-day-num" :class="{ 'today': currentDateStr === todayDateStr }" x-text="currentDate.getDate()"></div>
                            </div>
                        </div>

                        <!-- Линия текущего времени -->
                        <div x-show="isTodayInView && isTimeLineVisible"
                             class="current-time-line"
                             :style="{ top: timeLinePosition + 'px', left: '60px' }">
                            <div class="current-time-label" x-text="currentTimeFormatted"></div>
                        </div>

                        <!-- Time slots grid -->
                        <div class="calendar-grid calendar-grid-day">
                            <template x-for="slot in timeSlots" :key="slot.key">
                                <div class="contents">
                                    <div class="calendar-time-col" x-text="slot.label"></div>
                                    <div class="calendar-time-slot calendar-time-slot-clickable"
                                         :style="{ position: 'relative', minHeight: slotHeight + 'px' }"
                                         :class="{
                                             'calendar-today': currentDateStr === todayDateStr,
                                             'calendar-drop-target': isDropTarget(currentDateStr, slot.hour, slot.minute)
                                         }"
                                         @click="openCreateModal(currentDateStr, slot.hour, slot.minute)"
                                         @dragover.prevent="onDragOver($event, currentDateStr, slot.hour, slot.minute)"
                                         @dragleave="onDragLeave()"
                                         @drop="onDrop($event, currentDateStr, slot.hour, slot.minute)">
                                <!-- События с абсолютным позиционированием для отображения длительности -->
                                <!-- ОПТИМИЗИРОВАНО: используем предрасчитанные стили из event._style -->
                                <template x-for="event in getEventsStartingInSlot(currentDateStr, slot.hour, slot.minute)" :key="event.id">
                                    <div class="calendar-day-event"
                                         :style="'background-color:' + event.color + ';position:absolute;top:' + getEventTopOffsetPx(event, slot.hour, slot.minute) + 'px;height:' + (event._style?.height || 60) + 'px;left:' + (event._style?.left || 0) + '%;width:' + (event._style?.width || 99) + '%;min-width:100px;z-index:' + (event._style?.zIndex || 50) + ';overflow:hidden'"
                                         :title="event.title + ' - ' + event.teacher + '\n' + event.start_time + ' - ' + event.end_time"
                                         draggable="true"
                                         :class="{ 'calendar-event-dragging': isDragging(event.id) }"
                                         @click.stop="openViewModal(event.id)"
                                         @dragstart="onDragStart($event, event.id)"
                                         @dragend="onDragEnd()"
                                         @mouseenter="$event.currentTarget.style.zIndex = 200"
                                         @mouseleave="$event.currentTarget.style.zIndex = event._style?.zIndex || 50">
                                        <div class="calendar-day-event-title" x-text="event.title"></div>
                                        <div class="calendar-day-event-time" x-text="event.start_time + ' - ' + event.end_time"></div>
                                        <div class="calendar-day-event-teacher" x-text="event.teacher"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    </div>
                </div>
            </template>

            <!-- Day View - Rooms mode -->
            <template x-if="viewMode === 'day' && dayViewMode === 'rooms'">
                <div>
                    <!-- Scrollable container with sticky header -->
                    <div class="overflow-y-auto relative" style="max-height: 650px;">
                        <!-- Header row with rooms (sticky) -->
                        <div class="calendar-grid sticky top-0 z-10 bg-white" :style="'grid-template-columns: 60px repeat(' + (displayedRooms.length + (filters.rooms.length === 0 ? 1 : 0)) + ', 1fr)'">
                            <div class="calendar-time-col"></div>
                            <template x-for="room in displayedRooms" :key="room.id">
                                <div class="calendar-header-day calendar-room-header"
                                     :class="{ 'calendar-room-hover': hoveredRoomId === room.id }"
                                     @mouseenter="hoveredRoomId = room.id"
                                     @mouseleave="hoveredRoomId = null">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: room.color }"></span>
                                        <span class="calendar-header-day-name text-xs" x-text="room.code || room.name"></span>
                                    </div>
                                </div>
                            </template>
                            <!-- Column for events without room -->
                            <div class="calendar-header-day"
                                 x-show="filters.rooms.length === 0"
                                 :class="{ 'calendar-room-hover': hoveredRoomId === 'none' }"
                                 @mouseenter="hoveredRoomId = 'none'"
                                 @mouseleave="hoveredRoomId = null">
                                <div class="calendar-header-day-name text-xs text-gray-400">Без кабинета</div>
                            </div>
                        </div>

                        <!-- Time slots with rooms -->
                        <!-- Линия текущего времени -->
                        <div x-show="isTodayInView && isTimeLineVisible"
                             class="current-time-line"
                             :style="{ top: timeLinePosition + 'px' }">
                            <div class="current-time-label" x-text="currentTimeFormatted"></div>
                        </div>
                        <template x-for="slot in timeSlots" :key="slot.key">
                            <div class="calendar-grid" :style="'grid-template-columns: 60px repeat(' + (displayedRooms.length + (filters.rooms.length === 0 ? 1 : 0)) + ', 1fr)'">
                                <div class="calendar-time-col" x-text="slot.label"></div>
                                <template x-for="room in displayedRooms" :key="room.id + '-' + slot.key">
                                    <div class="calendar-time-slot calendar-time-slot-clickable calendar-room-cell"
                                         :style="{ position: 'relative', minHeight: slotHeight + 'px' }"
                                         :class="{
                                             'calendar-drop-target': isDropTarget(currentDateStr + '-' + room.id, slot.hour, slot.minute),
                                             'calendar-room-hover': hoveredRoomId === room.id
                                         }"
                                         @mouseenter="hoveredRoomId = room.id"
                                         @mouseleave="hoveredRoomId = null"
                                         @click="openCreateModal(currentDateStr, slot.hour, slot.minute, room.id)">
                                        <!-- События с абсолютным позиционированием -->
                                        <!-- ОПТИМИЗИРОВАНО: используем предрасчитанные стили из event._style -->
                                        <template x-for="(event, idx) in getEventsStartingInRoomSlot(currentDateStr, room.id, slot.hour, slot.minute)" :key="event.id">
                                            <div class="calendar-event"
                                                 :style="'background-color:' + event.color + ';position:absolute;top:' + getEventTopOffsetPx(event, slot.hour, slot.minute) + 'px;height:' + (event._style?.height || 60) + 'px;left:2px;right:2px;z-index:' + (event._style?.zIndex || 50) + ';overflow:hidden'"
                                                 :title="event.title + '\n' + event.teacher + '\n' + event.start_time + ' - ' + event.end_time"
                                                 draggable="true"
                                                 :class="{ 'calendar-event-dragging': isDragging(event.id) }"
                                                 @click.stop="openViewModal(event.id)"
                                                 @dragstart="onDragStart($event, event.id)"
                                                 @dragend="onDragEnd()"
                                                 @mouseenter="$event.currentTarget.style.zIndex = 200"
                                                 @mouseleave="$event.currentTarget.style.zIndex = event._style?.zIndex || 50">
                                                <div class="calendar-event-title" x-text="event.title"></div>
                                                <div class="calendar-event-time" x-text="event.start_time + ' - ' + event.end_time"></div>
                                                <div class="calendar-event-teacher text-[10px] opacity-80" x-text="event.teacher"></div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <!-- Column for events without room -->
                                <div x-show="filters.rooms.length === 0"
                                     class="calendar-time-slot calendar-time-slot-clickable calendar-room-cell bg-gray-50/50"
                                     :style="{ position: 'relative', minHeight: slotHeight + 'px' }"
                                     :class="{ 'calendar-room-hover': hoveredRoomId === 'none' }"
                                     @mouseenter="hoveredRoomId = 'none'"
                                     @mouseleave="hoveredRoomId = null"
                                     @click="openCreateModal(currentDateStr, slot.hour, slot.minute, null)">
                                    <!-- События без комнаты с абсолютным позиционированием -->
                                    <!-- ОПТИМИЗИРОВАНО: используем предрасчитанные стили из event._style -->
                                    <template x-for="(event, idx) in getEventsStartingWithoutRoom(currentDateStr, slot.hour, slot.minute)" :key="event.id">
                                        <div class="calendar-event"
                                             :style="'background-color:' + event.color + ';position:absolute;top:' + getEventTopOffsetPx(event, slot.hour, slot.minute) + 'px;height:' + (event._style?.height || 60) + 'px;left:2px;right:2px;z-index:' + (event._style?.zIndex || 50) + ';overflow:hidden'"
                                             :title="event.title + '\n' + event.teacher + '\n' + event.start_time + ' - ' + event.end_time"
                                             draggable="true"
                                             :class="{ 'calendar-event-dragging': isDragging(event.id) }"
                                             @click.stop="openViewModal(event.id)"
                                             @dragstart="onDragStart($event, event.id)"
                                             @dragend="onDragEnd()"
                                             @mouseenter="$event.currentTarget.style.zIndex = 200"
                                             @mouseleave="$event.currentTarget.style.zIndex = event._style?.zIndex || 50">
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
                <div class="overflow-x-auto">
                    <!-- Scrollable container with sticky header -->
                    <div class="overflow-y-auto relative" style="max-height: 650px;">
                        <!-- Header row (sticky) -->
                        <div class="calendar-grid calendar-grid-week sticky top-0 z-10 bg-white">
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
                        <!-- Линия текущего времени для недели -->
                        <div x-show="isTodayInView && isTimeLineVisible && todayIndexInWeek >= 0"
                             class="current-time-line current-time-line-week"
                             :style="{
                                 top: timeLinePosition + 'px',
                                 left: 'calc(60px + ' + todayIndexInWeek + ' * (100% - 60px) / 7)',
                                 width: 'calc((100% - 60px) / 7)'
                             }">
                            <div class="current-time-label" x-text="currentTimeFormatted"></div>
                        </div>
                        <template x-for="slot in timeSlots" :key="slot.key">
                            <div class="calendar-grid calendar-grid-week">
                                <div class="calendar-time-col" x-text="slot.label"></div>
                                <template x-for="day in weekDays" :key="day.dateStr + '-' + slot.key">
                                    <div class="calendar-time-slot calendar-time-slot-clickable"
                                         :style="{ position: 'relative', minHeight: slotHeight + 'px' }"
                                         :class="{
                                             'calendar-today': day.isToday,
                                             'calendar-drop-target': isDropTarget(day.dateStr, slot.hour, slot.minute)
                                         }"
                                         @click="openCreateModal(day.dateStr, slot.hour, slot.minute)"
                                         @dragover.prevent="onDragOver($event, day.dateStr, slot.hour, slot.minute)"
                                         @dragleave="onDragLeave()"
                                         @drop="onDrop($event, day.dateStr, slot.hour, slot.minute)">
                                        <!-- События с абсолютным позиционированием для отображения длительности -->
                                        <!-- ОПТИМИЗИРОВАНО: используем предрасчитанные стили из event._style -->
                                        <template x-for="event in getEventsStartingInSlot(day.dateStr, slot.hour, slot.minute)" :key="event.id">
                                            <div class="calendar-event"
                                                 :style="'background-color:' + event.color + ';position:absolute;top:' + getEventTopOffsetPx(event, slot.hour, slot.minute) + 'px;height:' + (event._style?.height || 60) + 'px;left:' + (event._style?.left || 0) + '%;width:' + (event._style?.width || 99) + '%;min-width:40px;z-index:' + (event._style?.zIndex || 50) + ';overflow:hidden'"
                                                 :title="event.title + '\n' + event.teacher + '\n' + event.start_time + ' - ' + event.end_time + (event.room ? '\n📍 ' + event.room : '')"
                                                 draggable="true"
                                                 :class="{ 'calendar-event-dragging': isDragging(event.id) }"
                                                 @click.stop="openViewModal(event.id)"
                                                 @dragstart="onDragStart($event, event.id)"
                                                 @dragend="onDragEnd()"
                                                 @mouseenter="$event.currentTarget.style.zIndex = 200"
                                                 @mouseleave="$event.currentTarget.style.zIndex = event._style?.zIndex || 50">
                                                <div class="calendar-event-title" x-text="event.title"></div>
                                                <div class="calendar-event-time" x-text="event.start_time + '-' + event.end_time"></div>
                                                <div x-show="event.room" class="calendar-event-room text-[10px] opacity-80 truncate" x-text="event.room"></div>
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
                                        <div class="calendar-month-event truncate"
                                             :style="{ backgroundColor: event.color }"
                                             :title="event.title + ' (' + event.start_time + '-' + event.end_time + ')' + '\n' + event.teacher + (event.room ? '\n📍 ' + event.room : '')"
                                             @click.stop="openViewModal(event.id)">
                                            <span x-text="event.room ? event.title + ' • ' + event.room : event.title"></span>
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

    <!-- Create Lesson Modal -->
    <?php Modal::begin(['id' => 'create-lesson-modal', 'title' => 'Новое занятие', 'size' => 'xl']); ?>
    <?= $this->render('_modal-form', ['isEdit' => false]) ?>
    <?php Modal::end(); ?>

    <!-- View Lesson Modal -->
    <?php Modal::begin(['id' => 'view-lesson-modal', 'title' => 'Детали занятия', 'size' => 'xl']); ?>
    <?= $this->render('_modal-view') ?>
    <?php Modal::end(); ?>

    <!-- Edit Lesson Modal -->
    <?php Modal::begin(['id' => 'edit-lesson-modal', 'title' => 'Редактировать занятие', 'size' => 'xl']); ?>
    <?= $this->render('_modal-form', ['isEdit' => true]) ?>
    <?php Modal::end(); ?>

    <!-- Delete Confirmation Modal -->
    <?php Modal::begin(['id' => 'delete-lesson-modal', 'title' => 'Удаление занятия']); ?>
    <div class="text-center py-4">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-danger-100 flex items-center justify-center">
            <?= Icon::show('trash', 'xl', 'text-danger-600') ?>
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

    <!-- Onboarding Modal -->
    <div x-data="{
        showOnboarding: !localStorage.getItem('schedule_onboarding_completed'),
        currentStep: 1,
        totalSteps: 4,
        completeOnboarding() {
            localStorage.setItem('schedule_onboarding_completed', 'true');
            this.showOnboarding = false;
        },
        nextStep() {
            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
            } else {
                this.completeOnboarding();
            }
        },
        skipOnboarding() {
            this.completeOnboarding();
        }
    }">
        <!-- Onboarding Overlay -->
        <div x-show="showOnboarding"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
             style="z-index: 9999;"
             @click.self="skipOnboarding()">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 style="z-index: 10000;">

                <!-- Progress bar -->
                <div class="h-1.5 bg-gray-100 rounded-t-2xl overflow-hidden">
                    <div class="h-full bg-primary-500 transition-all duration-300"
                         :style="{ width: (currentStep / totalSteps * 100) + '%' }"></div>
                </div>

                <!-- Step 1: Welcome -->
                <div x-show="currentStep === 1" class="text-center" style="padding: 40px 32px 24px 32px;">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-primary-100 flex items-center justify-center">
                        <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Добро пожаловать в Расписание!</h2>
                    <p class="text-gray-500 leading-relaxed">
                        Здесь вы можете планировать занятия, отслеживать загруженность преподавателей и кабинетов.
                        Давайте познакомимся с основными возможностями.
                    </p>
                </div>

                <!-- Step 2: Creating lessons -->
                <div x-show="currentStep === 2" class="text-center" style="padding: 40px 32px 24px 32px;">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-success-100 flex items-center justify-center">
                        <svg class="w-10 h-10 text-success-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Создание занятия</h2>
                    <p class="text-gray-500 leading-relaxed">
                        Кликните на <strong class="text-gray-700">пустое место в календаре</strong> или нажмите кнопку
                        <strong class="text-gray-700">«Добавить занятие»</strong> для создания нового занятия.
                        Вы также можете перетаскивать занятия для изменения времени.
                    </p>
                </div>

                <!-- Step 3: Templates -->
                <div x-show="currentStep === 3" class="text-center" style="padding: 40px 32px 24px 32px;">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-warning-100 flex items-center justify-center">
                        <svg class="w-10 h-10 text-warning-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Шаблоны расписания</h2>
                    <p class="text-gray-500 leading-relaxed">
                        Используйте <strong class="text-gray-700">Шаблоны</strong> для быстрого создания расписания на неделю или месяц.
                        <strong class="text-gray-700">Типовое расписание</strong> позволяет генерировать занятия автоматически.
                    </p>
                </div>

                <!-- Step 4: Complete -->
                <div x-show="currentStep === 4" class="text-center" style="padding: 40px 32px 24px 32px;">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-primary-100 flex items-center justify-center">
                        <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Готово!</h2>
                    <p class="text-gray-500 leading-relaxed">
                        Теперь вы готовы работать с расписанием.
                        При необходимости используйте фильтры для поиска нужных занятий.
                    </p>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between" style="padding: 24px 32px 32px 32px;">
                    <button type="button"
                            @click="skipOnboarding()"
                            class="text-sm text-gray-500 hover:text-gray-700 transition-colors cursor-pointer">
                        Пропустить
                    </button>

                    <!-- Step indicators -->
                    <div class="flex gap-2">
                        <template x-for="step in totalSteps" :key="step">
                            <div class="w-2 h-2 rounded-full transition-colors cursor-pointer"
                                 @click="currentStep = step"
                                 :class="step <= currentStep ? 'bg-primary-500' : 'bg-gray-300'"></div>
                        </template>
                    </div>

                    <button type="button"
                            @click="nextStep()"
                            class="btn btn-primary">
                        <span x-text="currentStep === totalSteps ? 'Начать работу' : 'Далее'"></span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
