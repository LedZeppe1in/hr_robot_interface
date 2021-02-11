<?php

namespace app\modules\main\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\modules\main\models\VideoInterviewProcessingStatus;
use app\modules\main\models\VideoInterviewProcessingStatusSearch;

/**
 * VideoInterviewProcessingStatusController implements the CRUD actions for VideoInterviewProcessingStatus model.
 */
class VideoInterviewProcessingStatusController extends Controller
{
    public $layout = 'main';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['list', 'view', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['list', 'view', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all VideoInterviewProcessingStatus models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new VideoInterviewProcessingStatusSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single VideoInterviewProcessingStatus model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Deletes an existing VideoInterviewProcessingStatus model.
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
     * Finds the VideoInterviewProcessingStatus model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return VideoInterviewProcessingStatus the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = VideoInterviewProcessingStatus::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}