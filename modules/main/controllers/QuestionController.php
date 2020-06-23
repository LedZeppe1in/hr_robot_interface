<?php

namespace app\modules\main\controllers;

use Yii;
use Exception;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\components\OSConnector;
use app\modules\main\models\Question;

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

        return $this->render('list', [
            'dataProvider' => $dataProvider,
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
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Question model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        // Создание модели вопроса со сценарием создания нового вопроса
        $model = new Question(['scenario' => Question::CREATE_QUESTION_SCENARIO]);
        // Загрузка полей модели через POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла с формы
            $audioFile = UploadedFile::getInstance($model, 'audioFile');
            $model->audioFile = $audioFile;
            // Валидация поля файла
            if ($model->validate(['audioFile'])) {
                // Если пользователь загрузил файл с озвучкой вопроса
                if ($audioFile && $audioFile->tempName)
                    $model->audio_file_name = $model->audioFile->baseName . '.' . $model->audioFile->extension;
                // Сохранение данных о вопросе в БД
                if ($model->save()) {
                    // Создание объекта коннектора с Yandex.Cloud Object Storage
                    $osConnector = new OSConnector();
                    // Сохранение файла с озвучкой вопроса на Object Storage
                    if ($model->audio_file_name != '')
                        $osConnector->saveFileToObjectStorage(
                            OSConnector::OBJECT_STORAGE_AUDIO_BUCKET,
                            $model->id,
                            $model->audio_file_name,
                            $audioFile->tempName
                        );
                    // Вывод сообщения об удачном вводе нового вопроса
                    Yii::$app->getSession()->setFlash('success', 'Вы успешно добавили новый вопрос!');

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Question model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        // Поиск модели вопроса по id
        $model = $this->findModel($id);
        // Подстановка времени в правильном формате
        $model->time = $model->getTime();
        // Загрузка полей модели через POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла с формы
            $audioFile = UploadedFile::getInstance($model, 'audioFile');
            $model->audioFile = $audioFile;
            // Валидация поля файла
            if ($model->validate(['audioFile'])) {
                // Старое название файла с озвучкой вопроса
                $old_audio_file_name = $model->audio_file_name;
                // Если пользователь загрузил файл с озвучкой вопроса
                if ($audioFile && $audioFile->tempName)
                    $model->audio_file_name = $model->audioFile->baseName . '.' . $model->audioFile->extension;
                // Сохранение данных о вопросе в БД
                if ($model->save()) {
                    // Если пользователь загрузил файл с озвучкой вопроса
                    if ($audioFile && $audioFile->tempName) {
                        // Создание объекта коннектора с Yandex.Cloud Object Storage
                        $osConnector = new OSConnector();
                        // Удаление старого файла с озвучкой вопроса на Object Storage
                        $osConnector->removeFileFromObjectStorage(
                            OSConnector::OBJECT_STORAGE_AUDIO_BUCKET,
                            $model->id,
                            $old_audio_file_name
                        );
                        // Сохранение нового файла с озвучкой вопроса на Object Storage
                        $osConnector->saveFileToObjectStorage(OSConnector::OBJECT_STORAGE_AUDIO_BUCKET,
                            $model->id, $model->audio_file_name, $audioFile->tempName);
                    }
                    // Вывод сообщения об удачном обновлении
                    Yii::$app->getSession()->setFlash('success', 'Вы успешно обновили вопрос!');

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
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
        // Поиск модели вопроса по id
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Удаление файла с озвучкой вопроса на Object Storage
        $osConnector->removeFileFromObjectStorage(
            OSConnector::OBJECT_STORAGE_AUDIO_BUCKET,
            $model->id,
            $model->audio_file_name
        );
        // Удалние записи из БД
        $model->delete();
        // Вывод сообщения об успешном удалении
        Yii::$app->getSession()->setFlash('success', 'Вы успешно удалили вопрос!');

        return $this->redirect(['list']);
    }

    /**
     * Скачивание файла с озвучкой вопроса.
     *
     * @param $id
     * @return \yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionAudioFileDownload($id)
    {
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Скачивание файла с озвучкой вопроса с Object Storage
        if ($model->audio_file_name != '') {
            $result = $osConnector->downloadFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_AUDIO_BUCKET,
                $model->id,
                $model->audio_file_name
            );
            return $result;
        }
        throw new Exception('Файл не найден!');
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