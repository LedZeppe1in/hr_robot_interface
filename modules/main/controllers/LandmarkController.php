<?php

namespace app\modules\main\controllers;

use Yii;
use Exception;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\components\OSConnector;
use app\modules\main\models\Landmark;
use app\modules\main\models\Question;

/**
 * LandmarkController implements the CRUD actions for AdvancedLandmark model.
 */
class LandmarkController extends Controller
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
     * Lists all Landmark models.
     * @return mixed
     */
    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Landmark::find(),
        ]);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Landmark model.
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
     * Creates a new Landmark model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpload()
    {
        // Создание модели цифровой маски со сценарием загрузки новой цифровой маски
        $model = new Landmark(['scenario' => Landmark::UPLOAD_LANDMARK_SCENARIO]);
        // Формирование списка вопросов
        $questions = ArrayHelper::map(Question::find()->all(), 'id', 'text');
        // POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла с формы
            $landmarkFile = UploadedFile::getInstance($model, 'landmarkFile');
            $model->landmarkFile = $landmarkFile;
            // Валидация поля файла
            if ($model->validate(['landmarkFile'])) {
                // Если пользователь загрузил файл с лицевыми точками
                if ($landmarkFile && $landmarkFile->tempName)
                    $model->landmark_file_name = $model->landmarkFile->baseName . '.' . $model->landmarkFile->extension;
                // Получение значения текста вопроса
                $questionText = Yii::$app->request->post('Landmark')['questionText'];
                // Если поле текста вопроса содержит значение "hidden"
                if ($questionText != 'hidden') {
                    // Создание и сохранение новой модели вопроса
                    $questionModel = new Question();
                    $questionModel->text = $questionText;
                    $questionModel->save();
                    // Формирование id вопроса
                    $model->question_id = $questionModel->id;
                }
                // Сохранение данных о цифровой маски в БД
                if ($model->save()) {
                    // Создание объекта коннектора с Yandex.Cloud Object Storage
                    $osConnector = new OSConnector();
                    // Сохранение файла с цифровой маской на Object Storage
                    if ($model->landmark_file_name != '')
                        $osConnector->saveFileToObjectStorage(
                            OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                            $model->id,
                            $model->landmark_file_name,
                            $landmarkFile->tempName
                        );
                    // Вывод сообщения об удачной загрузке
                    Yii::$app->getSession()->setFlash('success',
                        'Вы успешно загрузили файл с цифровой маской!');

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('upload', [
            'model' => $model,
            'questions' => $questions,
        ]);
    }

    /**
     * Updates an existing Landmark model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        // Поиск модели цифровой маски по id
        $model = $this->findModel($id);
        // Формирование списка вопросов
        $questions = ArrayHelper::map(Question::find()->all(), 'id', 'text');
        // POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла с формы
            $landmarkFile = UploadedFile::getInstance($model, 'landmarkFile');
            $model->landmarkFile = $landmarkFile;
            // Валидация поля файла
            if ($model->validate(['landmarkFile'])) {
                // Старое название файла с лицевыми точками
                $old_landmark_file_name = $model->landmark_file_name;
                // Если пользователь загрузил файл с лицевыми точками
                if ($landmarkFile && $landmarkFile->tempName)
                    $model->landmark_file_name = $model->landmarkFile->baseName . '.' . $model->landmarkFile->extension;
                // Сохранение данных о цифровой маски в БД
                if ($model->save()) {
                    // Если пользователь загрузил файл с лицевыми точками
                    if ($landmarkFile && $landmarkFile->tempName) {
                        // Создание объекта коннектора с Yandex.Cloud Object Storage
                        $osConnector = new OSConnector();
                        // Удаление старого файла с цифровой маской на Object Storage
                        $osConnector->removeFileFromObjectStorage(
                            OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                            $model->id,
                            $old_landmark_file_name
                        );
                        // Сохранение нового файла с цифровой маской на Object Storage
                        $osConnector->saveFileToObjectStorage(OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                            $model->id, $model->landmark_file_name, $landmarkFile->tempName);
                    }
                    // Вывод сообщения об удачной загрузке
                    Yii::$app->getSession()->setFlash('success', 'Вы успешно обновили цифровую маску!');

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
            'questions' => $questions,
        ]);
    }

    /**
     * Deletes an existing Landmark model.
     * If deletion is successful, the browser will be redirected to the 'list' page.
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        // Удалние записи из БД
        $model->delete();
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Удаление файла с лицевыми точками на Object Storage
        if ($model->landmark_file_name != '')
            $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $model->id, $model->landmark_file_name);
        // Вывод сообщения об успешном удалении
        Yii::$app->getSession()->setFlash('success',
            'Вы успешно удалили файл с цифровой маской!');

        return $this->redirect(['list']);
    }

    /**
     * Скачивание файла с лицевыми точками.
     *
     * @param $id
     * @return \yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionLandmarkFileDownload($id)
    {
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Скачивание файла с лицевыми точками с Object Storage
        if ($model->landmark_file_name != '') {
            $result = $osConnector->downloadFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $model->id,
                $model->landmark_file_name
            );
            return $result;
        }
        throw new Exception('Файл не найден!');
    }

    /**
     * Finds the Landmark model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Landmark the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Landmark::findOne($id)) !== null)
            return $model;

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}