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
        // Установка времени выполнения скрипта в 10 мин.
        set_time_limit(60*10);
        // Создание модели видеоинтервью со сценарием анализа
        $videoInterviewModel = new VideoInterview(['scenario' => VideoInterview::VIDEO_INTERVIEW_ANALYSIS_SCENARIO]);
        // POST-запрос
        if ($videoInterviewModel->load(Yii::$app->request->post())) {
            // Загрузка файла видеоинтервью с формы
            $videoInterviewFile = UploadedFile::getInstance($videoInterviewModel, 'videoInterviewFile');
            $videoInterviewModel->videoInterviewFile = $videoInterviewFile;
            // Валидация поля файла видеоинтервью
            if ($videoInterviewModel->validate(['videoInterviewFile'])) {
                // Если пользователь загрузил файл видеоинтервью
                if ($videoInterviewFile && $videoInterviewFile->tempName)
                    $videoInterviewModel->video_file_name = $videoInterviewModel->videoInterviewFile->baseName . '.' .
                        $videoInterviewModel->videoInterviewFile->extension;
                // Сохранение данных о видеоинтервью в БД
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
                    // Путь к программе обработки видео
                    $mainPath = '/home/-Common/';
                    // Путь к файлу видеоинтервью
                    $videoPath = $mainPath . 'video/';
                    // Путь к json-файлу результатов обработки видеоинтервью
                    $jsonResultPath = $mainPath . 'json/';
                    // Сохранение файла видеоинтервью на сервере
                    $videoInterviewModel->videoInterviewFile->saveAs($videoPath .
                        $videoInterviewModel->video_file_name);
                    // Формирование названия json-файла с результатами обработки видео
                    $jsonResultFileName = 'new_' . $videoInterviewModel->videoInterviewFile->baseName . '.json';
                    // Формирование названия видео-файла с результатами обработки видео
                    $videoResultFileName = 'new_' . $videoInterviewModel->videoInterviewFile->baseName . '.' .
                        $videoInterviewModel->videoInterviewFile->extension;
                    // Формирование массива с параметрами запуска программы обработки видео
                    $parameters['nameVidFilesIn'] = ['video/' . $videoInterviewModel->video_file_name];
                    $parameters['nameVidFilesOut'] = ['json/new_' . $videoInterviewModel->video_file_name];
                    $parameters['indexesTriagnleStats'] = [[31, 48, 51], [35, 51, 54], [31, 48, 74], [35, 54, 75],
                        [48, 74, 76], [54, 75, 77], [48, 59, 76], [54, 55, 77], [7, 57, 59], [9, 55, 57], [7, 9, 57],
                        [31, 40, 74], [35, 47, 75], [40, 41, 74], [46, 47, 75]];
                    $parameters['nameJsonFilesOut'] = ['json/' . $jsonResultFileName];
                    // Формирование json-строки на основе массива с параметрами запуска программы обработки видео
                    $jsonParameters = json_encode($parameters, JSON_UNESCAPED_UNICODE);
                    // Открытие файла на запись для сохранения параметров запуска программы обработки видео
                    $jsonFile = fopen($mainPath . 'test.json', 'a');
                    // Запись в файл json-строки с параметрами запуска программы обработки видео
                    fwrite($jsonFile, str_replace("\\", "", $jsonParameters));
                    // Закрытие файла
                    fclose($jsonFile);
                    // Статус обработки видеоинтервью
                    $success = false;
                    // Запуск программы обработки видео
                    chdir('/home/-Common/');
                    exec('./venv/bin/python ./main.py ./test.json');
                    // Отлов ошибки выполнения программы обработки видео
                    try {
                        // Получение json-файла с результатами обработки видео в виде цифровой маски
                        $landmarkFile = file_get_contents($jsonResultPath .
                            $jsonResultFileName, true);
                        // Сохранение данных о цифровой маски в БД
                        $landmarkModel = new Landmark();
                        $landmarkModel->landmark_file_name = $videoInterviewModel->videoInterviewFile->baseName .
                            '.json';
                        $landmarkModel->video_interview_id = $videoInterviewModel->id;
                        $landmarkModel->save();
                        // Сохранение файла с лицевыми точками на Object Storage
                        $dbConnector->saveFileToObjectStorage(
                            OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                            $landmarkModel->id,
                            $landmarkModel->landmark_file_name,
                            $landmarkFile
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
                        // Изменение статуса обработки видеоинтервью
                        $success = true;
                    } catch (Exception $e) {
                        // Вывод сообщения об ошибке обработки видеоинтервью
                        Yii::$app->getSession()->setFlash('error',
                            'При обработке видеоинтервью возникли ошибки!');
                    }
                    // Удаление файла с видеоинтервью
                    if (file_exists($videoPath . $videoInterviewModel->video_file_name))
                        unlink($videoPath . $videoInterviewModel->video_file_name);
                    // Удаление файла с параметрами запуска программы обработки видео
                    if (file_exists($mainPath . 'test.json'))
                        unlink($mainPath . 'test.json');
                    // Удаление файлов с результатами обработки видеоинтервью
                    if (file_exists($jsonResultPath . $jsonResultFileName) &&
                        file_exists($jsonResultPath . $videoResultFileName)) {
                        unlink($jsonResultPath . $jsonResultFileName);
                        unlink($jsonResultPath . $videoResultFileName);
                    }
                    // Если видеоинтервью обработалось корректно
                    if ($success) {
                        // Вывод сообщения об успешном анализе видеоинтервью
                        Yii::$app->getSession()->setFlash('success',
                            'Вы успешно проанализировали видеоинтервью!');

                        return $this->redirect(['/analysis-result/view/' . $analysisResultModel->id]);
                    }
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