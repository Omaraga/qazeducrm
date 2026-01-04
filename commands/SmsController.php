<?php

namespace app\commands;

use app\models\Lesson;
use app\models\LessonAttendance;
use app\models\Organizations;
use app\models\Payment;
use app\models\Pupil;
use app\models\PupilEducation;
use app\models\SmsLog;
use app\models\SmsTemplate;
use app\models\User;
use app\services\SmsService;
use yii\console\Controller;
use yii\console\ExitCode;
use Yii;

/**
 * Консольные команды для SMS уведомлений
 *
 * Использование:
 *   php yii sms/lesson-reminder       - напоминания о занятиях на завтра
 *   php yii sms/payment-due           - уведомления о задолженности
 *   php yii sms/birthday              - поздравления с днём рождения
 *   php yii sms/send <phone> <message> - отправить SMS вручную
 */
class SmsController extends Controller
{
    /**
     * @var SmsService
     */
    private $smsService;

    public function init()
    {
        parent::init();
        $this->smsService = new SmsService();
    }

    /**
     * Напоминания о занятиях на завтра
     * Рекомендуется запускать ежедневно в 18:00
     *
     * php yii sms/lesson-reminder
     */
    public function actionLessonReminder()
    {
        $this->stdout("Отправка напоминаний о занятиях...\n");

        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        // Получаем все организации с настроенным SMS
        $organizations = Organizations::find()
            ->andWhere(['status' => Organizations::STATUS_ACTIVE])
            ->andWhere(['not', ['sms_provider' => null]])
            ->andWhere(['not', ['sms_api_key' => null]])
            ->all();

        $totalSent = 0;

        foreach ($organizations as $org) {
            // Устанавливаем контекст организации
            Yii::$app->params['currentOrganizationId'] = $org->id;

            // Получаем занятия на завтра
            $lessons = Lesson::find()
                ->andWhere(['organization_id' => $org->id])
                ->andWhere(['date' => $tomorrow])
                ->andWhere(['status' => Lesson::STATUS_PLANED])
                ->andWhere(['!=', 'is_deleted', 1])
                ->all();

            foreach ($lessons as $lesson) {
                // Получаем учеников группы
                $pupils = $lesson->getPupils();

                foreach ($pupils as $pupil) {
                    $phone = $pupil->phone ?: $pupil->home_phone;
                    if (!$phone) continue;

                    $log = $this->smsService->sendByTemplate(
                        SmsTemplate::CODE_LESSON_REMINDER,
                        $phone,
                        [
                            'name' => $pupil->fio,
                            'pupil_name' => $pupil->fio,
                            'date' => date('d.m.Y', strtotime($lesson->date)),
                            'time' => date('H:i', strtotime($lesson->start_time)),
                            'group' => $lesson->group ? $lesson->group->name : '',
                            'teacher' => $lesson->teacher ? $lesson->teacher->fio : '',
                        ],
                        SmsLog::RECIPIENT_PUPIL,
                        $pupil->id
                    );

                    if ($log && $log->status === SmsLog::STATUS_SENT) {
                        $totalSent++;
                    }
                }
            }
        }

        $this->stdout("Отправлено SMS: {$totalSent}\n");
        return ExitCode::OK;
    }

    /**
     * Уведомления о задолженности
     * Рекомендуется запускать раз в неделю
     *
     * php yii sms/payment-due
     */
    public function actionPaymentDue()
    {
        $this->stdout("Отправка уведомлений о задолженности...\n");

        // Получаем все организации с настроенным SMS
        $organizations = Organizations::find()
            ->andWhere(['status' => Organizations::STATUS_ACTIVE])
            ->andWhere(['not', ['sms_provider' => null]])
            ->andWhere(['not', ['sms_api_key' => null]])
            ->all();

        $totalSent = 0;

        foreach ($organizations as $org) {
            Yii::$app->params['currentOrganizationId'] = $org->id;

            // Находим учеников с отрицательным балансом
            $pupils = Pupil::find()
                ->andWhere(['organization_id' => $org->id])
                ->andWhere(['<', 'balance', 0])
                ->andWhere(['!=', 'is_deleted', 1])
                ->all();

            foreach ($pupils as $pupil) {
                $phone = $pupil->phone ?: $pupil->home_phone;
                if (!$phone) continue;

                $debt = abs($pupil->balance);

                $log = $this->smsService->sendByTemplate(
                    SmsTemplate::CODE_PAYMENT_DUE,
                    $phone,
                    [
                        'name' => $pupil->fio,
                        'pupil_name' => $pupil->fio,
                        'amount' => number_format($debt, 0, ',', ' '),
                        'balance' => number_format($pupil->balance, 0, ',', ' '),
                    ],
                    SmsLog::RECIPIENT_PUPIL,
                    $pupil->id
                );

                if ($log && $log->status === SmsLog::STATUS_SENT) {
                    $totalSent++;
                }
            }
        }

        $this->stdout("Отправлено SMS: {$totalSent}\n");
        return ExitCode::OK;
    }

