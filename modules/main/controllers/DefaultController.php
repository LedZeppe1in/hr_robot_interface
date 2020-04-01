<?php

namespace app\modules\main\controllers;

use SoapClient;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\components\OSConnector;
use app\components\FacialFeatureDetector;
use app\modules\main\models\AnalysisResult;
use app\modules\main\models\VideoInterview;

class DefaultController extends Controller
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
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Страница анализа видео-интервью (полная цепочка анализа от загрузки исходного видеоинтервью до результатов
     * интерпретации признаков).
     *
     * @return mixed
     */
    public function actionAnalysis()
    {
        $model = new VideoInterview();
        // POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файлов с формы
            $videoInterviewFile = UploadedFile::getInstance($model, 'videoInterviewFile');
            $landmarkFile = UploadedFile::getInstance($model, 'landmarkFile');
            $model->videoInterviewFile = $videoInterviewFile;
            $model->landmarkFile = $landmarkFile;
            // Валидация полей файлов
            if ($model->validate(['videoInterviewFile']) && $model->validate(['landmarkFile'])) {
                // Если пользователь загрузил файл видеоинтервью
                if ($videoInterviewFile && $videoInterviewFile->tempName)
                    $model->video_file_name = $model->videoInterviewFile->baseName . '.' .
                        $model->videoInterviewFile->extension;
                // Если пользователь загрузил файл с лицевыми точками
                if ($landmarkFile && $landmarkFile->tempName)
                    $model->landmark_file_name = $model->landmarkFile->baseName . '.' . $model->landmarkFile->extension;
                // Сохранение данных о видео-интервью в БД
                if ($model->save()) {
                    // Создание объекта коннектора с Yandex.Cloud Object Storage
                    $dbConnector = new OSConnector();
                    // Сохранение файла видеоинтервью на Object Storage
                    if ($model->video_file_name != '')
                        $dbConnector->saveFileToObjectStorage(OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                            $model->id, $model->video_file_name, $videoInterviewFile->tempName);
                    // Сохранение файла с лицевыми точками на Object Storage
                    if ($model->landmark_file_name != '') {
                        $dbConnector->saveFileToObjectStorage(OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                            $model->id, $model->landmark_file_name, $landmarkFile->tempName);
                        // Создание модели для результатов определения признаков
                        $analysisResultModel = new AnalysisResult();
                        $analysisResultModel->video_interview_id = $model->id;
                        $analysisResultModel->detection_result_file_name = 'feature-detection-result.json';
                        $analysisResultModel->interpretation_result_file_name = 'feature-interpretation-result.json';
                        $analysisResultModel->save();
                        // Получение содержимого json-файла с лицевыми точками из Object Storage
                        $faceData = $dbConnector->getFileContentToObjectStorage(
                            OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                            $model->id,
                            $model->landmark_file_name
                        );
                        // Создание объекта обнаружения лицевых признаков
                        $facialFeatureDetector = new FacialFeatureDetector();
                        // Выявление признаков для лица
                        $facialFeatures = $facialFeatureDetector->detectFeatures($faceData);
                        // Сохранение json-файла с результатами определения признаков на Object Storage
                        $dbConnector->saveFileToObjectStorage(
                            OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                            $analysisResultModel->id,
                            $analysisResultModel->detection_result_file_name,
                            $facialFeatures
                        );
                        //  Преобразование массива с результатами функции определения признаков в массив шаблонов фактов
                        $factTemplates = $facialFeatureDetector->convertFeaturesToTemplates($facialFeatures);
                        // Сохранение json-файла с результатами конвертации определенных признаков в шаблоны фактов на Object Storage
                        $dbConnector->saveFileToObjectStorage(
                            OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                            $analysisResultModel->id,
                            'fact-templates.json',
                            $factTemplates
                        );
                        // Вывод сообщения об успешном анализе видеоинтервью
                        Yii::$app->getSession()->setFlash('success',
                            'Вы успешно проанализировали видеоинтервью!');

                        return $this->redirect(['/analysis-result/view/' . $analysisResultModel->id]);
                    }

                    return $this->redirect(['/video-interview/view/' . $model->id]);
                }
            }
        }

        return $this->render('analysis', [
            'model' => $model,
        ]);
    }
}