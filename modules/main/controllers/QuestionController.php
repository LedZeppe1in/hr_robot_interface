<?php

namespace app\modules\main\controllers;

use app\modules\main\models\AnalysisResult;
use Yii;
use Exception;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\components\OSConnector;
use app\modules\main\models\Question;
use app\modules\main\models\Landmark;

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
        // Поиск вопроса видеоинтервью по id
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Поиск цифровых масок для данного вопроса
        $landmarks = Landmark::find()->where(['video_interview_id' => $model->id])->all();
        // Обход всех найденных цифровых масок
        foreach ($landmarks as $landmark) {
            // Поиск результатов анализа, проведенных для данной цифровой маски
            $analysisResults = AnalysisResult::find()->where(['landmark_id' => $landmark->id])->all();
            // Обход всех найденных результатов анализа
            foreach ($analysisResults as $analysisResult) {
                // Удаление файла с результатами определения признаков и фактами на Object Storage
                if ($analysisResult->detection_result_file_name != '')
                    $osConnector->removeFileFromObjectStorage(
                        OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                        $analysisResult->id,
                        $analysisResult->detection_result_file_name
                    );
                // Удаление файла с набором фактов на Object Storage
                if ($analysisResult->facts_file_name != '')
                    $osConnector->removeFileFromObjectStorage(
                        OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                        $analysisResult->id,
                        $analysisResult->facts_file_name
                    );
                // Удаление файла с результатами интерпретации признаков на Object Storage
                if ($analysisResult->interpretation_result_file_name != '')
                    $osConnector->removeFileFromObjectStorage(
                        OSConnector::OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET,
                        $analysisResult->id,
                        $analysisResult->interpretation_result_file_name
                    );
            }
            // Удаление файла с лицевыми точками на Object Storage
            if ($landmark->landmark_file_name != '')
                $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                    $landmark->id, $landmark->landmark_file_name);
            // Удаление файла видео с нанесенной цифровой маской на Object Storage
            if ($landmark->processed_video_file_name != '')
                $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                    $landmark->id, $landmark->processed_video_file_name);
        }
        // Удаление файла видео с ответом на вопрос на Object Storage
        if ($model->video_file_name != '')
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