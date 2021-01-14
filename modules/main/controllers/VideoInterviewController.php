<?php

namespace app\modules\main\controllers;

use Yii;
use stdClass;
use Exception;
use SoapClient;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use vova07\console\ConsoleRunner;
use app\components\OSConnector;
use app\components\AnalysisHelper;
use app\components\FacialFeatureDetector;
use app\modules\main\models\Landmark;
use app\modules\main\models\Question;
use app\modules\main\models\FinalResult;
use app\modules\main\models\TopicQuestion;
use app\modules\main\models\AnalysisResult;
use app\modules\main\models\VideoInterview;
use app\modules\main\models\ProfileSurvey;
use app\modules\main\models\SurveyQuestion;
use app\modules\main\models\ProfileKnowledgeBase;
use app\modules\main\models\VideoProcessingModuleSettingForm;

/**
 * VideoInterviewController implements the CRUD actions for VideoInterview model.
 */
class VideoInterviewController extends Controller
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
                'only' => ['list', 'upload', 'view', 'update', 'delete', 'video-download',
                    'get-ivan-landmarks', 'get-andrey-landmarks', 'get-recognized-speech',
                    'run-analysis', 'run-features-detection', 'run-features-interpretation',
                    'delete-all-analysis-results'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['list', 'upload', 'view', 'update', 'delete', 'video-download',
                            'get-ivan-landmarks', 'get-andrey-landmarks', 'get-recognized-speech',
                            'run-analysis', 'run-features-detection', 'run-features-interpretation',
                            'delete-all-analysis-results'],
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
     * Lists all VideoInterview models.
     * @return mixed
     */
    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => VideoInterview::find(),
        ]);
        // Создание формы настройки параметров запуска модуля обработки видео Ивана и Андрея
        $videoProcessingModuleSettingForm = new VideoProcessingModuleSettingForm();

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'videoProcessingModuleSettingForm' => $videoProcessingModuleSettingForm
        ]);
    }

    /**
     * Displays a single VideoInterview model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // Создание формы настройки параметров запуска модуля обработки видео Ивана и Андрея
        $videoProcessingModuleSettingForm = new VideoProcessingModuleSettingForm();

        return $this->render('view', [
            'model' => $this->findModel($id),
            'videoProcessingModuleSettingForm' => $videoProcessingModuleSettingForm
        ]);
    }

    /**
     * Creates a new VideoInterview model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpload()
    {
        // Установка времени выполнения скрипта в 10 мин.
        set_time_limit(60*10);
        // Создание модели видео-интервью
        $model = new VideoInterview(['scenario' => VideoInterview::VIDEO_INTERVIEW_ANALYSIS_SCENARIO]);
        // POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла с формы
            $videoInterviewFile = UploadedFile::getInstance($model, 'videoInterviewFile');
            $model->videoInterviewFile = $videoInterviewFile;
            // Валидация поля файла
            if ($model->validate(['videoInterviewFile'])) {
                // Если пользователь загрузил файл видеоинтервью
                if ($videoInterviewFile && $videoInterviewFile->tempName)
                    $model->video_file_name = $model->videoInterviewFile->baseName . '.' .
                        $model->videoInterviewFile->extension;
                // Сохранение данных о видеоинтервью в БД
                if ($model->save()) {
                    // Создание объекта коннектора с Yandex.Cloud Object Storage
                    $osConnector = new OSConnector();
                    // Сохранение файла видеоинтервью на Object Storage
                    if ($model->video_file_name != '')
                        $osConnector->saveFileToObjectStorage(OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                            $model->id, $model->video_file_name, $videoInterviewFile->tempName);
                    // Вывод сообщения об удачной загрузке
                    Yii::$app->getSession()->setFlash('success', 'Вы успешно загрузили видеоинтервью!');

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('upload', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing VideoInterview model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        // POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла с формы
            $videoInterviewFile = UploadedFile::getInstance($model, 'videoInterviewFile');
            $model->videoInterviewFile = $videoInterviewFile;
            // Валидация поля файла
            if ($model->validate(['videoInterviewFile'])) {
                // Старое название файла видеоинтервью
                $old_video_file_name = $model->video_file_name;
                // Если пользователь загрузил файл видеоинтервью
                if ($videoInterviewFile && $videoInterviewFile->tempName)
                    // Формирование нового названия файла видеоинтервью
                    $model->video_file_name = $model->videoInterviewFile->baseName . '.' .
                        $model->videoInterviewFile->extension;
                // Сохранение данных о видеоинтервью в БД
                if ($model->save()) {
                    // Если пользователь загрузил файл видеоинтервью
                    if ($videoInterviewFile && $videoInterviewFile->tempName) {
                        // Создание объекта коннектора с Yandex.Cloud Object Storage
                        $osConnector = new OSConnector();
                        // Удаление старого файла видеоинтервью на Object Storage
                        $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                            $model->id, $old_video_file_name);
                        // Сохранение нового файла видеоинтервью на Object Storage
                        $osConnector->saveFileToObjectStorage(OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                            $model->id, $model->video_file_name, $videoInterviewFile->tempName);
                    }
                    // Вывод сообщения об удачной загрузке
                    Yii::$app->getSession()->setFlash('success', 'Вы успешно обновили видеоинтервью!');

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing VideoInterview model.
     * If deletion is successful, the browser will be redirected to the 'list' page.
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        // Поиск видеоинтервью по id
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Поиск цифровых масок для данного видеоинтервью
        $landmarks = Landmark::find()->where(['video_interview_id' => $model->id])->all();
        // Обход всех найденных цифровых масок
        foreach ($landmarks as $landmark) {
            // Поиск результатов анализа, проведенных для данной цифровой маски
            $analysisResults = AnalysisResult::find()->where(['landmark_id' => $landmark->id])->all();
            // Обход всех найденных результатов анализа
            foreach ($analysisResults as $analysisResult) {
                // Удаление файла с результатами определения признаков и фактами на Object Storage
                if ($analysisResult->detection_result_file_name != '')
                    $osConnector->removeFileFromObjectStorage(
                        OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                        $analysisResult->id,
                        $analysisResult->detection_result_file_name
                    );
                // Удаление файла с набором фактов на Object Storage
                if ($analysisResult->facts_file_name != '')
                    $osConnector->removeFileFromObjectStorage(
                        OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                        $analysisResult->id,
                        $analysisResult->facts_file_name
                    );
                // Удаление файла с результатами интерпретации признаков на Object Storage
                if ($analysisResult->interpretation_result_file_name != '')
                    $osConnector->removeFileFromObjectStorage(
                        OSConnector::OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET,
                        $analysisResult->id,
                        $analysisResult->interpretation_result_file_name
                    );
            }
            // Удаление файла с лицевыми точками на Object Storage
            if ($landmark->landmark_file_name != '')
                $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                    $landmark->id, $landmark->landmark_file_name);
            // Удаление файла видео с нанесенной цифровой маской на Object Storage
            if ($landmark->processed_video_file_name != '')
                $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                    $landmark->id, $landmark->processed_video_file_name);
        }
        // Поиск вопросов для данного видеоинтервью
        $questions = Question::find()->where(['video_interview_id' => $model->id])->all();
        // Обход всех найденных вопросов
        foreach ($questions as $question) {
            // Удаление файла видео с ответом на вопрос на Object Storage
            if ($question->video_file_name != '')
                $osConnector->removeFileFromObjectStorage(
                    OSConnector::OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET,
                    $question->id,
                    $question->video_file_name
                );
        }
        // Удаление файла видеоинтервью на Object Storage
        if ($model->video_file_name != '')
            $osConnector->removeFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                $model->id,
                $model->video_file_name
            );
        // Удалние записи из БД
        $model->delete();
        // Вывод сообщения об успешном удалении
        Yii::$app->getSession()->setFlash('success', 'Вы успешно удалили видеоинтервью!');

        return $this->redirect(['list']);
    }

    /**
     * Скачивание файла видеоинтервью.
     *
     * @param $id
     * @return \yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionVideoDownload($id)
    {
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Скачивание файла видеоинтервью с Object Storage
        if ($model->video_file_name != '') {
            $result = $osConnector->downloadFileFromObjectStorage(OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                $model->id, $model->video_file_name);
            return $result;
        }
        throw new Exception('Файл не найден!');
    }

    /**
     * Формирование файла цифровой маски путем запуска модуля обработки видео Ивана.
     *
     * @param $id - идентификатор видеоинтервью
     * @return bool|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionGetIvanLandmarks($id) {
        // Если пришел POST-запрос
        if (Yii::$app->request->isPost) {
            // Установка времени выполнения скрипта в 3 часа
            set_time_limit(60 * 200);
            // Поиск полного видеоинтервью по id
            $videoInterview = VideoInterview::findOne($id);
            // Создание вопроса видеоинтервью (видео ответа на вопрос) в БД
            $questionModel = new Question();
            $questionModel->description = 'Запись создана в рузультате повторного анализа файла полного видеоинтервью.';
            $questionModel->test_question_id = 48; // Калибровочный вопрос для профиля "Кассир"
            $questionModel->video_interview_id = $videoInterview->id;
            $questionModel->save();
            // Создание цифровой маски в БД
            $landmarkModel = new Landmark();
            $landmarkModel->start_time = '00:00:00:000';
            $landmarkModel->finish_time = '12:00:00:000';
            $landmarkModel->type = Landmark::TYPE_LANDMARK_IVAN_MODULE;
            $landmarkModel->rotation = (int)Yii::$app->request->post('VideoProcessingModuleSettingForm')['rotateMode'];
            $landmarkModel->mirroring = (bool)Yii::$app->request->post('VideoProcessingModuleSettingForm')['mirroring'];
            $landmarkModel->question_id = $questionModel->id;
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
            // Сохранение файла полного видеоинтервью на сервер
            $osConnector->saveFileToServer(
                OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                $videoInterview->id,
                $videoInterview->video_file_name,
                $videoPath
            );
            // Название видео-файла с результатами обработки видео
            $videoResultFile = 'out_' . $questionModel->id . '.avi';
            // Название json-файла с результатами обработки видео
            $jsonResultFile = 'out_' . $questionModel->id . '.json';
            // Название аудио-файла (mp3) с результатами обработки видео
            $audioResultFile = 'out_' . $questionModel->id . '.mp3';
            // Формирование массива с параметрами запуска программы обработки видео
            $parameters['nameVidFilesIn'] = 'video/' . $videoInterview->video_file_name;
            $parameters['nameVidFilesOut'] = 'json/out_{}.avi';
            $parameters['nameJsonFilesOut'] = 'json/out_{}.json';
            $parameters['nameAudioFilesOut'] = 'json/out_{}.mp3';
            $parameters['indexesTriagnleStats'] = [[21, 22, 28], [31, 48, 74], [31, 40, 74], [35, 54, 75],
                [35, 47, 75], [27, 35, 42], [27, 31, 39]];
            $parameters['rotate_mode'] = (int)Yii::$app->request->post('VideoProcessingModuleSettingForm')['rotateMode'];
            $parameters['enableAutoRotate'] = (bool)Yii::$app->request->post('VideoProcessingModuleSettingForm')['enableAutoRotate'];
            $parameters['Mirroring'] = (bool)Yii::$app->request->post('VideoProcessingModuleSettingForm')['mirroring'];
            $parameters['AlignMode'] = (int)Yii::$app->request->post('VideoProcessingModuleSettingForm')['alignMode'];
            $parameters['id'] = $questionModel->id;
            $parameters['landmark_mode'] = (int)Yii::$app->request->post('VideoProcessingModuleSettingForm')['landmarkMode'];
            $parameters['parameters'] = Yii::$app->request->post('VideoProcessingModuleSettingForm')['videoProcessingParameter'];
            // Формирование json-строки на основе массива с параметрами запуска программы обработки видео
            $jsonParameters = json_encode($parameters, JSON_UNESCAPED_UNICODE);
            // Открытие файла на запись для сохранения параметров запуска программы обработки видео
            $jsonFile = fopen($mainPath . 'test' . $questionModel->id . '.json', 'a');
            // Запись в файл json-строки с параметрами запуска программы обработки видео
            fwrite($jsonFile, str_replace("\\", "", $jsonParameters));
            // Закрытие файла
            fclose($jsonFile);
            try {
                // Запуск программы обработки видео Ивана
                chdir($mainPath);
                exec('./venv/bin/python ./main_new.py ./test' . $questionModel->id . '.json');
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
                $extVideoResultFile = 'out_' . $questionModel->id . '_ext.avi';
                // Название json-файла с результатами обработки видео
                $extJsonResultFile = 'out_' . $questionModel->id . '_ext.json';
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
                $additionalLandmarkModel->question_id = $questionModel->id;
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
            if (file_exists($videoPath . $videoInterview->video_file_name))
                unlink($videoPath . $videoInterview->video_file_name);
            // Удаление файла с параметрами запуска программы обработки видео
            if (file_exists($mainPath . 'test' . $questionModel->id . '.json'))
                unlink($mainPath . 'test' . $questionModel->id . '.json');
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
                return $this->redirect(['/video-interview/list']);
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
     * @param $id - идентификатор видеоинтервью
     * @return bool|string
     */
    public function actionGetRecognizedSpeech($id) {
        // Установка времени выполнения скрипта в 3 часа
        set_time_limit(60 * 200);
        // Поиск полного видеоинтервью по id
        $videoInterview = VideoInterview::findOne($id);
        // Если есть файл видеоинтервью
        if ($videoInterview->video_file_name != null) {
            // Путь к программе обработки видео от Ивана
            $mainPath = '/home/-Common/-ivan/';
            // Путь к файлу видеоинтервью
            $videoPath = $mainPath . 'video/';
            // Путь к json-файлу результатов обработки видеоинтервью
            $jsonResultPath = $mainPath . 'json/';
            // Создание объекта коннектора с Yandex.Cloud Object Storage
            $osConnector = new OSConnector();
            // Сохранение файла полного видеоинтервью на сервер
            $osConnector->saveFileToServer(
                OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                $videoInterview->id,
                $videoInterview->video_file_name,
                $videoPath
            );
            // Название json-файла с результатами обработки видео
            $jsonResultFile = 'out_' . $videoInterview->id . '_audio.json';
            // Формирование массива с параметрами запуска программы обработки видео
            $parameters['nameVidFilesIn'] = 'video/' . $videoInterview->video_file_name;
            $parameters['nameVidFilesOut'] = 'json/out_{}.avi';
            $parameters['nameJsonFilesOut'] = 'json/out_{}.json';
            $parameters['nameAudioFilesOut'] = 'json/out_{}.mp3';
            $parameters['indexesTriagnleStats'] = [[21, 22, 28], [31, 48, 74], [31, 40, 74], [35, 54, 75],
                [35, 47, 75], [27, 35, 42], [27, 31, 39]];
            $parameters['rotate_mode'] = VideoProcessingModuleSettingForm::ROTATE_MODE_ZERO;
            $parameters['enableAutoRotate'] = VideoProcessingModuleSettingForm::AUTO_ROTATE_TRUE;
            $parameters['Mirroring'] = VideoProcessingModuleSettingForm::MIRRORING_FALSE;
            $parameters['AlignMode'] = VideoProcessingModuleSettingForm::ALIGN_MODE_BY_THREE_FACIAL_POINTS;
            $parameters['id'] = $videoInterview->id;
            $parameters['landmark_mode'] = VideoProcessingModuleSettingForm::LANDMARK_MODE_FAST;
            $parameters['parameters'] = VideoProcessingModuleSettingForm::PARAMETER_CHECK_VIDEO_PARAMETERS;
            // Формирование json-строки на основе массива с параметрами запуска программы обработки видео
            $jsonParameters = json_encode($parameters, JSON_UNESCAPED_UNICODE);
            // Открытие файла на запись для сохранения параметров запуска программы обработки видео
            $jsonFile = fopen($mainPath . 'test' . $videoInterview->id . '.json', 'a');
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
                exec('./venv/bin/python ./main_audio.py ./test' . $videoInterview->id . '.json');
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
                        $recognizedSpeechText = json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            // Удаление файла с видеоинтервью
            if (file_exists($videoPath . $videoInterview->video_file_name))
                unlink($videoPath . $videoInterview->video_file_name);
            // Удаление файла с параметрами запуска программы обработки видео
            if (file_exists($mainPath . 'test' . $videoInterview->id . '.json'))
                unlink($mainPath . 'test' . $videoInterview->id . '.json');
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
                        'model' => $videoInterview,
                        'recognizedSpeechText' => $recognizedSpeechText
                    ]);
                }
        } else {
            // Вывод сообщения
            Yii::$app->getSession()->setFlash('warning',
                'Отсутствует файл полного видеоинтервью для распознования!');

            return $this->redirect(['/video-interview/view/' . $videoInterview->id]);
        }

        return false;
    }

    /**
     * Формирование файла цифровой маски путем запуска модуля обработки видео Андрея.
     *
     * @param $id - идентификатор видеоинтервью
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionGetAndreyLandmarks($id) {
        // Установка времени выполнения скрипта в 3 часа
        set_time_limit(60 * 200);
        // Поиск полного видеоинтервью по id
        $videoInterview = VideoInterview::findOne($id);
        // Путь к программе обработки видео от Андрея
        $mainAndrewModulePath = '/home/-Common/-andrey/';
        // Путь к файлу видеоинтервью
        $videoPath = $mainAndrewModulePath . 'video/';
        // Путь к json-файлу результатов обработки видео
        $jsonAndrewResultPath = $mainAndrewModulePath . 'Records/';
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Сохранение файла полного видеоинтервью на сервер
        $osConnector->saveFileToServer(
            OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
            $videoInterview->id,
            $videoInterview->video_file_name,
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
            $command = './EmotionDetection -f ' . $videoPath . $videoInterview->video_file_name;
            // Добавление параметра поворота видео к команде
            if ($rotateMode != 0)
                $command .= ' -rot ' . $rotateMode;
            // Запуск программы обработки видео Андрея
            chdir($mainAndrewModulePath);
            exec($command);
            // Получение имени файла без расширения
            $jsonFileName = preg_replace('/\.\w+$/', '', $videoInterview->video_file_name);
            // Проверка существования json-файл с результатами обработки видео
            if (file_exists($jsonAndrewResultPath . $jsonFileName . '.json')) {
                // Создание вопроса видеоинтервью (видео ответа на вопрос) в БД
                $questionModel = new Question();
                $questionModel->description = 'Запись создана в рузультате повторного анализа файла полного видеоинтервью.';
                $questionModel->test_question_id = 48; // Калибровочный вопрос для профиля "Кассир"
                $questionModel->video_interview_id = $videoInterview->id;
                $questionModel->save();
                // Создание цифровой маски в БД
                $landmarkModel = new Landmark();
                $landmarkModel->landmark_file_name = 'test.json';
                $landmarkModel->start_time = '00:00:00:000';
                $landmarkModel->finish_time = '12:00:00:000';
                $landmarkModel->type = Landmark::TYPE_LANDMARK_ANDREW_MODULE;
                $landmarkModel->rotation = Landmark::TYPE_ZERO;
                $landmarkModel->mirroring = Landmark::TYPE_MIRRORING_FALSE;
                $landmarkModel->description = $videoInterview->description . ' (время нарезки: ' .
                    $landmarkModel->start_time . ' - ' . $landmarkModel->finish_time . ')';
                $landmarkModel->question_id = $questionModel->id;
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
                // Удаление json-файла с результатами обработки видео ответа на вопрос
                if (file_exists($jsonAndrewResultPath . $jsonFileName . '.json'))
                    unlink($jsonAndrewResultPath . $jsonFileName . '.json');
                // Удаление файла с видеоинтервью
                if (file_exists($videoPath . $videoInterview->video_file_name))
                    unlink($videoPath . $videoInterview->video_file_name);

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

        return $this->redirect(['/video-interview/view/' . $id]);
    }

    /**
     * Запуск анализа видеоинтервью по всем вопросам (МОВ + МОП).
     *
     * @param $id - идентификатор видеоинтервью
     * @return bool|\yii\web\Response
     */
    public function actionRunAnalysis($id)
    {
        // Поиск всех видео ответов на вопросы для данного видеоинтервью
        $questions = Question::find()->where(['video_interview_id' => $id])->all();
        // Флаг существования калибровочного вопроса
        $calibrationQuestionFlag = false;
        // Обход всех видео ответов на вопросы
        foreach ($questions as $question) {
            // Поиск темы для вопроса - 27 (калибровочный для камеры)
            $topicQuestion = TopicQuestion::find()->where(['test_question_id' => $question->test_question_id])->one();
            // Если тема для вопроса найдена
            if (!empty($topicQuestion))
                // Если текущий вопрос является калибровочным
                if ($topicQuestion->topic_id == 27)
                    $calibrationQuestionFlag = true;
        }
        // Если у данного видеоинтервью есть калибровочный вопрос
        if ($calibrationQuestionFlag) {
            // Создание объекта запуска консольной команды
            $consoleRunner = new ConsoleRunner(['file' => '@app/yii']);
            // Выполнение команды определения базового кадра в фоновом режиме
            $consoleRunner->run('video-interview-analysis/start-base-frame-detection ' . $id);

            // Вывод сообщения об успешном запуске анализа видеоинтервью
            Yii::$app->getSession()->setFlash('success', 'Процесс анализа видеоинтервью успешно запущен!');

            return $this->redirect(['/video-interview/view/' . $id]);
        } else {
            // Вывод сообщения об отсутствии калибровочного вопроса
            Yii::$app->getSession()->setFlash('warning',
                'Процесс анализа видеоинтервью невозможен! В данном видеоинтервью отсутствует калибровочный вопрос.');

            return $this->redirect(['/video-interview/view/' . $id]);
        }
    }

    /**
     * Запуск МОП по всем цифровым маскам данного видеоинтервью.
     *
     * @param $id - идентификатор видеоинтервью
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionRunFeaturesDetection($id)
    {
        // Установка времени выполнения скрипта в 3 часа
        set_time_limit(60 * 200);
        $analysisResultIds = array();
        $errorMessages = '';
        // Поиск всех видео ответов на вопросы для данного видеоинтервью
        $questions = Question::find()->where(['video_interview_id' => $id])->all();
        // Обход всех видео ответов на вопросы
        foreach ($questions as $question) {
            // Поиск темы вопроса
            $topicQuestion = TopicQuestion::find()->where(['test_question_id' => $question->test_question_id])->one();
            // Если тема для вопроса найдена
            if (!empty($topicQuestion)) {
                // Если текущий вопрос не является калибровочным
                if ($topicQuestion->topic_id != 24 && $topicQuestion->topic_id != 25 && $topicQuestion->topic_id != 27) {
                    // Поиск цифровых масок полученных модулем Ивана для текущего вопроса видеоинтервью
                    $landmarks = Landmark::find()->where(['question_id' => $question->id, 'video_interview_id' => $id,
                        'type' => Landmark::TYPE_LANDMARK_IVAN_MODULE])->all();
                    // Если цифровые маски найдены
                    if (!empty($landmarks))
                        foreach ($landmarks as $landmark) {
                            // Если цифровая маска полученная не вторым скриптом Ивана
                            if (strripos($landmark->landmark_file_name, '_ext') === false) {
                                try {
                                    // Создание объекта AnalysisHelper
                                    $analysisHelper = new AnalysisHelper();
                                    // Получение базового кадра для видеоинтервью из json-файла на сервере
                                    $baseFrame = file_get_contents(Yii::$app->basePath .
                                        '/web/base-frame-' . $id . '.json', true);
                                    // Получение рузультатов анализа видеоинтервью (обработка модулем определения признаков)
                                    $analysisResultId = $analysisHelper->getAnalysisResult(
                                        $landmark,
                                        2, // Задание определения признаков по новому МОП
                                        $baseFrame,
                                        AnalysisHelper::NEW_FDM,
                                        null
                                    );
                                    // Сохранение id полученного результата определения признаков в массиве
                                    array_push($analysisResultIds, $analysisResultId);
                                } catch (Exception $e) {
                                    // Формирование текста с ошибкой работы МОП
                                    $errorMessages .= 'Ошибка МОП на данных Ивана для ЦМ id = ' . $landmark->id .
                                        ' Код ошибки: ' . $e->getMessage() . PHP_EOL;
                                }
                            }
                        }
                }
            }
        }
        // Если нет ошибки при работе МОП
        if ($errorMessages == '') {
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

            // Вывод сообщения об успешном определении статистики
            Yii::$app->getSession()->setFlash('success', 'Удачное определение статистики!');

            return $this->render('run-features-detection', [
                'model' => $this->findModel($id),
                'featureStatistics' => $featureStatistics,
                'summarizedFeatureStatistics' => $summarizedFeatureStatistics,
                'summarizedFeatureStatisticsFacts' => $summarizedFeatureStatisticsFacts,
            ]);
        } else {
            // Вывод сообщений об ошибках обработка МОП
            Yii::$app->getSession()->setFlash('warning', $errorMessages);

            return $this->redirect(['/video-interview/view/' . $id]);
        }
    }

    /**
     * Запус МИП первого и второго уровня для всех результатов определения признаков на данном видеоинтервью.
     *
     * @param $id - идентификатор видеоинтервью
     * @return \yii\web\Response
     * @throws \SoapFault
     */
    public function actionRunFeaturesInterpretation($id)
    {
        // Установка времени выполнения скрипта в 3 часа
        set_time_limit(60 * 200);
        // Поиск всех видео ответов на вопросы для данного видеоинтервью
        $questions = Question::find()->where(['video_interview_id' => $id])->all();
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
                $Landmarks = Landmark::find()->where(['video_interview_id' => $id])->all();
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
                // Запуск вывода по результатам интерпретации признаков (интерпретация второго уровня)
                ini_set('default_socket_timeout', 60 * 30);
                $addressOfRBRWebServiceDefinition = 'http://127.0.0.1:8888/RBRWebService?wsdl';
                $client = new SoapClient($addressOfRBRWebServiceDefinition);
                $addressForCodeOfKnowledgeBaseRetrieval = 'http://127.0.0.1/Drools/RetrieveData.php?DataSource=CodeOfKnowledgeBase&IDOfKnowledgeBase=' .
                    $profileKnowledgeBase->second_level_knowledge_base_id;
                $addressForInitialConditionsRetrieval = 'http://127.0.0.1/Drools/RetrieveData.php?DataSource=InitialDataOfReasoningProcess&Level=2&ID=' . $id;
                $addressToSendResults = 'http://127.0.0.1/Drools/RetrieveData.php';
                $additionalDataToSend = new stdClass;
                $additionalDataToSend->{'IDOfFile'} = $id;
                $additionalDataToSend->{'Type'} = 'Interpretation Level II';
                $client->LaunchReasoningProcessAndSendResultsToURL(array(
                    'arg0' => $addressForCodeOfKnowledgeBaseRetrieval,
                    'arg1' => $addressForInitialConditionsRetrieval,
                    'arg2' => $addressToSendResults,
                    'arg3' => 'ResultsOfReasoningProcess',
                    'arg4' => json_encode($additionalDataToSend)))->return;
                $client = Null;
            }

            // Если задана только база знаний для интерпретации первого уровня
            if ($profileKnowledgeBase->first_level_knowledge_base_id != null &&
                $profileKnowledgeBase->second_level_knowledge_base_id == null) {
                // Формирование массива идентификаторов из строки
                $analysisResultIdArray = explode(',', $analysisResultIds);
                // Вывод сообщения об успешной интерпретации 1 уровня
                Yii::$app->getSession()->setFlash('warning',
                    'Результаты МИП 1 увроня успешно получены для: ' . $analysisResultIds .
                    '. Результаты МИП 2 увроня сформировать не удалось, так как не задана база знаний!');

                return $this->redirect(['/interpretation-result/view/' . $analysisResultIdArray[0]]);
            }

            // Если заданы базы знаний для интерпретации первого и второго уровня
            if ($profileKnowledgeBase->first_level_knowledge_base_id != null &&
                $profileKnowledgeBase->second_level_knowledge_base_id != null) {
                // Поиск финального заключения для данного видеоинтервью
                $finalResult = FinalResult::find()->where(['video_interview_id' => $id])->one();
                // Если финальное заключение по данному видеоинтервью сформированно
                if (!empty($finalResult)) {
                    // Вывод сообщения об успешной интерпретации 1 и 2 уровня
                    Yii::$app->getSession()->setFlash('success',
                        'Результаты МИП 1 и 2 увроня успешно получены для: ' . $analysisResultIds .
                        '. База знаний: ' . $profileKnowledgeBase->secondLevelKnowledgeBase->name);

                    return $this->redirect(['/final-conclusion/view/' . $finalResult->id]);
                }
            }
        }

        // Вывод сообщения о неуспешной интерпретации
        Yii::$app->getSession()->setFlash('warning',
            'Интерпретация невозможна, так как не заданы базы знаний!');

        return $this->redirect(['/video-interview/view/' . $id]);
    }

    /**
     * Удаление всех результатов МОП и МИП из БД и Object Storage для данного видеоинтервью.
     *
     * @param $id - идентификатор видеоинтервью
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteAllAnalysisResults($id)
    {
        $analysisResultsExists = false;
        // Поиск видеоинтервью по id
        $model = $this->findModel($id);
        // Поиск цифровых масок для данного видеоинтервью
        $landmarks = Landmark::find()->where(['video_interview_id' => $model->id])->all();
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Обход всех найденных цифровых масок
        foreach ($landmarks as $landmark)
            // Если цифровая маска полученна не для вопроса
            if ($landmark->question_id != null) {
                // Поиск темы для вопроса
                $topicQuestion = TopicQuestion::find()
                    ->where(['test_question_id' => $landmark->question->test_question_id])
                    ->one();
                // Если тема для вопроса найдена
                if (!empty($topicQuestion))
                    // Если вопрос не калибровочный (темы 24, 25 и 27)
                    if ($topicQuestion->topic_id != 24 && $topicQuestion->topic_id != 25 &&
                        $topicQuestion->topic_id != 27) {
                        // Поиск результатов анализа, проведенных для данной цифровой маски
                        $analysisResults = AnalysisResult::find()->where(['landmark_id' => $landmark->id])->all();
                        // Если есть результаты анализа для данного видеоинтервью
                        if (!empty($analysisResults)) {
                            // Обход всех найденных результатов анализа
                            foreach ($analysisResults as $analysisResult) {
                                // Удаление файла с результатами определения признаков на Object Storage
                                if ($analysisResult->detection_result_file_name != '')
                                    $osConnector->removeFileFromObjectStorage(
                                        OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                                        $analysisResult->id,
                                        $analysisResult->detection_result_file_name
                                    );
                                // Удаление файла с набором фактов на Object Storage
                                if ($analysisResult->facts_file_name != '')
                                    $osConnector->removeFileFromObjectStorage(
                                        OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                                        $analysisResult->id,
                                        $analysisResult->facts_file_name
                                    );
                                // Удаление файла с результатами интерпретации признаков на Object Storage
                                if ($analysisResult->interpretation_result_file_name != '')
                                    $osConnector->removeFileFromObjectStorage(
                                        OSConnector::OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET,
                                        $analysisResult->id,
                                        $analysisResult->interpretation_result_file_name
                                    );
                                // Удаление результата анализа из БД
                                $analysisResult->delete();
                            }
                            $analysisResultsExists = true;
                        }
                    }
            }
        // Если были найдены результаты анализа видеоинтервью
        if ($analysisResultsExists)
            // Вывод сообщения об успешном удалении результатов МОП и МИП
            Yii::$app->getSession()->setFlash('success',
                'Вы успешно удалили все рузультаты МОП и МИП по данному видеоинтервью!');
        else
            // Вывод сообщения об успешном удалении результатов МОП и МИП
            Yii::$app->getSession()->setFlash('warning',
                'Для данного видеоинтервью нет рузультатов МОП и МИП!');

        return $this->redirect(['/video-interview/view/' . $id]);
    }

    /**
     * Finds the VideoInterview model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return VideoInterview the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = VideoInterview::findOne($id)) !== null)
            return $model;

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}