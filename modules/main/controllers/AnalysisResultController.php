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
        // Получение кода базы знаний
        $knowledgeBase = $osConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_KNOWLEDGE_BASE_BUCKET,
            null,
            'knowledge-base.txt'
        );
        // Получение json-файла с результатами интерпретации признаков
        $jsonFile = $osConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET,
            $model->id,
            $model->interpretation_result_file_name
        );
        $interpretationResult = json_decode($jsonFile, true);

        return $this->render('view', [
            'model' => $model,
            'eyeFeatures' => (isset($faceData['eye'])) ? $faceData['eye']['VALUES_REL'] : null,
            'mouthFeatures' => (isset($faceData['mouth'])) ? $faceData['mouth']['VALUES_REL'] : null,
            'browFeatures' => (isset($faceData['brow'])) ? $faceData['brow']['VALUES_REL'] : null,
            'eyebrowFeatures' => (isset($faceData['eyebrow'])) ? $faceData['eyebrow']['VALUES_REL'] : null,
            'noseFeatures' => (isset($faceData['nose'])) ? $faceData['nose']['VALUES_REL'] : null,
            'chinFeatures' => (isset($faceData['chin'])) ? $faceData['chin']['VALUES_REL'] : null,
            'facts' => $facts,
            'knowledgeBase' => $knowledgeBase,
            'interpretationResult' => $interpretationResult
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
     * Страница с результатами определения лицивых признаков.
     *
     * @param $id
     * @param $processingType
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionDetection($id, $processingType)
    {
        // Поиск цифровой маски по id в БД
        $landmark = Landmark::findOne($id);
        // Если цифровая маска получена программой Андрея, то меняем тип обработки на сырые точки
        if ($landmark->type == Landmark::TYPE_LANDMARK_ANDREW_MODULE)
            $processingType = 0;
        // Создание модели для результатов определения признаков
        $model = new AnalysisResult();
        $model->detection_result_file_name = 'feature-detection-result.json';
        $model->facts_file_name = 'facts.json';
        $model->landmark_id = $id;
        $model->description = $landmark->description . ($processingType == 0 ?
            ' (обработка сырых точек)' : ' (обработка нормализованных точек)');
        $model->save();
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Получение содержимого json-файла с лицевыми точками из Object Storage
        $faceData = $osConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
            $landmark->id,
            $landmark->landmark_file_name
        );
        // Создание объекта обнаружения лицевых признаков
        $facialFeatureDetector = new FacialFeatureDetector();
        // Определение нулевого кадра (нейтрального состояния лица)
        $basicFrame = $facialFeatureDetector->detectFeaturesForBasicFrameDetection($faceData, (int)$processingType);
        // Выявление лицевых признаков + нулевой кадр (нейтральное состояние лица)
        $facialFeatures = $facialFeatureDetector->detectFeaturesV2($faceData, (int)$processingType, $basicFrame);
        // Сохранение json-файла с результатами определения признаков на Object Storage
        $osConnector->saveFileToObjectStorage(OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
            $model->id, $model->detection_result_file_name, $facialFeatures);
        // Преобразование массива с результатами функции определения признаков в массив фактов
        $facts = $facialFeatureDetector->convertFeaturesToFacts($facialFeatures);
        // Если в json-файле цифровой маски есть данные по Action Units
        if (strpos($faceData,'AUs') !== false) {
            // Формирование json-строки
            $faceData = str_replace('{"AUs"',',{"AUs"', $faceData);
            $faceData = trim($faceData, ',');
            $faceData = '[' . $faceData . ']';
            // Конвертация данных по Action Units в набор фактов
            $initialData = json_decode($faceData);
            if ((count($facts) > 0) && (count($initialData) > 0)) {
                $frameData = $initialData[0];
                $targetPropertyName = 'AUs';
                if (property_exists($frameData, $targetPropertyName) === True)
                    foreach ($initialData as $frameIndex => $frameData) {
                        $actionUnits = $frameData -> {$targetPropertyName};
                        $actionUnitsAsFacts = $facialFeatureDetector->convertActionUnitsToFacts($actionUnits,
                            $frameIndex);
                        if (isset($facts[$frameIndex]) && count($actionUnitsAsFacts) > 0)
                            $facts[$frameIndex] = array_merge($facts[$frameIndex], $actionUnitsAsFacts);
                    }
            }
        }
        // Сохранение json-файла с результатами конвертации определенных признаков в набор фактов на Object Storage
        $osConnector->saveFileToObjectStorage(OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
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
        $osConnector = new OSConnector();
        // Скачивание файла с результатами определения признаков с Object Storage
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
        // Скачивание файла с результатами определения признаков в виде фактов с Object Storage
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
        $osConnector = new OSConnector();
        // Скачивание файла с результатами интерпретации признаков с Object Storage
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