<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\Group;
use app\models\Lesson;
use app\models\Organizations;
use app\models\Payment;
use app\models\Pupil;
use app\models\search\DateSearch;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Default controller for the CRM module - Dashboard
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [
                            SystemRoles::SUPER,
                            OrganizationRoles::ADMIN,
                            OrganizationRoles::DIRECTOR,
                            OrganizationRoles::GENERAL_DIRECTOR,
                            OrganizationRoles::TEACHER,
                        ]
                    ],
                    [
                        'allow' => false,
                        'roles' => ['?']
                    ]
                ],
            ],
        ];
    }

    /**
     * Dashboard - главная страница CRM
     */
    public function actionIndex()
    {
        $this->view->title = 'Dashboard';

        $search = new DateSearch();

        return $this->render('index', [
            'data' => $search->getWeekPayments(),
            'week' => $search->getWeeks(true),
        ]);
    }

    /**
     * Dashboard в новом дизайне (Tailwind CSS)
     * Для тестирования нового UI
     */
    public function actionDemo()
    {
        $this->layout = '@app/views/layouts/main-tailwind';

        $orgId = Organizations::getCurrentOrganizationId();
        $today = date('Y-m-d');

        // Статистика
        $stats = [
            'pupils' => Pupil::find()
                ->andWhere(['organization_id' => $orgId])
                ->andWhere(['!=', 'is_deleted', 1])
                ->count(),

            'groups' => Group::find()
                ->andWhere(['organization_id' => $orgId])
                ->andWhere(['!=', 'is_deleted', 1])
                ->andWhere(['status' => Group::STATUS_ACTIVE])
                ->count(),

            'revenue' => Payment::find()
                ->andWhere(['organization_id' => $orgId])
                ->andWhere(['!=', 'is_deleted', 1])
                ->andWhere(['>=', 'date', date('Y-m-01')])
                ->sum('summ') ?? 0,

            'lessons_today' => Lesson::find()
                ->andWhere(['organization_id' => $orgId])
                ->andWhere(['date' => $today])
                ->andWhere(['!=', 'is_deleted', 1])
                ->count(),

            'recent_payments' => [],
            'today_lessons' => [],
        ];

        // Последние платежи
        $payments = Payment::find()
            ->alias('p')
            ->joinWith(['pupil', 'payMethod'])
            ->andWhere(['p.organization_id' => $orgId])
            ->andWhere(['!=', 'p.is_deleted', 1])
            ->orderBy(['p.date' => SORT_DESC])
            ->limit(5)
            ->all();

        foreach ($payments as $payment) {
            $stats['recent_payments'][] = [
                'date' => $payment->date,
                'pupil' => $payment->pupil ? $payment->pupil->fio : 'Неизвестно',
                'amount' => $payment->summ,
                'method' => $payment->payMethod ? $payment->payMethod->name : 'Наличные',
            ];
        }

        // Занятия сегодня
        $lessons = Lesson::find()
            ->alias('l')
            ->joinWith(['group', 'teacher'])
            ->andWhere(['l.organization_id' => $orgId])
            ->andWhere(['l.date' => $today])
            ->andWhere(['!=', 'l.is_deleted', 1])
            ->orderBy(['l.start_time' => SORT_ASC])
            ->limit(5)
            ->all();

        foreach ($lessons as $lesson) {
            $stats['today_lessons'][] = [
                'time' => date('H:i', strtotime($lesson->start_time)),
                'group' => $lesson->group ? $lesson->group->name : 'Группа',
                'teacher' => $lesson->teacher ? $lesson->teacher->fio : 'Преподаватель',
                'pupils' => $lesson->group ? $lesson->group->getPupilsCount() : 0,
            ];
        }

        return $this->render('index-tailwind', [
            'stats' => $stats,
        ]);
    }
}
