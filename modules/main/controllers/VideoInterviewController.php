<?php

namespace app\modules\main\controllers;

use Yii;
use Exception;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use app\components\OSConnector;
use app\modules\main\models\Landmark;
use app\modules\main\models\Question;
use app\modules\main\models\TestQuestion;
use app\modules\main\models\AnalysisResult;
use app\modules\main\models\VideoInterview;

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

        return $this->render('list', [
            'dataProvider' => $dataProvider,
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
        return $this->render('view', [
            'model' => $this->findModel($id),
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
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionGetIvanLandmarks($id) {
        // Установка времени выполнения скрипта в 1 час.
        set_time_limit(60 * 60);
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
        $landmarkModel->rotation = Landmark::TYPE_ZERO;
        $landmarkModel->mirroring = Landmark::TYPE_MIRRORING_FALSE;
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
        $videoResultFile = 'out_' . $landmarkModel->id . '.avi';
        // Название json-файла с результатами обработки видео
        $jsonResultFile = 'out_' . $landmarkModel->id . '.json';
        // Название аудио-файла (mp3) с результатами обработки видео
        $audioResultFile = 'out_' . $landmarkModel->id . '.mp3';
        // Формирование информации по вопросу
        $questionParameter = array();
        $questionParameter['id'] = $landmarkModel->id;
        $questionParameter['start'] = 0;
        $questionParameter['finish'] = $landmarkModel->finish_time - $landmarkModel->start_time;
        // Формирование массива с параметрами запуска программы обработки видео
        $parameters['nameVidFilesIn'] = 'video/' . $videoInterview->video_file_name;
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
        $jsonFile = fopen($mainPath . 'test' . $questionModel->id . '.json', 'a');
        // Запись в файл json-строки с параметрами запуска программы обработки видео
        fwrite($jsonFile, str_replace("\\", "", $jsonParameters));
        // Закрытие файла
        fclose($jsonFile);
        try {
            // Запуск программы обработки видео Ивана
            chdir($mainPath);
            exec('./venv/bin/python ./main.py ./test' . $questionModel->id . '.json');
        } catch (Exception $e) {
            // Сохранение сообщения об ошибке МОВ Ивана
            $messages = 'Ошибка модуля обработки видео Ивана! ' . $e->getMessage();
        }

        $success = false;
        // Формирование названия json-файла с результатами обработки видео
        $landmarkModel->landmark_file_name = 'out_' . $landmarkModel->id . '.json';
        // Формирование названия видео-файла с нанесенной цифровой маской
        $landmarkModel->processed_video_file_name = 'out_' . $landmarkModel->id . '.avi';
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
            $success = true;
        }
        // Удаление записи о цифровой маски для которой не сформирован json-файл
        if ($success == false)
            Landmark::findOne($landmarkModel->id)->delete();
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
        else
            // Вывод сообщения об успешном формировании цифровой маски
            Yii::$app->getSession()->setFlash('success', 'Вы успешно сформировали цифровую маску!');

        return $this->redirect(['/landmark/view/' . $landmarkModel->id]);
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
        // Установка времени выполнения скрипта в 1 час.
        set_time_limit(60 * 60);
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
            // Запуск программы обработки видео Андрея
            chdir($mainAndrewModulePath);
            exec('./EmotionDetection -f ' . $videoPath . $videoInterview->video_file_name);
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
     * Формирование файла цифровой маски путем запуска модуля обработки видео.
     *
     * @param $id - идентификатор видеоинтервью
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionGetLandmarks($id)
    {
//        // Создание модели видеоинтервью со сценарием анализа
//        $model = $this->findModel($id);
//        // Создание массива с моделями цифровой маски
//        $landmarkModels = [new Landmark()];
//        // Формирование списка вопросов
//        $questions = ArrayHelper::map(Question::find()->all(), 'id', 'text');
//        // Загрузка и сохранение данных, пришедших методом POST
//        if ($model->loadAll(Yii::$app->request->post()) && $model->save()) {
//            // Путь к программе обработки видео от Ивана
//            $mainPath = '/home/-Common/-ivan/';
//            // Путь к файлу видеоинтервью
//            $videoPath = $mainPath . 'video/';
//            // Путь к json-файлу результатов обработки видеоинтервью
//            $jsonResultPath = $mainPath . 'json/';
//            // Создание объекта коннектора с Yandex.Cloud Object Storage
//            $osConnector = new OSConnector();
//            // Сохранение файла видеоинтервью из Object Storage на сервер
//            $model->videoInterviewFile = $osConnector->saveFileToServer(
//                OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
//                $model->id,
//                $model->video_file_name,
//                $videoPath
//            );
//            // Массивы для хранения параметров результатов обработки видео
//            $videoResultFiles = array();
//            $jsonResultFiles = array();
//            $audioResultFiles = array();
//            $questions = array();
//            // Массив для хранения сообщений о предупреждениях
//            $warningMassages = array();
//            // Получение значения поворота
//            $rotation = (int)Yii::$app->request->post('VideoInterview')['rotationParameter'];
//            // Создание цифровых масок в БД
//            $index = 0;
//            for ($i = 0; $i <= 100; $i++)
//                if (isset(Yii::$app->request->post('Landmark')[$index])) {
//                    $landmarkModel = new Landmark();
//                    $landmarkModel->start_time = Yii::$app->request->post('Landmark')[$index]['start_time'];
//                    $landmarkModel->finish_time = Yii::$app->request->post('Landmark')[$index]['finish_time'];
//                    $landmarkModel->type = Landmark::TYPE_LANDMARK_IVAN_MODULE;
//                    $landmarkModel->rotation = $rotation;
//                    $landmarkModel->mirroring = boolval(Yii::$app->request
//                        ->post('VideoInterview')['mirroringParameter']);
//                    $landmarkModel->question_id = Yii::$app->request->post('Landmark')[$index]['question_id'];
//                    $landmarkModel->video_interview_id = $model->id;
//                    $landmarkModel->save();
//                    $index++;
//                }
//            // Выборка всех созданных цифровых масок у данного видеоинтервью при запросе
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
//            $parameters['rotate_mode'] = $rotation;
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
//            // Удаление файла с видеоинтервью
//            if (file_exists($videoPath . $model->video_file_name))
//                unlink($videoPath . $model->video_file_name);
//            // Удаление файла с параметрами запуска программы обработки видео
//            if (file_exists($mainPath . 'test.json'))
//                unlink($mainPath . 'test.json');
//            // Удаление файла с выходной аудио-информацией
//            if (file_exists($mainPath . 'audio_out.mp3'))
//                unlink($mainPath . 'audio_out.mp3');
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
//                            $landmarkModel->start_time = Yii::$app->request->post('Landmark')[$key]['start_time'];
//                            $landmarkModel->finish_time = Yii::$app->request->post('Landmark')[$key]['finish_time'];
//                            $landmarkModel->type = Landmark::TYPE_LANDMARK_ANDREW_MODULE;
//                            $landmarkModel->rotation = $rotation;
//                            $landmarkModel->mirroring = boolval(Yii::$app->request
//                                ->post('VideoInterview')['mirroringParameter']);
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
//                            // Удаление json-файлов с результатами обработки видеоинтервью программой Андрея
//                            unlink($jsonAndrewResultPath . $jsonFileName . '.json');
//                        }
//                    } catch (Exception $e) {
//                        // Вывод сообщения об ошибке обработки видеоинтервью от программы Андрея
//                        Yii::$app->getSession()->setFlash('error',
//                            'При обработке видеоинтервью программой Андрея возникли ошибки!');
//                    }
//                    // Удаление видео-файлов с результатами обработки видеоинтервью
//                    unlink($jsonResultPath . $videoResultFile);
//                }
//            // Удаление json-файлов с результатами обработки видеоинтервью программой Ивана
//            foreach ($jsonResultFiles as $jsonResultFile)
//                if (file_exists($jsonResultPath . $jsonResultFile))
//                    unlink($jsonResultPath . $jsonResultFile);
//            // Удаление фудио-файлов с результатами обработки видеоинтервью программой Ивана
//            foreach ($audioResultFiles as $audioResultFile)
//                if (file_exists($jsonResultPath . $audioResultFile))
//                    unlink($jsonResultPath . $audioResultFile);
//            // Выборка последней добавленной цифровой маски для данного видеоинтервью
//            $landmark = Landmark::find()
//                ->where(['video_interview_id' => $model->id])
//                ->orderBy(['id' => SORT_DESC])
//                ->one();
//            // Если цифровая маска найдена
//            if ($landmark != '') {
//                // Дополнение текста сообщения об ошибке - ошибками по отдельным вопросам
//                if (empty($warningMassages))
//                    // Вывод сообщения об успешном формировании цифровой маски
//                    Yii::$app->getSession()->setFlash('success',
//                        'Вы успешно сформировали цифровую маску!');
//                else {
//                    // Формирование сообщения с предупреждением
//                    $message = 'Цифровая маска сформирована! Внимание! ';
//                    foreach ($warningMassages as $warningMassage)
//                        $message .= PHP_EOL . $warningMassage;
//                    Yii::$app->getSession()->setFlash('warning', $message);
//                }
//
//                return $this->redirect(['/landmark/view/' . $landmark->id]);
//            } else {
//                // Текст сообщения об ошибке
//                $errorMessage = 'Для данного видеоинтервью не удалось сформировать цифровцю маску!';
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
//                    $jsonFile = file_get_contents($jsonResultPath . 'out_error.json', true);
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
//        }
//
//        return $this->render('get-landmarks', [
//            'model' => $model,
//            'landmarkModels' => $landmarkModels,
//            'questions' => $questions
//        ]);
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