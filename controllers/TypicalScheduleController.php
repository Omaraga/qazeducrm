<?php

namespace app\controllers;

use app\helpers\OrganizationUrl;
use app\models\Group;
use app\models\Organizations;
use app\models\relations\TeacherGroup;
use app\models\TypicalSchedule;
use app\models\User;
use app\models\Users;
use common\models\relations\UserSection;
use common\models\Section;
use common\models\SectionGroup;
use yii\base\BaseObject;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TypicalScheduleController implements the CRUD actions for TypicalSchedule model.
 */
class TypicalScheduleController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all TypicalSchedule models.
     *
     * @return string
     */
    public function actionIndex()
    {

        return $this->render('index', [
        ]);
    }

    public function actionEvents(){
        $result = [];
        if (\Yii::$app->request->isAjax){

            $query = new Query();
            $query->select([
                'typical_schedule.id',
                'typical_schedule.start_time',
                'typical_schedule.end_time',
                'typical_schedule.date',
                'group.code as code',
                'group.color as color',
                'group.name as name',
                'user.fio as fio',
            ])->from(TypicalSchedule::tableName())->innerJoin(Group::tableName(),
                'typical_schedule.group_id = group.id AND group.is_deleted != 1')
                ->innerJoin(User::tableName(), 'typical_schedule.teacher_id = user.id')
                ->andWhere(['typical_schedule.organization_id' => Organizations::getCurrentOrganizationId()])
                ->andWhere('typical_schedule.is_deleted != 1')->orderBy('typical_schedule.date ASC typical_schedule.start_time ASC');
            $events = $query->all();
            foreach ($events as $i => $event){
                $result[$i]['start'] = strtotime($event['date'].' '.$event['start_time']);
                $result[$i]['end'] = strtotime($event['date'].' '.$event['end_time']);
                $result[$i]['title'] = $event['code'] .'-'. $event['name'];
                $result[$i]['color'] = $event['color'];
                $result[$i]['content'] = $event['fio'];
                $result[$i]['url'] = OrganizationUrl::to(['typical-schedule/update', 'id' => $event['id']]);
            }

        }
        return json_encode($result, true);

    }

    public function actionTeachers(){
        $result = [];
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost && $groupId = \Yii::$app->request->post('id')){
            $group = Group::findOne($groupId);
            $teacherGroups = TeacherGroup::find()->where(['target_id' => $group->id])->byOrganization()->notDeleted()->all();
            foreach ($teacherGroups as $i => $teacherGroup){
                $result[$i]['id'] = $teacherGroup->related_id;
                $result[$i]['fio'] = $teacherGroup->teacher->fio;
            }

        }
        return json_encode($result, true);
    }

    /**
     * Displays a single TypicalSchedule model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new TypicalSchedule model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new TypicalSchedule();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(OrganizationUrl::to(['typical-schedule/index']));
            }
        } else {
            $model->loadDefaultValues();
        }
        if (\Yii::$app->request->isAjax){
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TypicalSchedule model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(OrganizationUrl::to(['typical-schedule/index']));
        }
        if (\Yii::$app->request->isAjax){
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing TypicalSchedule model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TypicalSchedule model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return TypicalSchedule the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TypicalSchedule::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(\Yii::t('main', 'The requested page does not exist.'));
    }
}
