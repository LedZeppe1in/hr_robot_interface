<?php

namespace app\commands;

use Yii;
use stdClass;
use Exception;
use SoapClient;
use yii\helpers\Console;
use yii\console\Controller;
use vova07\console\ConsoleRunner;
use app\components\OSConnector;
use app\components\AnalysisHelper;
use app\components\FacialFeatureDetector;
use app\components\AnalysisHelperExperiment;
use app\modules\main\models\Landmark;
use app\modules\main\models\Question;
use app\modules\main\models\FinalResult;
use app\modules\main\models\TopicQuestion;
use app\modules\main\models\ModuleMessage;
use app\modules\main\models\ProfileSurvey;
use app\modules\main\models\SurveyQuestion;
use app\modules\main\models\VideoInterview;
use app\modules\main\models\AnalysisResult;
use app\modules\main\models\ProfileKnowledgeBase;
use app\modules\main\models\CalibrationConclusion;
use app\modules\main\models\QuestionProcessingStatus;
use app\modules\main\models\VideoInterviewProcessingStatus;
use app\modules\main\models\VideoProcessingModuleSettingForm;

/**
 * VideoInterviewAnalysisController - класс содержит команду для последовательного анализа видеоинтервью.
 * @package app\commands
 */
class VideoInterviewAnalysisController extends Controller
{
    /**
     * Инициализация команд.
     */
    public function actionIndex()
    {
        echo 'yii video-interview-analysis/preparation' . PHP_EOL;
        echo 'yii video-interview-analysis/start-full-video-analysis' . PHP_EOL;
        echo 'yii video-interview-analysis/start-base-frame-detection' . PHP_EOL;
        echo 'yii video-interview-analysis/start-video-processing' . PHP_EOL;
        echo 'yii video-interview-analysis/start-video-interview-analysis' . PHP_EOL;
        echo 'yii video-interview-analysis/start-calibration-questions-processing' . PHP_EOL;
    }

