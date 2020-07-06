<?php

namespace app\modules\main\controllers;

use Yii;
use Exception;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\OSConnector;
use app\modules\main\models\AnalysisResult;

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
        // Поиск записи в БД о результатах определения признаков
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Получение json-файла c результатами определения признаков
        $jsonFile = $osConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
            $model->id,
            $model->detection_result_file_name
        );
        $faceData = json_decode($jsonFile, true);
        // Получение json-файла c результатами определения признаков в виде массива наборов фактов
        $facts = $osConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
            $model->id,
            $model->facts_file_name
        );

        return $this->render('view', [
            'model' => $model,
            'eyeFeatures' => (isset($faceData['eye'])) ? $faceData['eye'] : null,
            'mouthFeatures' => (isset($faceData['mouth'])) ? $faceData['mouth'] : null,
            'browFeatures' => (isset($faceData['brow'])) ? $faceData['brow'] : null,
            'eyebrowFeatures' => (isset($faceData['eyebrow'])) ? $faceData['eyebrow'] : null,
            'noseFeatures' => (isset($faceData['nose'])) ? $faceData['nose'] : null,
            'chinFeatures' => (isset($faceData['chin'])) ? $faceData['chin'] : null,
            'facts' => $facts,
        ]);
    }

    /**
     * Updates an existing AnalysisResult model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Вывод сообщения об удачном обновлении
            Yii::$app->getSession()->setFlash('success', 'Вы успешно обновили описание результата анализа!');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AnalysisResult model.
     * If deletion is successful, the browser will be redirected to the 'list' page.
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        // Поиск результатов анализа по id
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Удаление файлов с результатами определения признаков и фактами на Object Storage
        if ($model->detection_result_file_name != '' && $model->facts_file_name != '') {
            $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                $model->id, $model->detection_result_file_name);
            $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                $model->id, $model->facts_file_name);
        }
        // Удаление файла с результатами интерпретации признаков на Object Storage
        if ($model->interpretation_result_file_name != '')
            $osConnector->removeFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET,
                $model->id,
                $model->interpretation_result_file_name
            );
        // Удалние записи из БД
        $model->delete();
        // Вывод сообщения об успешном удалении
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
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Скачивание файла с результатами определения признаков на Object Storage
        if ($model->detection_result_file_name != '') {
            $result = $osConnector->downloadFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                $model->id,
                $model->detection_result_file_name
            );

            return $result;
        }
        throw new Exception('Файл не найден!');
    }

    /**
     * Скачать json-файл с набором фактов.
     *
     * @param $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionFactsDownload($id)
    {
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Скачивание файла с результатами определения признаков в виде набора фактов с Object Storage
        if ($model->detection_result_file_name != '') {
            $result = $osConnector->downloadFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                $model->id,
                $model->facts_file_name
            );

            return $result;
        }
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