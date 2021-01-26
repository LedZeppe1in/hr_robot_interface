<?php

namespace app\modules\main\controllers;


use Yii;
use Exception;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use app\components\OSConnector;
use app\components\AnalysisHelper;
use app\modules\main\models\Landmark;
use app\modules\main\models\Question;
use app\modules\main\models\FeaturesDetectionModuleSettingForm;

/**
 * LandmarkController implements the CRUD actions for Landmark model.
 */
class LandmarkController extends Controller
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
                'only' => ['list', 'upload', 'view', 'update', 'delete', 'landmark-file-download',
                    'processed-video-file-download'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['list', 'upload', 'view', 'update', 'delete', 'landmark-file-download',
                            'processed-video-file-download'],
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
     * Lists all Landmark models.
     * @return mixed
     */
    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Landmark::find(),
        ]);
        // Создание формы настройки параметров запуска модуля определения признаков (МОП)
        $featuresDetectionModuleSettingForm = new FeaturesDetectionModuleSettingForm();

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'featuresDetectionModuleSettingForm' => $featuresDetectionModuleSettingForm
        ]);
    }

    /**
     * Displays a single Landmark model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // Создание формы настройки параметров запуска модуля определения признаков (МОП)
        $featuresDetectionModuleSettingForm = new FeaturesDetectionModuleSettingForm();

        return $this->render('view', [
            'model' => $this->findModel($id),
            'featuresDetectionModuleSettingForm' => $featuresDetectionModuleSettingForm
        ]);
    }

    /**
     * Creates a new Landmark model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpload()
    {
        // Создание модели цифровой маски со сценарием загрузки новой цифровой маски
        $model = new Landmark(['scenario' => Landmark::UPLOAD_LANDMARK_SCENARIO]);
        // POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла с формы
            $landmarkFile = UploadedFile::getInstance($model, 'landmarkFile');
            $model->landmarkFile = $landmarkFile;
            // Валидация поля файла
            if ($model->validate(['landmarkFile'])) {
                // Если пользователь загрузил файл с лицевыми точками
                if ($landmarkFile && $landmarkFile->tempName)
                    $model->landmark_file_name = $model->landmarkFile->baseName . '.' . $model->landmarkFile->extension;
                // Сохранение данных о цифровой маски в БД
                if ($model->save()) {
                    // Создание объекта коннектора с Yandex.Cloud Object Storage
                    $osConnector = new OSConnector();
                    // Сохранение файла с цифровой маской на Object Storage
                    if ($model->landmark_file_name != '')
                        $osConnector->saveFileToObjectStorage(
                            OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                            $model->id,
                            $model->landmark_file_name,
                            $landmarkFile->tempName
                        );
                    // Вывод сообщения об удачной загрузке
                    Yii::$app->getSession()->setFlash('success',
                        'Вы успешно загрузили файл с цифровой маской!');

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('upload', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Landmark model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        // Поиск модели цифровой маски по id
        $model = $this->findModel($id);
        // Подстановка времени в правильном формате
        $model->start_time = $model->getStartTime();
        $model->finish_time = $model->getFinishTime();
        // Формирование списка вопросов
        $questions = ArrayHelper::map(Question::find()->all(), 'id', 'text');
        // POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла с формы
            $landmarkFile = UploadedFile::getInstance($model, 'landmarkFile');
            $model->landmarkFile = $landmarkFile;
            // Валидация поля файла
            if ($model->validate(['landmarkFile'])) {
                // Старое название файла с лицевыми точками
                $old_landmark_file_name = $model->landmark_file_name;
                // Если пользователь загрузил файл с лицевыми точками
                if ($landmarkFile && $landmarkFile->tempName)
                    $model->landmark_file_name = $model->landmarkFile->baseName . '.' . $model->landmarkFile->extension;
                // Сохранение данных о цифровой маски в БД
                if ($model->save()) {
                    // Если пользователь загрузил файл с лицевыми точками
                    if ($landmarkFile && $landmarkFile->tempName) {
                        // Создание объекта коннектора с Yandex.Cloud Object Storage
                        $osConnector = new OSConnector();
                        // Удаление старого файла с цифровой маской на Object Storage
                        $osConnector->removeFileFromObjectStorage(
                            OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                            $model->id,
                            $old_landmark_file_name
                        );
                        // Сохранение нового файла с цифровой маской на Object Storage
                        $osConnector->saveFileToObjectStorage(OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                            $model->id, $model->landmark_file_name, $landmarkFile->tempName);
                    }
                    // Вывод сообщения об удачной загрузке
                    Yii::$app->getSession()->setFlash('success', 'Вы успешно обновили цифровую маску!');

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
            'questions' => $questions,
        ]);
    }

    /**
     * Deletes an existing Landmark model.
     * If deletion is successful, the browser will be redirected to the 'list' page.
     * @param $id - идентификатор цифровой маски
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        // Поиск цифровой маски по id
        $model = $this->findModel($id);
        // Создание объекта AnalysisHelper
        $analysisHelper = new AnalysisHelper();
        // Удаление всех результатов анализа для данной цифровой маски на Object Storage
        $analysisHelper->deleteAnalysisResultsInObjectStorage($model->id);
        // Удаление цифровой маски на Object Storage
        $analysisHelper->deleteLandmarkInObjectStorage($model);
        // Удалние записи из БД
        $model->delete();
        // Вывод сообщения об успешном удалении
        Yii::$app->getSession()->setFlash('success', 'Вы успешно удалили файл с цифровой маской!');

        return $this->redirect(['list']);
    }

    /**
     * Скачивание файла с лицевыми точками.
     *
     * @param $id - идентификатор цифровой маски
     * @return \yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionLandmarkFileDownload($id)
    {
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Скачивание файла с лицевыми точками с Object Storage
        if ($model->landmark_file_name != '') {
            $result = $osConnector->downloadFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $model->id,
                $model->landmark_file_name
            );
            return $result;
        }
        throw new Exception('Файл не найден!');
    }

    /**
     * Скачивание файла видео с нанесенными лицевыми точками.
     *
     * @param $id - идентификатор цифровой маски
     * @return \yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionProcessedVideoFileDownload($id)
    {
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Скачивание файла видео с нанесенными лицевыми точками с Object Storage
        if ($model->processed_video_file_name != '') {
            $result = $osConnector->downloadFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $model->id,
                $model->processed_video_file_name
            );
            return $result;
        }
        throw new Exception('Файл не найден!');
    }

    /**
     * Finds the Landmark model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Landmark the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Landmark::findOne($id)) !== null)
            return $model;

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}