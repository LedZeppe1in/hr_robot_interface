<?php

namespace app\modules\main\controllers;

use app\modules\main\models\FinalResult;
use app\modules\main\models\GerchikovTestConclusion;
use Yii;
use Exception;
use yii\web\Response;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\OSConnector;
use app\components\FacialFeatureDetector;
use app\modules\main\models\Landmark;
use app\modules\main\models\Question;
use app\modules\main\models\TestQuestion;
use app\modules\main\models\AnalysisResult;
use app\modules\main\models\VideoInterview;
use app\modules\main\models\SurveyQuestion;
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
                    'logout' => ['POST'],
                    'upload' => ['POST'],
                    'interview-analysis' => ['POST'],
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
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['interview']))
            $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new GerchikovTestConclusion();

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * Создание модели результатов анализа и запуск модуля определения признаков.
     *
     * @param $landmark - модель цифровой маски
     * @param $index - порядковый номер цифровой маски
     * @param $processingType - тип обработки получаемых цифровых масок (нормализованные или сырые точки)
     * @return int - id результатов анализа
     */
    public static function getAnalysisResult($landmark, $index, $processingType)
    {
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Если обрабатывается первая цифровая маска
        $basicFrame = '';
        if ($index == 0) {
            // Получение содержимого json-файла с лицевыми точками из Object Storage
            $faceData = $osConnector->getFileContentFromObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $landmark->id,
                $landmark->landmark_file_name
            );
            // Создание объекта обнаружения лицевых признаков
            $facialFeatureDetector = new FacialFeatureDetector();
            // Определение нулевого кадра (нейтрального состояния лица)
            $basicFrame = $facialFeatureDetector->detectFeaturesForBasicFrameDetection(
                $faceData,
                $processingType
            );
        }
        // Создание модели для результатов определения признаков
        $analysisResultModel = new AnalysisResult();
        $analysisResultModel->landmark_id = $landmark->id;
        $analysisResultModel->detection_result_file_name = 'feature-detection-result.json';
        $analysisResultModel->facts_file_name = 'facts.json';
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
        $facialFeatures = $facialFeatureDetector->detectFeaturesV2($faceData, $processingType, $basicFrame);
        // Сохранение json-файла с результатами определения признаков на Object Storage
        $osConnector->saveFileToObjectStorage(
            OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
            $analysisResultModel->id,
            $analysisResultModel->detection_result_file_name,
            $facialFeatures
        );
        // Преобразование массива с результатами определения признаков в массив фактов
        $facts = $facialFeatureDetector->convertFeaturesToFacts($faceData, $facialFeatures, $landmark->question->time);
        // Если в json-файле цифровой маски есть данные по Action Units
        if (strpos($faceData, 'AUs') !== false) {
            // Формирование json-строки
            $faceData = str_replace('{"AUs"', ',{"AUs"', $faceData);
            $faceData = trim($faceData, ',');
            $faceData = '[' . $faceData . ']';
            // Конвертация данных по Action Units в набор фактов
            $initialData = json_decode($faceData);
            if ((count($facts) > 0) && (count($initialData) > 0)) {
                $frameData = $initialData[0];
                $targetPropertyName = 'AUs';
                if (property_exists($frameData, $targetPropertyName) === True)
                    foreach ($initialData as $frameIndex => $frameData) {
                        $actionUnits = $frameData->{$targetPropertyName};
                        $actionUnitsAsFacts = $facialFeatureDetector->convertActionUnitsToFacts($actionUnits,
                            $frameIndex);
                        if (isset($facts[$frameIndex]) && count($actionUnitsAsFacts) > 0)
                            $facts[$frameIndex] = array_merge($facts[$frameIndex], $actionUnitsAsFacts);
                    }
            }
        }
        // Сохранение json-файла с результатами конвертации определенных признаков в набор фактов на Object Storage
        $osConnector->saveFileToObjectStorage(
            OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
            $analysisResultModel->id,
            $analysisResultModel->facts_file_name,
            $facts
        );

        return $analysisResultModel->id;
    }

    /**
     * Страница анализа видео-интервью (полная цепочка анализа от загрузки исходного видеоинтервью до результатов
     * интерпретации признаков).
     *
     * @return string|\yii\web\Response
     * @throws \Throwable
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
                if ($videoInterviewModel->save()) {
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
                    // Путь к программе обработки видео от Ивана
                    $mainPath = '/home/-Common/-ivan/';
                    // Путь к файлу видеоинтервью
                    $videoPath = $mainPath . 'video/';
                    // Путь к json-файлу результатов обработки видеоинтервью
                    $jsonResultPath = $mainPath . 'json/';
                    // Сохранение файла видеоинтервью на сервере
                    $videoInterviewModel->videoInterviewFile->saveAs($videoPath .
                        $videoInterviewModel->video_file_name);
                    // Массивы для хранения параметров результатов обработки видео
                    $videoResultFiles = array();
                    $jsonResultFiles = array();
                    $audioResultFiles = array();
                    $questions = array();
                    // Массив для хранения сообщений о предупреждениях
                    $warningMassages = array();
                    // Получение значения поворота
                    $rotation = (int)Yii::$app->request->post('VideoInterview')['rotationParameter'];
                    // Получение типа обработки получаемых цифровых масок
                    $processingType = Yii::$app->request->post('VideoInterview')['processingType'];
                    // Создание цифровых масок в БД
                    $index = 0;
                    for ($i = 0; $i <= 100; $i++)
                        if (isset(Yii::$app->request->post('Landmark')[$index])) {
                            $landmarkModel = new Landmark();
                            $landmarkModel->start_time = Yii::$app->request
                                ->post('Landmark')[$index]['start_time'];
                            $landmarkModel->finish_time = Yii::$app->request
                                ->post('Landmark')[$index]['finish_time'];
                            $landmarkModel->type = Landmark::TYPE_LANDMARK_IVAN_MODULE;
                            $landmarkModel->rotation = $rotation;
                            $landmarkModel->mirroring = boolval(Yii::$app->request
                                ->post('VideoInterview')['mirroringParameter']);
                            $landmarkModel->question_id = Yii::$app->request
                                ->post('Landmark')[$index]['question_id'];
                            $landmarkModel->video_interview_id = $videoInterviewModel->id;
                            $landmarkModel->save();
                            $index++;
                        }
                    // Выборка всех цифровых масок у данного видео-интервью
                    $landmarks = Landmark::find()
                        ->where(['video_interview_id' => $videoInterviewModel->id, 'landmark_file_name' => null])
                        ->all();
                    // Обход по всем найденным цифровым маскам
                    foreach ($landmarks as $landmark) {
                        // Добавление в массив названия видео-файла с результатами обработки видео
                        array_push($videoResultFiles, 'out_' . $landmark->id . '.avi');
                        // Добавление в массив названия json-файла с результатами обработки видео
                        array_push($jsonResultFiles, 'out_' . $landmark->id . '.json');
                        // Добавление в массив названия аудио-файла (mp3) с результатами обработки видео
                        array_push($audioResultFiles, 'out_' . $landmark->id . '.mp3');
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
                    $parameters['nameAudioFilesOut'] = 'json/out_{}.mp3';
//                    $parameters['indexesTriagnleStats'] = [[31, 48, 51], [35, 51, 54], [31, 48, 74], [35, 54, 75],
//                        [48, 74, 76], [54, 75, 77], [48, 59, 76], [54, 55, 77], [7, 57, 59], [9, 55, 57], [7, 9, 57],
//                        [31, 40, 74], [35, 47, 75], [40, 41, 74], [46, 47, 75]];
                    $parameters['indexesTriagnleStats'] = [[21, 22, 28], [31, 48, 74], [31, 40, 74], [35, 54, 75],
                        [35, 47, 75], [27, 35, 42], [27, 31, 39]];
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
                    // Запуск программы обработки видео Ивана
                    chdir($mainPath);
                    exec('./venv/bin/python ./main.py ./test.json');
                    $index = 0;
                    $analysisResultIds = '';
                    $lastAnalysisResultId = null;
                    // Обход по всем найденным цифровым маскам
                    foreach ($landmarks as $landmark) {
                        // Формирование названия json-файла с результатами обработки видео
                        $landmark->landmark_file_name = 'out_' . $landmark->id . '.json';
                        // Формирование описания цифровой маски
                        $landmark->description = $videoInterviewModel->description . ' (время нарезки: ' .
                            $landmark->getStartTime() . ' - ' . $landmark->getFinishTime() . ')';
                        // Обновление атрибутов цифровой маски в БД
                        $landmark->updateAttributes(['landmark_file_name', 'description']);
                        $success = false;
                        // Проверка существования json-файл с результатами обработки видео
                        if (file_exists($jsonResultPath . $landmark->landmark_file_name)) {
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
                            // Получение рузультатов анализа видеоинтервью (обработка модулем определения признаков)
                            $analysisResultId = self::getAnalysisResult($landmark, $index, $processingType);
                            // Формирование строки из всех id результатов анализа
                            if ($analysisResultIds == '')
                                $analysisResultIds = $analysisResultId;
                            else
                                $analysisResultIds .= ', ' . $analysisResultId;
                            // Запоминание последнего id анализа результата
                            $lastAnalysisResultId = $analysisResultId;
                            // Декодирование json-файла с результатами обработки видео в виде цифровой маски
                            $jsonLandmarkFile = json_decode($landmarkFile, true);
                            // Если в json-файле с цифровой маской есть текст с предупреждением
                            if (isset($jsonLandmarkFile['err_msg']))
                                // Добавление в массив предупреждений сообщения о предупреждении
                                array_push($warningMassages, $jsonLandmarkFile['err_msg']);
                            $success = true;
                        }
                        if ($success == false)
                            // Удаление записи о цифровой маски для которой не сформирован json-файл
                            Landmark::findOne($landmark->id)->delete();
                        // Увеличение индекса на 1
                        $index++;
                    }

                    // Обход видео-файлов нарезки исходного загруженного видео
                    foreach ($videoResultFiles as $key => $videoResultFile)
                        if (file_exists($jsonResultPath . $videoResultFile)) {
                            // Путь к программе обработки видео от Андрея
                            $mainAndrewModulePath = '/home/-Common/-andrey/';
                            // Путь к json-файлу результатов обработки видеоинтервью от Андрея
                            $jsonAndrewResultPath = $mainAndrewModulePath . 'Records/';
                            // Отлов ошибки выполнения программы обработки видео Андрея
                            try {
                                // Запуск программы обработки видео Андрея
                                chdir($mainAndrewModulePath);
                                exec('./EmotionDetection -f ' . $jsonResultPath . $videoResultFile);
                                // Получение имени файла без расширения
                                $jsonFileName = preg_replace('/\.\w+$/', '', $videoResultFile);
                                // Проверка существования json-файл с результатами обработки видео
                                if (file_exists($jsonAndrewResultPath . $jsonFileName . '.json')) {
                                    // Создание цифровой маски в БД
                                    $landmarkModel = new Landmark();
                                    $landmarkModel->landmark_file_name = $videoResultFile;
                                    $landmarkModel->start_time = Yii::$app->request
                                        ->post('Landmark')[$key]['start_time'];
                                    $landmarkModel->finish_time = Yii::$app->request
                                        ->post('Landmark')[$key]['finish_time'];
                                    $landmarkModel->type = Landmark::TYPE_LANDMARK_ANDREW_MODULE;
                                    $landmarkModel->rotation = $rotation;
                                    $landmarkModel->mirroring = boolval(Yii::$app->request
                                        ->post('VideoInterview')['mirroringParameter']);
                                    $landmarkModel->description = $videoInterviewModel->description .
                                        ' (время нарезки: ' . $landmarkModel->start_time . ' - ' .
                                        $landmarkModel->finish_time . ')';
                                    $landmarkModel->question_id = Yii::$app->request
                                        ->post('Landmark')[$key]['question_id'];
                                    $landmarkModel->video_interview_id = $videoInterviewModel->id;
                                    $landmarkModel->save();
                                    // Формирование названия json-файла с результатами обработки видео
                                    $landmarkModel->landmark_file_name = 'out_' . $landmarkModel->id . '.json';
                                    // Обновление атрибута цифровой маски в БД
                                    $landmarkModel->updateAttributes(['landmark_file_name']);
                                    // Получение json-файла с результатами обработки видео в виде цифровой маски
                                    $landmarkFile = file_get_contents($jsonAndrewResultPath .
                                        $jsonFileName . '.json', true);
                                    // Сохранение файла с лицевыми точками на Object Storage
                                    $osConnector->saveFileToObjectStorage(
                                        OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                                        $landmarkModel->id,
                                        $landmarkModel->landmark_file_name,
                                        $landmarkFile
                                    );
                                    // Получение рузультатов анализа видеоинтервью (обработка модулем определения признаков)
                                    $analysisResultId = self::getAnalysisResult(
                                        $landmarkModel,
                                        $key,
                                        VideoInterview::TYPE_RAW_POINTS
                                    );
                                    // Формирование строки из всех id результатов анализа
                                    if ($analysisResultIds == '')
                                        $analysisResultIds = $analysisResultId;
                                    else
                                        $analysisResultIds .= ', ' . $analysisResultId;
                                    // Запоминание последнего id анализа результата
                                    $lastAnalysisResultId = $analysisResultId;
                                    // Удаление json-файлов с результатами обработки видеоинтервью программой Андрея
                                    unlink($jsonAndrewResultPath . $jsonFileName . '.json');
                                }
                            } catch (Exception $e) {
                                // Вывод сообщения об ошибке обработки видеоинтервью от программы Андрея
                                Yii::$app->getSession()->setFlash('error',
                                    'При обработке видеоинтервью программой Андрея возникли ошибки!');
                            }
                        }

                    // Если есть результаты определения признаков
                    if ($analysisResultIds != '') {
                        // Формирование параметров запуска модуля интерпретации признаков
                        $parameters = array('DataSource' => 'ExecuteReasoningForSetOfInitialConditions',
                            'AddressForCodeOfKnowledgeBaseRetrieval' =>
                                'https://84.201.129.65/default/knowledge-base-download',
                            'AddressForInitialConditionsRetrieval' =>
                                'https://84.201.129.65/analysis-result/facts-download/',
                            'IDsOfInitialConditions' => '[' . $analysisResultIds . ']',
                            'AddressToSendResults' => 'https://84.201.129.65:9999/Drools/RetrieveData.php');
                        // Вызов модуля интерпретации признаков через CURL
                        $request = curl_init('https://84.201.129.65:9999/Drools/RetrieveData.php');
                        $dataToSend = http_build_query($parameters);
                        curl_setopt($request, CURLOPT_POSTFIELDS, $dataToSend);
                        curl_setopt($request, CURLOPT_RETURNTRANSFER, True);
                        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0); // Заплатка на период сертификата
                        curl_exec($request);
                        curl_close($request);
                    }

                    // Удаление файла с видеоинтервью
                    if (file_exists($videoPath . $videoInterviewModel->video_file_name))
                        unlink($videoPath . $videoInterviewModel->video_file_name);
                    // Удаление файла с параметрами запуска программы обработки видео
                    if (file_exists($mainPath . 'test.json'))
                        unlink($mainPath . 'test.json');
                    // Удаление файла с выходной аудио-информацией
                    if (file_exists($mainPath . 'audio_out.mp3'))
                        unlink($mainPath . 'audio_out.mp3');
                    // Удаление видео-файлов с результатами обработки видеоинтервью
                    foreach ($videoResultFiles as $key => $videoResultFile)
                        if (file_exists($jsonResultPath . $videoResultFile))
                            unlink($jsonResultPath . $videoResultFile);
                    // Удаление json-файлов с результатами обработки видеоинтервью программой Ивана
                    foreach ($jsonResultFiles as $jsonResultFile)
                        if (file_exists($jsonResultPath . $jsonResultFile))
                            unlink($jsonResultPath . $jsonResultFile);
                    // Удаление фудио-файлов с результатами обработки видеоинтервью программой Ивана
                    foreach ($audioResultFiles as $audioResultFile)
                        if (file_exists($jsonResultPath . $audioResultFile))
                            unlink($jsonResultPath . $audioResultFile);

                    // Если был сформирован результат анализа
                    if ($lastAnalysisResultId != null) {
                        // Дополнение текста сообщения об ошибке - ошибками по отдельным вопросам
                        if (empty($warningMassages))
                            // Вывод сообщения об успешном формировании цифровой маски
                            Yii::$app->getSession()->setFlash('success',
                                'Вы успешно проанализировали видеоинтервью!');
                        else {
                            // Формирование сообщения с предупреждением
                            $message = 'Видеоинтервью проанализировано! Внимание! ';
                            foreach ($warningMassages as $warningMassage)
                                $message .= PHP_EOL . $warningMassage;
                            Yii::$app->getSession()->setFlash('warning', $message);
                        }

                        return $this->redirect(['/analysis-result/view/' . $lastAnalysisResultId]);
                    } else {
                        // Текст сообщения об ошибке
                        $errorMessage = 'Не удалось проанализировать видеоинтервью!';
                        // Проверка существования json-файл с ошибками обработки видеоинтервью в корневой папке
                        if (file_exists($mainPath . 'error.json')) {
                            // Получение json-файл с ошибками обработки видеоинтервью
                            $jsonFile = file_get_contents($mainPath . 'error.json', true);
                            // Декодирование json
                            $jsonFile = json_decode($jsonFile, true);
                            // Дополнение текста сообщения об ошибке
                            $errorMessage .= PHP_EOL . $jsonFile['err_msg'];
                            // Удаление json-файла с сообщением ошибки
                            unlink($mainPath . 'error.json');
                        }
                        // Проверка существования json-файл с ошибками обработки видеоинтервью в папке json
                        if (file_exists($jsonResultPath . 'out_error.json')) {
                            // Получение json-файл с ошибками обработки видеоинтервью
                            $jsonFile = file_get_contents($jsonResultPath . 'out_error.json',
                                true);
                            // Декодирование json
                            $jsonFile = json_decode($jsonFile, true);
                            // Дополнение текста сообщения об ошибке
                            $errorMessage .= PHP_EOL . $jsonFile['err_msg'];
                            // Удаление json-файла с сообщением ошибки
                            unlink($jsonResultPath . 'out_error.json');
                        }
                        // Вывод сообщения о неуспешном формировании цифровой маски
                        Yii::$app->getSession()->setFlash('error', $errorMessage);

                        return $this->redirect(['/video-interview/view/' . $videoInterviewModel->id]);
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
     * Страница записи видеоинтервью.
     *
     * @return string
     */
    public function actionRecord()
    {
        return $this->render('record');
    }

    /**
     * Страница загрузки записанного видеоинтервью на сервер.
     *
     * @return string
     */
    public function actionUpload()
    {
        // Если пришел POST-запрос
        if (Yii::$app->request->isPost) {
            // Создание модели видеоинтервью
            $model = new VideoInterview();
            $videoInterviewFile = UploadedFile::getInstanceByName('FileToUpload');
            $model->videoInterviewFile = $videoInterviewFile;
            $model->video_file_name = $model->videoInterviewFile->baseName . '.' .
                $model->videoInterviewFile->extension;
            $model->description = 'Видео-интервью для профиля кассира.';
            $model->respondent_id = 1;
            $model->save();
            // Создание объекта коннектора с Yandex.Cloud Object Storage
            $osConnector = new OSConnector();
            // Сохранение файла видеоинтервью на Object Storage
            if ($model->video_file_name != '')
                $osConnector->saveFileToObjectStorage(
                    OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                    $model->id,
                    $model->video_file_name,
                    $videoInterviewFile->tempName
                );

            return $this->redirect(['/video-interview/view/' . $model->id]);
        }

        return false;
    }

    /**
     * Страница интервьюирования респондента.
     *
     * @return string
     */
    public function actionInterview()
    {
        // Если пришел POST-запрос
        if (Yii::$app->request->isPost) {
            // Создание модели видеоинтервью
            $videoInterviewModel = new VideoInterview();
            $videoInterviewModel->respondent_id = 1;
            $videoInterviewModel->save();
            // Создание модели итогового результата
            $FinalResultModel = new FinalResult();
            $FinalResultModel->description = 'Итоговый результат для интервью по профилю кассира.';
            $FinalResultModel->video_interview_id = $videoInterviewModel->id;
            $FinalResultModel->save();
            // Создание модели заключения по тесту Герчикова
            $gerchikovTestConclusionModel = new GerchikovTestConclusion();
            // Установка первичного ключа с итогового результата
            $gerchikovTestConclusionModel->id = $FinalResultModel->id;
            // Если пришли параметры с модуля опроса (теста Герчикова)
            if (Yii::$app->request->post('AcceptTest')) {
                $gerchikovTestConclusionModel->accept_test = Yii::$app->request->post('AcceptTest');
                $gerchikovTestConclusionModel->accept_level = Yii::$app->request->post('AcceptLevel');
                $gerchikovTestConclusionModel->instrumental_motivation = Yii::$app->request
                    ->post('MotivInstrumental');
                $gerchikovTestConclusionModel->professional_motivation = Yii::$app->request
                    ->post('MotivProfessional');
                $gerchikovTestConclusionModel->patriot_motivation = Yii::$app->request->post('MotivPatriot');
                $gerchikovTestConclusionModel->master_motivation = Yii::$app->request->post('MotivMaster');
                $gerchikovTestConclusionModel->avoid_motivation = Yii::$app->request->post('MotivAvoid');
                $gerchikovTestConclusionModel->description = 'Итоговое заключение по тесту Герчикова';
            } else
                // Если нет, то загрузка параметров модели из формы
                $gerchikovTestConclusionModel->load(Yii::$app->request->post());
            // Сохранение модели заключения по тесту Герчикова
            $gerchikovTestConclusionModel->save();

            // Если респондент прошел тест Герчикова
            if ($gerchikovTestConclusionModel->accept_test == GerchikovTestConclusion::TYPE_PASSED) {
                // Создание объекта коннектора с Yandex.Cloud Object Storage
                $osConnector = new OSConnector();
                // Поиск вопросов связанных с определенным опросом и сортировка записей по индексу и id вопроса опроса
                $surveyQuestions = SurveyQuestion::find()->where(['survey_id' => 29])->orderBy([
                    'index' => SORT_ASC,
                    'test_question_id' => SORT_ASC
                ])->all();
                // Формирование массива c id вопросов опроса
                $testQuestionIds = array();
                foreach ($surveyQuestions as $surveyQuestion)
                    array_push($testQuestionIds, $surveyQuestion->test_question_id);
                // Поиск вопросов опросов по набору id
                $testQuestions = TestQuestion::find()->where(['id' => $testQuestionIds])->all();
                // Поиск всех вопросов для видеоинтервью связанных с вопросами опроса
                $questions = Question::find()->where(['test_question_id' => $testQuestionIds])->all();
                // Создание массива для моделей цифровой маски
                $landmarkModels = array();
                // Массив с последовательностью id вопросов
                $questionIds = array();
                // Массивы с параметрами вопросов
                $questionTexts = array();
                $questionTimes = array();
                $questionAudioFilePaths = array();
                // Массив с максимальным временем ответов на вопросы
                $answerMaxTimes = array();
                // Формирование последовательности из id вопросов, полученных по индексам вопросов опроса
                foreach ($questions as $question) {
                    foreach ($surveyQuestions as $surveyQuestion)
                        if ($surveyQuestion->test_question_id == $question->test_question_id)
                            $questionIds[$surveyQuestion->index] = $question->id;
                        ksort($questionIds);
                }
                // Обход последовательности id вопросов
                foreach ($questionIds as $questionId)
                    foreach ($questions as $question)
                        if ($questionId == $question->id) {
                            // Формирование массивов с параметрами вопросов
                            $questionTexts[$question->id] = $question->text;
                            $questionTimes[$question->id] = $question->time;
                            $questionAudioFilePaths[$question->id] = $question->id . '/' . $question->audio_file_name;
                            // Формирование массива для максимального времени ответа на вопросы
                            foreach ($testQuestions as $testQuestion)
                                if ($testQuestion->id == $question->test_question_id)
                                    $answerMaxTimes[$question->id] = $testQuestion->maximum_time;
                            // Создание модели цифровой маски с полем id вопроса опроса
                            $landmarkModel = new Landmark();
                            $landmarkModel->question_id = $question->id;
                            // Формирование массива цифровых масок
                            array_push($landmarkModels, $landmarkModel);
                            // Создание директории для аудио-файла с озвучкой вопросов
                            if (!file_exists(Yii::getAlias('@webroot') . '/audio/' . $question->id))
                                mkdir(Yii::getAlias('@webroot') . '/audio/' . $question->id, 0777);
                            // Сохранение аудио-файла с озвучкой вопросов из Object Storage на сервер
                            $osConnector->saveFileToServer(
                                OSConnector::OBJECT_STORAGE_AUDIO_BUCKET,
                                $question->id,
                                $question->audio_file_name,
                                Yii::getAlias('@webroot') . '/audio/' . $question->id . '/'
                            );
                        }
                // Поиск не вопроса
                $notQuestion = Question::find()->where(['type' => Question::TYPE_NOT_QUESTION])->one();
                // Формирование пути до файла с аудио озвучкой для не вопроса
                $interviewPreparationAudio = $notQuestion->id . '/' . $notQuestion->audio_file_name;
                // Создание директории для аудио-файла с озвучкой не вопроса
                if (!file_exists(Yii::getAlias('@webroot') . '/audio/' . $notQuestion->id))
                    mkdir(Yii::getAlias('@webroot') . '/audio/' . $notQuestion->id, 0777);
                // Сохранение аудио-файла с озвучкой не вопроса из Object Storage на сервер
                $osConnector->saveFileToServer(
                    OSConnector::OBJECT_STORAGE_AUDIO_BUCKET,
                    $notQuestion->id,
                    $notQuestion->audio_file_name,
                    Yii::getAlias('@webroot') . '/audio/' . $notQuestion->id . '/'
                );

//                $foos = array();
//                foreach ($surveyQuestions as $surveyQuestion)
//                    $foos[$surveyQuestion->test_question_id] = 'index: ' . $surveyQuestion->index .
//                        ' - test_question_id: ' . $surveyQuestion->test_question_id;

                // Вывод сообщения об успешном прохождении теста Герчикова
                if ($gerchikovTestConclusionModel->accept_test == GerchikovTestConclusion::TYPE_PASSED)
                    Yii::$app->getSession()->setFlash('success',
                        'Вы успешно прошли тест по мотивации к труду по профилю «Кассир»!');

                return $this->render('interview', [
                    'videoInterviewModel' => $videoInterviewModel,
                    'gerchikovTestConclusionModel' => $gerchikovTestConclusionModel,
                    'landmarkModels' => $landmarkModels,
                    'questionTexts' => $questionTexts,
                    'questionTimes' => $questionTimes,
                    'questionAudioFilePaths' => $questionAudioFilePaths,
                    'answerMaxTimes' => $answerMaxTimes,
                    'interviewPreparationAudio' => $interviewPreparationAudio,
                ]);
            }

            // Вывод сообщения о не успешном прохождении теста Герчикова по профилю кассира
            if ($gerchikovTestConclusionModel->accept_test == GerchikovTestConclusion::TYPE_FAILED_PROFILE)
                Yii::$app->getSession()->setFlash('error',
                    'К сожалению, Вы не прошли тест по мотивации к труду по профилю «Кассир»!');
            // Вывод сообщения о не успешном прохождении теста Герчикова
            if ($gerchikovTestConclusionModel->accept_test == GerchikovTestConclusion::TYPE_NOT_ANSWER)
                Yii::$app->getSession()->setFlash('error',
                    'К сожалению, Вы не прошли тест по мотивации к труду по профилю «Кассир», так как не ответили на достаточное количество вопросов!');

            return $this->redirect(['gerchikov-test-conclusion-view', 'id' => $gerchikovTestConclusionModel->id]);
        }

        return false;
    }

    /**
     * Страница просмотра отрицательного результата по тесту Герчикова.
     *
     * @param $id - идентификатор итогового заключения по тесту Герчикова
     * @return string
     */
    public function actionGerchikovTestConclusionView($id)
    {
        $model = GerchikovTestConclusion::findOne($id);

        return $this->render('gerchikov-test-conclusion-view', [
            'model' => $model,
        ]);
    }

    /**
     * Страница анализа записанного интервью респондента.
     *
     * @param $id - идентификатор видеоинтервью
     * @return string
     */
    public function actionInterviewAnalysis($id)
    {
        // Установка времени выполнения скрипта в 10 мин.
        set_time_limit(60*10);
        // Если пришел POST-запрос
        if (Yii::$app->request->isPost) {
            // Поиск вопросов связанных с определенным опросом
            $surveyQuestions = SurveyQuestion::find()->where(['survey_id' => 29])->all();
            // Формирование массива c id вопросов опроса
            $testQuestionIds = array();
            foreach ($surveyQuestions as $surveyQuestion)
                array_push($testQuestionIds, $surveyQuestion->test_question_id);
            // Поиск всех вопросов для видеоинтервью связанных с вопросами опроса
            $questionModels = Question::find()
                ->where(['test_question_id' => $testQuestionIds, 'type' => Question::TYPE_NOT_QUESTION])
                ->all();
            // Удаление аудио-файлов с озвучкой вопросов
            foreach ($questionModels as $questionModel) {
                // Пусть до аудио-файла
                $path = Yii::getAlias('@webroot') . '/audio/' . $questionModel->id;
                if (file_exists($path)) {
                    // Удаление аудио-файла
                    unlink($path . '/' . $questionModel->audio_file_name);
                    // Удаление каталога
                    rmdir($path);
                }
            }

            // Поиск модели видеоинтервью по id
            $model = VideoInterview::findOne($id);
            $videoInterviewFile = UploadedFile::getInstanceByName('FileToUpload');
            $model->videoInterviewFile = $videoInterviewFile;
            $model->video_file_name = $model->videoInterviewFile->baseName . '.' .
                $model->videoInterviewFile->extension;
            $model->description = 'Видео-интервью для профиля кассира.';
            $model->respondent_id = Yii::$app->request->post('VideoInterview')['respondent_id'];
            // Обновление полей модели видеоинтервью
            $model->updateAttributes(['video_file_name', 'description', 'respondent_id']);
            // Создание объекта коннектора с Yandex.Cloud Object Storage
            $osConnector = new OSConnector();
            // Сохранение файла видеоинтервью на Object Storage
            if ($model->video_file_name != '')
                $osConnector->saveFileToObjectStorage(
                    OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                    $model->id,
                    $model->video_file_name,
                    $videoInterviewFile->tempName
                );

//            // Путь к программе обработки видео от Ивана
//            $mainPath = '/home/-Common/-ivan/';
//            // Путь к файлу видеоинтервью
//            $videoPath = $mainPath . 'video/';
//            // Путь к json-файлу результатов обработки видеоинтервью
//            $jsonResultPath = $mainPath . 'json/';
//            // Сохранение файла видеоинтервью на сервере
//            $model->videoInterviewFile->saveAs($videoPath . $model->video_file_name);
//            // Массивы для хранения параметров результатов обработки видео
//            $videoResultFiles = array();
//            $jsonResultFiles = array();
//            $audioResultFiles = array();
//            $questions = array();
//            // Массив для хранения сообщений о предупреждениях
//            $warningMassages = array();

            // Создание цифровых масок в БД
            $index = 0;
            for ($i = 0; $i <= 100; $i++)
                if (isset(Yii::$app->request->post('Landmark')[$index])) {
                    $landmarkModel = new Landmark();
                    $landmarkModel->start_time = Yii::$app->request->post('Landmark')[$index]['start_time'];
                    $landmarkModel->finish_time = Yii::$app->request->post('Landmark')[$index]['finish_time'];
                    $landmarkModel->type = Landmark::TYPE_LANDMARK_IVAN_MODULE;
                    $landmarkModel->rotation = Landmark::TYPE_ZERO;
                    $landmarkModel->mirroring = Landmark::TYPE_MIRRORING_FALSE;
                    $landmarkModel->question_id = Yii::$app->request->post('Landmark')[$index]['question_id'];
                    $landmarkModel->video_interview_id = $model->id;
                    $landmarkModel->save();
                    $index++;
                }

//            //Ajax-запрос
//            if (Yii::$app->request->isAjax) {
//                // Определение массива возвращаемых данных
//                $data = array();
//                // Установка формата JSON для возвращаемых данных
//                $response = Yii::$app->response;
//                $response->format = Response::FORMAT_JSON;
//                // Формирование данных о новом уровне
//                $data["success"] = 'Yes Yes Yes';
//                $data["video_id"] = $model->id;
//                // Возвращение данных
//                $response->data = $data;
//
//                return $response;
//            }

//            // Выборка всех цифровых масок у данного видео-интервью
//            $landmarks = Landmark::find()
//                ->where(['video_interview_id' => $model->id, 'landmark_file_name' => null])
//                ->all();
//            // Обход по всем найденным цифровым маскам
//            foreach ($landmarks as $landmark) {
//                // Добавление в массив названия видео-файла с результатами обработки видео
//                array_push($videoResultFiles, 'out_' . $landmark->id . '.avi');
//                // Добавление в массив названия json-файла с результатами обработки видео
//                array_push($jsonResultFiles, 'out_' . $landmark->id . '.json');
//                // Добавление в массив названия аудио-файла (mp3) с результатами обработки видео
//                array_push($audioResultFiles, 'out_' . $landmark->id . '.mp3');
//                // Формирование информации по вопросу
//                $question['id'] = $landmark->id;
//                $question['start'] = $landmark->start_time;
//                $question['finish'] = $landmark->finish_time;
//                // Добавление в массив вопроса
//                array_push($questions, $question);
//            }
//            // Формирование массива с параметрами запуска программы обработки видео
//            $parameters['nameVidFilesIn'] = 'video/' . $model->video_file_name;
//            $parameters['nameVidFilesOut'] = 'json/out_{}.avi';
//            $parameters['nameJsonFilesOut'] = 'json/out_{}.json';
//            $parameters['nameAudioFilesOut'] = 'json/out_{}.mp3';
//            $parameters['indexesTriagnleStats'] = [[21, 22, 28], [31, 48, 74], [31, 40, 74], [35, 54, 75],
//                [35, 47, 75], [27, 35, 42], [27, 31, 39]];
//            $parameters['rotate_mode'] = VideoInterview::TYPE_ZERO;
//            $parameters['questions'] = $questions;
//            // Формирование json-строки на основе массива с параметрами запуска программы обработки видео
//            $jsonParameters = json_encode($parameters, JSON_UNESCAPED_UNICODE);
//            // Открытие файла на запись для сохранения параметров запуска программы обработки видео
//            $jsonFile = fopen($mainPath . 'test.json', 'a');
//            // Запись в файл json-строки с параметрами запуска программы обработки видео
//            fwrite($jsonFile, str_replace("\\", "", $jsonParameters));
//            // Закрытие файла
//            fclose($jsonFile);
//            // Запуск программы обработки видео Ивана
//            chdir($mainPath);
//            exec('./venv/bin/python ./main.py ./test.json');
//            $index = 0;
//            $analysisResultIds = '';
//            $lastAnalysisResultId = null;
//            // Обход по всем найденным цифровым маскам
//            foreach ($landmarks as $landmark) {
//                // Формирование названия json-файла с результатами обработки видео
//                $landmark->landmark_file_name = 'out_' . $landmark->id . '.json';
//                // Формирование описания цифровой маски
//                $landmark->description = $model->description . ' (время нарезки: ' .
//                    $landmark->getStartTime() . ' - ' . $landmark->getFinishTime() . ')';
//                // Обновление атрибутов цифровой маски в БД
//                $landmark->updateAttributes(['landmark_file_name', 'description']);
//                $success = false;
//                // Проверка существования json-файл с результатами обработки видео
//                if (file_exists($jsonResultPath . $landmark->landmark_file_name)) {
//                    // Получение json-файла с результатами обработки видео в виде цифровой маски
//                    $landmarkFile = file_get_contents($jsonResultPath .
//                        $landmark->landmark_file_name, true);
//                    // Сохранение файла с лицевыми точками на Object Storage
//                    $osConnector->saveFileToObjectStorage(
//                        OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
//                        $landmark->id,
//                        $landmark->landmark_file_name,
//                        $landmarkFile
//                    );
//                    // Получение рузультатов анализа видеоинтервью (обработка модулем определения признаков)
//                    $analysisResultId = self::getAnalysisResult(
//                        $landmark,
//                        $index,
//                        VideoInterview::TYPE_NORMALIZED_POINTS
//                    );
//                    // Формирование строки из всех id результатов анализа
//                    if ($analysisResultIds == '')
//                        $analysisResultIds = $analysisResultId;
//                    else
//                        $analysisResultIds .= ', ' . $analysisResultId;
//                    // Запоминание последнего id анализа результата
//                    $lastAnalysisResultId = $analysisResultId;
//                    // Декодирование json-файла с результатами обработки видео в виде цифровой маски
//                    $jsonLandmarkFile = json_decode($landmarkFile, true);
//                    // Если в json-файле с цифровой маской есть текст с предупреждением
//                    if (isset($jsonLandmarkFile['err_msg']))
//                        // Добавление в массив предупреждений сообщения о предупреждении
//                        array_push($warningMassages, $jsonLandmarkFile['err_msg']);
//                    $success = true;
//                }
//                if ($success == false)
//                    // Удаление записи о цифровой маски для которой не сформирован json-файл
//                    Landmark::findOne($landmark->id)->delete();
//                // Увеличение индекса на 1
//                $index++;
//            }
//
//            // Обход видео-файлов нарезки исходного загруженного видео
//            foreach ($videoResultFiles as $key => $videoResultFile)
//                if (file_exists($jsonResultPath . $videoResultFile)) {
//                    // Путь к программе обработки видео от Андрея
//                    $mainAndrewModulePath = '/home/-Common/-andrey/';
//                    // Путь к json-файлу результатов обработки видеоинтервью от Андрея
//                    $jsonAndrewResultPath = $mainAndrewModulePath . 'Records/';
//                    // Отлов ошибки выполнения программы обработки видео Андрея
//                    try {
//                        // Запуск программы обработки видео Андрея
//                        chdir($mainAndrewModulePath);
//                        exec('./EmotionDetection -f ' . $jsonResultPath . $videoResultFile);
//                        // Получение имени файла без расширения
//                        $jsonFileName = preg_replace('/\.\w+$/', '', $videoResultFile);
//                        // Проверка существования json-файл с результатами обработки видео
//                        if (file_exists($jsonAndrewResultPath . $jsonFileName . '.json')) {
//                            // Создание цифровой маски в БД
//                            $landmarkModel = new Landmark();
//                            $landmarkModel->landmark_file_name = $videoResultFile;
//                            $landmarkModel->start_time = Yii::$app->request
//                                ->post('Landmark')[$key]['start_time'];
//                            $landmarkModel->finish_time = Yii::$app->request
//                                ->post('Landmark')[$key]['finish_time'];
//                            $landmarkModel->type = Landmark::TYPE_LANDMARK_ANDREW_MODULE;
//                            $landmarkModel->rotation = Landmark::TYPE_ZERO;
//                            $landmarkModel->mirroring = Landmark::TYPE_MIRRORING_FALSE;
//                            $landmarkModel->description = $model->description . ' (время нарезки: ' .
//                                $landmarkModel->start_time . ' - ' . $landmarkModel->finish_time . ')';
//                            $landmarkModel->question_id = Yii::$app->request
//                                ->post('Landmark')[$key]['question_id'];
//                            $landmarkModel->video_interview_id = $model->id;
//                            $landmarkModel->save();
//                            // Формирование названия json-файла с результатами обработки видео
//                            $landmarkModel->landmark_file_name = 'out_' . $landmarkModel->id . '.json';
//                            // Обновление атрибута цифровой маски в БД
//                            $landmarkModel->updateAttributes(['landmark_file_name']);
//                            // Получение json-файла с результатами обработки видео в виде цифровой маски
//                            $landmarkFile = file_get_contents($jsonAndrewResultPath .
//                                $jsonFileName . '.json', true);
//                            // Сохранение файла с лицевыми точками на Object Storage
//                            $osConnector->saveFileToObjectStorage(
//                                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
//                                $landmarkModel->id,
//                                $landmarkModel->landmark_file_name,
//                                $landmarkFile
//                            );
//                            // Получение рузультатов анализа видеоинтервью (обработка модулем определения признаков)
//                            $analysisResultId = self::getAnalysisResult(
//                                $landmarkModel,
//                                $key,
//                                VideoInterview::TYPE_RAW_POINTS
//                            );
//                            // Формирование строки из всех id результатов анализа
//                            if ($analysisResultIds == '')
//                                $analysisResultIds = $analysisResultId;
//                            else
//                                $analysisResultIds .= ', ' . $analysisResultId;
//                            // Запоминание последнего id анализа результата
//                            $lastAnalysisResultId = $analysisResultId;
//                            // Удаление json-файлов с результатами обработки видеоинтервью программой Андрея
//                            unlink($jsonAndrewResultPath . $jsonFileName . '.json');
//                        }
//                    } catch (Exception $e) {
//                        // Вывод сообщения об ошибке обработки видеоинтервью от программы Андрея
//                        Yii::$app->getSession()->setFlash('error',
//                            'При обработке видеоинтервью программой Андрея возникли ошибки!');
//                    }
//                }
//
//            // Если есть результаты определения признаков
//            if ($analysisResultIds != '') {
//                // Формирование параметров запуска модуля интерпретации признаков
//                $parameters = array('DataSource' => 'ExecuteReasoningForSetOfInitialConditions',
//                    'AddressForCodeOfKnowledgeBaseRetrieval' =>
//                        'https://84.201.129.65/default/knowledge-base-download',
//                    'AddressForInitialConditionsRetrieval' =>
//                        'https://84.201.129.65/analysis-result/facts-download/',
//                    'IDsOfInitialConditions' => '[' . $analysisResultIds . ']',
//                    'AddressToSendResults' => 'https://84.201.129.65:9999/Drools/RetrieveData.php');
//                // Вызов модуля интерпретации признаков через CURL
//                $request = curl_init('https://84.201.129.65:9999/Drools/RetrieveData.php');
//                $dataToSend = http_build_query($parameters);
//                curl_setopt($request, CURLOPT_POSTFIELDS, $dataToSend);
//                curl_setopt($request, CURLOPT_RETURNTRANSFER, True);
//                curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0); // Заплатка на период сертификата
//                curl_exec($request);
//                curl_close($request);
//            }
//
//            // Удаление файла с видеоинтервью
//            if (file_exists($videoPath . $model->video_file_name))
//                unlink($videoPath . $model->video_file_name);
//            // Удаление файла с параметрами запуска программы обработки видео
//            if (file_exists($mainPath . 'test.json'))
//                unlink($mainPath . 'test.json');
//            // Удаление файла с выходной аудио-информацией
//            if (file_exists($mainPath . 'audio_out.mp3'))
//                unlink($mainPath . 'audio_out.mp3');
//            // Удаление видео-файлов с результатами обработки видеоинтервью
//            foreach ($videoResultFiles as $key => $videoResultFile)
//                if (file_exists($jsonResultPath . $videoResultFile))
//                    unlink($jsonResultPath . $videoResultFile);
//            // Удаление json-файлов с результатами обработки видеоинтервью программой Ивана
//            foreach ($jsonResultFiles as $jsonResultFile)
//                if (file_exists($jsonResultPath . $jsonResultFile))
//                    unlink($jsonResultPath . $jsonResultFile);
//            // Удаление фудио-файлов с результатами обработки видеоинтервью программой Ивана
//            foreach ($audioResultFiles as $audioResultFile)
//                if (file_exists($jsonResultPath . $audioResultFile))
//                    unlink($jsonResultPath . $audioResultFile);
//
//            // Если был сформирован результат анализа
//            if ($lastAnalysisResultId != null) {
//                // Дополнение текста сообщения об ошибке - ошибками по отдельным вопросам
//                if (empty($warningMassages))
//                    // Вывод сообщения об успешном формировании цифровой маски
//                    Yii::$app->getSession()->setFlash('success',
//                        'Вы успешно проанализировали видеоинтервью!');
//                else {
//                    // Формирование сообщения с предупреждением
//                    $message = 'Видеоинтервью проанализировано! Внимание! ';
//                    foreach ($warningMassages as $warningMassage)
//                        $message .= PHP_EOL . $warningMassage;
//                    Yii::$app->getSession()->setFlash('warning', $message);
//                }
//
//                return $this->redirect(['/analysis-result/view/' . $lastAnalysisResultId]);
//            } else {
//                // Текст сообщения об ошибке
//                $errorMessage = 'Не удалось проанализировать видеоинтервью!';
//                // Проверка существования json-файл с ошибками обработки видеоинтервью в корневой папке
//                if (file_exists($mainPath . 'error.json')) {
//                    // Получение json-файл с ошибками обработки видеоинтервью
//                    $jsonFile = file_get_contents($mainPath . 'error.json', true);
//                    // Декодирование json
//                    $jsonFile = json_decode($jsonFile, true);
//                    // Дополнение текста сообщения об ошибке
//                    $errorMessage .= PHP_EOL . $jsonFile['err_msg'];
//                    // Удаление json-файла с сообщением ошибки
//                    unlink($mainPath . 'error.json');
//                }
//                // Проверка существования json-файл с ошибками обработки видеоинтервью в папке json
//                if (file_exists($jsonResultPath . 'out_error.json')) {
//                    // Получение json-файл с ошибками обработки видеоинтервью
//                    $jsonFile = file_get_contents($jsonResultPath . 'out_error.json',
//                        true);
//                    // Декодирование json
//                    $jsonFile = json_decode($jsonFile, true);
//                    // Дополнение текста сообщения об ошибке
//                    $errorMessage .= PHP_EOL . $jsonFile['err_msg'];
//                    // Удаление json-файла с сообщением ошибки
//                    unlink($jsonResultPath . 'out_error.json');
//                }
//                // Вывод сообщения о неуспешном формировании цифровой маски
//                Yii::$app->getSession()->setFlash('error', $errorMessage);
//
//                return $this->redirect(['/video-interview/view/' . $model->id]);
//            }
        }

        return false;
    }
}