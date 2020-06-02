<?php

namespace app\modules\main\controllers;

use Yii;
use Exception;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\components\OSConnector;
use app\components\FacialFeatureDetector;
use app\modules\main\models\Landmark;
use app\modules\main\models\Question;
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
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionAnalysis()
    {
        // Установка времени выполнения скрипта в 10 мин.
        set_time_limit(60*10);
        // Создание модели видеоинтервью со сценарием анализа
        $videoInterviewModel = new VideoInterview(['scenario' => VideoInterview::VIDEO_INTERVIEW_ANALYSIS_SCENARIO]);
        // Создание массива с моделями цифровой маски
        $landmarkModels = [new Landmark()];
        // Формирование списка вопросов
        $questions = ArrayHelper::map(Question::find()->all(), 'id', 'text');
        // Загрузка данных, пришедших методом POST
        if ($videoInterviewModel->loadAll(Yii::$app->request->post())) {
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
                if ($videoInterviewModel->saveAll()) {
                    // Создание объекта коннектора с Yandex.Cloud Object Storage
                    $osConnector = new OSConnector();
                    // Сохранение файла видеоинтервью на Object Storage
                    if ($videoInterviewModel->video_file_name != '')
                        $osConnector->saveFileToObjectStorage(
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
                    // Получение значения поворота
                    $rotation = (int)Yii::$app->request->post('VideoInterview')['rotationParameter'];
                    // Получение значения наличия отзеркаливания
                    $mirroring = Yii::$app->request->post('VideoInterview')['mirroringParameter'];
                    // Массивы для хранения параметров результатов обработки видео
                    $videoResultFiles = array();
                    $jsonResultFiles = array();
                    $questions = array();
                    // Выборка всех цифровых масок у данного видео-интервью
                    $landmarks = Landmark::find()->where(['video_interview_id' => $videoInterviewModel->id])->all();
                    // Обход по всем найденным цифровым маскам
                    foreach ($landmarks as $landmark) {
                        // Формирование названия видео-файла с результатами обработки видео
                        $videoResultFileName = 'out_' . $landmark->id . '.' .
                            $videoInterviewModel->videoInterviewFile->extension;
                        // Добавление в массив названия видео-файла с результатами обработки видео
                        array_push($videoResultFiles, $videoResultFileName);
                        // Формирование названия json-файла с результатами обработки видео
                        $jsonResultFileName = 'out_' . $landmark->id . '.json';
                        // Добавление в массив названия json-файла с результатами обработки видео
                        array_push($jsonResultFiles, $jsonResultFileName);
                        // Формирование информации по вопросу
                        $question['id'] = $landmark->id;
                        $question['start'] = $landmark->start_time;
                        $question['finish'] = $landmark->finish_time;
                        // Добавление в массив вопроса
                        array_push($questions, $question);
                    }
                    // Формирование массива с параметрами запуска программы обработки видео
                    $parameters['nameVidFilesIn'] = 'video/' . $videoInterviewModel->video_file_name;
                    $parameters['nameVidFilesOut'] = 'json/out_{}.avi';
                    $parameters['nameJsonFilesOut'] = 'json/out_{}.json';
                    $parameters['indexesTriagnleStats'] = [[31, 48, 51], [35, 51, 54], [31, 48, 74], [35, 54, 75],
                        [48, 74, 76], [54, 75, 77], [48, 59, 76], [54, 55, 77], [7, 57, 59], [9, 55, 57], [7, 9, 57],
                        [31, 40, 74], [35, 47, 75], [40, 41, 74], [46, 47, 75]];
                    $parameters['rotate_mode'] = $rotation;
                    $parameters['questions'] = $questions;
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
                    chdir($mainPath);
                    exec('./venv/bin/python ./main.py ./test.json');
                    // Отлов ошибки выполнения программы обработки видео
                    try {
                        $index = 0;
                        // Обход по всем найденным цифровым маскам
                        foreach ($landmarks as $landmark) {
                            // Получение значения текста вопроса
                            $questionText = Yii::$app->request->post('Landmark')[$index]['questionText'];
                            // Если поле текста вопроса содержит значение "hidden"
                            if ($questionText != 'hidden') {
                                // Создание и сохранение новой модели вопроса
                                $questionModel = new Question();
                                $questionModel->text = $questionText;
                                $questionModel->save();
                                // Формирование id вопроса
                                $landmark->question_id = $questionModel->id;
                            }
                            // Увеличение индекса на 1
                            $index++;
                            // Формирование названия json-файла с результатами обработки видео
                            $landmark->landmark_file_name = 'out_' . $landmark->id . '.json';
                            // Формирование описания цифровой маски
                            $landmark->description = $videoInterviewModel->description . ' (время нарезки: ' .
                                $landmark->getStartTime() . ' - ' . $landmark->getFinishTime() . ')';
                            // Формирование значения поворота
                            $landmark->rotation = $rotation;
                            // Формирование значения наличия отзеркаливания
                            $landmark->mirroring = boolval($mirroring);
                            // Обновление атрибутов цифровой маски в БД
                            $landmark->updateAttributes(['landmark_file_name', 'description', 'rotation',
                                'mirroring', 'question_id']);
                            // Получение json-файла с результатами обработки видео в виде цифровой маски
                            $landmarkFile = file_get_contents($jsonResultPath .
                                $landmark->landmark_file_name, true);
                            // Сохранение файла с лицевыми точками на Object Storage
                            $osConnector->saveFileToObjectStorage(
                                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                                $landmark->id,
                                $landmark->landmark_file_name,
                                $landmarkFile
                            );
                            // Получение типа обработки получаемых цифровых масок
                            $processingType = Yii::$app->request->post('VideoInterview')['processingType'];
                            // Создание модели для результатов определения признаков
                            $analysisResultModel = new AnalysisResult();
                            $analysisResultModel->landmark_id = $landmark->id;
                            $analysisResultModel->detection_result_file_name = 'feature-detection-result.json';
                            $analysisResultModel->facts_file_name = 'facts.json';
                            //$analysisResultModel->interpretation_result_file_name = 'feature-interpretation-result.json';
                            $analysisResultModel->description = $landmark->description . ($processingType == 0 ?
                                ' (обработка сырых точек)' : ' (обработка нормализованных точек)');
                            $analysisResultModel->save();
                            // Получение содержимого json-файла с лицевыми точками из Object Storage
                            $faceData = $osConnector->getFileContentFromObjectStorage(
                                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                                $landmark->id,
                                $landmark->landmark_file_name
                            );
                            // Создание объекта обнаружения лицевых признаков
                            $facialFeatureDetector = new FacialFeatureDetector();
                            // Выявление признаков для лица
                            $facialFeatures = $facialFeatureDetector->detectFeatures($faceData, $processingType);
                            // Сохранение json-файла с результатами определения признаков на Object Storage
                            $osConnector->saveFileToObjectStorage(
                                OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                                $analysisResultModel->id,
                                $analysisResultModel->detection_result_file_name,
                                $facialFeatures
                            );
                            // Преобразование массива с результатами определения признаков в массив фактов
                            $facts = $facialFeatureDetector->convertFeaturesToFacts($facialFeatures);
                            // Если в json-файле цифровой маски есть данные по Action Units
                            if (strpos($faceData,'AUs') !== false) {
                                // Формирование json-строки
                                $faceData = str_replace('{"AUs"',',{"AUs"', $faceData);
                                $faceData = trim($faceData, ',');
                                $faceData = '[' . $faceData . ']';
                                // Конвертация данных по Action Units в набор фактов
                                $initialData = json_decode($faceData);
                                if ((count($facts) > 0) && (count($initialData) > 0)) {
                                    $frameData = $initialData[0];
                                    $targetPropertyName = 'AUs';
                                    if (property_exists($frameData, $targetPropertyName) === True)
                                        foreach ($initialData as $frameIndex => $frameData) {
                                            $actionUnits = $frameData -> {$targetPropertyName};
                                            $actionUnitsAsFacts = $facialFeatureDetector->convertActionUnitsToFacts(
                                                $actionUnits,
                                                $frameIndex
                                            );
                                            if (isset($facts[$frameIndex]) && count($actionUnitsAsFacts) > 0)
                                                $facts[$frameIndex] = array_merge($facts[$frameIndex],
                                                    $actionUnitsAsFacts);
                                        }
                                }
                            }
                            // Сохранение json-файла с результатами конвертации определенных признаков в
                            // набор фактов на Object Storage
                            $osConnector->saveFileToObjectStorage(
                                OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                                $analysisResultModel->id,
                                $analysisResultModel->facts_file_name,
                                $facts
                            );
                        }
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
                    foreach ($videoResultFiles as $videoResultFile)
                        if (file_exists($jsonResultPath . $videoResultFile))
                            unlink($jsonResultPath . $videoResultFile);
                    foreach ($jsonResultFiles as $jsonResultFile)
                        if (file_exists($jsonResultPath . $jsonResultFile))
                            unlink($jsonResultPath . $jsonResultFile);
                    // Если видеоинтервью обработалось корректно
                    if ($success) {
                        // Вывод сообщения об успешном анализе видеоинтервью
                        Yii::$app->getSession()->setFlash('success',
                            'Вы успешно проанализировали видеоинтервью!');

                        return $this->redirect(['/analysis-result/list/']);
                    }
                }
            }
        }

        return $this->render('analysis', [
            'model' => $videoInterviewModel,
            'landmarkModels' => $landmarkModels,
            'questions' => $questions
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
        $osConnector = new OSConnector();
        // Получение кода базы знаний из Object Storage
        $knowledgeBase = $osConnector->getFileContentFromObjectStorage(
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
                $osConnector = new OSConnector();
                // Сохранение загруженного файла базы знаний на Object Storage
                $osConnector->saveFileToObjectStorage(
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
        $osConnector = new OSConnector();
        // Скачивание файла базы знаний с Object Storage
        $result = $osConnector->downloadFileFromObjectStorage(
            OSConnector::OBJECT_STORAGE_KNOWLEDGE_BASE_BUCKET,
            null,
            'knowledge-base.txt'
        );
        if ($result != '')
            return $result;
        throw new Exception('Файл не найден!');
    }
}