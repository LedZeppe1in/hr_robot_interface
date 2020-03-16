<?php

namespace app\modules\main\controllers;

use app\components\EmotionDetector;
use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use app\modules\main\models\JsonFileForm;
use app\components\FaceFeatureDetector;

class DefaultController extends Controller
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
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Страница загрузки данных с точками в формате JSON.
     *
     * @return string|\yii\web\Response
     */
    public function actionFaceFeatureDetection()
    {
        // Создание формы файла JSON
        $jsonFileForm = new JsonFileForm();
        // Если POST-запрос
        if (Yii::$app->request->isPost) {
            $jsonFileForm->jsonFile = UploadedFile::getInstance($jsonFileForm, 'jsonFile');
            if ($jsonFileForm->validate()) {
                // Сохранение загруженного файла JSON
                $jsonFileForm->jsonFile->saveAs('uploads/json-facial-landmark-data.json');
                // Вывод сообщения об успехной загрузке файла JSON
                Yii::$app->getSession()->setFlash('success', 'Вы успешно загрузили JSON-файл');

                return $this->redirect('face-feature-detection-result');
            }
        }

        return $this->render('face-feature-detection', [
            'jsonFileForm' => $jsonFileForm,
        ]);
    }

    /**
     * Страница с результатами определения лицивых признаков.
     *
     * @return string
     */
    public function actionFaceFeatureDetectionResult()
    {
        // Получение загруженного файла JSON
        $json = file_get_contents(Yii::$app->basePath . '/web/uploads/json-facial-landmark-data.json',
            true);
        $faceData = json_decode($json, true);
        // Создание объекта обнаружения лицевых признаков
        $faceFeatureDetector = new FaceFeatureDetector();
        // Выявление признаков для глаз
        $eyeFeatures = $faceFeatureDetector->EyeDetector($faceData);
        // Выявление признаков для рта
        $mouthFeatures = $faceFeatureDetector->mouthDetector($faceData);

        return $this->render('face-feature-detection-result', [
            'eyeFeatures' => $eyeFeatures,
            'mouthFeatures' => $mouthFeatures,
        ]);
    }

    /**
     * Страница загрузки данных с тестовыми признаками в формате JSON.
     *
     * @return string|\yii\web\Response
     */
    public function actionFaceFeatureInterpretation()
    {
        // Создание формы файла JSON
        $jsonFileForm = new JsonFileForm();
        // Если POST-запрос
        if (Yii::$app->request->isPost) {
            $jsonFileForm->jsonFile = UploadedFile::getInstance($jsonFileForm, 'jsonFile');
            if ($jsonFileForm->validate()) {
                // Сохранение загруженного файла JSON
                $jsonFileForm->jsonFile->saveAs('uploads/json-facial-feature-data.json');
                // Вывод сообщения об успехной загрузке файла JSON
                Yii::$app->getSession()->setFlash('success', 'Вы успешно загрузили JSON-файл');

                return $this->redirect('face-feature-interpretation-result');
            }
        }

        return $this->render('face-feature-interpretation', [
            'jsonFileForm' => $jsonFileForm,
        ]);
    }
}