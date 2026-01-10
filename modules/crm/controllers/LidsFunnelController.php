<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\forms\LidConversionForm;
use app\models\Group;
use app\models\Lids;
use app\models\Organizations;
use app\models\PayMethod;
use app\models\SalesScript;
use app\models\services\LidService;
use app\models\Tariff;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * LidsFunnelController - воронка продаж и аналитика
 *
 * Отвечает за:
 * - Kanban-доску лидов
 * - Аналитику воронки
 * - Смену статусов (drag & drop)
 * - Скрипты продаж
 */
class LidsFunnelController extends CrmBaseController
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
                        'change-status' => ['POST'],
                        'convert-ajax' => ['POST'],
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
     * Kanban-доска лидов
     *
     * @return string
     */
    public function actionKanban()
    {
        // Собираем фильтры из GET-параметров
        $filters = [
            'search' => $this->request->get('search', ''),
            'source' => $this->request->get('source', ''),
            'manager_id' => $this->request->get('manager_id', ''),
            'class_id' => $this->request->get('class_id', ''),
            'overdue_only' => $this->request->get('overdue_only', ''),
            'date_from' => $this->request->get('date_from', ''),
            'date_to' => $this->request->get('date_to', ''),
            // Новые фильтры
            'my_leads_only' => $this->request->get('my_leads_only', ''),
            'contact_today' => $this->request->get('contact_today', ''),
            'stale_only' => $this->request->get('stale_only', ''),
            'show_not_target' => $this->request->get('show_not_target', ''),
            'tags' => $this->request->get('tags', []),
        ];

        // Убираем пустые значения (массивы проверяем отдельно)
        $filters = array_filter($filters, function($v) {
            if (is_array($v)) return !empty($v);
            return $v !== '' && $v !== null;
        });

        $columns = LidService::getKanbanData($filters);
        $funnelStats = Lids::getFunnelStats();

        return $this->render('@app/modules/crm/views/lids/kanban', [
            'columns' => $columns,
            'funnelStats' => $funnelStats,
            'filters' => $filters,
            'managers' => LidService::getManagersForDropdown(),
        ]);
    }

    /**
     * Страница аналитики
     *
     * @return string
     */
    public function actionAnalytics()
    {
        $dateFrom = $this->request->get('date_from', date('Y-m-01'));
        $dateTo = $this->request->get('date_to', date('Y-m-d'));
        $managerId = $this->request->get('manager_id');

        return $this->render('@app/modules/crm/views/lids/analytics', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'managerId' => $managerId,
            'funnelAnalytics' => LidService::getFunnelAnalytics($dateFrom, $dateTo, $managerId),
            'managerStats' => LidService::getManagerStats($dateFrom, $dateTo),
            'lostReasons' => LidService::getTopLostReasons(),
            'sourceStats' => LidService::getSourceStats(),
            'managers' => LidService::getManagersForDropdown(),
        ]);
    }

    /**
     * AJAX: Смена статуса (для Kanban drag & drop)
     * Теперь возвращает needs_conversion если нужно показать модалку конверсии
     *
     * @return array
     */
    public function actionChangeStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = $this->request->post('id');
        $newStatus = (int)$this->request->post('status');
        $comment = $this->request->post('comment');

        try {
            $lid = $this->findLid($id);

            $result = LidService::changeStatus($lid, $newStatus, $comment);

            if ($result['success']) {
                $lid->refresh();

                $response = [
                    'success' => true,
                    'message' => $result['message'],
                    'status' => $newStatus,
                    'status_label' => $lid->getStatusLabel(),
                    'needs_conversion' => $result['needs_conversion'] ?? false,
                ];

                // Если лид уже конвертирован
                if ($lid->pupil_id) {
                    $response['pupil_id'] = $lid->pupil_id;
                    $response['needs_conversion'] = false;
                }

                // Если нужна конверсия - возвращаем данные для модалки
                if ($response['needs_conversion']) {
                    $response['lid'] = $this->getLidDataForConversion($lid);
                }

                return $response;
            }

            return ['success' => false, 'message' => $result['message'] ?? 'Невозможно изменить статус'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * AJAX: Конвертация лида в ученика
     *
     * @return array
     */
    public function actionConvertAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = $this->request->post('lid_id');

        try {
            $lid = $this->findLid($id);

            if (!$lid->canConvertToPupil()) {
                return [
                    'success' => false,
                    'message' => $lid->isConverted()
                        ? 'Лид уже конвертирован в ученика'
                        : 'Невозможно конвертировать лида'
                ];
            }

            $form = new LidConversionForm();
            $form->loadFromLid($lid);

            // Загружаем данные из POST (исключаем lid_id, он уже установлен в loadFromLid)
            $postData = $this->request->post();
            unset($postData['lid_id']);

            if ($form->load($postData, '')) {
                $pupil = $form->convert();

                if ($pupil) {
                    return [
                        'success' => true,
                        'message' => 'Ученик успешно создан',
                        'pupil_id' => $pupil->id,
                        'pupil_fio' => $pupil->fio,
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Ошибка создания ученика',
                    'errors' => $form->errors,
                ];
            }

            return [
                'success' => false,
                'message' => 'Данные не загружены',
            ];

        } catch (\Exception $e) {
            Yii::error('actionConvertAjax error: ' . $e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * AJAX: Получить данные для модалки конверсии
     *
     * @param int $id ID лида
     * @return array
     */
    public function actionGetConversionData($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $lid = $this->findLid($id);

            return [
                'success' => true,
                'lid' => $this->getLidDataForConversion($lid),
                'tariffs' => LidConversionForm::getTariffList(),
                'payMethods' => LidConversionForm::getPayMethodList(),
                'sexList' => LidConversionForm::getSexList(),
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * AJAX: Получить группы по тарифу
     *
     * @return array
     */
    public function actionGetGroupsByTariff()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $tariffId = $this->request->get('tariff_id');

        $groups = LidConversionForm::getGroupListByTariff($tariffId ? (int)$tariffId : null);

        return [
            'success' => true,
            'groups' => $groups,
        ];
    }

    /**
     * Подготовить данные лида для модалки конверсии
     *
     * @param Lids $lid
     * @return array
     */
    private function getLidDataForConversion(Lids $lid): array
    {
        // Разбираем ФИО
        $fioParts = preg_split('/\s+/', trim($lid->fio), 3);

        return [
            'id' => $lid->id,
            'fio' => $lid->fio,
            'last_name' => $fioParts[0] ?? '',
            'first_name' => $fioParts[1] ?? '',
            'middle_name' => $fioParts[2] ?? '',
            'phone' => $lid->phone,
            'parent_fio' => $lid->parent_fio,
            'parent_phone' => $lid->parent_phone,
            'school' => $lid->school,
            'class_id' => $lid->class_id,
            'contact_phone' => $lid->getContactPhone(),
            'total_sum' => $lid->total_sum,
            'sale' => $lid->sale,
            'status' => $lid->status,
            'can_convert' => $lid->canConvertToPupil(),
        ];
    }

    /**
     * Получить скрипт продаж для статуса (AJAX)
     *
     * @return array
     */
    public function actionGetSalesScript()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $status = $this->request->get('status');

        if (!$status) {
            return ['success' => false, 'error' => 'Статус не указан'];
        }

        $scripts = SalesScript::getForStatus($status);

        if (empty($scripts)) {
            // Если скриптов нет, создаём дефолтные
            SalesScript::createDefaults(Organizations::getCurrentOrganizationId());
            $scripts = SalesScript::getForStatus($status);
        }

        $result = [];
        foreach ($scripts as $script) {
            $result[] = $script->toApiArray();
        }

        return [
            'success' => true,
            'scripts' => $result,
            'status' => $status,
        ];
    }

    /**
     * Получить все скрипты продаж (AJAX)
     *
     * @return array
     */
    public function actionGetAllSalesScripts()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $grouped = SalesScript::getAllGroupedByStatus();

        if (empty($grouped)) {
            // Если скриптов нет, создаём дефолтные
            SalesScript::createDefaults(Organizations::getCurrentOrganizationId());
            $grouped = SalesScript::getAllGroupedByStatus();
        }

        $result = [];
        foreach ($grouped as $status => $scripts) {
            $result[$status] = [];
            foreach ($scripts as $script) {
                $result[$status][] = $script->toArray();
            }
        }

        return [
            'success' => true,
            'scripts' => $result,
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
