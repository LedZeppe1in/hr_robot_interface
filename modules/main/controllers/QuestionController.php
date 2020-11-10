<?php

namespace app\modules\main\controllers;

use Yii;
use Exception;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\components\OSConnector;
use app\modules\main\models\Question;

/**
 * QuestionController implements the CRUD actions for Question model.
 */
class QuestionController extends Controller
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
     * Lists all Question models.
     * @return mixed
     */
    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Question::find(),
        ]);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Question model.
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
     * Deletes an existing Question model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        // Поиск модели вопроса видеоинтервью по id
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Удаление файла видео с ответом на вопрос на Object Storage
        $osConnector->removeFileFromObjectStorage(
            OSConnector::OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET,
            $model->id,
            $model->video_file_name
        );
        // Удалние записи из БД
        $model->delete();
        // Вывод сообщения об успешном удалении
        Yii::$app->getSession()->setFlash('success', 'Вы успешно удалили вопрос опроса!');

        return $this->redirect(['list']);
    }

    /**
     * Скачивание файла видео с ответом на вопрос.
     *
     * @param $id
     * @return \yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionVideoFileDownload($id)
    {
        // Поиск модели вопроса видеоинтервью по id
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Скачивание файла видео с ответом на вопрос с Object Storage
        if ($model->video_file_name != '') {
            $result = $osConnector->downloadFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET,
                $model->id,
                $model->video_file_name
            );
            return $result;
        }
        throw new Exception('Файл не найден!');
    }

    /**
     * Finds the Question model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Question the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Question::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}