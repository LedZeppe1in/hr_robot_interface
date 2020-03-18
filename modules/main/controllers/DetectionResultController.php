<?php

namespace app\modules\main\controllers;

use Exception;
use Yii;
use app\modules\main\models\AnalysisResult;
use yii\data\ActiveDataProvider;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DetectionResultController implements the CRUD actions for AnalysisResult model.
 */
class DetectionResultController extends Controller
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
        $leftEyeInner = array();
        $leftEyeOuter = array();
        $leftUpperEyelid = array();
        $leftLowerEyelid = array();
        $leftEyeWidth = array();
        $rightEyeInner = array();
        $rightEyeOuter = array();
        $rightUpperEyelid = array();
        $rightLowerEyelid = array();
        $rightEyeWidth = array();
        $features = array();
        // Массив для признаков рта
        $mouthFeatures = array();
        // Поиск записи в БД о результатах определения признаков
        $model = $this->findModel($id);
        // Получение файла JSON c результатами определения признаков
        $jsonFile = file_get_contents($model->detection_result_file, true);
        $faceData = json_decode($jsonFile, true);
        // Обход файла
        foreach ($faceData as $key => $item) {
            if ($key == 'left_eye_inner')
                $leftEyeInner = [$key => $item];
            if ($key == 'left_eye_outer')
                $leftEyeOuter = [$key => $item];
            if ($key == 'left_upper_eyelid')
                $leftUpperEyelid = [$key => $item];
            if ($key == 'left_lower_eyelid')
                $leftLowerEyelid = [$key => $item];
            if ($key == 'left_eye_width')
                $leftEyeWidth = [$key => $item];
            if ($key == 'right_eye_inner')
                $rightEyeInner = [$key => $item];
            if ($key == 'right_eye_outer')
                $rightEyeOuter = [$key => $item];
            if ($key == 'right_upper_eyelid')
                $rightUpperEyelid = [$key => $item];
            if ($key == 'right_lower_eyelid')
                $rightLowerEyelid = [$key => $item];
            if ($key == 'right_eye_width')
                $rightEyeWidth = [$key => $item];
            // Сохранение признаков для глаз
            $features[$key] = $item;
            $eyeFeatures = ['eye' => $features];
            // Сохранение признаков для рта
            if ($key == 'mouth')
                $mouthFeatures = [$key => $item];
        }

        return $this->render('view', [
            'model' => $model,
            'eyeFeatures' => $eyeFeatures,
            'mouthFeatures' => $mouthFeatures,
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
    public function actionFileDownload($id)
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