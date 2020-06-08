<?php

namespace app\modules\main\controllers;

use app\components\FacialFeatureDetector;
use app\modules\main\models\Question;
use Yii;
use Exception;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\components\OSConnector;
use app\modules\main\models\VideoInterview;
use app\modules\main\models\Landmark;
use app\modules\main\models\AnalysisResult;

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
        // Поиск цифровых масок для данного видеоинтервью
        $landmarks = Landmark::find()->where(['video_interview_id' => $model->id])->all();
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Обход всех найденных цифровых масок
        foreach ($landmarks as $landmark) {
            // Поиск результатов анализа, проведенных для данной цифровой маски
            $analysisResults = AnalysisResult::find()->where(['landmark_id' => $landmark->id])->all();
            // Обход всех найденных результатов анализа
            foreach ($analysisResults as $analysisResult) {
                // Удаление файлов с результатами определения признаков и фактами на Object Storage
                if ($analysisResult->detection_result_file_name != '')
                    $osConnector->removeFileFromObjectStorage(
                        OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                        $analysisResult->id,
                        $analysisResult->detection_result_file_name
                    );
                // Удаление файлов с набором фактов на Object Storage
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
        }
        // Удаление файла видеоинтервью на Object Storage
        if ($model->video_file_name != '')
            $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                $model->id, $model->video_file_name);
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
     * Формирование файла цифровой маски путем запуска модуля обработки видео.
     *
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionGetLandmarks($id)
    {
        // Установка времени выполнения скрипта в 10 мин.
        set_time_limit(60*10);
        // Создание модели видеоинтервью со сценарием анализа
        $model = $this->findModel($id);
        // Создание массива с моделями цифровой маски
        $landmarkModels = [new Landmark()];
        // Формирование списка вопросов
        $questions = ArrayHelper::map(Question::find()->all(), 'id', 'text');
        // Загрузка и сохранение данных, пришедших методом POST
        if ($model->loadAll(Yii::$app->request->post()) && $model->saveAll()) {
            // Путь к программе обработки видео
            $mainPath = '/home/-Common/-ivan/';
            // Путь к файлу видеоинтервью
            $videoPath = $mainPath . 'video/';
            // Путь к json-файлу результатов обработки видеоинтервью
            $jsonResultPath = $mainPath . 'json/';
            // Создание объекта коннектора с Yandex.Cloud Object Storage
            $osConnector = new OSConnector();
            // Сохранение файла видеоинтервью из Object Storage на сервер
            $model->videoInterviewFile = $osConnector->saveFileToServer(
                OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                $model->id,
                $model->video_file_name,
                $videoPath
            );
            // Получение значения поворота
            $rotation = (int)Yii::$app->request->post('VideoInterview')['rotationParameter'];
            // Получение значения наличия отзеркаливания
            $mirroring = Yii::$app->request->post('VideoInterview')['mirroringParameter'];
            // Массивы для хранения параметров результатов обработки видео
            $videoResultFiles = array();
            $jsonResultFiles = array();
            $questions = array();
            // Выборка всех цифровых масок у данного видеоинтервью
            $landmarks = Landmark::find()->where(['video_interview_id' => $model->id])->all();
            // Обход по всем найденным цифровым маскам
            foreach ($landmarks as $landmark) {
                // Добавление в массив названия видео-файла с результатами обработки видео
                array_push($videoResultFiles, 'out_' . $landmark->id . '.' .
                    pathinfo($model->video_file_name, PATHINFO_EXTENSION));
                // Добавление в массив названия json-файла с результатами обработки видео
                array_push($jsonResultFiles, 'out_' . $landmark->id . '.json');
                // Формирование информации по вопросу
                $question['id'] = $landmark->id;
                $question['start'] = $landmark->start_time;
                $question['finish'] = $landmark->finish_time;
                // Добавление в массив вопроса
                array_push($questions, $question);
            }
            // Формирование массива с параметрами запуска программы обработки видео
            $parameters['nameVidFilesIn'] = 'video/' . $model->video_file_name;
            $parameters['nameVidFilesOut'] = 'json/out_{}.avi';
            $parameters['nameJsonFilesOut'] = 'json/out_{}.json';
            $parameters['indexesTriagnleStats'] = [[21, 22, 28], [21, 48, 74], [31, 40, 74], [35, 54, 75],
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
                    $landmark->description = $model->description . ' (время нарезки: ' .
                        $landmark->getStartTime() . ' - ' . $landmark->getFinishTime() . ')';
                    // Формирование значения поворота
                    $landmark->rotation = $rotation;
                    // Формирование значения наличия отзеркаливания
                    $landmark->mirroring = boolval($mirroring);
                    // Обновление атрибутов цифровой маски в БД
                    $landmark->updateAttributes(['landmark_file_name', 'description', 'rotation',
                        'mirroring', 'question_id']);
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
                    } else
                        // Удаление записи о цифровой маски для которой не сформирован json-файл
                        Landmark::findOne($landmark->id)->delete();
                }
                // Изменение статуса обработки видеоинтервью
                $success = true;
            } catch (Exception $e) {
                // Вывод сообщения об ошибке обработки видеоинтервью
                Yii::$app->getSession()->setFlash('error',
                    'При обработке видеоинтервью возникли ошибки!');
            }
            // Удаление файла с видеоинтервью
            if (file_exists($videoPath . $model->video_file_name))
                unlink($videoPath . $model->video_file_name);
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
                // Выборка последней добавленной цифровой маски для данного видеоинтервью
                $landmark = Landmark::find()
                    ->where(['video_interview_id' => $model->id])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();
                if ($landmark != '') {
                    // Вывод сообщения об успешном анализе видеоинтервью
                    Yii::$app->getSession()->setFlash('success',
                        'Вы успешно сформировали цифровые маски!');

                    return $this->redirect(['/landmark/view/' . $landmark->id]);
                } else {
                    // Вывод сообщения о неуспешном анализе видеоинтервью
                    Yii::$app->getSession()->setFlash('error',
                        'Цифровые маски сформировать не удалось!');

                    return $this->redirect(['/landmark/list/']);
                }
            }
        }

        return $this->render('get-landmarks', [
            'model' => $model,
            'landmarkModels' => $landmarkModels,
            'questions' => $questions
        ]);
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