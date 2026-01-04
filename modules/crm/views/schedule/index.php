<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\OrganizationUrl;

/** @var yii\web\View $this */

$this->title = 'Расписание';
$this->params['breadcrumbs'][] = $this->title;

$eventsUrl = OrganizationUrl::to(['schedule/events']);

$js = <<<JS
// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('tailwind-calendar');
    if (!calendarEl) return;

    // Current date state
    let currentDate = new Date();
    let currentView = 'week';

    // Format date helpers
    const formatDate = (date) => date.toISOString().split('T')[0];
    const formatTime = (date) => date.toTimeString().slice(0, 5);

    // Get week start (Monday)
    const getWeekStart = (date) => {
        const d = new Date(date);
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? -6 : 1);
        return new Date(d.setDate(diff));
    };

    // Get week end (Sunday)
    const getWeekEnd = (date) => {
        const start = getWeekStart(date);
        return new Date(start.getTime() + 6 * 24 * 60 * 60 * 1000);
    };

    // Format week range
    const formatWeekRange = (date) => {
        const start = getWeekStart(date);
        const end = getWeekEnd(date);
        const months = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
        return start.getDate() + ' ' + months[start.getMonth()] + ' - ' + end.getDate() + ' ' + months[end.getMonth()] + ' ' + end.getFullYear();
    };

    // Days of week
    const daysOfWeek = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
    const hoursRange = Array.from({length: 14}, (_, i) => i + 8); // 8:00 - 21:00

    let events = [];

    // Render calendar
    const renderCalendar = () => {
        const weekStart = getWeekStart(currentDate);

        // Update header
        document.getElementById('calendar-title').textContent = formatWeekRange(currentDate);

        // Generate days
        const days = [];
        for (let i = 0; i < 7; i++) {
            const day = new Date(weekStart);
            day.setDate(weekStart.getDate() + i);
            days.push(day);
        }

        // Render grid
        let html = '<div class="grid grid-cols-8 border-b border-gray-200">';
        html += '<div class="p-2 text-center text-xs font-medium text-gray-500 border-r border-gray-200"></div>';
        days.forEach((day, idx) => {
            const isToday = formatDate(day) === formatDate(new Date());
            const todayClass = isToday ? 'bg-primary-50' : '';
            html += '<div class="p-2 text-center border-r border-gray-200 ' + todayClass + '">';
            html += '<div class="text-xs text-gray-500">' + daysOfWeek[idx] + '</div>';
            html += '<div class="text-lg font-semibold ' + (isToday ? 'text-primary-600' : 'text-gray-900') + '">' + day.getDate() + '</div>';
            html += '</div>';
        });
        html += '</div>';

        // Time slots
        html += '<div class="overflow-y-auto" style="max-height: 600px;">';
        hoursRange.forEach(hour => {
            html += '<div class="grid grid-cols-8 border-b border-gray-100" style="min-height: 60px;">';
            html += '<div class="p-2 text-xs text-gray-500 text-right border-r border-gray-200 bg-gray-50">' + hour + ':00</div>';

            days.forEach((day, dayIdx) => {
                const dayStr = formatDate(day);
                const dayEvents = events.filter(e => {
                    const eventDate = new Date(e.start * 1000);
                    return formatDate(eventDate) === dayStr && eventDate.getHours() === hour;
                });

                html += '<div class="p-1 border-r border-gray-100 relative">';
                dayEvents.forEach(event => {
                    const startTime = new Date(event.start * 1000);
                    const duration = (event.end - event.start) / 60; // minutes
                    const height = Math.max(duration, 30);
                    html += '<a href="' + event.url + '" class="block p-1 rounded text-xs text-white mb-1 hover:opacity-90 cursor-pointer" style="background-color: ' + (event.color || '#3b82f6') + '">';
                    html += '<div class="font-medium truncate">' + event.title.replace(/<[^>]*>/g, '') + '</div>';
                    html += '<div class="opacity-75">' + formatTime(startTime) + '</div>';
                    html += '</a>';
                });
                html += '</div>';
            });
            html += '</div>';
        });
        html += '</div>';

        document.getElementById('calendar-grid').innerHTML = html;
    };

    // Load events
    const loadEvents = () => {
        const weekStart = getWeekStart(currentDate);
        const weekEnd = getWeekEnd(currentDate);
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.content : '';

        fetch('$eventsUrl', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrfToken
            },
            body: 'start=' + Math.floor(weekStart.getTime() / 1000) + '&end=' + Math.floor(weekEnd.getTime() / 1000 + 86400)
        })
        .then(response => response.json())
        .then(data => {
            events = data || [];
            renderCalendar();
        })
        .catch(err => {
            console.error('Error loading events:', err);
            renderCalendar();
        });
    };

    // Navigation
    document.getElementById('prev-week').addEventListener('click', () => {
        currentDate.setDate(currentDate.getDate() - 7);
        loadEvents();
    });

    document.getElementById('next-week').addEventListener('click', () => {
        currentDate.setDate(currentDate.getDate() + 7);
        loadEvents();
    });

    document.getElementById('today-btn').addEventListener('click', () => {
        currentDate = new Date();
        loadEvents();
    });

    // Initial load
    loadEvents();
});
JS;
$this->registerJs($js);
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-gray-500 mt-1">Управление занятиями</p>
        </div>
        <div class="flex gap-3">
            <a href="<?= OrganizationUrl::to(['typical-schedule']) ?>" class="btn btn-outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Типовое расписание
            </a>
            <a href="<?= OrganizationUrl::to(['create']) ?>" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Добавить занятие
            </a>
        </div>
    </div>

    <!-- Legend -->
    <div class="card">
        <div class="card-body py-3">
            <div class="flex flex-wrap items-center gap-6 text-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-gray-600">Посещение проставлено</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-gray-600">Ожидает заполнения</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="card" id="tailwind-calendar">
        <!-- Calendar Header -->
        <div class="card-header flex items-center justify-between">
            <div class="flex items-center gap-2">
                <button type="button" id="prev-week" class="btn btn-icon btn-sm btn-outline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button type="button" id="next-week" class="btn btn-icon btn-sm btn-outline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <button type="button" id="today-btn" class="btn btn-sm btn-secondary ml-2">
                    Сегодня
                </button>
            </div>
            <h2 id="calendar-title" class="text-lg font-semibold text-gray-900"></h2>
            <div></div>
        </div>

        <!-- Calendar Grid -->
        <div id="calendar-grid" class="border-t border-gray-200">
            <div class="flex items-center justify-center py-12 text-gray-500">
                <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Загрузка расписания...
            </div>
        </div>
    </div>
</div>