    /**
     * Команда запуска анализа ответов респондента на калибровочные вопросы.
     *
     * @param $questionId - идентификатор вопроса видеоинтервью
     * @param $landmarkId - идентификатор цифровой маски
     * @param $topicId - идентификатор темы
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionPreparation($questionId, $landmarkId, $topicId)
    {
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
        // Сохранение файла видео ответа на вопрос на сервер
        $osConnector->saveFileToServer(
            OSConnector::OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET,
            $question->id,
            $question->video_file_name,
            $videoPath
        );
        // Название видео-файла с результатами обработки видео
        $videoResultFile = 'out_' . $question->id . '.avi';
        // Название json-файла с результатами обработки видео
        $jsonResultFile = 'out_' . $question->id . '.json';
        // Название аудио-файла (mp3) с результатами обработки видео
        $audioResultFile = 'out_' . $question->id . '.mp3';
        // Формирование массива с параметрами запуска программы обработки видео
        $parameters['nameVidFilesIn'] = 'video/' . $question->video_file_name;
        $parameters['nameVidFilesOut'] = 'json/out_{}.avi';
        $parameters['nameJsonFilesOut'] = 'json/out_{}.json';
        $parameters['nameAudioFilesOut'] = 'json/out_{}.mp3';
        $parameters['indexesTriagnleStats'] = [[21, 22, 28], [31, 48, 74], [31, 40, 74], [35, 54, 75],
            [35, 47, 75], [27, 35, 42], [27, 31, 39]];
        $parameters['rotate_mode'] = VideoProcessingModuleSettingForm::ROTATE_MODE_ZERO;
        $parameters['enableAutoRotate'] = VideoProcessingModuleSettingForm::AUTO_ROTATE_TRUE;
        $parameters['Mirroring'] = VideoProcessingModuleSettingForm::MIRRORING_FALSE;
        $parameters['AlignMode'] = VideoProcessingModuleSettingForm::ALIGN_MODE_BY_THREE_FACIAL_POINTS;
        $parameters['id'] = $question->id;
        $parameters['landmark_mode'] = VideoProcessingModuleSettingForm::LANDMARK_MODE_EXPRESS;
        if ((int)$topicId == 27)
            $parameters['parameters'] = VideoProcessingModuleSettingForm::PARAMETER_CHECK_VIDEO_PARAMETERS;
        if ((int)$topicId == 24 || (int)$topicId == 25)
            $parameters['parameters'] = VideoProcessingModuleSettingForm::PARAMETER_CHECK_ALL_VIDEO_DATA;
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
        } else {
            $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_IN_PROGRESS;
            $videoInterviewProcessingStatus->updateAttributes(['status']);
            $questionProcessingStatusModel->video_interview_processing_status_id = $videoInterviewProcessingStatus->id;
        }
        // Сохранение модели статуса обработки вопроса в БД
        $questionProcessingStatusModel->save();
        try {
            // Запуск программы обработки видео Ивана
            chdir($mainPath);
            exec('./venv/bin/python ./main_new.py ./test' . $question->id . '.json');
        } catch (Exception $e) {
            // Создание сообщения об ошибке МОВ Ивана в БД
            $moduleMessageModel = new ModuleMessage();
            $moduleMessageModel->message = 'Ошибка модуля обработки видео Ивана! ' . $e->getMessage();
            $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
            $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
            $moduleMessageModel->save();
        }

        $firstScriptSuccess = false;
        // Формирование названия json-файла с результатами обработки видео
        $landmark->landmark_file_name = $jsonResultFile;
        // Формирование названия видео-файла с нанесенной цифровой маской
        $landmark->processed_video_file_name = $videoResultFile;
        // Формирование описания цифровой маски
        $landmark->description = $videoInterview->description . ' (время нарезки: ' .
            $landmark->getStartTime() . ' - ' . $landmark->getFinishTime() . ')';
        // Обновление атрибутов цифровой маски в БД
        $landmark->updateAttributes(['landmark_file_name', 'processed_video_file_name', 'description']);
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
            // Сохранение файла видео с нанесенной цифровой маской на Object Storage
            $osConnector->saveFileToObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $landmark->id,
                $landmark->processed_video_file_name,
                $jsonResultPath . $landmark->processed_video_file_name
            );
            // Декодирование json-файла с результатами обработки видео в виде цифровой маски
            $jsonLandmarkFile = json_decode($landmarkFile, true);
            // Если в json-файле с цифровой маской есть текст с предупреждением
            if (isset($jsonLandmarkFile['err_msg'])) {
                // Создание сообщения о предупреждении МОВ Ивана в БД
                $moduleMessageModel = new ModuleMessage();
                $moduleMessageModel->message = $jsonLandmarkFile['err_msg'];
                $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
                $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
                $moduleMessageModel->save();
            }
            $firstScriptSuccess = true;
        }
        // Удаление записи о цифровой маски для которой не сформирован json-файл
        if ($firstScriptSuccess == false) {
            Landmark::findOne($landmark->id)->delete();
            // Создание сообщения о не созданной цифровой маски
            $moduleMessageModel = new ModuleMessage();
            $moduleMessageModel->message = 'Не удалось сформировать цифровую маску!';
            $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
            $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
            $moduleMessageModel->save();
        }
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

        $secondScriptSuccess = false;
        // Название видео-файла с результатами обработки видео
        $extVideoResultFile = 'out_' . $question->id . '_ext.avi';
        // Название json-файла с результатами обработки видео
        $extJsonResultFile = 'out_' . $question->id . '_ext.json';
        // Создание второй цифровой маски в БД
        $additionalLandmarkModel = new Landmark();
        $additionalLandmarkModel->start_time = '00:00:00:000';
        $additionalLandmarkModel->finish_time = '12:00:00:000';
        $additionalLandmarkModel->type = Landmark::TYPE_LANDMARK_IVAN_MODULE;
        $additionalLandmarkModel->rotation = Landmark::TYPE_ZERO;
        $additionalLandmarkModel->mirroring = Landmark::TYPE_MIRRORING_FALSE;
        $additionalLandmarkModel->description = 'Цифровая маска получена на основе цифровой маски №' . $landmark->id;
        $additionalLandmarkModel->landmark_file_name = $extJsonResultFile;
        $additionalLandmarkModel->processed_video_file_name = $extVideoResultFile;
        $additionalLandmarkModel->question_id = $question->id;
        $additionalLandmarkModel->video_interview_id = $videoInterview->id;
        $additionalLandmarkModel->save();
        try {
            // Запуск второго скрипта модуля обработки видео Ивана
            chdir($mainPath);
            exec('./venv/bin/python ./main_new2.py ./json/' . $jsonResultFile);
        } catch (Exception $e) {
            // Создание сообщения об ошибке МОВ Ивана в БД
            $moduleMessageModel = new ModuleMessage();
            $moduleMessageModel->message = 'Ошибка второго скрипта модуля обработки видео Ивана! ' . $e->getMessage();
            $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
            $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
            $moduleMessageModel->save();
        }
        // Проверка существования json-файл с результатами обработки видео
        if (file_exists($jsonResultPath . $additionalLandmarkModel->landmark_file_name)) {
            // Получение json-файла с результатами обработки видео в виде цифровой маски
            $landmarkFile = file_get_contents($jsonResultPath .
                $additionalLandmarkModel->landmark_file_name, true);
            // Сохранение файла с лицевыми точками на Object Storage
            $osConnector->saveFileToObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $additionalLandmarkModel->id,
                $additionalLandmarkModel->landmark_file_name,
                $landmarkFile
            );
            // Сохранение файла видео с нанесенной цифровой маской на Object Storage
            $osConnector->saveFileToObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $additionalLandmarkModel->id,
                $additionalLandmarkModel->processed_video_file_name,
                $jsonResultPath . $additionalLandmarkModel->processed_video_file_name
            );
            // Декодирование json-файла с результатами обработки видео в виде цифровой маски
            $jsonLandmarkFile = json_decode($landmarkFile, true);
            // Если в json-файле с цифровой маской есть текст с предупреждением
            if (isset($jsonLandmarkFile['err_msg'])) {
                // Создание сообщения о предупреждении МОВ Ивана в БД
                $moduleMessageModel = new ModuleMessage();
                $moduleMessageModel->message = $jsonLandmarkFile['err_msg'];
                $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
                $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
                $moduleMessageModel->save();
            }
            $secondScriptSuccess = true;
        }
        // Удаление записи о цифровой маски для которой не сформирован json-файл
        if ($secondScriptSuccess == false) {
            Landmark::findOne($additionalLandmarkModel->id)->delete();
            // Создание сообщения о не созданной цифровой маски
            $moduleMessageModel = new ModuleMessage();
            $moduleMessageModel->message = 'Не удалось сформировать цифровую маску вторым скриптом МОВ Ивана!';
            $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
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
        // Удаление видео-файла с результатами обработки видеоинтервью вторым скриптом МОВ ИВана
        if (file_exists($jsonResultPath . $extVideoResultFile))
            unlink($jsonResultPath . $extVideoResultFile);
        // Удаление json-файла с результатами обработки видео вторым скриптом МОВ ИВана
        if (file_exists($jsonResultPath . $extJsonResultFile))
            unlink($jsonResultPath . $extJsonResultFile);

        $completed = true;
        // Поиск статуса обработки видеоинтервью по id видеоинтервью
        $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::find()
            ->where(['video_interview_id' => $videoInterview->id])
            ->one();
        // Поиск статусов обработки вопросов по id статуса обработки видеоинтервью
        $questionProcessingStatuses = QuestionProcessingStatus::find()
            ->where(['video_interview_processing_status_id' => $videoInterviewProcessingStatus->id])
            ->all();
        // Определение кол-ва записей статусов обработки вопросов по id статуса обработки видеоинтервью
        $questionProcessingStatusCount = QuestionProcessingStatus::find()
            ->where(['video_interview_processing_status_id' => $videoInterviewProcessingStatus->id])
            ->count();
        // Обход всех статусов обработки вопросов и определение завершенности каждого
        foreach ($questionProcessingStatuses as $questionProcessingStatus)
            if ($questionProcessingStatus->status != QuestionProcessingStatus::STATUS_COMPLETED)
                $completed = false;
        // Если анализ всех видео ответов на калибровочные вопросы завершен
        if ($completed && $questionProcessingStatusCount == 3) {
            // Обновление атрибутов статуса обработки видеоинтервью в БД
            $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_COMPLETED;
            $videoInterviewProcessingStatus->updateAttributes(['status']);
        }
        // Если анализ видео ответов на вопросы завершен, но есть видео не по всем калибровочным вопросам
        if ($completed && $questionProcessingStatusCount < 3) {
            // Обновление атрибутов статуса обработки видеоинтервью в БД
            $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_REJECTION;
            $videoInterviewProcessingStatus->updateAttributes(['status']);
        }
    }

//    /**
//     * Команда запуска полного анализа видео.
//     *
//     * @param $questionId - идентификатор вопроса видеоинтервью
//     * @param $landmarkId - идентификатор цифровой маски
//     * @throws \Throwable
//     * @throws \yii\db\StaleObjectException
//     */
//    public function actionStartFullVideoAnalysis($questionId, $landmarkId)
//    {
//        // Время начала выполнения анализа видеоинтервью
//        $videoInterviewProcessingStart = microtime(true);
//
//        // Поиск вопроса видеоинтервью по id
//        $question = Question::findOne((int)$questionId);
//        // Поиск цифровой маски по id
//        $landmark = Landmark::findOne((int)$landmarkId);
//        // Поиск полного видеоинтервью по id
//        $videoInterview = VideoInterview::findOne($landmark->video_interview_id);
//
//        // Поиск статуса обработки видеоинтервью по id видеоинтервью
//        $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::find()
//            ->where(['video_interview_id' => $videoInterview->id])
//            ->one();
//        // Обновление атрибута статуса обработки видеоинтервью в БД
//        $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_IN_PROGRESS;
//        $videoInterviewProcessingStatus->updateAttributes(['status']);
//
//        // Путь к программе обработки видео от Ивана
//        $mainPath = '/home/-Common/-ivan/';
//        // Путь к файлу видеоинтервью
//        $videoPath = $mainPath . 'video/';
//        // Путь к json-файлу результатов обработки видеоинтервью
//        $jsonResultPath = $mainPath . 'json/';
//        // Создание объекта коннектора с Yandex.Cloud Object Storage
//        $osConnector = new OSConnector();
//        // Сохранение файла видео ответа на вопрос на сервер
//        $osConnector->saveFileToServer(
//            OSConnector::OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET,
//            $question->id,
//            $question->video_file_name,
//            $videoPath
//        );
//        // Название видео-файла с результатами обработки видео
//        $videoResultFile = 'out_' . $question->id . '.avi';
//        // Название json-файла с результатами обработки видео
//        $jsonResultFile = 'out_' . $question->id . '.json';
//        // Название аудио-файла (mp3) с результатами обработки видео
//        $audioResultFile = 'out_' . $question->id . '.mp3';
//        // Формирование массива с параметрами запуска программы обработки видео
//        $parameters['nameVidFilesIn'] = 'video/' . $question->video_file_name;
//        $parameters['nameVidFilesOut'] = 'json/out_{}.avi';
//        $parameters['nameJsonFilesOut'] = 'json/out_{}.json';
//        $parameters['nameAudioFilesOut'] = 'json/out_{}.mp3';
//        $parameters['indexesTriagnleStats'] = [[21, 22, 28], [31, 48, 74], [31, 40, 74], [35, 54, 75],
//            [35, 47, 75], [27, 35, 42], [27, 31, 39]];
//        $parameters['rotate_mode'] = VideoProcessingModuleSettingForm::ROTATE_MODE_ZERO;
//        $parameters['enableAutoRotate'] = VideoProcessingModuleSettingForm::AUTO_ROTATE_TRUE;
//        $parameters['Mirroring'] = $landmark->mirroring;
//        $parameters['AlignMode'] = VideoProcessingModuleSettingForm::ALIGN_MODE_BY_THREE_FACIAL_POINTS;
//        $parameters['id'] = $question->id;
//        $parameters['landmark_mode'] = VideoProcessingModuleSettingForm::LANDMARK_MODE_FAST;
//        $parameters['parameters'] = VideoProcessingModuleSettingForm::PARAMETER_CHECK_ALL_VIDEO_DATA;
//        // Формирование json-строки на основе массива с параметрами запуска программы обработки видео
//        $jsonParameters = json_encode($parameters, JSON_UNESCAPED_UNICODE);
//        // Открытие файла на запись для сохранения параметров запуска программы обработки видео
//        $jsonFile = fopen($mainPath . 'test' . $question->id . '.json', 'a');
//        // Запись в файл json-строки с параметрами запуска программы обработки видео
//        fwrite($jsonFile, str_replace("\\", "", $jsonParameters));
//        // Закрытие файла
//        fclose($jsonFile);
//        // Создание модели статуса обработки вопроса
//        $questionProcessingStatusModel = new QuestionProcessingStatus();
//        $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_IVAN_VIDEO_PROCESSING_MODULE_IN_PROGRESS;
//        $questionProcessingStatusModel->question_id = $question->id;
//        $questionProcessingStatusModel->video_interview_processing_status_id = $videoInterviewProcessingStatus->id;
//        $questionProcessingStatusModel->save();
//        // Время начала выполнения МОВ Ивана
//        $ivanVideoAnalysisStart = microtime(true);
//        try {
//            // Запуск программы обработки видео Ивана
//            chdir($mainPath);
//            exec('./venv/bin/python ./main_new.py ./test' . $question->id . '.json');
//        } catch (Exception $e) {
//            // Создание сообщения об ошибке МОВ Ивана в БД
//            $moduleMessageModel = new ModuleMessage();
//            $moduleMessageModel->message = 'Ошибка модуля обработки видео Ивана! ' . $e->getMessage();
//            $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
//            $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
//            $moduleMessageModel->save();
//        }
//
////        // Название json-файла с результатами обработки видео
////        $extJsonResultFile = 'out_' . $question->id . '_ext.json';
////        try {
////            // Запуск второго скрипта модуля обработки видео Ивана
////            chdir($mainPath);
////            exec('./venv/bin/python ./main_new2.py ./json/' . $jsonResultFile);
////        } catch (Exception $e) {
////            // Создание сообщения об ошибке МОВ Ивана в БД
////            $moduleMessageModel = new ModuleMessage();
////            $moduleMessageModel->message = 'Ошибка второго скрипта модуля обработки видео Ивана! ' . $e->getMessage();
////            $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
////            $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
////            $moduleMessageModel->save();
////        }
////        // Проверка существования json-файл с результатами обработки видео
////        if (file_exists($jsonResultPath . $extJsonResultFile)) {
////            // Получение json-файла с результатами обработки видео в виде цифровой маски
////            $jsonLandmarkFile = file_get_contents($jsonResultPath . $extJsonResultFile, true);
////            // Замена в строке некорректных значений для правильного декодирования json-формата
////            $jsonLandmarkFile = str_ireplace('NaN','99999', $jsonLandmarkFile);
////            // Декодирование json-файла с результатами обработки видео в виде цифровой маски
////            $landmarkFile = json_decode($jsonLandmarkFile, true);
////
////            // Определение кол-ва событий поворотов головы вправо и влево
////            foreach ($landmarkFile as $key => $value)
////                if (strpos(Trim($key), 'frame_') !== false)
////                    if (isset($value['EVENTS']))
////                        foreach ($value['EVENTS'] as $event) {
////                            if ($event == VideoProcessingModuleSettingForm::TURN_RIGHT_EVENT)
////                                $turnRightNumber++;
////                            if ($event == VideoProcessingModuleSettingForm::TURN_LEFT_EVENT)
////                                $turnLeftNumber++;
////                        }
////        }
//
//        // Время окончания выполнения МОВ Ивана
//        $ivanVideoAnalysisEnd = microtime(true);
//        // Вычисление времени выполнения МОВ Ивана
//        $ivanVideoAnalysisRuntime = $ivanVideoAnalysisEnd - $ivanVideoAnalysisStart;
//        // Обновление атрибута времени выполнения МОВ Ивана в БД
//        $questionProcessingStatusModel->ivan_video_analysis_runtime = $ivanVideoAnalysisRuntime;
//        $questionProcessingStatusModel->updateAttributes(['ivan_video_analysis_runtime']);
//
//        $success = false;
//        $analysisResultId = '';
//        $analysisResultIds = '';
//        // Формирование названия json-файла с результатами обработки видео
//        $landmark->landmark_file_name = $jsonResultFile;
//        // Формирование названия видео-файла с нанесенной цифровой маской
//        $landmark->processed_video_file_name = $videoResultFile;
//        // Формирование описания цифровой маски
//        $landmark->description = $videoInterview->description . ' (время нарезки: ' .
//            $landmark->getStartTime() . ' - ' . $landmark->getFinishTime() . ')';
//        // Обновление атрибутов цифровой маски в БД
//        $landmark->updateAttributes(['landmark_file_name', 'processed_video_file_name', 'description']);
//        // Проверка существования json-файл с результатами обработки видео
//        if (file_exists($jsonResultPath . $landmark->landmark_file_name)) {
//            // Получение json-файла с результатами обработки видео в виде цифровой маски
//            $landmarkFile = file_get_contents($jsonResultPath .
//                $landmark->landmark_file_name, true);
//            // Сохранение файла с лицевыми точками на Object Storage
//            $osConnector->saveFileToObjectStorage(
//                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
//                $landmark->id,
//                $landmark->landmark_file_name,
//                $landmarkFile
//            );
//            // Сохранение файла видео с нанесенной цифровой маской на Object Storage
//            $osConnector->saveFileToObjectStorage(
//                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
//                $landmark->id,
//                $landmark->processed_video_file_name,
//                $jsonResultPath . $landmark->processed_video_file_name
//            );
//            // Обновление атрибута статуса обработки вопроса в БД
//            $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_FEATURE_DEFINITION_MODULE_IN_PROGRESS;
//            $questionProcessingStatusModel->updateAttributes(['status']);
//            // Время начала выполнения МОП
//            $featureDetectionStart = microtime(true);
//            try {
//                // Получение рузультатов анализа видеоинтервью (обработка модулем определения признаков)
//                $analysisHelper = new AnalysisHelper();
//                $baseFrame = $analysisHelper->getBaseFrame($landmark->video_interview_id);
//                $analysisResultId = $analysisHelper->getAnalysisResult(
//                    $landmark,
//                    VideoInterview::TYPE_NORMALIZED_POINTS,
//                    $baseFrame
//                );
//            } catch (Exception $e) {
//                // Создание сообщения об ошибке МОП в БД
//                $moduleMessageModel = new ModuleMessage();
//                $moduleMessageModel->message = 'Ошибка МОП на данных Ивана! ' . $e->getMessage();
//                $moduleMessageModel->module_name = ModuleMessage::FEATURE_DETECTION_MODULE;
//                $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
//                $moduleMessageModel->save();
//            }
//            // Время окончания выполнения МОП
//            $featureDetectionEnd = microtime(true);
//            // Вычисление времени выполнения МОП
//            $featureDetectionRuntime = $featureDetectionEnd - $featureDetectionStart;
//            // Обновление атрибута времени выполнения МОП в БД
//            $questionProcessingStatusModel->feature_detection_runtime = $featureDetectionRuntime;
//            $questionProcessingStatusModel->updateAttributes(['feature_detection_runtime']);
//            // Формирование строки из всех id результатов анализа
//            if ($analysisResultIds == '')
//                $analysisResultIds = $analysisResultId;
//            else
//                $analysisResultIds .= ', ' . $analysisResultId;
//            // Декодирование json-файла с результатами обработки видео в виде цифровой маски
//            $jsonLandmarkFile = json_decode($landmarkFile, true);
//            // Если в json-файле с цифровой маской есть текст с предупреждением
//            if (isset($jsonLandmarkFile['err_msg'])) {
//                // Создание сообщения о предупреждении МОВ Ивана в БД
//                $moduleMessageModel = new ModuleMessage();
//                $moduleMessageModel->message = $jsonLandmarkFile['err_msg'];
//                $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
//                $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
//                $moduleMessageModel->save();
//            }
//            $success = true;
//        }
//        // Удаление записи о цифровой маски для которой не сформирован json-файл
//        if ($success == false)
//            Landmark::findOne($landmark->id)->delete();
////        // Текст сообщения об ошибке
////        $errorMessage = 'Не удалось проанализировать видеоинтервью!';
////        // Проверка существования json-файл с ошибками обработки видеоинтервью в корневой папке
////        if (file_exists($mainPath . 'error.json')) {
////            // Получение json-файл с ошибками обработки видеоинтервью
////            $jsonErrorFile = file_get_contents($mainPath . 'error.json', true);
////            // Декодирование json
////            $jsonErrorFile = json_decode($jsonErrorFile, true);
////            // Дополнение текста сообщения об ошибке
////            $errorMessage .= PHP_EOL . $jsonErrorFile['err_msg'];
////            // Удаление json-файла с сообщением ошибке
////            unlink($mainPath . 'error.json');
////        }
////        // Проверка существования json-файл с ошибками обработки видеоинтервью в папке json
////        if (file_exists($jsonResultPath . 'out_error.json')) {
////            // Получение json-файл с ошибками обработки видеоинтервью
////            $jsonErrorFile = file_get_contents($jsonResultPath . 'out_error.json', true);
////            // Декодирование json
////            $jsonErrorFile = json_decode($jsonErrorFile, true);
////            // Дополнение текста сообщения об ошибке
////            $errorMessage .= PHP_EOL . $jsonErrorFile['err_msg'];
////            // Удаление json-файла с сообщением ошибке
////            unlink($jsonResultPath . 'out_error.json');
////        }
//
//        // Путь к программе обработки видео от Андрея
//        $mainAndrewModulePath = '/home/-Common/-andrey/';
//        // Путь к json-файлу результатов обработки видеоинтервью от Андрея
//        $jsonAndrewResultPath = $mainAndrewModulePath . 'Records/';
//        // Обновление атрибута статуса обработки вопроса в БД
//        $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_ANDREY_VIDEO_PROCESSING_MODULE_IN_PROGRESS;
//        $questionProcessingStatusModel->updateAttributes(['status']);
//        // Время начала выполнения МОВ Андрея
//        $andreyVideoAnalysisStart = microtime(true);
//        try {
//            // Запуск программы обработки видео Андрея
//            chdir($mainAndrewModulePath);
//            exec('./EmotionDetection -f ' . $videoPath . $question->video_file_name);
//            // Время окончания выполнения МОВ Андрея
//            $andreyVideoAnalysisEnd = microtime(true);
//            // Вычисление времени выполнения МОВ Андрея
//            $andreyVideoAnalysisRuntime = $andreyVideoAnalysisEnd - $andreyVideoAnalysisStart;
//            // Обновление атрибута времени выполнения МОВ Андрея в БД
//            $questionProcessingStatusModel->andrey_video_analysis_runtime = $andreyVideoAnalysisRuntime;
//            $questionProcessingStatusModel->updateAttributes(['andrey_video_analysis_runtime']);
//            // Получение имени файла без расширения
//            $jsonFileName = preg_replace('/\.\w+$/', '', $question->video_file_name);
//            // Проверка существования json-файл с результатами обработки видео
//            if (file_exists($jsonAndrewResultPath . $jsonFileName . '.json')) {
//                // Создание цифровой маски в БД
//                $landmarkModel = new Landmark();
//                $landmarkModel->landmark_file_name = $videoResultFile;
//                $landmarkModel->start_time = Landmark::formatMilliseconds($landmark->start_time);
//                $landmarkModel->finish_time = Landmark::formatMilliseconds($landmark->finish_time);
//                $landmarkModel->type = Landmark::TYPE_LANDMARK_ANDREW_MODULE;
//                $landmarkModel->rotation = Landmark::TYPE_ZERO;
//                $landmarkModel->mirroring = Landmark::TYPE_MIRRORING_FALSE;
//                $landmarkModel->description = $videoInterview->description . ' (время нарезки: ' .
//                    $landmarkModel->start_time . ' - ' . $landmarkModel->finish_time . ')';
//                $landmarkModel->question_id = $question->id;
//                $landmarkModel->video_interview_id = $videoInterview->id;
//                $landmarkModel->save();
//                // Получение json-файла с результатами обработки видео в виде цифровой маски
//                $landmarkFile = file_get_contents($jsonAndrewResultPath .
//                    $jsonFileName . '.json', true);
//                // Сохранение файла с лицевыми точками на Object Storage
//                $osConnector->saveFileToObjectStorage(
//                    OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
//                    $landmarkModel->id,
//                    $landmarkModel->landmark_file_name,
//                    $landmarkFile
//                );
//                // Обновление атрибута статуса обработки вопроса в БД
//                $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_FEATURE_DEFINITION_MODULE_IN_PROGRESS;
//                $questionProcessingStatusModel->updateAttributes(['status']);
//                // Время начала выполнения МОП
//                $featureDetectionStart = microtime(true);
//                try {
//                    // Получение рузультатов анализа видеоинтервью (обработка модулем определения признаков)
//                    $analysisHelper = new AnalysisHelper();
//                    $baseFrame = $analysisHelper->getBaseFrame($landmarkModel->video_interview_id);
//                    $analysisResultId = $analysisHelper->getAnalysisResult(
//                        $landmarkModel,
//                        VideoInterview::TYPE_RAW_POINTS,
//                        $baseFrame
//                    );
//                } catch (Exception $e) {
//                    // Создание сообщения об ошибке МОП в БД
//                    $moduleMessageModel = new ModuleMessage();
//                    $moduleMessageModel->message = 'Ошибка МОП на данных Андрея! ' . $e->getMessage();
//                    $moduleMessageModel->module_name = ModuleMessage::FEATURE_DETECTION_MODULE;
//                    $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
//                    $moduleMessageModel->save();
//                }
//                // Время окончания выполнения МОП
//                $featureDetectionEnd = microtime(true);
//                // Вычисление времени выполнения МОП
//                $featureDetectionRuntime = $featureDetectionEnd - $featureDetectionStart;
//                // Обновление атрибута времени выполнения МОП в БД
//                $questionProcessingStatusModel->feature_detection_runtime += $featureDetectionRuntime;
//                $questionProcessingStatusModel->updateAttributes(['feature_detection_runtime']);
//                // Формирование строки из всех id результатов анализа
//                if ($analysisResultIds == '')
//                    $analysisResultIds = $analysisResultId;
//                else
//                    $analysisResultIds .= ', ' . $analysisResultId;
//                // Удаление json-файла с результатами обработки видеоинтервью программой Андрея
//                unlink($jsonAndrewResultPath . $jsonFileName . '.json');
//            }
//        } catch (Exception $e) {
//            // Создание сообщения об ошибке МОВ Андрея в БД
//            $moduleMessageModel = new ModuleMessage();
//            $moduleMessageModel->message = 'Ошибка модуля обработки видео Андрея! ' . $e->getMessage();
//            $moduleMessageModel->module_name = ModuleMessage::FEATURE_DETECTION_MODULE;
//            $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
//            $moduleMessageModel->save();
//        }
//
//        // Если есть результаты определения признаков
//        if ($analysisResultIds != '')
//            try {
//                // Обновление атрибута статуса обработки вопроса в БД
//                $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_FEATURE_INTERPRETATION_MODULE_IN_PROGRESS;
//                $questionProcessingStatusModel->updateAttributes(['status']);
//                // Время начала выполнения МИП
//                $featureInterpretationStart = microtime(true);
//                // Запуск интерпретации признаков по результатам МОП (интерпретация первого уровня)
//                ini_set('default_socket_timeout', 60 * 30);
//                $addressOfRBRWebServiceDefinition = 'http://127.0.0.1:8888/RBRWebService?wsdl';
//                $client = new SoapClient($addressOfRBRWebServiceDefinition);
//                //$addressForCodeOfKnowledgeBaseRetrieval = 'https://84.201.129.65/knowledge-base/knowledge-base-download/1';
//                $addressForCodeOfKnowledgeBaseRetrieval = 'http://127.0.0.1/Drools/RetrieveData.php?DataSource=CodeOfKnowledgeBase&IDOfKnowledgeBase=1';
//                //$addressForInitialConditionsRetrieval = 'https://84.201.129.65/analysis-result/facts-download/';
//                $addressForInitialConditionsRetrieval = 'http://127.0.0.1/Drools/RetrieveData.php?DataSource=InitialDataOfReasoningProcess&ID=';
//                $idsOfInitialConditions = '[' . $analysisResultIds . ']';
//                //$addressToSendResults = 'https://84.201.129.65:9999/Drools/RetrieveData.php';
//                $addressToSendResults = 'http://127.0.0.1/Drools/RetrieveData.php';
//                $additionalDataToSend = new stdClass;
//                $additionalDataToSend->{'IDOfFile'} = Null;
//                $client->LaunchReasoningProcessForSetOfInitialConditions(array(
//                    'arg0' => $addressForCodeOfKnowledgeBaseRetrieval,
//                    'arg1' => $addressForInitialConditionsRetrieval,
//                    'arg2' => $idsOfInitialConditions,
//                    'arg3' => $addressToSendResults,
//                    'arg4' => 'ResultsOfReasoningProcess',
//                    'arg5' => 'IDOfFile',
//                    'arg6' => json_encode($additionalDataToSend)))->return;
//                $client = Null;
//                // Время окончания выполнения МИП
//                $featureInterpretationEnd = microtime(true);
//                // Вычисление времени выполнения МИП
//                $featureInterpretationRuntime = $featureInterpretationEnd - $featureInterpretationStart;
//                // Обновление атрибута времени выполнения МИП в БД
//                $questionProcessingStatusModel->feature_interpretation_runtime = $featureInterpretationRuntime;
//                $questionProcessingStatusModel->updateAttributes(['feature_interpretation_runtime']);
//            } catch (Exception $e) {
//                // Создание сообщения об ошибке МИП в БД
//                $moduleMessageModel = new ModuleMessage();
//                $moduleMessageModel->message = 'Ошибка МИП (первый уровень)! ' . $e->getMessage();
//                $moduleMessageModel->module_name = ModuleMessage::FEATURE_INTERPRETATION_MODULE;
//                $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
//                $moduleMessageModel->save();
//            }
//        // Обновление атрибута статуса обработки вопроса в БД
//        $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_COMPLETED;
//        $questionProcessingStatusModel->updateAttributes(['status']);
//
//        // Удаление файла с видеоинтервью
//        if (file_exists($videoPath . $question->video_file_name))
//            unlink($videoPath . $question->video_file_name);
//        // Удаление файла с параметрами запуска программы обработки видео
//        if (file_exists($mainPath . 'test' . $question->id . '.json'))
//            unlink($mainPath . 'test' . $question->id . '.json');
//        // Удаление файла с выходной аудио-информацией
//        //if (file_exists($mainPath . 'audio_out.mp3'))
//        //    unlink($mainPath . 'audio_out.mp3');
//        // Удаление видео-файла с результатами обработки видеоинтервью
//        if (file_exists($jsonResultPath . $videoResultFile))
//            unlink($jsonResultPath . $videoResultFile);
//        // Удаление json-файла с результатами обработки видео программой Ивана
//        if (file_exists($jsonResultPath . $jsonResultFile))
//            unlink($jsonResultPath . $jsonResultFile);
//        // Удаление аудио-файла с результатами обработки видео программой Ивана
//        if (file_exists($jsonResultPath . $audioResultFile))
//            unlink($jsonResultPath . $audioResultFile);
//
//        $completed = true;
//        // Поиск статусов обработки вопросов по id статуса обработки видеоинтервью
//        $questionProcessingStatuses = QuestionProcessingStatus::find()
//            ->where(['video_interview_processing_status_id' => $videoInterviewProcessingStatus->id])
//            ->all();
//        // Обход всех статусов обработки вопросов и определение завершенности каждого
//        foreach ($questionProcessingStatuses as $questionProcessingStatus)
//            if ($questionProcessingStatus->status != QuestionProcessingStatus::STATUS_COMPLETED)
//                $completed = false;
//        // Если анализ всех видео ответов на вопросы завершен
//        if ($completed) {
//            // Обновление атрибута статуса обработки видеоинтервью в БД
//            $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_FINAL_RESULT_FORMATION;
//            $videoInterviewProcessingStatus->updateAttributes(['status']);
//            // Время начала выполнения МИП
//            $emotionInterpretationStart = microtime(true);
//            // Поиск итоговых результатов по id видеоинтервью
//            $finalResult = FinalResult::find()->where(['video_interview_id' => $videoInterview->id])->one();
//            // Создание модели заключения по видеоинтервью
//            $finalConclusionModel = new FinalConclusion();
//            // Установка первичного ключа с итогового результата
//            $finalConclusionModel->id = $finalResult->id;
//            // Сохранение модели заключения по видеоинтервью
//            $finalConclusionModel->save();
//            try {
//                // Запуск вывода по результатам интерпретации признаков (интерпретация второго уровня)
//                ini_set('default_socket_timeout', 60 * 30);
//                $addressOfRBRWebServiceDefinition = 'http://127.0.0.1:8888/RBRWebService?wsdl';
//                $client = new SoapClient($addressOfRBRWebServiceDefinition);
//                //$addressForCodeOfKnowledgeBaseRetrieval = 'https://84.201.129.65/knowledge-base/knowledge-base-download/2';
//                $addressForCodeOfKnowledgeBaseRetrieval = 'http://127.0.0.1/Drools/RetrieveData.php?DataSource=CodeOfKnowledgeBase&IDOfKnowledgeBase=2';
//                //$addressForInitialConditionsRetrieval = 'https://84.201.129.65/analysis-result/interpretation-facts-download/' . $finalConclusionModel->id;
//                $addressForInitialConditionsRetrieval = 'http://127.0.0.1/Drools/RetrieveData.php?DataSource=InitialDataOfReasoningProcess&Level=2&ID=' .
//                    $finalConclusionModel->id;
//                //$addressToSendResults = 'https://84.201.129.65:9999/Drools/RetrieveData.php';
//                $addressToSendResults = 'http://127.0.0.1/Drools/RetrieveData.php';
//                $additionalDataToSend = new stdClass;
//                $additionalDataToSend -> {'IDOfFile'} = $finalConclusionModel->id;
//                $additionalDataToSend -> {'Type'} = 'Interpretation Level II';
//                $client->LaunchReasoningProcessAndSendResultsToURL(array(
//                    'arg0' => $addressForCodeOfKnowledgeBaseRetrieval,
//                    'arg1' => $addressForInitialConditionsRetrieval,
//                    'arg2' => $addressToSendResults,
//                    'arg3' => 'ResultsOfReasoningProcess',
//                    'arg4' => json_encode($additionalDataToSend)))->return;
//                $client = Null;
//            } catch (Exception $e) {
//                // Создание сообщения об ошибке МИП в БД
//                $moduleMessageModel = new ModuleMessage();
//                $moduleMessageModel->message = 'Ошибка МИП (второй уровень)! ' . $e->getMessage();
//                $moduleMessageModel->module_name = ModuleMessage::FEATURE_INTERPRETATION_MODULE;
//                $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
//                $moduleMessageModel->save();
//            }
//            // Время окончания выполнения МИП
//            $emotionInterpretationEnd = microtime(true);
//            // Вычисление времени выполнения МИП
//            $emotionInterpretationRuntime = $emotionInterpretationEnd - $emotionInterpretationStart;
//            // Время окончания выполнения анализа видеоинтервью
//            $videoInterviewProcessingEnd = microtime(true);
//            // Вычисление времени выполнения анализа видеоинтервью
//            $videoInterviewProcessingRuntime = $videoInterviewProcessingEnd - $videoInterviewProcessingStart;
//            // Обновление атрибутов статуса обработки видеоинтервью, полного времени анализа видеоинтервью и
//            // времени выполнения интерпретации эмоций (второй уровень интерпретации) в БД
//            $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_COMPLETED;
//            $videoInterviewProcessingStatus->all_runtime = $videoInterviewProcessingRuntime;
//            $videoInterviewProcessingStatus->emotion_interpretation_runtime = $emotionInterpretationRuntime;
//            $videoInterviewProcessingStatus->updateAttributes(['status', 'all_runtime',
//                'emotion_interpretation_runtime']);
//        }
//    }

