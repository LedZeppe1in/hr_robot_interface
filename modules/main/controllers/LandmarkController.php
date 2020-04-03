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
use app\modules\main\models\Landmark;

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
        $model = new Landmark();
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
                // Сохранение данных о цифровой маски в БД
                if ($model->save()) {
                    // Создание объекта коннектора с Yandex.Cloud Object Storage
                    $dbConnector = new OSConnector();
                    // Сохранение файла с цифровой маской на Object Storage
                    if ($model->landmark_file_name != '')
                        $dbConnector->saveFileToObjectStorage(
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
        $dbConnector = new OSConnector();
        // Удаление файла с лицевыми точками на Object Storage
        if ($model->landmark_file_name != '')
            $dbConnector->removeFileToObjectStorage(OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
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
        $dbConnector = new OSConnector();
        // Скачивание файла с лицевыми точками с Object Storage
        if ($model->landmark_file_name != '') {
            $result = $dbConnector->downloadFileToObjectStorage(
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