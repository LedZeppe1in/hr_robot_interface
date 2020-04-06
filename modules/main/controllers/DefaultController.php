<?php

namespace app\modules\main\controllers;

use Yii;
use Exception;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\components\OSConnector;
use app\components\FacialFeatureDetector;
use app\modules\main\models\Landmark;
use app\modules\main\models\AnalysisResult;
use app\modules\main\models\VideoInterview;
use app\modules\main\models\KnowledgeBaseFileForm;

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
        $videoInterviewModel = new VideoInterview();
        // POST-запрос
        if ($videoInterviewModel->load(Yii::$app->request->post())) {
            // Загрузка файлов с формы
            $videoInterviewFile = UploadedFile::getInstance($videoInterviewModel, 'videoInterviewFile');
            $landmarkFile = UploadedFile::getInstance($videoInterviewModel, 'landmarkFile');
            $videoInterviewModel->videoInterviewFile = $videoInterviewFile;
            $videoInterviewModel->landmarkFile = $landmarkFile;
            // Валидация полей файлов
            if ($videoInterviewModel->validate(['videoInterviewFile']) &&
                $videoInterviewModel->validate(['landmarkFile'])) {
                // Если пользователь загрузил файл видеоинтервью
                if ($videoInterviewFile && $videoInterviewFile->tempName)
                    $videoInterviewModel->video_file_name = $videoInterviewModel->videoInterviewFile->baseName . '.' .
                        $videoInterviewModel->videoInterviewFile->extension;
                // Сохранение данных о видео-интервью в БД
                if ($videoInterviewModel->save()) {
                    // Создание объекта коннектора с Yandex.Cloud Object Storage
                    $dbConnector = new OSConnector();
                    // Сохранение файла видеоинтервью на Object Storage
                    if ($videoInterviewModel->video_file_name != '')
                        $dbConnector->saveFileToObjectStorage(
                            OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                            $videoInterviewModel->id,
                            $videoInterviewModel->video_file_name,
                            $videoInterviewFile->tempName
                        );
                    // Если пользователь загрузил файл с лицевыми точками
                    if ($landmarkFile && $landmarkFile->tempName) {
                        // Сохранение данных о цифровой маски в БД
                        $landmarkModel = new Landmark();
                        $landmarkModel->landmark_file_name = $videoInterviewModel->landmarkFile->baseName . '.' .
                            $videoInterviewModel->landmarkFile->extension;
                        $landmarkModel->video_interview_id = $videoInterviewModel->id;
                        $landmarkModel->save();
                        // Если файл с лицевыми точками задан
                        if ($landmarkModel->landmark_file_name != '') {
                            // Сохранение файла с лицевыми точками на Object Storage
                            $dbConnector->saveFileToObjectStorage(
                                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                                $landmarkModel->id,
                                $landmarkModel->landmark_file_name,
                                $landmarkFile->tempName
                            );
                            // Создание модели для результатов определения признаков
                            $analysisResultModel = new AnalysisResult();
                            $analysisResultModel->landmark_id = $landmarkModel->id;
                            $analysisResultModel->detection_result_file_name = 'feature-detection-result.json';
                            $analysisResultModel->facts_file_name = 'facts.json';
                            $analysisResultModel->interpretation_result_file_name = 'feature-interpretation-result.json';
                            $analysisResultModel->save();
                            // Получение содержимого json-файла с лицевыми точками из Object Storage
                            $faceData = $dbConnector->getFileContentFromObjectStorage(
                                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                                $landmarkModel->id,
                                $landmarkModel->landmark_file_name
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
                            // Преобразование массива с результатами определения признаков в массив фактов
                            $facts = $facialFeatureDetector->convertFeaturesToFacts($facialFeatures);
                            // Сохранение json-файла с результатами конвертации определенных признаков в набор фактов на Object Storage
                            $dbConnector->saveFileToObjectStorage(
                                OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                                $analysisResultModel->id,
                                $analysisResultModel->facts_file_name,
                                $facts
                            );
                            // Вывод сообщения об успешном анализе видеоинтервью
                            Yii::$app->getSession()->setFlash('success',
                                'Вы успешно проанализировали видеоинтервью!');

                            return $this->redirect(['/analysis-result/view/' . $analysisResultModel->id]);
                        }
                    }

                    return $this->redirect(['/video-interview/view/' . $videoInterviewModel->id]);
                }
            }
        }

        return $this->render('analysis', [
            'model' => $videoInterviewModel,
        ]);
    }

    /**
     * Страница просмотра кода базы знаний.
     *
     * @return string
     */
    public function actionKnowledgeBase()
    {
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $dbConnector = new OSConnector();
        // Получение кода базы знаний из Object Storage
        $knowledgeBase = $dbConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_KNOWLEDGE_BASE_BUCKET,
            null,
            'knowledge-base.txt'
        );

        return $this->render('knowledge-base', [
            'knowledgeBase' => $knowledgeBase,
        ]);
    }

    /**
     * Страница загрузки базы знаний.
     *
     * @return string|\yii\web\Response
     */
    public function actionKnowledgeBaseUpload()
    {
        // Создание формы файла базы знаний
        $knowledgeBaseFileForm = new KnowledgeBaseFileForm();
        // Если POST-запрос
        if (Yii::$app->request->isPost) {
            $knowledgeBaseFileForm->knowledgeBaseFile = UploadedFile::getInstance($knowledgeBaseFileForm,
                'knowledgeBaseFile');
            if ($knowledgeBaseFileForm->validate()) {
                // Создание объекта коннектора с Yandex.Cloud Object Storage
                $dbConnector = new OSConnector();
                // Сохранение загруженного файла базы знаний на Object Storage
                $dbConnector->saveFileToObjectStorage(
                    OSConnector::OBJECT_STORAGE_KNOWLEDGE_BASE_BUCKET,
                    null,
                    'knowledge-base.txt',
                    $knowledgeBaseFileForm->knowledgeBaseFile->tempName
                );
                // Вывод сообщения об успешной загрузке файла базы знаний
                Yii::$app->getSession()->setFlash('success', 'Вы успешно загрузили базу знаний!');

                return $this->redirect('knowledge-base');
            }
        }

        return $this->render('knowledge-base-upload', [
            'knowledgeBaseFileForm' => $knowledgeBaseFileForm,
        ]);
    }

    /**
     * Скачать файл с базой знаний.
     *
     * @return mixed
     * @throws Exception
     */
    public function actionKnowledgeBaseDownload()
    {
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $dbConnector = new OSConnector();
        // Скачивание файла базы знаний с Object Storage
        $result = $dbConnector->downloadFileFromObjectStorage(
            OSConnector::OBJECT_STORAGE_KNOWLEDGE_BASE_BUCKET,
            null,
            'knowledge-base.txt'
        );
        if ($result != '')
            return $result;
        throw new Exception('Файл не найден!');
    }
}