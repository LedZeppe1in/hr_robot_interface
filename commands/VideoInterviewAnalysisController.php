<?php

namespace app\commands;

use app\modules\main\models\FinalConclusion;
use app\modules\main\models\FinalResult;
use app\modules\main\models\ModuleMessage;
use app\modules\main\models\QuestionProcessingStatus;
use app\modules\main\models\VideoInterviewProcessingStatus;
use Exception;
use SoapClient;
use stdClass;
use yii\helpers\Console;
use yii\console\Controller;
use app\components\OSConnector;
use app\components\FacialFeatureDetector;
use app\modules\main\models\Landmark;
use app\modules\main\models\Question;
use app\modules\main\models\VideoInterview;
use app\modules\main\models\AnalysisResult;

/**
 * Class VideoInterviewAnalysisController - содержит команду для последовательного анализа видеоинтервью.
 * @package app\commands
 */
class VideoInterviewAnalysisController extends Controller
{
    /**
     * Инициализация команд.
     */
    public function actionIndex()
    {
        echo 'yii video-interview-analysis/start' . PHP_EOL;
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
        if ($index == 1) {
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
     * Команда запуска анализа видеоинтервью респондента.
     * @param $questionId - идентификатор вопроса видеоинтервью
     * @param $landmarkId - идентификатор цифровой маски
     * @param $questionIndex - номер вопроса
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionStart($questionId, $landmarkId, $questionIndex)
    {
        // Время начала выполнения анализа видеоинтервью
        $videoInterviewProcessingStart = microtime(true);

        // Поиск вопроса видеоинтервью по id
        $question = Question::findOne((int)$questionId);
        // Поиск цифровой маски по id
        $landmark = Landmark::findOne((int)$landmarkId);
        // Поиск полного видеоинтервью по id
        $videoInterview = VideoInterview::findOne($landmark->video_interview_id);

        // Путь к программе обработки видео от Ивана
        $mainPath = '/home/-Common/-ivan/';
        // Путь к файлу видеоинтервью
        $videoPath = $mainPath . 'video/';
        // Путь к json-файлу результатов обработки видеоинтервью
        $jsonResultPath = $mainPath . 'json/';
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Сохранение файла видео ответа на вопрос на сервере
        $osConnector->saveFileToServer(
            OSConnector::OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET,
            $question->id,
            $question->video_file_name,
            $videoPath
        );
        // Название видео-файла с результатами обработки видео
        $videoResultFile = 'out_' . $landmark->id . '.avi';
        // Название json-файла с результатами обработки видео
        $jsonResultFile = 'out_' . $landmark->id . '.json';
        // Название аудио-файла (mp3) с результатами обработки видео
        $audioResultFile = 'out_' . $landmark->id . '.mp3';
        // Формирование информации по вопросу
        $questionParameter = array();
        $questionParameter['id'] = $landmark->id;
        $questionParameter['start'] = 0;
        $questionParameter['finish'] = $landmark->finish_time - $landmark->start_time;
        // Формирование массива с параметрами запуска программы обработки видео
        $parameters['nameVidFilesIn'] = 'video/' . $question->video_file_name;
        $parameters['nameVidFilesOut'] = 'json/out_{}.avi';
        $parameters['nameJsonFilesOut'] = 'json/out_{}.json';
        $parameters['nameAudioFilesOut'] = 'json/out_{}.mp3';
        $parameters['indexesTriagnleStats'] = [[21, 22, 28], [31, 48, 74], [31, 40, 74], [35, 54, 75],
            [35, 47, 75], [27, 35, 42], [27, 31, 39]];
        $parameters['rotate_mode'] = VideoInterview::TYPE_ZERO;
        $parameters['questions'] = [$questionParameter];
        // Формирование json-строки на основе массива с параметрами запуска программы обработки видео
        $jsonParameters = json_encode($parameters, JSON_UNESCAPED_UNICODE);
        // Открытие файла на запись для сохранения параметров запуска программы обработки видео
        $jsonFile = fopen($mainPath . 'test' . $question->id . '.json', 'a');
        // Запись в файл json-строки с параметрами запуска программы обработки видео
        fwrite($jsonFile, str_replace("\\", "", $jsonParameters));
        // Закрытие файла
        fclose($jsonFile);
        // Создание модели статуса обработки вопроса
        $questionProcessingStatusModel = new QuestionProcessingStatus();
        $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_IVAN_VIDEO_PROCESSING_MODULE_IN_PROGRESS;
        $questionProcessingStatusModel->question_id = $question->id;
        // Поиск статуса обработки видеоинтервью по id видеоинтервью
        $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::find()
            ->where(['video_interview_id' => $videoInterview->id])
            ->one();
        // Если статус обработки видеоинтервью еще не создан
        if (empty($videoInterviewProcessingStatus)) {
            // Создание статуса обработки видеоинтервью в БД
            $videoInterviewProcessingStatusModel = new VideoInterviewProcessingStatus();
            $videoInterviewProcessingStatusModel->status = VideoInterviewProcessingStatus::STATUS_IN_PROGRESS;
            $videoInterviewProcessingStatusModel->video_interview_id = $videoInterview->id;
            $videoInterviewProcessingStatusModel->save();
            $questionProcessingStatusModel->video_interview_processing_status_id = $videoInterviewProcessingStatusModel->id;
        } else
            $questionProcessingStatusModel->video_interview_processing_status_id = $videoInterviewProcessingStatus->id;
        // Сохранение модели статуса обработки вопроса в БД
        $questionProcessingStatusModel->save();
        // Время начала выполнения МОВ Ивана
        $ivanVideoAnalysisStart = microtime(true);
        try {
            // Запуск программы обработки видео Ивана
            chdir($mainPath);
            exec('./venv/bin/python ./main.py ./test' . $question->id . '.json');
        } catch (Exception $e) {
            // Создание сообщения об ошибке МОВ Ивана в БД
            $moduleMessageModel = new ModuleMessage();
            $moduleMessageModel->message = 'Ошибка модуля обработки видео Ивана! ' . $e->getMessage();
            $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
            $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
            $moduleMessageModel->save();
        }
        // Время окончания выполнения МОВ Ивана
        $ivanVideoAnalysisEnd = microtime(true);
        // Вычисление времени выполнения МОВ Ивана
        $ivanVideoAnalysisRuntime = $ivanVideoAnalysisEnd - $ivanVideoAnalysisStart;
        // Обновление атрибута времени выполнения МОВ Ивана в БД
        $questionProcessingStatusModel->ivan_video_analysis_runtime = $ivanVideoAnalysisRuntime;
        $questionProcessingStatusModel->updateAttributes(['ivan_video_analysis_runtime']);

        $success = false;
        $analysisResultId = '';
        $analysisResultIds = '';
        // Сообщение предупреждения от программы обработки видео
        $warningMassage = '';
        // Формирование названия json-файла с результатами обработки видео
        $landmark->landmark_file_name = 'out_' . $landmark->id . '.json';
        // Формирование описания цифровой маски
        $landmark->description = $videoInterview->description . ' (время нарезки: ' .
            $landmark->getStartTime() . ' - ' . $landmark->getFinishTime() . ')';
        // Обновление атрибутов цифровой маски в БД
        $landmark->updateAttributes(['landmark_file_name', 'description']);
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
            // Обновление атрибута статуса обработки вопроса в БД
            $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_FEATURE_DEFINITION_MODULE_IN_PROGRESS;
            $questionProcessingStatusModel->updateAttributes(['status']);
            // Время начала выполнения МОП
            $featureDetectionStart = microtime(true);
            try {
                // Получение рузультатов анализа видеоинтервью (обработка модулем определения признаков)
                $analysisResultId = self::getAnalysisResult(
                    $landmark,
                    (int)$questionIndex,
                    VideoInterview::TYPE_NORMALIZED_POINTS
                );
            } catch (Exception $e) {
                // Создание сообщения об ошибке МОП в БД
                $moduleMessageModel = new ModuleMessage();
                $moduleMessageModel->message = 'Ошибка МОП на данных Ивана! ' . $e->getMessage();
                $moduleMessageModel->module_name = ModuleMessage::FEATURE_DETECTION_MODULE;
                $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
                $moduleMessageModel->save();
            }
            // Время окончания выполнения МОП
            $featureDetectionEnd = microtime(true);
            // Вычисление времени выполнения МОП
            $featureDetectionRuntime = $featureDetectionEnd - $featureDetectionStart;
            // Обновление атрибута времени выполнения МОП в БД
            $questionProcessingStatusModel->feature_detection_runtime = $featureDetectionRuntime;
            $questionProcessingStatusModel->updateAttributes(['feature_detection_runtime']);
            // Формирование строки из всех id результатов анализа
            if ($analysisResultIds == '')
                $analysisResultIds = $analysisResultId;
            else
                $analysisResultIds .= ', ' . $analysisResultId;
            // Декодирование json-файла с результатами обработки видео в виде цифровой маски
            $jsonLandmarkFile = json_decode($landmarkFile, true);
            // Если в json-файле с цифровой маской есть текст с предупреждением
            if (isset($jsonLandmarkFile['err_msg']))
                // Запоминание текста сообщения о предупреждении
                $warningMassage = $jsonLandmarkFile['err_msg'];
            $success = true;
        }
        // Удаление записи о цифровой маски для которой не сформирован json-файл
        if ($success == false)
            Landmark::findOne($landmark->id)->delete();
//        // Текст сообщения об ошибке
//        $errorMessage = 'Не удалось проанализировать видеоинтервью!';
//        // Проверка существования json-файл с ошибками обработки видеоинтервью в корневой папке
//        if (file_exists($mainPath . 'error.json')) {
//            // Получение json-файл с ошибками обработки видеоинтервью
//            $jsonErrorFile = file_get_contents($mainPath . 'error.json', true);
//            // Декодирование json
//            $jsonErrorFile = json_decode($jsonErrorFile, true);
//            // Дополнение текста сообщения об ошибке
//            $errorMessage .= PHP_EOL . $jsonErrorFile['err_msg'];
//            // Удаление json-файла с сообщением ошибке
//            unlink($mainPath . 'error.json');
//        }
//        // Проверка существования json-файл с ошибками обработки видеоинтервью в папке json
//        if (file_exists($jsonResultPath . 'out_error.json')) {
//            // Получение json-файл с ошибками обработки видеоинтервью
//            $jsonErrorFile = file_get_contents($jsonResultPath . 'out_error.json', true);
//            // Декодирование json
//            $jsonErrorFile = json_decode($jsonErrorFile, true);
//            // Дополнение текста сообщения об ошибке
//            $errorMessage .= PHP_EOL . $jsonErrorFile['err_msg'];
//            // Удаление json-файла с сообщением ошибке
//            unlink($jsonResultPath . 'out_error.json');
//        }

        // Путь к программе обработки видео от Андрея
        $mainAndrewModulePath = '/home/-Common/-andrey/';
        // Путь к json-файлу результатов обработки видеоинтервью от Андрея
        $jsonAndrewResultPath = $mainAndrewModulePath . 'Records/';
        // Обновление атрибута статуса обработки вопроса в БД
        $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_ANDREY_VIDEO_PROCESSING_MODULE_IN_PROGRESS;
        $questionProcessingStatusModel->updateAttributes(['status']);
        // Время начала выполнения МОВ Андрея
        $andreyVideoAnalysisStart = microtime(true);
        try {
            // Запуск программы обработки видео Андрея
            chdir($mainAndrewModulePath);
            exec('./EmotionDetection -f ' . $videoPath . $question->video_file_name);
            // Время окончания выполнения МОВ Андрея
            $andreyVideoAnalysisEnd = microtime(true);
            // Вычисление времени выполнения МОВ Андрея
            $andreyVideoAnalysisRuntime = $andreyVideoAnalysisEnd - $andreyVideoAnalysisStart;
            // Обновление атрибута времени выполнения МОВ Андрея в БД
            $questionProcessingStatusModel->andrey_video_analysis_runtime = $andreyVideoAnalysisRuntime;
            $questionProcessingStatusModel->updateAttributes(['andrey_video_analysis_runtime']);
            // Получение имени файла без расширения
            $jsonFileName = preg_replace('/\.\w+$/', '', $question->video_file_name);
            // Проверка существования json-файл с результатами обработки видео
            if (file_exists($jsonAndrewResultPath . $jsonFileName . '.json')) {
                // Создание цифровой маски в БД
                $landmarkModel = new Landmark();
                $landmarkModel->landmark_file_name = $videoResultFile;
                $landmarkModel->start_time = Landmark::formatMilliseconds($landmark->start_time);
                $landmarkModel->finish_time = Landmark::formatMilliseconds($landmark->finish_time);
                $landmarkModel->type = Landmark::TYPE_LANDMARK_ANDREW_MODULE;
                $landmarkModel->rotation = Landmark::TYPE_ZERO;
                $landmarkModel->mirroring = Landmark::TYPE_MIRRORING_FALSE;
                $landmarkModel->description = $videoInterview->description . ' (время нарезки: ' .
                    $landmarkModel->start_time . ' - ' . $landmarkModel->finish_time . ')';
                $landmarkModel->question_id = $question->id;
                $landmarkModel->video_interview_id = $videoInterview->id;
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
                // Обновление атрибута статуса обработки вопроса в БД
                $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_FEATURE_DEFINITION_MODULE_IN_PROGRESS;
                $questionProcessingStatusModel->updateAttributes(['status']);
                // Время начала выполнения МОП
                $featureDetectionStart = microtime(true);
                try {
                    // Получение рузультатов анализа видеоинтервью (обработка модулем определения признаков)
                    $analysisResultId = self::getAnalysisResult(
                        $landmarkModel,
                        (int)$questionIndex,
                        VideoInterview::TYPE_RAW_POINTS
                    );
                } catch (Exception $e) {
                    // Создание сообщения об ошибке МОП в БД
                    $moduleMessageModel = new ModuleMessage();
                    $moduleMessageModel->message = 'Ошибка МОП на данных Андрея! ' . $e->getMessage();
                    $moduleMessageModel->module_name = ModuleMessage::FEATURE_DETECTION_MODULE;
                    $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
                    $moduleMessageModel->save();
                }
                // Время окончания выполнения МОП
                $featureDetectionEnd = microtime(true);
                // Вычисление времени выполнения МОП
                $featureDetectionRuntime = $featureDetectionEnd - $featureDetectionStart;
                // Обновление атрибута времени выполнения МОП в БД
                $questionProcessingStatusModel->feature_detection_runtime += $featureDetectionRuntime;
                $questionProcessingStatusModel->updateAttributes(['feature_detection_runtime']);
                // Формирование строки из всех id результатов анализа
                if ($analysisResultIds == '')
                    $analysisResultIds = $analysisResultId;
                else
                    $analysisResultIds .= ', ' . $analysisResultId;
                // Удаление json-файла с результатами обработки видеоинтервью программой Андрея
                unlink($jsonAndrewResultPath . $jsonFileName . '.json');
            }
        } catch (Exception $e) {
            // Создание сообщения об ошибке МОВ Андрея в БД
            $moduleMessageModel = new ModuleMessage();
            $moduleMessageModel->message = 'Ошибка модуля обработки видео Андрея! ' . $e->getMessage();
            $moduleMessageModel->module_name = ModuleMessage::FEATURE_DETECTION_MODULE;
            $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
            $moduleMessageModel->save();
        }

        // Если есть результаты определения признаков
        if ($analysisResultIds != '')
            try {
                // Обновление атрибута статуса обработки вопроса в БД
                $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_FEATURE_INTERPRETATION_MODULE_IN_PROGRESS;
                $questionProcessingStatusModel->updateAttributes(['status']);
                // Время начала выполнения МИП
                $featureInterpretationStart = microtime(true);
                // Запуск интерпретации признаков по результатам МОП (интерпретация первого уровня)
                ini_set('default_socket_timeout', 60 * 30);
                $addressOfRBRWebServiceDefinition = 'http://127.0.0.1:8888/RBRWebService?wsdl';
                $client = new SoapClient($addressOfRBRWebServiceDefinition);
                $addressForCodeOfKnowledgeBaseRetrieval = 'https://84.201.129.65/knowledge-base/knowledge-base-download/1';
                $addressForInitialConditionsRetrieval = 'https://84.201.129.65/analysis-result/facts-download/';
                $idsOfInitialConditions = '[' . $analysisResultIds . ']';
                $addressToSendResults = 'https://84.201.129.65:9999/Drools/RetrieveData.php';
                $additionalDataToSend = new stdClass;
                $additionalDataToSend->{'IDOfFile'} = Null;
                $client->LaunchReasoningProcessForSetOfInitialConditions(array(
                    'arg0' => $addressForCodeOfKnowledgeBaseRetrieval,
                    'arg1' => $addressForInitialConditionsRetrieval,
                    'arg2' => $idsOfInitialConditions,
                    'arg3' => $addressToSendResults,
                    'arg4' => 'ResultsOfReasoningProcess',
                    'arg5' => 'IDOfFile',
                    'arg6' => json_encode($additionalDataToSend)))->return;
                $client = Null;
                // Время окончания выполнения МИП
                $featureInterpretationEnd = microtime(true);
                // Вычисление времени выполнения МИП
                $featureInterpretationRuntime = $featureInterpretationEnd - $featureInterpretationStart;
                // Обновление атрибута времени выполнения МИП в БД
                $questionProcessingStatusModel->feature_interpretation_runtime = $featureInterpretationRuntime;
                $questionProcessingStatusModel->updateAttributes(['feature_interpretation_runtime']);
            } catch (Exception $e) {
                // Создание сообщения об ошибке МИП в БД
                $moduleMessageModel = new ModuleMessage();
                $moduleMessageModel->message = 'Ошибка МИП (первый уровень)! ' . $e->getMessage();
                $moduleMessageModel->module_name = ModuleMessage::FEATURE_DETECTION_MODULE;
                $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
                $moduleMessageModel->save();
            }
        // Обновление атрибута статуса обработки вопроса в БД
        $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_COMPLETED;
        $questionProcessingStatusModel->updateAttributes(['status']);

        // Удаление файла с видеоинтервью
        if (file_exists($videoPath . $question->video_file_name))
            unlink($videoPath . $question->video_file_name);
        // Удаление файла с параметрами запуска программы обработки видео
        if (file_exists($mainPath . 'test' . $question->id . '.json'))
            unlink($mainPath . 'test' . $question->id . '.json');
        // Удаление файла с выходной аудио-информацией
        //if (file_exists($mainPath . 'audio_out.mp3'))
        //    unlink($mainPath . 'audio_out.mp3');
        // Удаление видео-файла с результатами обработки видеоинтервью
        if (file_exists($jsonResultPath . $videoResultFile))
            unlink($jsonResultPath . $videoResultFile);
        // Удаление json-файла с результатами обработки видео программой Ивана
        if (file_exists($jsonResultPath . $jsonResultFile))
            unlink($jsonResultPath . $jsonResultFile);
        // Удаление аудио-файла с результатами обработки видео программой Ивана
        if (file_exists($jsonResultPath . $audioResultFile))
            unlink($jsonResultPath . $audioResultFile);

        $completed = true;
        // Поиск статуса обработки видеоинтервью по id видеоинтервью
        $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::find()
            ->where(['video_interview_id' => $videoInterview->id])
            ->one();
        // Поиск статусов обработки вопросов по id статуса обработки видеоинтервью
        $questionProcessingStatuses = QuestionProcessingStatus::find()
            ->where(['video_interview_processing_status_id' => $videoInterviewProcessingStatus->id])
            ->all();
        // Обход всех статусов обработки вопросов и определение завершенности каждого
        foreach ($questionProcessingStatuses as $questionProcessingStatus)
            if ($questionProcessingStatus->status != QuestionProcessingStatus::STATUS_COMPLETED)
                $completed = false;
        // Если анализ всех видео ответов на вопросы завершен
        if ($completed) {
            // Обновление атрибута статуса обработки видеоинтервью в БД
            $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_FINAL_RESULT_FORMATION;
            $videoInterviewProcessingStatus->updateAttributes(['status']);
            // Время начала выполнения МИП
            $emotionInterpretationStart = microtime(true);
            // Поиск итоговых результатов по id видеоинтервью
            $finalResult = FinalResult::find()->where(['video_interview_id' => $videoInterview->id])->one();
            // Создание модели заключения по видеоинтервью
            $finalConclusionModel = new FinalConclusion();
            // Установка первичного ключа с итогового результата
            $finalConclusionModel->id = $finalResult->id;
            // Сохранение модели заключения по видеоинтервью
            $finalConclusionModel->save();
            try {
                // Запуск вывода по результатам интерпретации признаков (интерпретация второго уровня)
                ini_set('default_socket_timeout', 60 * 30);
                $addressOfRBRWebServiceDefinition = 'http://127.0.0.1:8888/RBRWebService?wsdl';
                $client = new SoapClient($addressOfRBRWebServiceDefinition);
                $addressForCodeOfKnowledgeBaseRetrieval =
                    'https://84.201.129.65/knowledge-base/knowledge-base-download/2';
                $addressForInitialConditionsRetrieval =
                    'https://84.201.129.65/analysis-result/interpretation-facts-download/' . $finalConclusionModel->id;
                $addressToSendResults = 'https://84.201.129.65:9999/Drools/RetrieveData.php';
                $additionalDataToSend = new stdClass;
                $additionalDataToSend -> {'IDOfFile'} = $finalConclusionModel->id;
                $additionalDataToSend -> {'Type'} = 'Interpretation Level II';
                $client->LaunchReasoningProcessAndSendResultsToURL(array(
                    'arg0' => $addressForCodeOfKnowledgeBaseRetrieval,
                    'arg1' => $addressForInitialConditionsRetrieval,
                    'arg2' => $addressToSendResults,
                    'arg3' => 'ResultsOfReasoningProcess',
                    'arg4' => json_encode($additionalDataToSend)))->return;
                $client = Null;
            } catch (Exception $e) {
                // Создание сообщения об ошибке МИП в БД
                $moduleMessageModel = new ModuleMessage();
                $moduleMessageModel->message = 'Ошибка МИП (второй уровень)! ' . $e->getMessage();
                $moduleMessageModel->module_name = ModuleMessage::FEATURE_DETECTION_MODULE;
                $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
                $moduleMessageModel->save();
            }
            // Время окончания выполнения МИП
            $emotionInterpretationEnd = microtime(true);
            // Вычисление времени выполнения МИП
            $emotionInterpretationRuntime = $emotionInterpretationEnd - $emotionInterpretationStart;
            // Время окончания выполнения анализа видеоинтервью
            $videoInterviewProcessingEnd = microtime(true);
            // Вычисление времени выполнения анализа видеоинтервью
            $videoInterviewProcessingRuntime = $videoInterviewProcessingEnd - $videoInterviewProcessingStart;
            // Обновление атрибутов статуса обработки видеоинтервью, полного времени анализа видеоинтервью и
            // времени выполнения интерпретации эмоций (второй уровень интерпретации) в БД
            $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_COMPLETED;
            $videoInterviewProcessingStatus->all_runtime = $videoInterviewProcessingRuntime;
            $videoInterviewProcessingStatus->emotion_interpretation_runtime = $emotionInterpretationRuntime;
            $videoInterviewProcessingStatus->updateAttributes(['status', 'all_runtime',
                'emotion_interpretation_runtime']);
        }
    }

    /**
     * Вывод сообщений на экран (консоль)
     * @param bool $success
     */
    private function log($success)
    {
        if ($success) {
            $this->stdout('Success!', Console::FG_GREEN, Console::BOLD);
        } else {
            $this->stderr('Error!', Console::FG_RED, Console::BOLD);
        }
        echo PHP_EOL;
    }
}