<?php

namespace app\modules\main\controllers;

use Yii;
use stdClass;
use Exception;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\OSConnector;
use app\components\AnalysisHelper;
use app\modules\main\models\Landmark;
use app\modules\main\models\FinalResult;
use app\modules\main\models\KnowledgeBase;
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
        // Поиск базы знаний по id
        $knowledgeBaseModel = KnowledgeBase::findOne(1);
        // Получение кода базы знаний
        $knowledgeBase = $osConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_KNOWLEDGE_BASE_BUCKET,
            $knowledgeBaseModel->id,
            $knowledgeBaseModel->knowledge_base_file_name
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
            'eyeFeatures' => (isset($faceData['eye'])) ? $faceData['eye'] : null,
            'mouthFeatures' => (isset($faceData['mouth'])) ? $faceData['mouth'] : null,
            'browFeatures' => (isset($faceData['brow'])) ? $faceData['brow'] : null,
            'eyebrowFeatures' => (isset($faceData['eyebrow'])) ? $faceData['eyebrow'] : null,
            'noseFeatures' => (isset($faceData['nose'])) ? $faceData['nose'] : null,
            'chinFeatures' => (isset($faceData['chin'])) ? $faceData['chin'] : null,
            'facts' => $facts,
            'knowledgeBaseModel' => $knowledgeBaseModel,
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
     * @param $id - идентификатор цифровой маски
     * @param $processingType - тип обработки точек (сырые или нормализованные)
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionDetection($id, $processingType)
    {
        // Поиск цифровой маски по id в БД
        $landmark = Landmark::findOne($id);
        // Получение рузультатов анализа видеоинтервью (обработка модулем определения признаков)
        $analysisHelper = new AnalysisHelper();
        $analysisResultId = $analysisHelper->getAnalysisResult(
            $landmark,
            1,
            $processingType
        );
        // Вывод сообщения об успешном обнаружении признаков
        Yii::$app->getSession()->setFlash('success', 'Вы успешно определили признаки!');

        return $this->redirect(['/detection-result/view/' . $analysisResultId]);
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
     * Скачать json-файл с результатами интерпретации признаков.
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
     * Скачать факты результатов интерпретации признаков.
     *
     * @param $id - идентификатор итогового результата
     * @return array
     * @throws Exception
     */
    public function actionInterpretationFactsDownload($id)
    {
        //
        $finalResult = FinalResult::findOne($id);
        //
        $landmarks = Landmark::find()->where(['video_interview_id' => $finalResult->video_interview_id])->all();
        // Формирование массива c id цифровых масок
        $landmarkIds = array();
        foreach ($landmarks as $landmark)
            array_push($landmarkIds, $landmark->id);
        //
        $analysisResults = AnalysisResult::find()->where(['landmark_id' => $landmarkIds])->all();
        // Создаем структуру для данных всего интервью
        $InitialDataForReasoningProcess = array();
        //
        foreach ($analysisResults as $analysisResult) {
            // Создание объекта коннектора с Yandex.Cloud Object Storage
            $osConnector = new OSConnector();
            // Если есть результат интерпретации признаков
            if ($analysisResult->interpretation_result_file_name != '') {
                // Получение json-файла с результатами интерпретации признаков
                $jsonFile = $osConnector->getFileContentFromObjectStorage(
                    OSConnector::OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET,
                    $analysisResult->id,
                    $analysisResult->interpretation_result_file_name
                );
                $interpretationResult = json_decode($jsonFile, true);
                // Формирование только необходимых фактов с результатами интерпретации признаков
                $TargetTemplates = array('T1957', /* Психоэмоциональное состояние */
                    'T1924', /* Эмоции */
                    'T2046', /* Признаки общего поведения */
                    'T2047' /* Признаки аномального поведения */);
                $DescriptionOfTemplates = $interpretationResult['DescriptionsOfTemplates'];
                $DataOfSteps = $interpretationResult['Steps'];
                $CountOFSteps = count($DataOfSteps);
                $DataOfLastStep = $DataOfSteps[$CountOFSteps - 1]['ContentOfWorkingMemory'];
                // Создаем структуру для данных одного из вопросов интервью
                $ItemOfInitialDataForReasoningProcess = array();
                foreach ($DataOfLastStep as $NameOfTemplate => $FactsOfTemplate) {
                    if (in_array($NameOfTemplate, $TargetTemplates) === True) {
                        $CountOfFactsOfTemplate = count($FactsOfTemplate);
                        $DescriptionOfTemplate = $DescriptionOfTemplates[$NameOfTemplate];
                        for ($i = 0; $i < $CountOfFactsOfTemplate; $i++) {
                            $Fact = new stdClass;
                            $Fact->{'NameOfTemplate'} = $NameOfTemplate;
                            $FactOfTemplate = $FactsOfTemplate[$i];
                            foreach ($FactOfTemplate as $IndexOfSlot => $ValueOfSlot)
                                $Fact->{$DescriptionOfTemplate['Slots'][$IndexOfSlot]['InternalName']} = $ValueOfSlot;
                            $ItemOfInitialDataForReasoningProcess[] = $Fact;
                        }
                    } else
                        continue;
                }
                //
                $landmark = Landmark::findOne($analysisResult->landmark_id);
                //
                $questionFact = new stdClass;
                $questionFact->{'NameOfTemplate'} = 'T2048';
                $questionFact->{'s921'} = $landmark->question->text;
                $ItemOfInitialDataForReasoningProcess[] = $questionFact;
                // И добавляем ее в данные интервью
                $InitialDataForReasoningProcess[] = $ItemOfInitialDataForReasoningProcess;
            }
        }
        //
        if (!empty($InitialDataForReasoningProcess))
            return json_encode($InitialDataForReasoningProcess);

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