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
     * Создание модели результатов анализа и запуск модуля определения признаков.
     *
     * @param $landmark - модель цифровой маски
     * @param $processingType - тип обработки получаемых цифровых масок (нормализованные или сырые точки)
     * @param $osConnector - объект соединения с Yandex.Cloud Object Storage
     * @return int - id результатов анализа
     */
    public static function getAnalysisResult($landmark, $processingType, $osConnector)
    {
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
                            $landmarkModel->video_interview_id = $videoInterviewModel->id;
                            $landmarkModel->questionText = Yii::$app->request
                                ->post('Landmark')[$index]['questionText'];
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
                        } else
                            // Формирование id вопроса
                            $landmark->question_id = Yii::$app->request->post('Landmark')[$index]['question_id'];
                        // Формирование названия json-файла с результатами обработки видео
                        $landmark->landmark_file_name = 'out_' . $landmark->id . '.json';
                        // Формирование описания цифровой маски
                        $landmark->description = $videoInterviewModel->description . ' (время нарезки: ' .
                            $landmark->getStartTime() . ' - ' . $landmark->getFinishTime() . ')';
                        // Обновление атрибутов цифровой маски в БД
                        $landmark->updateAttributes(['landmark_file_name', 'description', 'question_id']);
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
                            $analysisResultId = self::getAnalysisResult($landmark, $processingType, $osConnector);
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
                                    // Получение значения текста вопроса
                                    $landmarkModel->questionText = Yii::$app->request
                                        ->post('Landmark')[$key]['questionText'];
                                    // Если поле текста вопроса содержит значение "hidden"
                                    if ($landmarkModel->questionText != 'hidden') {
                                        // Формирование id вопроса
                                        $question = Question::find()
                                            ->where(['text' => $landmarkModel->questionText])
                                            ->one();
                                        $landmarkModel->question_id = $question->id;
                                    } else
                                        // Формирование id вопроса
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
                                    // Получение рузультатов анализа видеоинтервью
                                    // (обработка модулем определения признаков)
                                    $analysisResultId = self::getAnalysisResult($landmarkModel, $processingType,
                                        $osConnector);
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
                                'http://84.201.129.65/default/knowledge-base-download',
                            'AddressForInitialConditionsRetrieval' =>
                                'http://84.201.129.65/analysis-result/facts-download/',
                            'IDsOfInitialConditions' => '[' . $analysisResultIds . ']',
                            'AddressToSendResults' => 'http://84.201.129.65:9999/Drools/RetrieveData.php');
                        // Вызов модуля интерпретации признаков через CURL
                        $request = curl_init('http://84.201.129.65:9999/Drools/RetrieveData.php');
                        $dataToSend = http_build_query($parameters);
                        curl_setopt($request, CURLOPT_POSTFIELDS, $dataToSend);
                        curl_setopt($request, CURLOPT_RETURNTRANSFER, True);
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