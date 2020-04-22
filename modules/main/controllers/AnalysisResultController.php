<?php

namespace app\modules\main\controllers;

use Yii;
use Exception;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\OSConnector;
use app\components\FacialFeatureDetector;
use app\modules\main\models\Landmark;
use app\modules\main\models\AnalysisResult;

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
        // Поиск записи в БД о результатах определения признаков
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $dbConnector = new OSConnector();
        // Получение json-файла c результатами определения признаков
        $jsonFile = $dbConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
            $model->id,
            $model->detection_result_file_name
        );
        $faceData = json_decode($jsonFile, true);
        // Получение json-файла c результатами определения признаков в виде массива наборов фактов
        $facts = $dbConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
            $model->id,
            $model->facts_file_name
        );
        // Получение кода базы знаний
        $knowledgeBase = $dbConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_KNOWLEDGE_BASE_BUCKET,
            null,
            'knowledge-base.txt'
        );
        // Получение json-файла с результатами интерпретации признаков
        $interpretationResult = $dbConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET,
            $model->id,
            $model->interpretation_result_file_name
        );

        return $this->render('view', [
            'model' => $model,
            'eyeFeatures' => $faceData['eye'],
            'mouthFeatures' => $faceData['mouth'],
            'browFeatures' => $faceData['brow'],
            'eyebrowFeatures' => $faceData['eyebrow'],
            'noseFeatures' => $faceData['nose'],
            'facts' => $facts,
            'knowledgeBase' => $knowledgeBase,
            'interpretationResult' => $interpretationResult
        ]);
    }

    /**
     * Страница с результатами определения лицивых признаков.
     *
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionDetection($id)
    {
        // Создание модели для результатов определения признаков
        $model = new AnalysisResult();
        $model->detection_result_file_name = 'feature-detection-result.json';
        $model->facts_file_name = 'facts.json';
        $model->landmark_id = $id;
        $model->save();
        // Поиск цифровой маски по id в БД
        $landmark = Landmark::findOne($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $dbConnector = new OSConnector();
        // Получение содержимого json-файла с лицевыми точками из Object Storage
        $faceData = $dbConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
            $landmark->id,
            $landmark->landmark_file_name
        );
        // Создание объекта обнаружения лицевых признаков
        $facialFeatureDetector = new FacialFeatureDetector();
        // Выявление признаков для лица
        $facialFeatures = $facialFeatureDetector->detectFeatures($faceData);
        // Сохранение json-файла с результатами определения признаков на Object Storage
        $dbConnector->saveFileToObjectStorage(OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
            $model->id, $model->detection_result_file_name, $facialFeatures);
        // Преобразование массива с результатами функции определения признаков в массив фактов
        $facts = $facialFeatureDetector->convertFeaturesToFacts($facialFeatures);
        // Сохранение json-файла с результатами конвертации определенных признаков в набор фактов на Object Storage
        $dbConnector->saveFileToObjectStorage(OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
            $model->id, $model->facts_file_name, $facts);
        // Вывод сообщения об успешном обнаружении признаков
        Yii::$app->getSession()->setFlash('success', 'Вы успешно определили признаки!');

        return $this->redirect(['/detection-result/view/' . $model->id]);
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
        $dbConnector = new OSConnector();
        // Удаление файлов с результатами определения признаков и фактами на Object Storage
        if ($model->detection_result_file_name != '') {
            $dbConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                $model->id, $model->detection_result_file_name);
            $dbConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                $model->id, $model->facts_file_name);
        }
        // Удаление файла с результатами интерпретации признаков на Object Storage
        if ($model->interpretation_result_file_name != '')
            $dbConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET,
                $model->id, $model->interpretation_result_file_name);
        // Вывод сообщения об успешном удалении
        Yii::$app->getSession()->setFlash('success', 'Вы успешно удалили результаты анализа интервью!');

        return $this->redirect(['list']);
    }

    /**
     * Скачать json-файл с результатами определения признаков.
     *
     * @param $id - идентификатор модели результатов анализа
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDetectionFileDownload($id)
    {
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $dbConnector = new OSConnector();
        // Скачивание файла с результатами определения признаков с Object Storage
        if ($model->detection_result_file_name != '') {
            $result = $dbConnector->downloadFileFromObjectStorage(
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
        $dbConnector = new OSConnector();
        // Скачивание файла с результатами определения признаков в виде фактов с Object Storage
        if ($model->detection_result_file_name != '') {
            $result = $dbConnector->downloadFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                $model->id,
                $model->facts_file_name
            );

            return $result;
        }
        throw new Exception('Файл не найден!');
    }

    /**
     * Скачать json-файл с результатами определения признаков.
     *
     * @param $id - идентификатор модели результатов анализа
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionInterpretationFileDownload($id)
    {
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $dbConnector = new OSConnector();
        // Скачивание файла с результатами интерпретации признаков с Object Storage
        if ($model->interpretation_result_file_name != '') {
            $result = $dbConnector->downloadFileFromObjectStorage(
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