    /**
     * Команда запуска анализа видео ответа на калибровочный вопрос видеоинтервью и формирование базового кадра.
     *
     * @param $videoInterviewId - идентификатор видеоинтервью
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionStartBaseFrameDetection($videoInterviewId)
    {
        // Время начала выполнения анализа видеоинтервью
        $videoInterviewProcessingStart = microtime(true);

        // Поиск видеоинтервью по id
        $videoInterview = VideoInterview::findOne((int)$videoInterviewId);

        // Поиск статуса обработки видеоинтервью по id видеоинтервью
        $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::find()
            ->where(['video_interview_id' => $videoInterview->id])
            ->one();
        // Обновление атрибута статуса и времени обработки видеоинтервью в БД
        $videoInterviewProcessingStatus->updated_at = time();
        $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_IN_PROGRESS;
        $videoInterviewProcessingStatus->all_runtime = $videoInterviewProcessingStart;
        $videoInterviewProcessingStatus->updateAttributes(['updated_at', 'status', 'all_runtime']);

        // Поиск всех видео ответов на вопросы для данного видеоинтервью
        $questions = Question::find()->where(['video_interview_id' => $videoInterview->id])->all();
        // Базовый кадр
        $baseFrame = null;
        // Переменные для отслеживания статуса работы МОВ и МОП
        $landmarkFileExists = false;
        $fdmResultFileExists = false;
        // Обход всех видео ответов на вопросы видеоинтервью
        foreach ($questions as $question) {
            // Поиск темы для вопроса - 27 (калибровочный для камеры)
            $topicQuestion = TopicQuestion::find()->where(['test_question_id' => $question->test_question_id])->one();
            // Если есть видео ответ на калибровочный вопрос (27 - посмотрите в камеру)
            if (!empty($topicQuestion) && $topicQuestion->topic_id == 27) {
                // Поиск цифровой маски по калибровочному вопросу, сформированной во время записи видеоинтервью
                $landmarkModel = Landmark::find()->where(['question_id' => $question->id])->one();
                // Если цифровой маски нет
                if (empty($landmarkModel)) {
                    // Создание цифровой маски в БД
                    $landmarkModel = new Landmark();
                    $landmarkModel->start_time = '00:00:00:000';
                    $landmarkModel->finish_time = '12:00:00:000';
                    $landmarkModel->type = Landmark::TYPE_LANDMARK_IVAN_MODULE;
                    $landmarkModel->rotation = Landmark::TYPE_ZERO;             // TODO - по-умолчанию нет поворота
                    $landmarkModel->mirroring = Landmark::TYPE_MIRRORING_FALSE; // TODO - по-умолчанию нет зеркаливания
                    $landmarkModel->question_id = $question->id;
                    $landmarkModel->video_interview_id = $videoInterview->id;
                    $landmarkModel->save();
                }
                // Путь к программе обработки видео от Ивана
                $mainPath = '/home/-Common/-ivan/';
                // Путь к файлу видеоинтервью
                $videoPath = $mainPath . 'video/';
                // Путь к json-файлу результатов обработки видеоинтервью
                $jsonResultPath = $mainPath . 'json/';
                // Создание объекта коннектора с Yandex.Cloud Object Storage
                $osConnector = new OSConnector();
                // Сохранение файла видео ответа на вопрос на сервер
                $osConnector->saveFileToServer(
                    OSConnector::OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET,
                    $question->id,
                    $question->video_file_name,
                    $videoPath
                );
                // Название видео-файла с результатами обработки видео
                $videoResultFile = 'out_' . $question->id . '.avi';
                // Название json-файла с результатами обработки видео
                $jsonResultFile = 'out_' . $question->id . '.json';
                // Название аудио-файла (mp3) с результатами обработки видео
                $audioResultFile = 'out_' . $question->id . '.mp3';
                // Формирование массива с параметрами запуска программы обработки видео
                $parameters['nameVidFilesIn'] = 'video/' . $question->video_file_name;
                $parameters['nameVidFilesOut'] = 'json/out_{}.avi';
                $parameters['nameJsonFilesOut'] = 'json/out_{}.json';
                $parameters['nameAudioFilesOut'] = 'json/out_{}.mp3';
                $parameters['indexesTriagnleStats'] = [[21, 22, 28], [31, 48, 74], [31, 40, 74], [35, 54, 75],
                    [35, 47, 75], [27, 35, 42], [27, 31, 39]];
                $parameters['rotate_mode'] = $landmarkModel->rotation;
                $parameters['enableAutoRotate'] = VideoProcessingModuleSettingForm::AUTO_ROTATE_FALSE;
                $parameters['Mirroring'] = $landmarkModel->mirroring;
                $parameters['AlignMode'] = VideoProcessingModuleSettingForm::ALIGN_MODE_BY_THREE_FACIAL_POINTS;
                $parameters['id'] = $question->id;
                $parameters['landmark_mode'] = VideoProcessingModuleSettingForm::LANDMARK_MODE_FAST;
                $parameters['parameters'] = VideoProcessingModuleSettingForm::PARAMETER_CHECK_ALL_VIDEO_DATA;
                // Формирование json-строки на основе массива с параметрами запуска программы обработки видео
                $jsonParameters = json_encode($parameters, JSON_UNESCAPED_UNICODE);
                // Открытие файла на запись для сохранения параметров запуска программы обработки видео
                $jsonFile = fopen($mainPath . 'test' . $question->id . '.json', 'a');
                // Запись в файл json-строки с параметрами запуска программы обработки видео
                fwrite($jsonFile, str_replace("\\", "", $jsonParameters));
                // Закрытие файла
                fclose($jsonFile);
                // Поиск статуса обработки вопроса для данного видеоинтервью
                $questionProcessingStatus = QuestionProcessingStatus::find()
                    ->where([
                        'video_interview_processing_status_id' => $videoInterviewProcessingStatus->id,
                        'question_id' => $question->id
                    ])
                    ->one();
                // Если статус обработки для данного вопроса не существует
                if (empty($questionProcessingStatus)) {
                    // Создание модели статуса обработки вопроса
                    $questionProcessingStatus = new QuestionProcessingStatus();
                    $questionProcessingStatus->status = QuestionProcessingStatus::STATUS_IVAN_VIDEO_PROCESSING_MODULE_IN_PROGRESS;
                    $questionProcessingStatus->question_id = $question->id;
                    $questionProcessingStatus->video_interview_processing_status_id = $videoInterviewProcessingStatus->id;
                    $questionProcessingStatus->save();
                } else {
                    // Обновление статуса обработки вопроса для данного видео
                    $questionProcessingStatus->updated_at = time();
                    $questionProcessingStatus->status = QuestionProcessingStatus::STATUS_IVAN_VIDEO_PROCESSING_MODULE_IN_PROGRESS;
                    $questionProcessingStatus->updateAttributes(['updated_at', 'status']);
                    // Удаление всех сообщений для данного состояния процесса обработки вопроса
                    $moduleMessages = ModuleMessage::find()
                        ->where(['question_processing_status_id' => $questionProcessingStatus->id])
                        ->all();
                    foreach ($moduleMessages as $moduleMessage)
                        $moduleMessage->delete();
                }
                // Время начала выполнения МОВ Ивана
                $ivanVideoAnalysisStart = microtime(true);
                try {
                    // Запуск программы обработки видео Ивана
                    chdir($mainPath);
                    exec('./venv/bin/python ./main_new.py ./test' . $question->id . '.json');
                } catch (Exception $e) {
                    // Создание сообщения об ошибке МОВ Ивана в БД
                    $moduleMessageModel = new ModuleMessage();
                    $moduleMessageModel->message = 'Ошибка модуля обработки видео Ивана! ' . $e->getMessage();
                    $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
                    $moduleMessageModel->question_processing_status_id = $questionProcessingStatus->id;
                    $moduleMessageModel->save();
                }
                // Время окончания выполнения МОВ Ивана
                $ivanVideoAnalysisEnd = microtime(true);
                // Вычисление времени выполнения МОВ Ивана
                $ivanVideoAnalysisRuntime = $ivanVideoAnalysisEnd - $ivanVideoAnalysisStart;
                // Обновление атрибута времени выполнения МОВ Ивана в БД
                $questionProcessingStatus->ivan_video_analysis_runtime = $ivanVideoAnalysisRuntime;
                $questionProcessingStatus->updateAttributes(['ivan_video_analysis_runtime']);

                // Обновление времени редактирования записи в БД
                $landmarkModel->updated_at = time();
                // Формирование названия json-файла с результатами обработки видео
                $landmarkModel->landmark_file_name = $jsonResultFile;
                // Формирование названия видео-файла с нанесенной цифровой маской
                $landmarkModel->processed_video_file_name = $videoResultFile;
                // Обновление атрибутов цифровой маски в БД
                $landmarkModel->updateAttributes(['updated_at', 'landmark_file_name', 'processed_video_file_name']);
                // Проверка существования json-файл с результатами обработки видео
                if (file_exists($jsonResultPath . $landmarkModel->landmark_file_name)) {
                    // Получение json-файла с результатами обработки видео в виде цифровой маски
                    $landmarkFile = file_get_contents($jsonResultPath .
                        $landmarkModel->landmark_file_name, true);
                    // Сохранение файла с лицевыми точками на Object Storage
                    $osConnector->saveFileToObjectStorage(
                        OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                        $landmarkModel->id,
                        $landmarkModel->landmark_file_name,
                        $landmarkFile
                    );
                    // Сохранение файла видео с нанесенной цифровой маской на Object Storage
                    $osConnector->saveFileToObjectStorage(
                        OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                        $landmarkModel->id,
                        $landmarkModel->processed_video_file_name,
                        $jsonResultPath . $landmarkModel->processed_video_file_name
                    );
                    // Время начала выполнения МОП
                    $featureDetectionStart = microtime(true);
                    // Обновление атрибута статуса обработки вопроса в БД
                    $questionProcessingStatus->status = QuestionProcessingStatus::STATUS_FEATURE_DEFINITION_MODULE_IN_PROGRESS;
                    $questionProcessingStatus->updateAttributes(['status']);
                    try {
                        // Создание объекта AnalysisHelper
                        $analysisHelper = new AnalysisHelper();
                        // Определение базового кадра для видеоинтервью
                        list($resultExist, $baseFrame) = $analysisHelper->getBaseFrame($videoInterview->id, null);
                        // Если базовый кадр определен
                        if ($resultExist && isset($baseFrame)) {
                            // Получение рузультатов анализа видеоинтервью (обработка модулем определения признаков)
                            // по новому методу МОП
                            $analysisHelper->getAnalysisResult(
                                $landmarkModel,
                                2, // Задание определения признаков по новому МОП
                                $baseFrame,
                                AnalysisHelper::NEW_FDM,
                                null
                            );
                            $fdmResultFileExists = true;
                        } else {
                            // Создание сообщения об ошибке определения базового кадра в БД
                            $moduleMessageModel = new ModuleMessage();
                            $moduleMessageModel->message = 'МОП не смог сформировать базовый кадр! Текст ошибки: ' .
                                $baseFrame;
                            $moduleMessageModel->module_name = ModuleMessage::FEATURE_DETECTION_MODULE;
                            $moduleMessageModel->question_processing_status_id = $questionProcessingStatus->id;
                            $moduleMessageModel->save();
                            $baseFrame = null;
                        }
                    } catch (Exception $e) {
                        // Создание сообщения об ошибке МОП в БД
                        $moduleMessageModel = new ModuleMessage();
                        $moduleMessageModel->message = 'Ошибка МОП на данных Ивана! ' . $e->getMessage();
                        $moduleMessageModel->module_name = ModuleMessage::FEATURE_DETECTION_MODULE;
                        $moduleMessageModel->question_processing_status_id = $questionProcessingStatus->id;
                        $moduleMessageModel->save();
                    }
                    // Время окончания выполнения МОП
                    $featureDetectionEnd = microtime(true);
                    // Вычисление времени выполнения МОП
                    $featureDetectionRuntime = $featureDetectionEnd - $featureDetectionStart;
                    // Обновление атрибута времени выполнения МОП в БД
                    $questionProcessingStatus->feature_detection_runtime = $featureDetectionRuntime;
                    $questionProcessingStatus->updateAttributes(['feature_detection_runtime']);
                    // Декодирование json-файла с результатами обработки видео в виде цифровой маски
                    $jsonLandmarkFile = json_decode($landmarkFile, true);
                    // Если в json-файле с цифровой маской есть текст с предупреждением
                    if (isset($jsonLandmarkFile['err_msg'])) {
                        // Создание сообщения о предупреждении МОВ Ивана в БД
                        $moduleMessageModel = new ModuleMessage();
                        $moduleMessageModel->message = $jsonLandmarkFile['err_msg'];
                        $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
                        $moduleMessageModel->question_processing_status_id = $questionProcessingStatus->id;
                        $moduleMessageModel->save();
                    }
                    $landmarkFileExists = true;
                }

                // Обновление атрибута статуса обработки вопроса в БД
                $questionProcessingStatus->status = QuestionProcessingStatus::STATUS_COMPLETED;
                $questionProcessingStatus->updateAttributes(['updated_at', 'status']);

                // Если не сформирован json-файл или не был сформирован результат МОП или не был получен базовый кадр
                if ($landmarkFileExists == false || $fdmResultFileExists == false || $baseFrame == null) {
                    // Удаление записи о цифровой маски из БД
                    //Landmark::findOne($landmarkModel->id)->delete();
                    // Время окончания выполнения анализа видеоинтервью
                    $videoInterviewProcessingEnd = microtime(true);
                    // Вычисление времени выполнения анализа видеоинтервью
                    $videoInterviewProcessingRuntime = $videoInterviewProcessingEnd - $videoInterviewProcessingStart;
                    // Обновление атрибутов статуса обработки видеоинтервью, полного времени анализа видеоинтервью и
                    // времени выполнения интерпретации эмоций (второй уровень интерпретации) в БД
                    $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_PARTIALLY_COMPLETED;
                    $videoInterviewProcessingStatus->all_runtime = $videoInterviewProcessingRuntime;
                    $videoInterviewProcessingStatus->updateAttributes(['status', 'all_runtime']);
                }

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
            }
        }

        // Если сформирован json-файл, получен результат МОП и получен базовый кадр
        if ($landmarkFileExists && $fdmResultFileExists && isset($baseFrame)) {
            // Параметр наличия зеркаливания
            $mirroring = 0;
            // Параметры наличия поворота головы вправо и влево
            $turnRight = null;
            $turnLeft = null;
            // Обход всех видео ответов на вопросы для данного видеоинтервью
            foreach ($questions as $question) {
                // Поиск цифровых масок по определенному вопросу
                $landmarks = Landmark::find()
                    ->where(['question_id' => $question->id])
                    ->all();
                // Если цифровые маски по данному вопросу сформированы
                if (!empty($landmarks)) {
                    foreach ($landmarks as $landmark) {
                        // Поиск темы вопроса по id вопроса
                        $topicQuestion = TopicQuestion::find()
                            ->where(['test_question_id' => $question->test_question_id])
                            ->one();
                        // Создание объекта AnalysisHelper
                        $analysisHelper = new AnalysisHelper();
                        // Определение поворота головы, если калибровочный вопрос с темой 24 (поворот головы вправо)
                        if ($topicQuestion->topic_id == 24)
                            if (strripos($landmark->landmark_file_name, '_ext') !== false)
                                $turnRight = $analysisHelper->determineTurn($landmark);
                        // Определение поворота головы, если калибровочный вопрос с темой 25 (поворот головы влево)
                        if ($topicQuestion->topic_id == 25)
                            if (strripos($landmark->landmark_file_name, '_ext') !== false)
                                $turnLeft = $analysisHelper->determineTurn($landmark);
                    }
                }
            }
            // Если повороты определены
            if ($turnRight != null && $turnLeft != null)
                // Если заркаливание есть
                if ($turnRight != AnalysisHelper::TURN_RIGHT && $turnLeft != AnalysisHelper::TURN_LEFT)
                    $mirroring = 1;

            // Обход всех видео ответов на вопросы для данного видеоинтервью
            foreach ($questions as $question) {
                // Поиск темы для вопроса - 27 (калибровочный для камеры)
                $topicQuestion = TopicQuestion::find()->where(['test_question_id' => $question->test_question_id])->one();
                // Если тема для вопроса найдена
                if (!empty($topicQuestion))
                    // Если текущий вопрос не является калибровочным
                    if ($topicQuestion->topic_id != 24 && $topicQuestion->topic_id != 25 &&
                        $topicQuestion->topic_id != 27) {

                        // Поиск статусов обработки видео по id видео ответа на вопрос
                        $questionProcessingStatuses = QuestionProcessingStatus::find()
                            ->where(['question_id' => $question->id])
                            ->all();
                        // Удаление всех статусов видео по вопросам для данного вопроса
                        foreach ($questionProcessingStatuses as $questionProcessingStatus)
                            $questionProcessingStatus->delete();
                        // Поиск цифровых масок для данного вопроса видеоинтервью
                        $landmarks = Landmark::find()->where(['question_id' => $question->id])->all();
                        // Если цифровые маски для данного вопроса уже сформированы
                        if (!empty($landmarks))
                            foreach ($landmarks as $landmark) {
                                // Создание объекта AnalysisHelper
                                $analysisHelper = new AnalysisHelper();
                                // Удаление всех результатов анализа для данной цифровой маски на Object Storage
                                $analysisHelper->deleteAnalysisResultsInObjectStorage($landmark->id);
                                // Удаление цифровой маски на Object Storage
                                $analysisHelper->deleteLandmarkInObjectStorage($landmark);
                                // Удаление цифровой маски для данного видеоинтервью в БД
                                $landmark->delete();
                            }

//                        // Создание объекта запуска консольной команды
//                        $consoleRunner = new ConsoleRunner(['file' => '@app/yii']);
//                        // Выполнение команды анализа видео ответа на вопрос в фоновом режиме (этапы МОВ и МОП)
//                        $consoleRunner->run('video-interview-analysis/start-video-processing ' . $question->id .
//                            ' ' . $mirroring);

                        //
                        $analysisHelperExperiment = new AnalysisHelperExperiment();
                        $analysisHelperExperiment->startVideoProcessing($question->id, $mirroring);
                    }
            }
            //
            $completed = true;
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
                // Время окончания выполнения анализа видеоинтервью
                $videoInterviewProcessingEnd = microtime(true);
                // Вычисление времени выполнения анализа видеоинтервью
                $videoInterviewProcessingRuntime = $videoInterviewProcessingEnd - $videoInterviewProcessingStatus->all_runtime;
                // Обновление атрибутов статуса обработки видеоинтервью, полного времени анализа видеоинтервью в БД
                $videoInterviewProcessingStatus->all_runtime = $videoInterviewProcessingRuntime;
                $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_COMPLETED;
                $videoInterviewProcessingStatus->updateAttributes(['status', 'all_runtime']);
            }
        }
    }

    /**
     * Команда запуска анализа видео ответа на вопрос (формирование цифровой маски).
     *
     * @param $questionId - идентификатор видео ответа на вопрос
     * @param $mirroring - наличие зеркаливания
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionStartVideoProcessing($questionId, $mirroring)
    {
        // Поиск вопроса видеоинтервью по id
        $question = Question::findOne((int)$questionId);
        // Поиск полного видеоинтервью по id
        $videoInterview = VideoInterview::findOne($question->video_interview_id);
        // Создание цифровой маски в БД
        $landmarkModel = new Landmark();
        $landmarkModel->start_time = '00:00:00:000';
        $landmarkModel->finish_time = '12:00:00:000';
        $landmarkModel->type = Landmark::TYPE_LANDMARK_IVAN_MODULE;
        $landmarkModel->rotation = Landmark::TYPE_ZERO; // TODO - надо вычислить поворот
        $landmarkModel->mirroring = boolval($mirroring);
        $landmarkModel->question_id = $question->id;
        $landmarkModel->video_interview_id = $videoInterview->id;
        $landmarkModel->save();
        // Создание дополнительной цифровой маски в БД
        $additionalLandmarkModel = new Landmark();
        $additionalLandmarkModel->start_time = '00:00:00:000';
        $additionalLandmarkModel->finish_time = '12:00:00:000';
        $additionalLandmarkModel->type = Landmark::TYPE_LANDMARK_IVAN_MODULE;
        $additionalLandmarkModel->rotation = Landmark::TYPE_ZERO; // TODO - надо вычислить поворот
        $additionalLandmarkModel->mirroring = boolval($mirroring);
        $additionalLandmarkModel->description = 'Цифровая маска получена на основе цифровой маски №' .
            $landmarkModel->id;
        $additionalLandmarkModel->question_id = $question->id;
        $additionalLandmarkModel->video_interview_id = $videoInterview->id;
        $additionalLandmarkModel->save();

        // Путь к программе обработки видео от Ивана
        $mainPath = '/home/-Common/-ivan/';
        // Путь к файлу видеоинтервью
        $videoPath = $mainPath . 'video/';
        // Путь к json-файлу результатов обработки видеоинтервью
        $jsonResultPath = $mainPath . 'json/';
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Сохранение файла видео ответа на вопрос на сервер
        $osConnector->saveFileToServer(
            OSConnector::OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET,
            $question->id,
            $question->video_file_name,
            $videoPath
        );
        // Название видео-файла с результатами обработки видео (первый скрипт)
        $videoResultFile = 'out_' . $question->id . '.avi';
        // Название json-файла с результатами обработки видео (первый скрипт)
        $jsonResultFile = 'out_' . $question->id . '.json';
        // Название аудио-файла (mp3) с результатами обработки видео (первый скрипт)
        $audioResultFile = 'out_' . $question->id . '.mp3';
        // Название видео-файла с результатами обработки видео (второй скрипт)
        $extVideoResultFile = 'out_' . $question->id . '_ext.avi';
        // Название json-файла с результатами обработки видео (второй скрипт)
        $extJsonResultFile = 'out_' . $question->id . '_ext.json';
        // Формирование массива с параметрами запуска программы обработки видео
        $parameters['nameVidFilesIn'] = 'video/' . $question->video_file_name;
        $parameters['nameVidFilesOut'] = 'json/out_{}.avi';
        $parameters['nameJsonFilesOut'] = 'json/out_{}.json';
        $parameters['nameAudioFilesOut'] = 'json/out_{}.mp3';
        $parameters['indexesTriagnleStats'] = [[21, 22, 28], [31, 48, 74], [31, 40, 74], [35, 54, 75],
            [35, 47, 75], [27, 35, 42], [27, 31, 39]];
        $parameters['rotate_mode'] = VideoProcessingModuleSettingForm::ROTATE_MODE_ZERO;
        $parameters['enableAutoRotate'] = VideoProcessingModuleSettingForm::AUTO_ROTATE_FALSE;
        $parameters['Mirroring'] = $landmarkModel->mirroring;
        $parameters['AlignMode'] = VideoProcessingModuleSettingForm::ALIGN_MODE_BY_THREE_FACIAL_POINTS;
        $parameters['id'] = $question->id;
        $parameters['landmark_mode'] = VideoProcessingModuleSettingForm::LANDMARK_MODE_FAST;
        $parameters['parameters'] = VideoProcessingModuleSettingForm::PARAMETER_CHECK_ALL_VIDEO_DATA;
        // Формирование json-строки на основе массива с параметрами запуска программы обработки видео
        $jsonParameters = json_encode($parameters, JSON_UNESCAPED_UNICODE);
        // Открытие файла на запись для сохранения параметров запуска программы обработки видео
        $jsonFile = fopen($mainPath . 'test' . $question->id . '.json', 'a');
        // Запись в файл json-строки с параметрами запуска программы обработки видео
        fwrite($jsonFile, str_replace("\\", "", $jsonParameters));
        // Закрытие файла
        fclose($jsonFile);

        // Поиск статуса обработки видеоинтервью по id видеоинтервью
        $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::find()
            ->where(['video_interview_id' => $videoInterview->id])
            ->one();
        // Создание модели статуса обработки вопроса
        $questionProcessingStatusModel = new QuestionProcessingStatus();
        $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_IVAN_VIDEO_PROCESSING_MODULE_IN_PROGRESS;
        $questionProcessingStatusModel->question_id = $question->id;
        $questionProcessingStatusModel->video_interview_processing_status_id = $videoInterviewProcessingStatus->id;
        $questionProcessingStatusModel->save();
        // Время начала выполнения МОВ Ивана
        $ivanVideoAnalysisStart = microtime(true);

        try {
            // Запуск программы обработки видео Ивана (первый скрипт)
            chdir($mainPath);
            exec('./venv/bin/python ./main_new.py ./test' . $question->id . '.json');
        } catch (Exception $e) {
            // Создание сообщения об ошибке МОВ Ивана в БД
            $moduleMessageModel = new ModuleMessage();
            $moduleMessageModel->message = 'Ошибка модуля обработки видео Ивана! ' . $e->getMessage();
            $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
            $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
            $moduleMessageModel->save();
        }

        try {
            // Запуск второго скрипта модуля обработки видео Ивана
            chdir($mainPath);
            exec('./venv/bin/python ./main_new2.py ./json/' . $jsonResultFile);
        } catch (Exception $e) {
            // Создание сообщения об ошибке МОВ Ивана в БД
            $moduleMessageModel = new ModuleMessage();
            $moduleMessageModel->message = 'Ошибка второго скрипта модуля обработки видео Ивана! ' . $e->getMessage();
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
        $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_COMPLETED;
        $questionProcessingStatusModel->updateAttributes(['ivan_video_analysis_runtime', 'status']);

        $firstLandmarkFileExists = false;
        // Проверка существования json-файл с результатами обработки видео первым скриптом Ивана
        if (file_exists($jsonResultPath . $jsonResultFile)) {
            // Формирование названия json-файла с результатами обработки видео
            $landmarkModel->landmark_file_name = $jsonResultFile;
            // Формирование названия видео-файла с нанесенной цифровой маской
            $landmarkModel->processed_video_file_name = $videoResultFile;
            // Формирование описания цифровой маски
            $landmarkModel->description = $videoInterview->description . ' (время нарезки: ' .
                $landmarkModel->getStartTime() . ' - ' . $landmarkModel->getFinishTime() . ')';
            // Обновление атрибутов цифровой маски в БД
            $landmarkModel->updateAttributes(['landmark_file_name', 'processed_video_file_name', 'description']);
            // Получение json-файла с результатами обработки видео в виде цифровой маски
            $landmarkFile = file_get_contents($jsonResultPath .
                $landmarkModel->landmark_file_name, true);
            // Сохранение файла с лицевыми точками на Object Storage
            $osConnector->saveFileToObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $landmarkModel->id,
                $landmarkModel->landmark_file_name,
                $landmarkFile
            );
            // Сохранение файла видео с нанесенной цифровой маской на Object Storage
            $osConnector->saveFileToObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $landmarkModel->id,
                $landmarkModel->processed_video_file_name,
                $jsonResultPath . $landmarkModel->processed_video_file_name
            );
            // Декодирование json-файла с результатами обработки видео в виде цифровой маски
            $jsonLandmarkFile = json_decode($landmarkFile, true);
            // Если в json-файле с цифровой маской есть текст с предупреждением
            if (isset($jsonLandmarkFile['err_msg'])) {
                // Создание сообщения о предупреждении МОВ Ивана в БД
                $moduleMessageModel = new ModuleMessage();
                $moduleMessageModel->message = $jsonLandmarkFile['err_msg'];
                $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
                $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
                $moduleMessageModel->save();
            }
            $firstLandmarkFileExists = true;
        }
        // Удаление записи о цифровой маски для которой не сформирован json-файл
        if ($firstLandmarkFileExists == false) {
            Landmark::findOne($landmarkModel->id)->delete();
            // Создание сообщения о не созданной цифровой маски
            $moduleMessageModel = new ModuleMessage();
            $moduleMessageModel->message = 'Не удалось сформировать цифровую маску первым скриптом МОВ Ивана!';
            $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
            $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
            $moduleMessageModel->save();
        }

        $secondLandmarkFileExists = false;
        // Проверка существования json-файл с результатами обработки видео вторым скриптом Ивана
        if (file_exists($jsonResultPath . $extJsonResultFile)) {
            // Формирование названия json-файла с результатами обработки видео
            $additionalLandmarkModel->landmark_file_name = $extJsonResultFile;
            // Формирование названия видео-файла с нанесенной цифровой маской
            $additionalLandmarkModel->processed_video_file_name = $videoResultFile;
            // Обновление атрибутов цифровой маски в БД
            $additionalLandmarkModel->updateAttributes(['landmark_file_name', 'processed_video_file_name']);
            // Получение json-файла с результатами обработки видео в виде цифровой маски
            $landmarkFile = file_get_contents($jsonResultPath .
                $additionalLandmarkModel->landmark_file_name, true);
            // Сохранение файла с лицевыми точками на Object Storage
            $osConnector->saveFileToObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $additionalLandmarkModel->id,
                $additionalLandmarkModel->landmark_file_name,
                $landmarkFile
            );
            // Сохранение файла видео с нанесенной цифровой маской на Object Storage
            $osConnector->saveFileToObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $additionalLandmarkModel->id,
                $additionalLandmarkModel->processed_video_file_name,
                $jsonResultPath . $additionalLandmarkModel->processed_video_file_name
            );
            // Декодирование json-файла с результатами обработки видео в виде цифровой маски
            $jsonLandmarkFile = json_decode($landmarkFile, true);
            // Если в json-файле с цифровой маской есть текст с предупреждением
            if (isset($jsonLandmarkFile['err_msg'])) {
                // Создание сообщения о предупреждении МОВ Ивана в БД
                $moduleMessageModel = new ModuleMessage();
                $moduleMessageModel->message = $jsonLandmarkFile['err_msg'];
                $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
                $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
                $moduleMessageModel->save();
            }
            $secondLandmarkFileExists = true;
        }
        // Удаление записи о цифровой маски для которой не сформирован json-файл
        if ($secondLandmarkFileExists == false) {
            Landmark::findOne($additionalLandmarkModel->id)->delete();
            // Создание сообщения о не созданной цифровой маски
            $moduleMessageModel = new ModuleMessage();
            $moduleMessageModel->message = 'Не удалось сформировать цифровую маску вторым скриптом МОВ Ивана!';
            $moduleMessageModel->module_name = ModuleMessage::IVAN_VIDEO_PROCESSING_MODULE;
            $moduleMessageModel->question_processing_status_id = $questionProcessingStatusModel->id;
            $moduleMessageModel->save();
        }

        // Время начала выполнения МОВ Андрея
        $andreyVideoAnalysisStart = microtime(true);
        // Обновление атрибута статуса обработки вопроса в БД
        $questionProcessingStatusModel->status = QuestionProcessingStatus::STATUS_ANDREY_VIDEO_PROCESSING_MODULE_IN_PROGRESS;
        $questionProcessingStatusModel->updateAttributes(['status']);

        try {
            // Путь к программе обработки видео от Андрея
            $mainAndrewModulePath = '/home/-Common/-andrey/';
            // Путь к json-файлу результатов обработки видеоинтервью от Андрея
            $jsonAndrewResultPath = $mainAndrewModulePath . 'Records/';
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
                $landmarkModel->landmark_file_name = $jsonResultFile;
                $landmarkModel->start_time = '00:00:00:000';
                $landmarkModel->finish_time = '12:00:00:000';
                $landmarkModel->type = Landmark::TYPE_LANDMARK_ANDREW_MODULE;
                $landmarkModel->rotation = Landmark::TYPE_ZERO; // TODO - надо вычислить поворот
                $landmarkModel->mirroring = boolval($mirroring);
                $landmarkModel->description = $videoInterview->description . ' (время нарезки: ' .
                    $landmarkModel->start_time . ' - ' . $landmarkModel->finish_time . ')';
                $landmarkModel->question_id = $question->id;
                $landmarkModel->video_interview_id = $videoInterview->id;
                $landmarkModel->save();
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
                // Удаление json-файла с результатами обработки видеоинтервью программой Андрея
                unlink($jsonAndrewResultPath . $jsonFileName . '.json');
            }
        } catch (Exception $e) {
            // Создание сообщения об ошибке МОВ Андрея в БД
            $moduleMessageModel = new ModuleMessage();
            $moduleMessageModel->message = 'Ошибка модуля обработки видео Андрея! ' . $e->getMessage();
            $moduleMessageModel->module_name = ModuleMessage::ANDREY_VIDEO_PROCESSING_MODULE;
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
        // Удаление видео-файла с результатами обработки видеоинтервью вторым скриптом МОВ ИВана
        if (file_exists($jsonResultPath . $extVideoResultFile))
            unlink($jsonResultPath . $extVideoResultFile);
        // Удаление json-файла с результатами обработки видео вторым скриптом МОВ ИВана
        if (file_exists($jsonResultPath . $extJsonResultFile))
            unlink($jsonResultPath . $extJsonResultFile);

        $completed = true;
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
            // Время окончания выполнения анализа видеоинтервью
            $videoInterviewProcessingEnd = microtime(true);
            // Вычисление времени выполнения анализа видеоинтервью
            $videoInterviewProcessingRuntime = $videoInterviewProcessingEnd - $videoInterviewProcessingStatus->all_runtime;
            // Обновление атрибутов статуса обработки видеоинтервью, полного времени анализа видеоинтервью в БД
            $videoInterviewProcessingStatus->all_runtime = $videoInterviewProcessingRuntime;
            $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_COMPLETED;
            $videoInterviewProcessingStatus->updateAttributes(['status', 'all_runtime']);
        }
    }

    /**
     * Команда запуска определения и интерпретации признаков.
     *
     * @param $videoInterviewId - идентификатор видеоинтервью
     * @throws \SoapFault
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionStartVideoInterviewAnalysis($videoInterviewId)
    {
        // Ожидание завершения обработки всех видео по обычным вопросам
        do {
            // Задержка выполнения скрипта в 5 секунд
            sleep(5);
            // Поиск статуса обработки видеоинтервью по id видеоинтервью
            $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::find()
                ->where(['video_interview_id' => (int)$videoInterviewId])
                ->one();
        } while ($videoInterviewProcessingStatus->status !== VideoInterviewProcessingStatus::STATUS_COMPLETED);

        // Время начала выполнения анализа видеоинтервью
        $videoInterviewProcessingStart = microtime(true);
        // Поиск статуса обработки видеоинтервью по id видеоинтервью
        $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::find()
            ->where(['video_interview_id' => (int)$videoInterviewId])
            ->one();
        // Обновление атрибута статуса и времени обработки видеоинтервью в БД
        $videoInterviewProcessingStatus->updated_at = time();
        $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_IN_PROGRESS;
        $videoInterviewProcessingStatus->updateAttributes(['updated_at', 'status']);

        $detectionResultExist = false;
        $analysisResultIds = array();
        $errorExist = false;
        // Поиск всех видео ответов на вопросы для данного видеоинтервью
        $questions = Question::find()->where(['video_interview_id' => (int)$videoInterviewId])->all();
        // Обход всех видео ответов на вопросы
        foreach ($questions as $question) {
            // Поиск темы вопроса
            $topicQuestion = TopicQuestion::find()->where(['test_question_id' => $question->test_question_id])->one();
            // Если тема для вопроса найдена
            if (!empty($topicQuestion)) {
                // Если текущий вопрос не является калибровочным
                if ($topicQuestion->topic_id != 24 && $topicQuestion->topic_id != 25 && $topicQuestion->topic_id != 27) {
                    // Поиск цифровых масок полученных модулем Ивана для текущего вопроса видеоинтервью
                    $landmarks = Landmark::find()->where([
                        'question_id' => $question->id,
                        'video_interview_id' => (int)$videoInterviewId,
                        'type' => Landmark::TYPE_LANDMARK_IVAN_MODULE
                    ])->all();
                    // Если цифровые маски найдены
                    if (!empty($landmarks))
                        foreach ($landmarks as $landmark) {

                            // Поиск результатов анализа для данной цифровой маски
                            $analysisResults = AnalysisResult::find()->where(['landmark_id' => $landmark->id])->all();
                            // Если результаты анализа для данной цифровой маски уже сформированы
                            if (!empty($analysisResults)) {
                                // Создание объекта AnalysisHelper
                                $analysisHelper = new AnalysisHelper();
                                // Удаление всех результатов анализа для данной цифровой маски на Object Storage
                                $analysisHelper->deleteAnalysisResultsInObjectStorage($landmark->id);
                                // Удаление всех результатов анализа для данной цифровой маски в БД
                                foreach ($analysisResults as $analysisResult)
                                    $analysisResult->delete();
                            }

                            // Если цифровая маска полученная не вторым скриптом Ивана
                            if (strripos($landmark->landmark_file_name, '_ext') === false) {
                                // Время начала выполнения МОП
                                $featuresDetectionStart = microtime(true);
                                // Обновление атрибута статуса обработки вопроса в БД
                                $questionProcessingStatus = QuestionProcessingStatus::find()
                                    ->where([
                                        'video_interview_processing_status_id' => $videoInterviewProcessingStatus->id,
                                        'question_id' => $question->id])
                                    ->one();
                                $questionProcessingStatus->status = QuestionProcessingStatus::STATUS_FEATURE_DEFINITION_MODULE_IN_PROGRESS;
                                $questionProcessingStatus->updateAttributes(['status']);
                                try {
                                    // Создание объекта AnalysisHelper
                                    $analysisHelper = new AnalysisHelper();
                                    // Получение базового кадра для видеоинтервью из json-файла на сервере
                                    $baseFrame = file_get_contents(Yii::$app->basePath .
                                        '/web/base-frame-' . $videoInterviewId . '.json', true);
                                    // Получение рузультатов анализа видеоинтервью (обработка модулем определения признаков)
                                    list($resultExist, $analysisResultId) = $analysisHelper->getAnalysisResult(
                                        $landmark,
                                        2, // Задание определения признаков по новому МОП
                                        $baseFrame,
                                        AnalysisHelper::NEW_FDM,
                                        null
                                    );
                                    // Если нет ошибки при работе МОП
                                    if ($resultExist)
                                        // Сохранение id полученного результата определения признаков в массиве
                                        array_push($analysisResultIds, $analysisResultId);
                                    else {
                                        $errorExist = true;
                                        // Создание сообщения об ошибке МОП в БД
                                        $moduleMessageModel = new ModuleMessage();
                                        $moduleMessageModel->message = 'Ошибка МОП на данных Ивана для ЦМ id = ' .
                                            $landmark->id . ' Текст ошибки: ' . $analysisResultId;
                                        $moduleMessageModel->module_name = ModuleMessage::FEATURE_DETECTION_MODULE;
                                        $moduleMessageModel->question_processing_status_id = $questionProcessingStatus->id;
                                        $moduleMessageModel->save();
                                    }
                                } catch (Exception $e) {
                                    $errorExist = true;
                                    // Создание сообщения об ошибке МОП в БД
                                    $moduleMessageModel = new ModuleMessage();
                                    $moduleMessageModel->message = 'Ошибка МОП на данных Ивана для ЦМ id = ' .
                                        $landmark->id . ' Текст ошибки: ' . $e->getMessage();
                                    $moduleMessageModel->module_name = ModuleMessage::FEATURE_DETECTION_MODULE;
                                    $moduleMessageModel->question_processing_status_id = $questionProcessingStatus->id;
                                    $moduleMessageModel->save();
                                }
                                // Время окончания выполнения МОП
                                $featuresDetectionEnd = microtime(true);
                                // Вычисление времени выполнения МОП
                                $featuresDetectionRuntime = $featuresDetectionEnd - $featuresDetectionStart;
                                // Обновление атрибута времени и статуса выполнения МОП в БД
                                $questionProcessingStatus->feature_detection_runtime = $featuresDetectionRuntime;
                                $questionProcessingStatus->status = QuestionProcessingStatus::STATUS_COMPLETED;
                                $questionProcessingStatus->updateAttributes(['feature_detection_runtime', 'status']);
                            }
                        }
                }
            }
        }
        // Если нет ошибки при работе МОП
        if ($errorExist == false) {
            // Массив всех статистик, сформированных по всем видео на вопросы
            $featureStatistics = array();
            // Создание объекта коннектора с Yandex.Cloud Object Storage
            $osConnector = new OSConnector();
            // Обход всех идентификаторов результатов анализа
            foreach ($analysisResultIds as $analysisResultId) {
                // Поиск результата анализа (определения признаков) по id
                $analysisResult = AnalysisResult::findOne($analysisResultId);
                // Получение содержимого json-файла с результатами определения признаков из Object Storage
                $jsonAnalysisResultFile = $osConnector->getFileContentFromObjectStorage(
                    OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                    $analysisResult->id,
                    $analysisResult->detection_result_file_name
                );
                // Декодирование json-файла с результатами определения признаков
                $analysisResultFile = json_decode($jsonAnalysisResultFile, true);
                // Обход содержимого результатов определения признаков
                foreach ($analysisResultFile as $key => $value)
                    if ($key == 'feature_statistics')
                        // Добавление текущей статистики в массив всех статистик по видео на вопрос
                        array_push($featureStatistics, $value);
            }
            // Создание объекта обнаружения лицевых признаков
            $facialFeatureDetector = new FacialFeatureDetector();
            // Определение статистики по всем результатам определения признаков
            $summarizedFeatureStatistics = $facialFeatureDetector->detectSummarizedFeatureStatistics($featureStatistics);
            // Конвертация определенной статистики в набор фактов
            $summarizedFeatureStatisticsFacts = AnalysisHelper::convertSummarizedFeatureStatistics($summarizedFeatureStatistics['summarized_feature_statistics']);
            // Обход всех идентификаторов результатов анализа
            foreach ($analysisResultIds as $analysisResultId) {
                // Поиск результата анализа (определения признаков) по id
                $analysisResult = AnalysisResult::findOne($analysisResultId);
                // Получение содержимого json-файла с набором фактов из Object Storage
                $jsonFacts = $osConnector->getFileContentFromObjectStorage(
                    OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                    $analysisResult->id,
                    $analysisResult->facts_file_name
                );
                // Декодирование json-файла с набором фактов
                $facts = json_decode($jsonFacts, true);
                // Если результатом конвертации определенной статистики в набор фактов является массив,
                // то формирование двух фактов и добавление их в общий набор в первый (0) кадр
                if (is_array($summarizedFeatureStatisticsFacts))
                    foreach ($summarizedFeatureStatisticsFacts as $summarizedFeatureStatisticsFact)
                        array_push($facts[0], $summarizedFeatureStatisticsFact);
                // Сохранение json-файла с измененным набор фактов на Object Storage
                $osConnector->saveFileToObjectStorage(
                    OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                    $analysisResult->id,
                    $analysisResult->facts_file_name,
                    $facts
                );
            }
            $detectionResultExist = true;
        }
        // Если есть результаты определения признаков
        if ($detectionResultExist) {
            // Поиск всех видео ответов на вопросы для данного видеоинтервью
            $questions = Question::find()->where(['video_interview_id' => (int)$videoInterviewId])->all();
            // Если есть видео ответы на вопросы
            if (!empty($questions)) {
                // Обход всех видео ответов на вопросы
                foreach ($questions as $question) {
                    // Поиск темы для вопроса
                    $topicQuestion = TopicQuestion::find()
                        ->where(['test_question_id' => $question->test_question_id])
                        ->one();
                    // Если тема для вопроса найдена
                    if (!empty($topicQuestion)) {
                        // Если вопрос не калибровочный (темы 24, 25 и 27)
                        if ($topicQuestion->topic_id != 24 && $topicQuestion->topic_id != 25 &&
                            $topicQuestion->topic_id != 27) {
                            // Поиск связанной базы знаний с заданным профилем интервью
                            $surveyQuestion = SurveyQuestion::find()
                                ->where(['test_question_id' => $question->test_question_id])
                                ->one();
                            if (!empty($surveyQuestion)) {
                                $profileSurvey = ProfileSurvey::find()
                                    ->where(['survey_id' => $surveyQuestion->survey_id])
                                    ->one();
                                if (!empty($profileSurvey)) {
                                    $profileKnowledgeBase = ProfileKnowledgeBase::find()
                                        ->where(['profile_id' => $profileSurvey->profile_id])
                                        ->one();
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            // Если существует связь профиля с базами знаний по данному видеоинтервью
            if (!empty($profileKnowledgeBase)) {
                // Если задана база знаний для интерпретации первого уровня
                if ($profileKnowledgeBase->first_level_knowledge_base_id != null) {
                    // Массив идентификаторов результатов анализа
                    $analysisResultIds = '';
                    // Поиск всех цифровых масок для данного видеоинтервью
                    $Landmarks = Landmark::find()->where(['video_interview_id' => (int)$videoInterviewId])->all();
                    // Обход всех найденных цифровых масок
                    foreach ($Landmarks as $Landmark) {
                        // Поиск всех результатов определения признаков для данной цифровой маски
                        $analysisResults = AnalysisResult::find()->where(['landmark_id' => $Landmark->id])->all();
                        // Обход всех результатов определения признаков
                        foreach ($analysisResults as $analysisResult)
                            if ($analysisResultIds == '')
                                $analysisResultIds .= $analysisResult->id;
                            else
                                $analysisResultIds .= ',' . $analysisResult->id;
                    }
                    // Запуск интерпретации признаков по результатам МОП (интерпретация первого уровня)
                    ini_set('default_socket_timeout', 60 * 30);
                    $addressOfRBRWebServiceDefinition = 'http://127.0.0.1:8888/RBRWebService?wsdl';
                    $client = new SoapClient($addressOfRBRWebServiceDefinition);
                    $addressForCodeOfKnowledgeBaseRetrieval = 'http://127.0.0.1/Drools/RetrieveData.php?DataSource=CodeOfKnowledgeBase&IDOfKnowledgeBase=' .
                        $profileKnowledgeBase->first_level_knowledge_base_id;
                    $addressForInitialConditionsRetrieval = 'http://127.0.0.1/Drools/RetrieveData.php?DataSource=InitialDataOfReasoningProcess&ID=';
                    $idsOfInitialConditions = '[' . $analysisResultIds . ']';
                    $addressToSendResults = 'http://127.0.0.1/Drools/RetrieveData.php';
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
                }
                // Если задана база знаний для интерпретации второго уровня
                if ($profileKnowledgeBase->second_level_knowledge_base_id != null) {
                    // Время начала выполнения МИП (второй уровень)
                    $emotionInterpretationStart = microtime(true);
                    // Обновление атрибутов статуса обработки видеоинтервью в БД
                    $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_FINAL_RESULT_FORMATION;
                    $videoInterviewProcessingStatus->updateAttributes(['status']);

                    // Запуск вывода по результатам интерпретации признаков (интерпретация второго уровня)
                    ini_set('default_socket_timeout', 60 * 30);
                    $addressOfRBRWebServiceDefinition = 'http://127.0.0.1:8888/RBRWebService?wsdl';
                    $client = new SoapClient($addressOfRBRWebServiceDefinition);
                    $addressForCodeOfKnowledgeBaseRetrieval = 'http://127.0.0.1/Drools/RetrieveData.php?DataSource=CodeOfKnowledgeBase&IDOfKnowledgeBase=' .
                        $profileKnowledgeBase->second_level_knowledge_base_id;
                    $addressForInitialConditionsRetrieval = 'http://127.0.0.1/Drools/RetrieveData.php?DataSource=InitialDataOfReasoningProcess&Level=2&ID=' . $videoInterviewId;
                    $addressToSendResults = 'http://127.0.0.1/Drools/RetrieveData.php';
                    $additionalDataToSend = new stdClass;
                    $additionalDataToSend->{'IDOfFile'} = (int)$videoInterviewId;
                    $additionalDataToSend->{'Type'} = 'Interpretation Level II';
                    $client->LaunchReasoningProcessAndSendResultsToURL(array(
                        'arg0' => $addressForCodeOfKnowledgeBaseRetrieval,
                        'arg1' => $addressForInitialConditionsRetrieval,
                        'arg2' => $addressToSendResults,
                        'arg3' => 'ResultsOfReasoningProcess',
                        'arg4' => json_encode($additionalDataToSend)))->return;
                    $client = Null;

                    // Время окончания выполнения МИП (второй уровень)
                    $emotionInterpretationEnd = microtime(true);
                    // Вычисление времени выполнения МИП (второй уровень)
                    $emotionInterpretationRuntime = $emotionInterpretationEnd - $emotionInterpretationStart;
                    // Обновление атрибутов времени МИП (второй уровень) в БД
                    $videoInterviewProcessingStatus->emotion_interpretation_runtime = $emotionInterpretationRuntime;
                    $videoInterviewProcessingStatus->updateAttributes(['emotion_interpretation_runtime']);
                }
            }
        }
        // Время окончания выполнения анализа видеоинтервью
        $videoInterviewProcessingEnd = microtime(true);
        // Вычисление времени выполнения анализа видеоинтервью
        $videoInterviewProcessingRuntime = $videoInterviewProcessingEnd - $videoInterviewProcessingStart;
        // Обновление атрибутов статуса обработки видеоинтервью, полного времени анализа видеоинтервью в БД
        $videoInterviewProcessingStatus->all_runtime = $videoInterviewProcessingStatus->all_runtime +
            $videoInterviewProcessingRuntime;
        $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_COMPLETED;
        $videoInterviewProcessingStatus->updateAttributes(['status', 'all_runtime']);
    }

    /**
     * Команда запуска обработки калибровочных вопросов для видеоинтервью (для чат-бота).
     *
     * @param $videoInterviewId - идентификатор видеоинтервью
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionStartCalibrationQuestionsProcessing($videoInterviewId)
    {
        // Поиск статуса обработки видеоинтервью по id видеоинтервью
        $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::find()
            ->where(['video_interview_id' => (int)$videoInterviewId])
            ->one();
        // Если данное видеоинтервью не находится в обработке
        if (empty($videoInterviewProcessingStatus) ||
            $videoInterviewProcessingStatus->status != VideoInterviewProcessingStatus::STATUS_IN_PROGRESS) {

            // Создание объекта AnalysisHelper
            $analysisHelper = new AnalysisHelper();
            // Удаление всех цифровых масок и связанных с ними результатов анализа для данного видеоинтервью на Object Storage
            $analysisHelper->deleteLandmarksInObjectStorage((int)$videoInterviewId);
            // Поиск всех цифровых масок принадлежащих данному видеоинтервью
            $landmarks = Landmark::find()->where(['video_interview_id' => (int)$videoInterviewId])->all();
            // Удаление всех цифровых масок из БД
            foreach ($landmarks as $landmark)
                $landmark->delete();
            // Если интервью новое
            if (isset($videoInterviewProcessingStatus) === True) {
                // Поиск статуса обработки видеоинтервью по id видеоинтервью
                $questionProcessingStatuses = QuestionProcessingStatus::find()
                    ->where(['video_interview_processing_status_id' => $videoInterviewProcessingStatus->id])
                    ->all();
                // Удаление всех статусов по вопросам у данного видеоинтервью из БД
                foreach ($questionProcessingStatuses as $questionProcessingStatus)
                    $questionProcessingStatus->delete();
            }
            // Поиск всех видео ответов на вопросы для данного видеоинтервью
            $questions = Question::find()->where(['video_interview_id' => (int)$videoInterviewId])->all();

            $IDsOfProcessingQuestions = array();

            // Если есть видео ответов на вопросы для данного видеоинтервью
            if (!empty($questions)) {
                // Обход всех видео ответов на вопросы для данного видеоинтервью
                foreach ($questions as $question) {
                    // Поиск темы для вопроса - Topic 24 (поворот вправо), 25 (поворот влево), 27 (калибровочный для камеры)
                    $topicQuestion = TopicQuestion::find()
                        ->where(['test_question_id' => $question->test_question_id])
                        ->one();
                    // Если тема для вопроса найдена
                    if (!empty($topicQuestion)) {
                        // Если вопросы калибровочные (темы 24, 25 и 27)
                        if ($topicQuestion->topic_id == 24 || $topicQuestion->topic_id == 25 ||
                            $topicQuestion->topic_id == 27) {

                            // Сохраним ID'ы обрабатываемых вопросов
                            $IDsOfProcessingQuestions[] = $question->id;

                            // Создание цифровой маски в БД
                            $landmarkModel = new Landmark();
                            $landmarkModel->start_time = '00:00:00:000';
                            $landmarkModel->finish_time = '12:00:00:000';
                            $landmarkModel->type = Landmark::TYPE_LANDMARK_IVAN_MODULE;
                            $landmarkModel->rotation = Landmark::TYPE_ZERO;
                            $landmarkModel->mirroring = Landmark::TYPE_MIRRORING_FALSE;
                            $landmarkModel->question_id = $question->id;
                            $landmarkModel->video_interview_id = $question->video_interview_id;
                            $landmarkModel->save();
                            // Создание объекта запуска консольной команды
                            $consoleRunner = new ConsoleRunner(['file' => '@app/yii']);
                            // Выполнение команды анализа видео ответа на калибровочный вопрос в фоновом режиме
                            $consoleRunner->run('video-interview-analysis/preparation ' . $question->id . ' ' .
                                $landmarkModel->id . ' ' . $topicQuestion->topic_id);
                            // Задержка выполнения скрипта в 1 секунду
                            sleep(1);
                        }
                    }
                }

                // Ожидание завершения обработки всех видео по обычным вопросам
                $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::find()
                    ->where(['video_interview_id' => (int)$videoInterviewId])
                    ->one();
                do {
                    $processingFinished = true;
                    $questionProcessingStatuses = QuestionProcessingStatus::find()
                        ->where(['video_interview_processing_status_id' => $videoInterviewProcessingStatus->id])
                        ->all();
                    foreach($questionProcessingStatuses as $questionProcessingStatus) {
                        if ($questionProcessingStatus->status !== QuestionProcessingStatus::STATUS_COMPLETED) {
                            $processingFinished = false;
                            break;
                        }
                    }
                    sleep(5); // Задержка выполнения скрипта в 5 секунд
                    if ($processingFinished) {
                        // Обновление атрибутов статуса обработки видеоинтервью в БД
                        $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_COMPLETED;
                        $videoInterviewProcessingStatus->updateAttributes(['status']);
                    }
                } while ($processingFinished === false);

                // Поиск всех статусов обработки видео на вопрос по id статуса обработки видеоинтервью
                $questionProcessingStatuses = QuestionProcessingStatus::find()
                    ->where(['video_interview_processing_status_id' => $videoInterviewProcessingStatus->id])
                    ->orderBy(['question_id' => SORT_ASC])
                    ->all();
                // Массив сформированных цифровых масок МОВ Ивана
                $formedLandmarks = array();
                // Параметры наличия поворота головы вправо и влево
                $turnRight = null;
                $turnLeft = null;
                // Значение FPS
                $fpsValue = 0;
                // Показатель качества видео
                $qualityVideo = false;
                // Массив с коэффициентами качества видео
                $videoQualityParameters = array();
                // Обход всех статусов обработки видео на вопрос
                foreach ($questionProcessingStatuses as $questionProcessingStatus) {
                    // Поиск видео ответа на вопрос по id
                    $question = Question::findOne($questionProcessingStatus->question_id);
                    // Поиск темы вопроса по id вопроса
                    $topicQuestion = TopicQuestion::find()
                        ->where(['test_question_id' => $question->test_question_id])
                        ->one();
                    // Поиск цифровых масок по определенному вопросу
                    $landmarks = Landmark::find()
                        ->where(['question_id' => $questionProcessingStatus->question_id])
                        ->all();
                    // Переменная существования цифровой маски
                    $landmarkExist = false;
                    // Создание объекта AnalysisHelper
                    $analysisHelper = new AnalysisHelper();
                    // Если вопрос калибровочный (сядьте прямо, посмотрите в камеру)
                    if ($topicQuestion->topic_id == 27)
                        if (!empty($landmarks))
                            foreach ($landmarks as $landmark) {
                                $landmarkExist = true;
                                // Определение качества видео
                                list($fpsValue, $qualityVideo, $videoQualityParameters) = $analysisHelper->determineQuality($landmark);
                            }
                    // Если вопрос калибровочный (поверните голову вправо)
                    if ($topicQuestion->topic_id == 24)
                        if (!empty($landmarks))
                            foreach ($landmarks as $landmark)
                                // Если цифровая маска содержит события и получена вторым скриптом МОВ Ивана
                                if (strripos($landmark->landmark_file_name, '_ext') !== false) {
                                    $landmarkExist = true;
                                    // Определение поворота головы, если калибровочный вопрос с темой 24 (поворот головы вправо)
                                    $turnRight = $analysisHelper->determineTurn($landmark);
                                }
                    // Если вопрос калибровочный (поверните голову влево)
                    if ($topicQuestion->topic_id == 25)
                        if (!empty($landmarks))
                            foreach ($landmarks as $landmark)
                                // Если цифровая маска содержит события и получена вторым скриптом МОВ Ивана
                                if (strripos($landmark->landmark_file_name, '_ext') !== false) {
                                    $landmarkExist = true;
                                    // Определение поворота головы, если калибровочный вопрос с темой 25 (поворот головы влево)
                                    $turnLeft = $analysisHelper->determineTurn($landmark);
                                }
                    // Формирование массива получения цифровых масок
                    array_push($formedLandmarks, [$question->test_question_id, $landmarkExist]);
                }
                // Формирование результата
                $result = array($formedLandmarks, $turnRight, $turnLeft, $fpsValue,
                    $qualityVideo, $videoQualityParameters);
                // Поиск итогового заключения по данному видеоинтервью
                $finalResult = FinalResult::find()->where(['video_interview_id' => (int)$videoInterviewId])->one();
                // Если итоговое заключение существует
                if (!empty($finalResult)) {
                    // Поиск результатов обработки калибровочных вопросов
                    $calibrationConclusion = CalibrationConclusion::findOne($finalResult->id);
                    // Если результаты обработки калибровочных вопросов для данного видеоинтервью уже созданы
                    if (!empty($calibrationConclusion)) {
                        // Обновление записи о результатах обработки калибровочных вопросов в БД
                        $calibrationConclusion->text = json_encode($result, JSON_UNESCAPED_UNICODE);
                        $calibrationConclusion->updateAttributes(['text']);
                    }
                }
            }
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