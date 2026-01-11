<?php

/** @var yii\web\View $this */
/** @var app\models\Pupil[] $pupils */
/** @var app\models\Pupil|null $selectedPupil */
/** @var app\models\Group[] $groups */

use app\modules\cabinet\Module;
use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Расписание');
$orgId = Module::getOrganizationId();
?>

<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <?= Icon::show('calendar', 'md', 'text-indigo-600') ?>
            <?= Yii::t('app', 'Расписание') ?>
        </h1>
        <a href="<?= Url::to(['/cabinet/schedule/week', 'org' => $orgId, 'pupil_id' => $selectedPupil ? $selectedPupil->id : null]) ?>"
           class="btn-ios-ghost">
            <?= Icon::show('bars-3', 'sm') ?>
            <?= Yii::t('app', 'На неделю') ?>
        </a>
    </div>

    <!-- Pupil Selector (Segment Control) -->
    <?php if (count($pupils) > 1): ?>
        <div class="segment-control">
            <a href="<?= Url::to(['/cabinet/schedule/index', 'org' => $orgId]) ?>"
               class="segment-item <?= !$selectedPupil ? 'active' : '' ?>">
                <?= Yii::t('app', 'Все') ?>
            </a>
            <?php foreach ($pupils as $pupil): ?>
                <a href="<?= Url::to(['/cabinet/schedule/index', 'org' => $orgId, 'pupil_id' => $pupil->id]) ?>"
                   class="segment-item <?= $selectedPupil && $selectedPupil->id == $pupil->id ? 'active' : '' ?>">
                    <?= Html::encode($pupil->first_name) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Groups Cards -->
    <?php if (!empty($groups)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <?php foreach ($groups as $group): ?>
                <div class="card-glass-solid p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <?= Icon::show('academic-cap', 'md', 'text-indigo-600') ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 truncate"><?= Html::encode($group->name) ?></h3>
                        <?php if ($group->subject): ?>
                            <p class="text-sm text-gray-500 flex items-center gap-1 mt-0.5">
                                <?= Icon::show('book-open', 'xs', 'text-gray-400') ?>
                                <?= Html::encode($group->subject->name) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?= Icon::show('chevron-right', 'sm', 'text-gray-300') ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-ios">
            <div class="empty-ios-icon">
                <?= Icon::show('calendar', 'xl', 'text-gray-300') ?>
            </div>
            <p class="empty-ios-text"><?= Yii::t('app', 'Ученик пока не записан в группы') ?></p>
        </div>
    <?php endif; ?>

    <!-- Calendar Container -->
    <div class="card-glass-solid overflow-hidden">
        <div class="p-4 sm:p-6">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<?php
// FullCalendar
$this->registerCssFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js', ['position' => \yii\web\View::POS_END]);

$eventsUrl = Url::to(['/cabinet/schedule/events', 'org' => $orgId, 'pupil_id' => $selectedPupil ? $selectedPupil->id : null]);

$css = <<<CSS
/* iOS-style FullCalendar */
.fc {
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Inter', system-ui, sans-serif;
}

/* Header toolbar */
.fc .fc-toolbar {
    flex-wrap: wrap;
    gap: 0.75rem;
}

.fc .fc-toolbar-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
}

/* Button styling */
.fc .fc-button {
    font-weight: 500;
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    border-radius: 10px;
    transition: all 0.15s ease;
}

.fc .fc-button-primary {
    background-color: #4f46e5;
    border-color: #4f46e5;
}

.fc .fc-button-primary:hover {
    background-color: #4338ca;
    border-color: #4338ca;
}

.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active {
    background-color: #3730a3;
    border-color: #3730a3;
}

.fc .fc-button:focus {
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
}

/* Today button */
.fc .fc-today-button {
    background-color: #f3f4f6;
    border-color: #e5e7eb;
    color: #374151;
}

.fc .fc-today-button:hover {
    background-color: #e5e7eb;
    border-color: #d1d5db;
}

.fc .fc-today-button:disabled {
    opacity: 0.5;
}

/* Day grid */
.fc .fc-daygrid-day {
    transition: background-color 0.15s ease;
}

.fc .fc-daygrid-day.fc-day-today {
    background-color: #eef2ff;
}

.fc .fc-daygrid-day:hover {
    background-color: #f9fafb;
}

.fc .fc-daygrid-day-number {
    font-weight: 500;
    color: #374151;
    padding: 8px;
}

.fc .fc-day-today .fc-daygrid-day-number {
    background-color: #4f46e5;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Events */
.fc-event {
    border-radius: 8px;
    padding: 4px 8px;
    font-size: 0.75rem;
    font-weight: 500;
    border: none;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.fc-event-main {
    padding: 2px 0;
}

/* Column headers */
.fc .fc-col-header-cell {
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #6b7280;
    padding: 12px 0;
}

/* Borders */
.fc th, .fc td, .fc .fc-scrollgrid {
    border-color: #f3f4f6;
}

/* Mobile optimization */
@media (max-width: 640px) {
    .fc .fc-toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .fc .fc-toolbar-chunk {
        display: flex;
        justify-content: center;
    }

    .fc .fc-toolbar-title {
        font-size: 1rem;
    }

    .fc-event {
        font-size: 0.7rem;
        padding: 2px 4px;
    }
}
CSS;
$this->registerCss($css);

$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth',
        locale: 'ru',
        firstDay: 1,
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: {
            today: 'Сегодня',
            month: 'Месяц',
            week: 'Неделя',
            list: 'Список'
        },
        events: '{$eventsUrl}',
        eventClick: function(info) {
            var props = info.event.extendedProps;
            var modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50';
            modal.innerHTML = '<div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-xl">' +
                '<h3 class="font-bold text-lg text-gray-900">' + info.event.title + '</h3>' +
                (props.subject ? '<p class="text-gray-600 mt-2"><strong>Предмет:</strong> ' + props.subject + '</p>' : '') +
                (props.teacher ? '<p class="text-gray-600 mt-1"><strong>Преподаватель:</strong> ' + props.teacher + '</p>' : '') +
                (props.room ? '<p class="text-gray-600 mt-1"><strong>Кабинет:</strong> ' + props.room + '</p>' : '') +
                '<button class="btn-ios-primary w-full mt-4">Закрыть</button>' +
            '</div>';
            modal.onclick = function(e) {
                if (e.target === modal || e.target.tagName === 'BUTTON') {
                    modal.remove();
                }
            };
            document.body.appendChild(modal);
        },
        eventDidMount: function(info) {
            var props = info.event.extendedProps;
            var tooltip = props.teacher || '';
            if (props.room) tooltip += (tooltip ? ', ' : '') + props.room;
            if (tooltip) {
                info.el.title = tooltip;
            }
        }
    });
    calendar.render();
});
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
