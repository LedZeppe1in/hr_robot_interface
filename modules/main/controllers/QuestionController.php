<?php

namespace app\modules\main\controllers;

use Yii;
use Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use app\components\OSConnector;
use app\components\AnalysisHelper;
use app\modules\main\models\Question;
use app\modules\main\models\Landmark;
use app\modules\main\models\VideoInterview;
use app\modules\main\models\VideoProcessingModuleSettingForm;

/**
 * QuestionController implements the CRUD actions for Question model.
 */
class QuestionController extends Controller
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
                'only' => ['list', 'view', 'delete', 'video-file-download', 'get-ivan-landmarks',
                    'get-andrey-landmarks', 'get-recognized-speech'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['list', 'view', 'delete', 'video-file-download', 'get-ivan-landmarks',
                            'get-andrey-landmarks', 'get-recognized-speech'],
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
     * Lists all Question models.
     * @return mixed
     */
    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Question::find(),
        ]);
        // Создание формы настройки параметров запуска модуля обработки видео (Иван)
        $videoProcessingModuleSettingForm = new VideoProcessingModuleSettingForm();

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'videoProcessingModuleSettingForm' => $videoProcessingModuleSettingForm
        ]);
    }

    /**
     * Displays a single Question model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // Создание формы настройки параметров запуска модуля обработки видео (Иван)
        $videoProcessingModuleSettingForm = new VideoProcessingModuleSettingForm();

        return $this->render('view', [
            'model' => $this->findModel($id),
            'videoProcessingModuleSettingForm' => $videoProcessingModuleSettingForm
        ]);
    }

    /**
     * Deletes an existing Question model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        // Поиск вопроса видеоинтервью по id
        $model = $this->findModel($id);
        // Создание объекта AnalysisHelper
        $analysisHelper = new AnalysisHelper();
        // Удаление видео ответа на вопрос и всех связанных с ним цифровых масок и их результатов анализа на Object Storage
        $analysisHelper->deleteQuestionInObjectStorage($model);
        // Удалние записи из БД
        $model->delete();
        // Вывод сообщения об успешном удалении
        Yii::$app->getSession()->setFlash('success', 'Вы успешно удалили вопрос опроса!');

        return $this->redirect(['list']);
    }

    /**
     * Скачивание файла видео с ответом на вопрос.
     *
     * @param $id
     * @return \yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionVideoFileDownload($id)
    {
        // Поиск модели вопроса видеоинтервью по id
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Скачивание файла видео с ответом на вопрос с Object Storage
        if ($model->video_file_name != '') {
            $result = $osConnector->downloadFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET,
                $model->id,
                $model->video_file_name
            );
            return $result;
        }
        throw new Exception('Файл не найден!');
    }

    /**
     * Формирование файла цифровой маски путем запуска модуля обработки видео Ивана.
     *
     * @param $id - идентификатор вопроса видеоинтервью
     * @return bool|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionGetIvanLandmarks($id) {
        // Если пришел POST-запрос
        if (Yii::$app->request->isPost) {
            // Установка времени выполнения скрипта в 3 часа
            set_time_limit(60 * 200);
            // Поиск вопроса видеоинтервью по id
            $question = Question::findOne($id);
            // Поиск полного видеоинтервью по id
            $videoInterview = VideoInterview::findOne($question->video_interview_id);
            // Создание цифровой маски в БД
            $landmarkModel = new Landmark();
            $landmarkModel->start_time = '00:00:00:000';
            $landmarkModel->finish_time = '12:00:00:000';
            $landmarkModel->type = Landmark::TYPE_LANDMARK_IVAN_MODULE;
            $landmarkModel->rotation = Landmark::TYPE_ZERO;
            $landmarkModel->mirroring = Landmark::TYPE_MIRRORING_FALSE;
            $landmarkModel->question_id = $question->id;
            $landmarkModel->video_interview_id = $videoInterview->id;
            $landmarkModel->save();
            // Сообщения о ходе формирования цифровых масок
            $messages = '';

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
            $parameters['rotate_mode'] = (int)Yii::$app->request->post('VideoProcessingModuleSettingForm')['rotateMode'];
            $parameters['enableAutoRotate'] = (bool)Yii::$app->request->post('VideoProcessingModuleSettingForm')['enableAutoRotate'];
            $parameters['Mirroring'] = (bool)Yii::$app->request->post('VideoProcessingModuleSettingForm')['mirroring'];
            $parameters['AlignMode'] = (int)Yii::$app->request->post('VideoProcessingModuleSettingForm')['alignMode'];
            $parameters['id'] = $question->id;
            $parameters['landmark_mode'] = (int)Yii::$app->request->post('VideoProcessingModuleSettingForm')['landmarkMode'];
            $parameters['parameters'] = Yii::$app->request->post('VideoProcessingModuleSettingForm')['videoProcessingParameter'];
            // Формирование json-строки на основе массива с параметрами запуска программы обработки видео
            $jsonParameters = json_encode($parameters, JSON_UNESCAPED_UNICODE);
            // Открытие файла на запись для сохранения параметров запуска программы обработки видео
            $jsonFile = fopen($mainPath . 'test' . $question->id . '.json', 'a');
            // Запись в файл json-строки с параметрами запуска программы обработки видео
            fwrite($jsonFile, str_replace("\\", "", $jsonParameters));
            // Закрытие файла
            fclose($jsonFile);
            try {
                // Запуск программы обработки видео Ивана
                chdir($mainPath);
                exec('./venv/bin/python ./main_new.py ./test' . $question->id . '.json');
            } catch (Exception $e) {
                // Сохранение сообщения об ошибке МОВ Ивана
                $messages = 'Ошибка модуля обработки видео Ивана! ' . $e->getMessage();
            }

            $firstScriptSuccess = false;
            $secondScriptSuccess = false;
            // Формирование названия json-файла с результатами обработки видео
            $landmarkModel->landmark_file_name = $jsonResultFile;
            // Формирование названия видео-файла с нанесенной цифровой маской
            $landmarkModel->processed_video_file_name = $videoResultFile;
            // Формирование описания цифровой маски
            $landmarkModel->description = $videoInterview->description . ' (время нарезки: ' .
                $landmarkModel->getStartTime() . ' - ' . $landmarkModel->getFinishTime() . ')';
            // Обновление атрибутов цифровой маски в БД
            $landmarkModel->updateAttributes(['landmark_file_name', 'processed_video_file_name', 'description']);
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
                // Декодирование json-файла с результатами обработки видео в виде цифровой маски
                $jsonLandmarkFile = json_decode($landmarkFile, true);
                // Если в json-файле с цифровой маской есть текст с предупреждением
                if (isset($jsonLandmarkFile['err_msg']))
                    // Сохранение сообщения о предупреждении МОВ Ивана
                    $messages .= ' ' . $jsonLandmarkFile['err_msg'];
                $firstScriptSuccess = true;
            }
            // Удаление записи о цифровой маски для которой не сформирован json-файл
            if ($firstScriptSuccess == false) {
                Landmark::findOne($landmarkModel->id)->delete();
                $messages .= ' Не удалось сформировать цифровую маску!';
            }

            $additionalLandmarkModel = null;
            // Если включен запуск второго скрипта МОВ Ивана
            if ((bool)Yii::$app->request->post('VideoProcessingModuleSettingForm')['enableSecondScript']) {
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
                $additionalLandmarkModel->description = 'Цифровая маска получена на основе цифровой маски №' .
                    $landmarkModel->id;
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
                    // Сохранение сообщения об ошибке второго скрипта МОВ Ивана
                    $messages = 'Ошибка второго скрипта модуля обработки видео Ивана! ' . $e->getMessage();
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
                    if (isset($jsonLandmarkFile['err_msg']))
                        // Сохранение сообщения о предупреждении МОВ Ивана
                        $messages .= ' ' . $jsonLandmarkFile['err_msg'];
                    $secondScriptSuccess = true;
                }
                // Удаление записи о цифровой маски для которой не сформирован json-файл
                if ($secondScriptSuccess == false) {
                    Landmark::findOne($additionalLandmarkModel->id)->delete();
                    $messages .= ' Не удалось сформировать цифровую маску вторым скриптом МОВ Ивана!';
                }
                // Удаление видео-файла с результатами обработки видеоинтервью вторым скриптом МОВ ИВана
                if (file_exists($jsonResultPath . $extVideoResultFile))
                    unlink($jsonResultPath . $extVideoResultFile);
                // Удаление json-файла с результатами обработки видео вторым скриптом МОВ ИВана
                if (file_exists($jsonResultPath . $extJsonResultFile))
                    unlink($jsonResultPath . $extJsonResultFile);
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

            if ($messages != '')
                // Вывод всех сообщений, сформированных по ходу формирования цифровой маски
                Yii::$app->getSession()->setFlash('warning', $messages);
            else {
                if (($firstScriptSuccess && !$secondScriptSuccess) || (!$firstScriptSuccess && $secondScriptSuccess))
                    // Вывод сообщения об успешном формировании одной цифровой маски
                    Yii::$app->getSession()->setFlash('success', 'Вы успешно сформировали цифровую маску!');
                if ($firstScriptSuccess && $secondScriptSuccess)
                    // Вывод сообщения об успешном формировании двух цифровых масок
                    Yii::$app->getSession()->setFlash('success', 'Вы успешно сформировали цифровые маски!');
            }

            if ($firstScriptSuccess == false)
                return $this->redirect(['/question/list']);
            else {
                if ($firstScriptSuccess && !$secondScriptSuccess)
                    return $this->redirect(['/landmark/view/' . $landmarkModel->id]);
                if ($secondScriptSuccess && $additionalLandmarkModel !== null)
                    return $this->redirect(['/landmark/view/' . $additionalLandmarkModel->id]);
            }
        }

        return false;
    }

    /**
     * Формирование текста распознанной речи.
     *
     * @param $id - идентификатор видео ответа на вопрос
     * @return bool|string
     */
    public function actionGetRecognizedSpeech($id) {
        // Установка времени выполнения скрипта в 3 часа
        set_time_limit(60 * 200);
        // Поиск видео ответа на вопрос по id
        $question = Question::findOne($id);
        // Если есть файл видео ответа на вопрос
        if ($question->video_file_name != null) {
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
            // Название json-файла с результатами обработки видео
            $jsonResultFile = 'out_' . $question->id . '_audio.json';
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
            $parameters['landmark_mode'] = VideoProcessingModuleSettingForm::LANDMARK_MODE_FAST;
            $parameters['parameters'] = VideoProcessingModuleSettingForm::PARAMETER_CHECK_VIDEO_PARAMETERS;
            // Формирование json-строки на основе массива с параметрами запуска программы обработки видео
            $jsonParameters = json_encode($parameters, JSON_UNESCAPED_UNICODE);
            // Открытие файла на запись для сохранения параметров запуска программы обработки видео
            $jsonFile = fopen($mainPath . 'test' . $question->id . '.json', 'a');
            // Запись в файл json-строки с параметрами запуска программы обработки видео
            fwrite($jsonFile, str_replace("\\", "", $jsonParameters));
            // Закрытие файла
            fclose($jsonFile);
            // Сообщение об ошибке формирования текста распознанной речи
            $errorMessage = '';
            // Текст распознанной речи
            $recognizedSpeechText = 'Текст отсутствует';

            try {
                // Запуск программы обработки видео Ивана
                chdir($mainPath);
                exec('./venv/bin/python ./main_audio.py ./test' . $question->id . '.json');
            } catch (Exception $e) {
                // Сохранение сообщения об ошибке МОВ Ивана
                $errorMessage = 'Ошибка модуля обработки видео Ивана (скрипт распознования речи)! ' . $e->getMessage();
            }

            // Проверка существования json-файл с результатами обработки видео
            if (file_exists($jsonResultPath . $jsonResultFile)) {
                // Получение json-файла с результатами обработки видео в виде текста распознанной речи
                $jsonRecognizedSpeechFile = file_get_contents($jsonResultPath . $jsonResultFile,
                    true);
                // Декодирование json-файла с результатами обработки видео в виде текста распознанной речи
                $recognizedSpeechFile = json_decode($jsonRecognizedSpeechFile, true);
                // Запоминание массива с распознанным текстом
                foreach ($recognizedSpeechFile as $key => $value)
                    if ($key == 'TEXT' && $value != null)
                        $recognizedSpeechText = $value;
            }

            // Удаление файла с видео ответом на вопрос
            if (file_exists($videoPath . $question->video_file_name))
                unlink($videoPath . $question->video_file_name);
            // Удаление файла с параметрами запуска программы обработки видео
            if (file_exists($mainPath . 'test' . $question->id . '.json'))
                unlink($mainPath . 'test' . $question->id . '.json');
            // Удаление json-файла с результатами обработки видео программой Ивана
            if (file_exists($jsonResultPath . $jsonResultFile))
                unlink($jsonResultPath . $jsonResultFile);

            if ($errorMessage != '')
                // Вывод сообщения об ошибке формирования текста распознанной речи
                Yii::$app->getSession()->setFlash('warning', $errorMessage);
            else
                if ($recognizedSpeechText != '') {
                    // Вывод сообщения об успешном формировании текста распознанной речи
                    Yii::$app->getSession()->setFlash('success', 'Распознование речи прошло успешно!');

                    return $this->render('recognized-speech-text', [
                        'model' => $question,
                        'recognizedSpeechText' => $recognizedSpeechText
                    ]);
                }
        } else {
            // Вывод сообщения
            Yii::$app->getSession()->setFlash('warning',
                'Отсутствует файл видео ответа на вопрос для распознования!');

            return $this->redirect(['/question/view/' . $question->id]);
        }

        return false;
    }

    /**
     * Формирование файла цифровой маски путем запуска модуля обработки видео Андрея.
     *
     * @param $id - идентификатор вопроса видеоинтервью
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionGetAndreyLandmarks($id) {
        // Установка времени выполнения скрипта в 1 час.
        set_time_limit(60 * 60);
        // Поиск вопроса видеоинтервью по id
        $question = Question::findOne($id);
        // Поиск полного видеоинтервью по id
        $videoInterview = VideoInterview::findOne($question->video_interview_id);
        // Путь к программе обработки видео от Андрея
        $mainAndrewModulePath = '/home/-Common/-andrey/';
        // Путь к файлу видеоинтервью
        $videoPath = $mainAndrewModulePath . 'video/';
        // Путь к json-файлу результатов обработки видео
        $jsonAndrewResultPath = $mainAndrewModulePath . 'Records/';
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Сохранение файла видео ответа на вопрос на сервер
        $osConnector->saveFileToServer(
            OSConnector::OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET,
            $question->id,
            $question->video_file_name,
            $videoPath
        );
        // Сообщения о ходе формирования цифровых масок
        $messages = '';

        try {
            // Формирование значения поворота видео
            $rotateMode = (int)Yii::$app->request->post('VideoProcessingModuleSettingForm')['rotateMode'];
            if ($rotateMode == VideoProcessingModuleSettingForm::ROTATE_MODE_ONE_HUNDRED_EIGHTY)
                $rotateMode = 3;
            if ($rotateMode == VideoProcessingModuleSettingForm::ROTATE_MODE_TWO_HUNDRED_AND_SEVENTY)
                $rotateMode = 2;
            // Формирование команды для запука программы обработки видео Андрея
            $command = './EmotionDetection -f ' . $videoPath . $question->video_file_name;
            // Добавление параметра поворота видео к команде
            if ($rotateMode != 0)
                $command .= ' -rot ' . $rotateMode;
            // Запуск программы обработки видео Андрея
            chdir($mainAndrewModulePath);
            exec($command);
            // Получение имени файла без расширения
            $jsonFileName = preg_replace('/\.\w+$/', '', $question->video_file_name);
            // Проверка существования json-файл с результатами обработки видео
            if (file_exists($jsonAndrewResultPath . $jsonFileName . '.json')) {
                // Создание цифровой маски в БД
                $landmarkModel = new Landmark();
                $landmarkModel->landmark_file_name = 'out_' . $question->id . '.json';
                $landmarkModel->start_time = '00:00:00:000';
                $landmarkModel->finish_time = '12:00:00:000';
                $landmarkModel->type = Landmark::TYPE_LANDMARK_ANDREW_MODULE;
                $landmarkModel->rotation = Landmark::TYPE_ZERO;
                $landmarkModel->mirroring = Landmark::TYPE_MIRRORING_FALSE;
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
                // Удаление json-файла с результатами обработки видео ответа на вопрос
                if (file_exists($jsonAndrewResultPath . $jsonFileName . '.json'))
                    unlink($jsonAndrewResultPath . $jsonFileName . '.json');
                // Удаление файла с видео ответа на вопрос
                if (file_exists($videoPath . $question->video_file_name))
                    unlink($videoPath . $question->video_file_name);

                // Вывод сообщения об успешном формировании цифровой маски
                Yii::$app->getSession()->setFlash('success', 'Вы успешно сформировали цифровую маску!');

                return $this->redirect(['/landmark/view/' . $landmarkModel->id]);
            }
        } catch (Exception $e) {
            // Сохранение сообщения об ошибке МОВ Андрея
            $messages = ' Ошибка модуля обработки видео Андрея! ' . $e->getMessage();
        }

        // Вывод всех сообщений, сформированных по ходу формирования цифровой маски
        Yii::$app->getSession()->setFlash('error', $messages);

        return $this->redirect(['/question/view/' . $id]);
    }

    /**
     * Finds the Question model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Question the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Question::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}