<?php

namespace app\modules\main\controllers;

use Yii;
use app\modules\main\models\VideoInterview;
use yii\data\ActiveDataProvider;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

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
        $model = new VideoInterview();
        // POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            $videoInterviewFile = UploadedFile::getInstance($model, 'videoInterviewFile');
            $landmarkFile = UploadedFile::getInstance($model, 'landmarkFile');
            if ($videoInterviewFile && $videoInterviewFile->tempName && $landmarkFile && $landmarkFile->tempName) {
                $model->videoInterviewFile = $videoInterviewFile;
                $model->landmarkFile = $landmarkFile;
                if ($model->validate(['videoInterviewFile']) && $model->validate(['landmarkFile'])) {
                    // Формирование пути к файлам с видеоинтервью и лицевыми точками
                    $dir = Yii::getAlias('@webroot') . '/uploads/video-interview/';
                    $videoInterviewFileName = $model->videoInterviewFile->baseName . '.' .
                        $model->videoInterviewFile->extension;
                    $model->video_file = $dir . $videoInterviewFileName;
                    $landmarkFileName = $model->landmarkFile->baseName . '.' . $model->landmarkFile->extension;
                    $model->landmark_file = $dir . $landmarkFileName;
                    // Формирование названия видеоинтервью
                    $model->name = $videoInterviewFileName;
                    // Сохранение данных о видео-интервью
                    if ($model->save()) {
                        // Формирование новой директории для файлов с видеоинтервью и лицевыми точками
                        $dir .= $model->id . '/';
                        // Создание новой директории для файлов с видеоинтервью и лицевыми точками
                        FileHelper::createDirectory($dir);
                        // Обновление пути к для файлов с видеоинтервью и лицевыми точками
                        $model->updateAttributes(['video_file' => $dir . $videoInterviewFileName]);
                        $model->updateAttributes(['landmark_file' => $dir . $landmarkFileName]);
                        // Сохранение файлов с видеоинтервью и лицевыми точками
                        $model->videoInterviewFile->saveAs($dir . $videoInterviewFileName);
                        $model->landmarkFile->saveAs($dir . $landmarkFileName);
                        Yii::$app->getSession()->setFlash('success', 'Вы успешно загрузили видеоинтервью!');

                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            }
        }

        return $this->render('upload', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing VideoInterview model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        // Удаление файла с видеоинтервью
        unlink($model->video_file);
        // Удаление файла с лицевыми точками
        unlink($model->landmark_file);
        // Определение директории где расположен файл видеоинтервью
        $pos = strrpos($model->video_file, '/');
        $dir = substr($model->video_file, 0, $pos);
        // Удаление файла видеоинтервью и директории где он хранился
        FileHelper::removeDirectory($dir);
        // Удалние записи из БД
        $model->delete();
        Yii::$app->getSession()->setFlash('success', 'Вы успешно удалили видеоинтервью!');

        $this->findModel($id)->delete();

        return $this->redirect(['list']);
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
        if (($model = VideoInterview::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}