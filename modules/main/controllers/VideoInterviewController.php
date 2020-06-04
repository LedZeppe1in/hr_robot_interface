<?php

namespace app\modules\main\controllers;

use Yii;
use Exception;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\components\OSConnector;
use app\modules\main\models\VideoInterview;
use app\modules\main\models\Landmark;
use app\modules\main\models\AnalysisResult;

/**
 * VideoInterviewController implements the CRUD actions for VideoInterview model.
 */
class VideoInterviewController extends Controller
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
     * Lists all VideoInterview models.
     * @return mixed
     */
    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => VideoInterview::find(),
        ]);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single VideoInterview model.
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
     * Creates a new VideoInterview model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpload()
    {
        // Установка времени выполнения скрипта в 10 мин.
        set_time_limit(60*10);
        // Создание модели видео-интервью
        $model = new VideoInterview(['scenario' => VideoInterview::VIDEO_INTERVIEW_ANALYSIS_SCENARIO]);
        // POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла с формы
            $videoInterviewFile = UploadedFile::getInstance($model, 'videoInterviewFile');
            $model->videoInterviewFile = $videoInterviewFile;
            // Валидация поля файла
            if ($model->validate(['videoInterviewFile'])) {
                // Если пользователь загрузил файл видеоинтервью
                if ($videoInterviewFile && $videoInterviewFile->tempName)
                    $model->video_file_name = $model->videoInterviewFile->baseName . '.' .
                        $model->videoInterviewFile->extension;
                // Сохранение данных о видеоинтервью в БД
                if ($model->save()) {
                    // Создание объекта коннектора с Yandex.Cloud Object Storage
                    $osConnector = new OSConnector();
                    // Сохранение файла видеоинтервью на Object Storage
                    if ($model->video_file_name != '')
                        $osConnector->saveFileToObjectStorage(OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                            $model->id, $model->video_file_name, $videoInterviewFile->tempName);
                    // Вывод сообщения об удачной загрузке
                    Yii::$app->getSession()->setFlash('success', 'Вы успешно загрузили видеоинтервью!');

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('upload', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing VideoInterview model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        // POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла с формы
            $videoInterviewFile = UploadedFile::getInstance($model, 'videoInterviewFile');
            $model->videoInterviewFile = $videoInterviewFile;
            // Валидация поля файла
            if ($model->validate(['videoInterviewFile'])) {
                // Старое название файла видеоинтервью
                $old_video_file_name = $model->video_file_name;
                // Если пользователь загрузил файл видеоинтервью
                if ($videoInterviewFile && $videoInterviewFile->tempName)
                    // Формирование нового названия файла видеоинтервью
                    $model->video_file_name = $model->videoInterviewFile->baseName . '.' .
                        $model->videoInterviewFile->extension;
                // Сохранение данных о видеоинтервью в БД
                if ($model->save()) {
                    // Если пользователь загрузил файл видеоинтервью
                    if ($videoInterviewFile && $videoInterviewFile->tempName) {
                        // Создание объекта коннектора с Yandex.Cloud Object Storage
                        $osConnector = new OSConnector();
                        // Удаление старого файла видеоинтервью на Object Storage
                        $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                            $model->id, $old_video_file_name);
                        // Сохранение нового файла видеоинтервью на Object Storage
                        $osConnector->saveFileToObjectStorage(OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                            $model->id, $model->video_file_name, $videoInterviewFile->tempName);
                    }
                    // Вывод сообщения об удачной загрузке
                    Yii::$app->getSession()->setFlash('success', 'Вы успешно обновили видеоинтервью!');

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing VideoInterview model.
     * If deletion is successful, the browser will be redirected to the 'list' page.
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        // Поиск видеоинтервью по id
        $model = $this->findModel($id);
        // Поиск цифровых масок для данного видеоинтервью
        $landmarks = Landmark::find()->where(['video_interview_id' => $model->id])->all();
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Обход всех найденных цифровых масок
        foreach ($landmarks as $landmark) {
            // Поиск результатов анализа, проведенных для данной цифровой маски
            $analysisResults = AnalysisResult::find()->where(['landmark_id' => $landmark->id])->all();
            // Обход всех найденных результатов анализа
            foreach ($analysisResults as $analysisResult) {
                // Удаление файлов с результатами определения признаков и фактами на Object Storage
                if ($analysisResult->detection_result_file_name != '')
                    $osConnector->removeFileFromObjectStorage(
                        OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                        $analysisResult->id,
                        $analysisResult->detection_result_file_name
                    );
                // Удаление файлов с набором фактов на Object Storage
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
        }
        // Удаление файла видеоинтервью на Object Storage
        if ($model->video_file_name != '')
            $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                $model->id, $model->video_file_name);
        // Удалние записи из БД
        $model->delete();
        // Вывод сообщения об успешном удалении
        Yii::$app->getSession()->setFlash('success', 'Вы успешно удалили видеоинтервью!');

        return $this->redirect(['list']);
    }

    /**
     * Скачивание файла видеоинтервью.
     *
     * @param $id
     * @return \yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionVideoDownload($id)
    {
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Скачивание файла видеоинтервью с Object Storage
        if ($model->video_file_name != '') {
            $result = $osConnector->downloadFileFromObjectStorage(OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                $model->id, $model->video_file_name);
            return $result;
        }
        throw new Exception('Файл не найден!');
    }

    /**
     * Finds the VideoInterview model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return VideoInterview the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = VideoInterview::findOne($id)) !== null)
            return $model;

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}