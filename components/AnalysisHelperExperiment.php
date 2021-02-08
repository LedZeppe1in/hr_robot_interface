<?php

namespace app\components;

use app\modules\main\models\ModuleMessage;
use Exception;
use app\modules\main\models\Landmark;
use app\modules\main\models\Question;
use app\modules\main\models\VideoInterview;
use app\modules\main\models\QuestionProcessingStatus;
use app\modules\main\models\VideoInterviewProcessingStatus;
use app\modules\main\models\VideoProcessingModuleSettingForm;

/**
 * AnalysisHelperExperiment - класс с общими функциями анализа видео-интервью.
 */
class AnalysisHelperExperiment
{
    /**
     * Запуск анализа видео ответа на вопрос (формирование цифровой маски).
     *
     * @param $questionId - идентификатор видео ответа на вопрос
     * @param $mirroring - наличие зеркаливания
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function startVideoProcessing($questionId, $mirroring)
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
    }
}