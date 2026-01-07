<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\Lids;
use app\models\LidTag;
use app\models\Organizations;
use app\models\services\LidService;
use app\models\SmsTemplate;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * LidsInteractionController - взаимодействия с лидами
 *
 * Отвечает за:
 * - Добавление взаимодействий (звонки, встречи)
 * - Конверсию в ученика
 * - Работу с тегами
 * - Inline-редактирование полей
 * - WhatsApp интеграцию
 * - Проверку дубликатов
 */
class LidsInteractionController extends CrmBaseController
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'add-interaction' => ['POST'],
                        'toggle-tag' => ['POST'],
                        'update-field' => ['POST'],
                    ],
                ],
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
                            ]
                        ],
                        [
                            'allow' => false,
                            'roles' => ['?']
                        ]
                    ],
                ],
            ]
        );
    }

    /**
     * AJAX: Добавление взаимодействия
     *
     * @return array
     */
    public function actionAddInteraction()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $lidId = $this->request->post('lid_id');
        $type = $this->request->post('type');
        $comment = $this->request->post('comment');
        $nextContactDate = $this->request->post('next_contact_date');
        $callDuration = $this->request->post('call_duration');

        try {
            $lid = $this->findLid($lidId);

            if (LidService::addInteraction($lid, $type, $comment, $nextContactDate, $callDuration)) {
                // Получаем последнюю запись истории для рендера
                $history = $lid->getHistories()->with('user')->one();

                return [
                    'success' => true,
                    'message' => 'Добавлено',
                    'history' => $this->renderPartial('@app/modules/crm/views/lids/_history-item', ['item' => $history]),
                ];
            }

            return ['success' => false, 'message' => 'Ошибка добавления'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Конверсия лида в ученика (ручная)
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionConvertToPupil($id)
    {
        $lid = $this->findLid($id);

        // Если уже конвертирован
        if ($lid->isConverted()) {
            Yii::$app->session->setFlash('info', 'Лид уже был конвертирован в ученика');
            return $this->redirect(['pupil/view', 'id' => $lid->pupil_id]);
        }

        if ($this->request->isPost) {
            $pupil = LidService::convertToPupil($lid);

            if ($pupil) {
                Yii::$app->session->setFlash('success', 'Ученик успешно создан');
                return $this->redirect(['pupil/update', 'id' => $pupil->id]);
            }

            Yii::$app->session->setFlash('error', 'Ошибка создания ученика');
        }

        return $this->render('@app/modules/crm/views/lids/convert-to-pupil', [
            'lid' => $lid,
        ]);
    }

    /**
     * AJAX: Переключение тега
     *
     * @return array
     */
    public function actionToggleTag()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = $this->request->post('id');
        $tagId = (int) $this->request->post('tag_id');

        try {
            $model = $this->findLid($id);

            // Проверяем существование тега
            $tag = LidTag::find()
                ->byOrganization()
                ->notDeleted()
                ->andWhere(['id' => $tagId])
                ->one();

            if (!$tag) {
                return ['success' => false, 'message' => 'Тег не найден'];
            }

            $hadTag = $model->hasTag($tagId);

            if ($model->toggleTag($tagId)) {
                // Обновляем связи для получения актуальных данных
                $model->refresh();

                return [
                    'success' => true,
                    'tags' => $model->getTags(),
                    'hasTag' => !$hadTag,
                    'message' => !$hadTag ? 'Тег добавлен' : 'Тег удалён',
                ];
            }

            return ['success' => false, 'message' => 'Ошибка обновления тега'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * AJAX: Обновление отдельного поля (inline-edit)
     *
     * @return array
     */
    public function actionUpdateField()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = $this->request->post('id');
        $field = $this->request->post('field');
        $value = $this->request->post('value');

        try {
            $model = $this->findLid($id);
            return LidService::updateField($model, $field, $value);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * AJAX: Получить WhatsApp шаблоны
     *
     * @return array
     */
    public function actionGetWhatsappTemplates()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $templates = SmsTemplate::findWhatsAppTemplates();

        $result = [];
        foreach ($templates as $template) {
            $result[] = [
                'id' => $template->id,
                'code' => $template->code,
                'name' => $template->name,
                'content' => $template->content,
            ];
        }

        return [
            'success' => true,
            'templates' => $result,
        ];
    }

    /**
     * AJAX: Получить сформированное WhatsApp сообщение для лида
     *
     * @return array
     */
    public function actionRenderWhatsappMessage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $lidId = $this->request->get('lid_id');
        $templateId = $this->request->get('template_id');

        try {
            $lid = $this->findLid($lidId);
            $template = SmsTemplate::findOne($templateId);

            if (!$template) {
                return ['success' => false, 'message' => 'Шаблон не найден'];
            }

            // Получаем данные для подстановки
            $org = Organizations::findOne(Organizations::getCurrentOrganizationId());
            $manager = Yii::$app->user->identity;

            $data = [
                'name' => $lid->getContactName() ?: 'Клиент',
                'pupil_name' => $lid->fio ?: '',
                'manager' => $manager ? $manager->fio : '',
                'org_name' => $org ? $org->name : '',
                'date' => '{дата}',
                'time' => '{время}',
                'address' => $org ? ($org->address ?? '') : '',
                'subject' => '',
            ];

            $message = $template->render($data);

            // Формируем WhatsApp URL с текстом
            $phone = Lids::cleanPhone($lid->getContactPhone());
            if ($phone) {
                $phone = ltrim($phone, '+');
                if (strpos($phone, '8') === 0) {
                    $phone = '7' . substr($phone, 1);
                }
            }

            $whatsappUrl = 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);

            return [
                'success' => true,
                'message' => $message,
                'whatsapp_url' => $whatsappUrl,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * AJAX: Проверка на дубликаты по телефону
     *
     * @return array
     */
    public function actionCheckDuplicates()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $phone = $this->request->get('phone');
        $excludeId = $this->request->get('exclude_id');

        if (!$phone) {
            return ['success' => true, 'duplicates' => []];
        }

        $duplicates = Lids::findDuplicates($phone, $excludeId);

        $result = [];
        foreach ($duplicates as $lid) {
            $result[] = [
                'id' => $lid->id,
                'fio' => $lid->fio ?: 'Без имени',
                'phone' => $lid->phone,
                'parent_phone' => $lid->parent_phone,
                'status' => $lid->getStatusLabel(),
                'status_color' => $lid->getStatusColor(),
                'created_at' => Yii::$app->formatter->asDate($lid->created_at, 'php:d.m.Y'),
            ];
        }

        return [
            'success' => true,
            'duplicates' => $result,
            'has_duplicates' => count($result) > 0,
        ];
    }

    /**
     * Поиск модели лида по ID
     *
     * @param int $id ID
     * @return Lids
     * @throws NotFoundHttpException
     */
    protected function findLid($id)
    {
        $model = Lids::find()
            ->byOrganization()
            ->andWhere(['id' => $id])
            ->notDeleted()
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException(Yii::t('main', 'Лид не найден.'));
        }

        return $model;
    }
}