    /**
     * Поздравления с днём рождения
     * Рекомендуется запускать ежедневно в 9:00
     *
     * php yii sms/birthday
     */
    public function actionBirthday()
    {
        $this->stdout("Отправка поздравлений с днём рождения...\n");

        $today = date('m-d');

        // Получаем все организации с настроенным SMS
        $organizations = Organizations::find()
            ->andWhere(['status' => Organizations::STATUS_ACTIVE])
            ->andWhere(['not', ['sms_provider' => null]])
            ->andWhere(['not', ['sms_api_key' => null]])
            ->all();

        $totalSent = 0;

        foreach ($organizations as $org) {
            Yii::$app->params['currentOrganizationId'] = $org->id;

            // Находим учеников с днём рождения сегодня
            $pupils = Pupil::find()
                ->andWhere(['organization_id' => $org->id])
                ->andWhere(['like', 'birth_date', '-' . $today])
                ->andWhere(['!=', 'is_deleted', 1])
                ->all();

            foreach ($pupils as $pupil) {
                $phone = $pupil->phone ?: $pupil->home_phone;
                if (!$phone) continue;

                $log = $this->smsService->sendByTemplate(
                    SmsTemplate::CODE_BIRTHDAY,
                    $phone,
                    [
                        'name' => $pupil->fio,
                        'pupil_name' => $pupil->fio,
                    ],
                    SmsLog::RECIPIENT_PUPIL,
                    $pupil->id
                );

                if ($log && $log->status === SmsLog::STATUS_SENT) {
                    $totalSent++;
                }
            }
        }

        $this->stdout("Отправлено SMS: {$totalSent}\n");
        return ExitCode::OK;
    }

    /**
     * Отправить SMS вручную
     *
     * php yii sms/send "+77001234567" "Тестовое сообщение"
     *
     * @param string $phone Номер телефона
     * @param string $message Текст сообщения
     * @param int $orgId ID организации (по умолчанию 1)
     */
    public function actionSend($phone, $message, $orgId = 1)
    {
        Yii::$app->params['currentOrganizationId'] = $orgId;

        $this->stdout("Отправка SMS на {$phone}...\n");

        $log = $this->smsService->send($phone, $message);

        if ($log->status === SmsLog::STATUS_SENT) {
            $this->stdout("SMS отправлено успешно. ID: {$log->id}\n");
        } else {
            $this->stderr("Ошибка: {$log->error_message}\n");
        }

        return ExitCode::OK;
    }

    /**
     * Статистика SMS
     *
     * php yii sms/stats
     */
    public function actionStats()
    {
        $this->stdout("Статистика SMS за последние 30 дней:\n\n");

        $startDate = date('Y-m-d', strtotime('-30 days'));

        $stats = SmsLog::find()
            ->select([
                'status',
                'COUNT(*) as count'
            ])
            ->andWhere(['>=', 'created_at', $startDate])
            ->groupBy('status')
            ->asArray()
            ->all();

        $statusLabels = SmsLog::getStatusList();

        foreach ($stats as $stat) {
            $label = $statusLabels[$stat['status']] ?? 'Неизвестно';
            $this->stdout("  {$label}: {$stat['count']}\n");
        }

        $total = array_sum(array_column($stats, 'count'));
        $this->stdout("\n  Всего: {$total}\n");

        return ExitCode::OK;
    }

    /**
     * Создать стандартные шаблоны для организации
     *
     * php yii sms/create-templates <orgId>
     *
     * @param int $orgId ID организации
     */
    public function actionCreateTemplates($orgId)
    {
        $org = Organizations::findOne($orgId);

        if (!$org) {
            $this->stderr("Организация не найдена: {$orgId}\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Проверяем, есть ли уже шаблоны
        $existing = SmsTemplate::find()
            ->andWhere(['organization_id' => $orgId])
            ->andWhere(['!=', 'is_deleted', 1])
            ->count();

        if ($existing > 0) {
            $this->stdout("У организации уже есть {$existing} шаблонов.\n");
            return ExitCode::OK;
        }

        SmsTemplate::createDefaults($orgId);

        $this->stdout("Созданы стандартные шаблоны для организации: {$org->name}\n");

        return ExitCode::OK;
    }
}
