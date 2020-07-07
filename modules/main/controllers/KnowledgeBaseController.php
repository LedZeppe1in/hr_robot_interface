<?php

namespace app\modules\main\controllers;

use app\components\OSConnector;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\modules\main\models\KnowledgeBase;
use yii\web\UploadedFile;

/**
 * KnowledgeBaseController implements the CRUD actions for KnowledgeBase model.
 */
class KnowledgeBaseController extends Controller
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
     * Lists all KnowledgeBase models.
     * @return mixed
     */
    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => KnowledgeBase::find(),
        ]);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single KnowledgeBase model.
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
     * Upload a new KnowledgeBase model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpload()
    {
        // Создание модели базы знаний со сценарием загрузки новой базы знаний
        $model = new KnowledgeBase(['scenario' => KnowledgeBase::UPLOAD_KNOWLEDGE_BASE_SCENARIO]);
        // POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла с формы
            $knowledgeBaseFile = UploadedFile::getInstance($model, 'knowledgeBaseFile');
            $model->knowledgeBaseFile = $knowledgeBaseFile;
            // Валидация поля файла
            if ($model->validate(['knowledgeBaseFile'])) {
                // Если пользователь загрузил файл с кодом базы знаний
                if ($knowledgeBaseFile && $knowledgeBaseFile->tempName)
                    $model->knowledge_base_file_name = $model->knowledgeBaseFile->baseName . '.' .
                        $model->knowledgeBaseFile->extension;
                // Сохранение данных о базе знаний в БД
                if ($model->save()) {
                    // Создание объекта коннектора с Yandex.Cloud Object Storage
                    $osConnector = new OSConnector();
                    // Сохранение файла с кодом базы знаний на Object Storage
                    if ($model->knowledge_base_file_name != '')
                        $osConnector->saveFileToObjectStorage(
                            OSConnector::OBJECT_STORAGE_KNOWLEDGE_BASE_BUCKET,
                            $model->id,
                            $model->knowledge_base_file_name,
                            $knowledgeBaseFile->tempName
                        );
                    // Вывод сообщения об удачной загрузке
                    Yii::$app->getSession()->setFlash('success',
                        'Вы успешно загрузили файл с кодом базы знаний!');

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('upload', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing KnowledgeBase model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        // Поиск базы знаний по id
        $model = $this->findModel($id);
        // POST-запрос
        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла с формы
            $knowledgeBaseFile = UploadedFile::getInstance($model, 'knowledgeBaseFile');
            $model->knowledgeBaseFile = $knowledgeBaseFile;
            // Валидация поля файла
            if ($model->validate(['knowledgeBaseFile'])) {
                // Старое название файла с кодом базы знаний
                $old_knowledge_base_file_name = $model->knowledge_base_file_name;
                // Если пользователь загрузил файл с кодом новой базы знаний
                if ($knowledgeBaseFile && $knowledgeBaseFile->tempName)
                    // Формирование нового названия файла с кодом базы знаний
                    $model->knowledge_base_file_name = $model->knowledgeBaseFile->baseName . '.' .
                        $model->knowledgeBaseFile->extension;
                // Сохранение данных о базе знаний в БД
                if ($model->save()) {
                    // Если пользователь загрузил файл с кодом новой базы знаний
                    if ($knowledgeBaseFile && $knowledgeBaseFile->tempName) {
                        // Создание объекта коннектора с Yandex.Cloud Object Storage
                        $osConnector = new OSConnector();
                        // Удаление старого файла с кодом базы знаний на Object Storage
                        $osConnector->removeFileFromObjectStorage(
                            OSConnector::OBJECT_STORAGE_KNOWLEDGE_BASE_BUCKET,
                            $model->id,
                            $old_knowledge_base_file_name
                        );
                        // Сохранение нового файла с кодом базы знаний на Object Storage
                        $osConnector->saveFileToObjectStorage(
                            OSConnector::OBJECT_STORAGE_KNOWLEDGE_BASE_BUCKET,
                            $model->id,
                            $model->knowledge_base_file_name,
                            $knowledgeBaseFile->tempName
                        );
                    }
                    // Вывод сообщения об удачном обновлении базы знаний
                    Yii::$app->getSession()->setFlash('success', 'Вы успешно обновили базу знаний!');

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing KnowledgeBase model.
     * If deletion is successful, the browser will be redirected to the 'list' page.
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        // Поиск базы знаний по id
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Удаление файла с кодом базы знаний на Object Storage
        if ($model->knowledge_base_file_name != '')
            $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_KNOWLEDGE_BASE_BUCKET,
                $model->id, $model->knowledge_base_file_name);
        // Удалние записи из БД
        $model->delete();
        // Вывод сообщения об успешном удалении базы знаний
        Yii::$app->getSession()->setFlash('success', 'Вы успешно удалили базу знаний!');

        return $this->redirect(['list']);
    }

    /**
     * Скачивание файла с кодом базы знаний.
     *
     * @param $id
     * @return \yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionKnowledgeBaseDownload($id)
    {
        // Поиск базы знаний по id
        $model = $this->findModel($id);
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Скачивание файла базы знаний с Object Storage
        if ($model->knowledge_base_file_name != '') {
            $result = $osConnector->downloadFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_KNOWLEDGE_BASE_BUCKET,
                $model->id,
                $model->knowledge_base_file_name
            );
            return $result;
        }
        throw new Exception('Файл не найден!');
    }

    /**
     * Finds the KnowledgeBase model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return KnowledgeBase the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = KnowledgeBase::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}