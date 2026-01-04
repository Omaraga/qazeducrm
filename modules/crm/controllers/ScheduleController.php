<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationUrl;
use app\models\forms\TypicalLessonForm;
use app\models\Group;
use app\models\Lesson;
use app\models\Organizations;
use app\models\relations\TeacherGroup;
use app\models\TypicalSchedule;
use app\models\User;
use yii\base\BaseObject;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TypicalScheduleController implements the CRUD actions for TypicalSchedule model.
 */
class ScheduleController extends Controller
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
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (\Yii::$app->request->isAjax){

            $query = new Query();
            $query->select([
                'lesson.id',
                'lesson.start_time',
                'lesson.end_time',
                'lesson.date',
                'group.code as code',
                'group.color as color',
                'group.name as name',
                'user.fio as fio',
            ])->from(Lesson::tableName())->innerJoin(Group::tableName(),
                'lesson.group_id = group.id AND group.is_deleted != 1')
                ->innerJoin(User::tableName(), 'lesson.teacher_id = user.id')
                ->andWhere(['lesson.organization_id' => Organizations::getCurrentOrganizationId()])
                ->andWhere('lesson.is_deleted != 1')->orderBy('lesson.start_time ASC');
            $events = $query->all();
            foreach ($events as $i => $event){
                $result[$i]['start'] = strtotime($event['date'].' '.$event['start_time']);
                $result[$i]['end'] = strtotime($event['date'].' '.$event['end_time']);
                $result[$i]['title'] = $event['code'] .'-'. $event['name'];
                $result[$i]['color'] = $event['color'];
                $result[$i]['content'] = $event['fio'];
                $result[$i]['url'] = OrganizationUrl::to(['schedule/update', 'id' => $event['id']]);
            }
            usort($result, function ($a, $b)
            {
                $aDif = $a['end'] - $a['start'];
                $bDif = $b['end'] - $b['start'];
                return $aDif > $bDif ? 1 : -1;
            });

        }
        return $result;

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

    public function actionTypicalSchedule(){
        $model = new TypicalLessonForm();
        $model->loadDefault();
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(OrganizationUrl::to(['schedule/index']));
            }
        }
        return $this->render('typical-schedule', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new TypicalSchedule model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Lesson();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(OrganizationUrl::to(['schedule/index']));
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
        $model = Lesson::findOne($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(OrganizationUrl::to(['schedule/index']));
        }else{
            $model->date = $model->date ? date('d.m.Y', strtotime($model->date)) : date('d.m.Y');
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
        $this->findModel($id)->delete();

        return $this->redirect(OrganizationUrl::to(['schedule/index']));
    }

    /**
     * Finds the TypicalSchedule model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Lesson the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Lesson::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(\Yii::t('main', 'The requested page does not exist.'));
    }
}
