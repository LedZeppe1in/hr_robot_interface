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
 * InterpretationResultController implements the CRUD actions for AnalysisResult model.
 */
class InterpretationResultController extends Controller
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
        // Поиск записи в БД о результатах интерпретации признаков
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Получение json-файла c результатами интерпретации признаков
        $jsonFile = $osConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET,
            $model->id,
            $model->interpretation_result_file_name
        );
        $interpretationResult = json_decode($jsonFile, true);

        return $this->render('view', [
            'model' => $model,
            'interpretationResult' => $interpretationResult,
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
        $model = $this->findModel($id);
        // Удалние записи из БД
        $model->delete();
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Удаление файлов с результатами интерпретации признаков и фактами с Object Storage
        if ($model->interpretation_result_file_name != '') {
            $osConnector->removeFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET,
                $model->id,
                $model->interpretation_result_file_name
            );
        }
        // Вывод сообщения об успешном удалении
        Yii::$app->getSession()->setFlash('success',
            'Вы успешно удалили результаты интерпретации признаков!');

        return $this->redirect(['list']);
    }

    /**
     * Скачать json-файл с результатами интерпретации признаков.
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
        // Скачивание файла с результатами интерпретации признаков на Object Storage
        if ($model->interpretation_result_file_name != '') {
            $result = $osConnector->downloadFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET,
                $model->id,
                $model->interpretation_result_file_name
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