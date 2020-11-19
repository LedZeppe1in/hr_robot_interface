<?php

namespace app\modules\main\controllers;

use app\modules\main\models\ModuleMessage;
use yii\data\SqlDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use app\modules\main\models\QuestionProcessingStatus;

/**
 * QuestionProcessingStatusController implements the CRUD actions for QuestionProcessingStatus model.
 */
class QuestionProcessingStatusController extends Controller
{
    public $layout = 'main';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all QuestionProcessingStatus models.
     * @return mixed
     */
    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => QuestionProcessingStatus::find(),
        ]);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single QuestionProcessingStatus model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // Поиск статуса обработки вопроса по id
        $model = $this->findModel($id);
        // Поиск всех сообщений для данного статуса обработки вопроса
        $moduleMessages = ModuleMessage::find()->where(['question_processing_status_id' => $model->id])->all();

        return $this->render('view', [
            'model' => $model,
            'moduleMessages' => $moduleMessages,
        ]);
    }

    /**
     * Deletes an existing QuestionProcessingStatus model.
     * If deletion is successful, the browser will be redirected to the 'list' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['list']);
    }

    /**
     * Finds the QuestionProcessingStatus model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return QuestionProcessingStatus the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = QuestionProcessingStatus::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}