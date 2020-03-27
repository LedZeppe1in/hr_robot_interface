<?php

namespace app\modules\main\controllers;

use app\components\FacialFeatureDetector;
use app\modules\main\models\VideoInterview;
use Exception;
use Yii;
use app\modules\main\models\AnalysisResult;
use yii\data\ActiveDataProvider;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AnalysisResultController implements the CRUD actions for AnalysisResult model.
 */
class AnalysisResultController extends Controller
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
     * Lists all AnalysisResult models.
     * @return mixed
     */
    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => AnalysisResult::find(),
        ]);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AnalysisResult model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // Массивы для признаков глаза
        $eyeFeatures = array();
        // Массив для признаков рта
        $mouthFeatures = array();
        // Массив для признаков лба
        $browFeatures = array();
        // Массив для признаков бровей
        $eyebrowFeatures = array();
        // Поиск записи в БД о результатах определения признаков
        $model = $this->findModel($id);
        // Получение файла JSON c результатами определения признаков
        $jsonFile = file_get_contents($model->detection_result_file, true);
        $faceData = json_decode($jsonFile, true);
        // Обход файла
        foreach ($faceData as $key => $item) {
            // Сохранение признаков для глаз
            if ($key == 'eye')
                $eyeFeatures = [$key => $item];
            // Сохранение признаков для рта
            if ($key == 'mouth')
                $mouthFeatures = [$key => $item];
            // Сохранение признаков для лба
            if ($key == 'brow')
                $browFeatures = [$key => $item];
            // Сохранение признаков для бровей
            if ($key == 'eyebrow')
                $eyebrowFeatures = [$key => $item];
        }

        return $this->render('view', [
            'model' => $model,
            'eyeFeatures' => $eyeFeatures,
            'mouthFeatures' => $mouthFeatures,
            'browFeatures' => $browFeatures,
            'eyebrowFeatures' => $eyebrowFeatures,
        ]);
    }

    /**
     * Страница с результатами определения лицивых признаков.
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionDetection($id)
    {
        // Создание модели для результатов определения признаков
        $model = new AnalysisResult();
        $model->video_interview_id = $id;
        $model->save();
        // Поиск видеоинтервью по его id
        $videoInterview = VideoInterview::findOne($id);
        // Получение имени файла
        $fileName = basename($videoInterview->landmark_file);
        // Создание объекта обнаружения лицевых признаков
        $facialFeatureDetector = new FacialFeatureDetector();
        // Выявление признаков для лица
        $facialFeatures = $facialFeatureDetector->detectFeatures(Yii::$app->basePath .
            '/web/uploads/video-interview/' . $id . '/' . $fileName);
        // Формирование пути к файлу результатов определения признаков
        $dir = Yii::getAlias('@webroot') . '/uploads/detection-results/' . $model->id . '/';
        // Создание новой директории для файла с результатами определения признаков
        FileHelper::createDirectory($dir);
        // Файл результатов определения признаков
        $targetJsonFile = $dir . 'detection-result.json';
        // Создание файла JSON с результатами определения признаков
        file_put_contents($targetJsonFile, json_encode($facialFeatures));
        // Сохранение результатов определения признаков в БД
        $model->detection_result_file = $targetJsonFile;
        $model->updateAttributes(['detection_result_file']);

        return $this->render('view', [
            'model' => $model,
            'eyeFeatures' => $facialFeatures['eye'],
            'mouthFeatures' => $facialFeatures['mouth'],
            'browFeatures' => $facialFeatures['brow'],
            'eyebrowFeatures' => $facialFeatures['eyebrow'],
        ]);
    }

    /**
     * Deletes an existing AnalysisResult model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        // Удаление файла с результатами определения признаков
        if ($model->detection_result_file != '')
            unlink($model->detection_result_file);
        // Удаление файла с результатами интерпретации признаков
        if ($model->interpretation_result_file != '')
            unlink($model->interpretation_result_file);
        // Определение директории где расположен файл с результатами определения признаков
        $pos = strrpos($model->detection_result_file, '/');
        $dir = substr($model->detection_result_file, 0, $pos);
        // Удаление директории где хранился файл с результатами определения признаков
        FileHelper::removeDirectory($dir);
        // Удалние записи из БД
        $model->delete();
        // Вывод сообщения
        Yii::$app->getSession()->setFlash('success', 'Вы успешно удалили результаты определения признаков!');

        return $this->redirect(['list']);
    }

    /**
     * Скачать json-файл с результатами определения признаков.
     *
     * @param $id
     * @return \yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDetectionFileDownload($id)
    {
        $model = $this->findModel($id);
        if (file_exists($model->detection_result_file))
            return Yii::$app->response->sendFile($model->detection_result_file);
        throw new Exception('Файл не найден!');
    }

    /**
     * Finds the AnalysisResult model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AnalysisResult the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AnalysisResult::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}