<?php

namespace app\components;

use app\modules\main\models\Landmark;
use app\modules\main\models\ModuleMessage;
use app\modules\main\models\AnalysisResult;

/**
 * AnalysisHelper - класс с общими функциями анализа видео-интервью.
 */
class AnalysisHelper
{
    /**
     * Создание модели результатов анализа и запуск модуля определения признаков.
     *
     * @param $landmark - модель цифровой маски
     * @param $index - порядковый номер цифровой маски
     * @param $processingType - тип обработки получаемых цифровых масок (нормализованные или сырые точки)
     * @return int - id результатов анализа
     */
    public static function getAnalysisResult($landmark, $index, $processingType)
    {
        // Создание модели для результатов определения признаков
        $analysisResultModel = new AnalysisResult();
        $analysisResultModel->landmark_id = $landmark->id;
        $analysisResultModel->facts_file_name = 'facts.json';
        $analysisResultModel->description = $landmark->description . ($processingType == 0 ?
                ' (обработка сырых точек)' : ' (обработка нормализованных точек)');
        $analysisResultModel->save();
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Получение содержимого json-файла с лицевыми точками из Object Storage
        $faceData = $osConnector->getFileContentFromObjectStorage(
            OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
            $landmark->id,
            $landmark->landmark_file_name
        );
        // Массив фактов
        $facts = array();
        // Создание объекта обнаружения лицевых признаков
        $facialFeatureDetector = new FacialFeatureDetector();
        // Если вызывается модуль обработки видео Ивана
        if ($landmark->type == Landmark::TYPE_LANDMARK_IVAN_MODULE) {
            // Базовый (нулевой) кадр
            $basicFrame = '';
            // Если обрабатывается первая цифровая маска
            if ($index == 1)
                // Определение нулевого кадра (нейтрального состояния лица)
                $basicFrame = $facialFeatureDetector->detectFeaturesForBasicFrameDetection(
                    $faceData,
                    $processingType
                );
            // Выявление признаков для лица
            $facialFeatures = $facialFeatureDetector->detectFeaturesV2($faceData, $processingType, $basicFrame);
            // Сохранение json-файла с результатами определения признаков на Object Storage
            $osConnector->saveFileToObjectStorage(
                OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                $analysisResultModel->id,
                $analysisResultModel->detection_result_file_name,
                $facialFeatures
            );
            // Время на вопрос
            $questionTime = null;
            // Если к цифровой маски привязан вопрос, то запоминание времени на вопрос
            if ($landmark->question_id != null)
                $questionTime = $landmark->question->testQuestion->time;
            // Преобразование массива с результатами определения признаков в массив фактов
            $facts = $facialFeatureDetector->convertFeaturesToFacts(
                $faceData,
                $facialFeatures,
                $questionTime
            );
            // Обновление атрибута названия файла с результатами определения признаков в БД
            $analysisResultModel->detection_result_file_name = 'feature-detection-result.json';
            $analysisResultModel->updateAttributes(['detection_result_file_name']);
        }
        // Если в json-файле цифровой маски есть данные по Action Units
        if (strpos($faceData, 'AUs') !== false) {
            // Формирование json-строки
            $faceData = str_replace('{"AUs"', ',{"AUs"', $faceData);
            $faceData = trim($faceData, ',');
            $faceData = '[' . $faceData . ']';
            // Конвертация данных по Action Units в набор фактов
            $initialData = json_decode($faceData);
            if (count($initialData) > 0) {
                $frameData = $initialData[0];
                $targetPropertyName = 'AUs';
                if (property_exists($frameData, $targetPropertyName) === True)
                    foreach ($initialData as $frameIndex => $frameData) {
                        $actionUnits = $frameData->{$targetPropertyName};
                        $actionUnitsAsFacts = $facialFeatureDetector->convertActionUnitsToFacts($actionUnits,
                            $frameIndex);
                        if (count($actionUnitsAsFacts) > 0)
                            $facts[$frameIndex] = $actionUnitsAsFacts;
                    }
            }
//            if ((count($facts) > 0) && (count($initialData) > 0)) {
//                $frameData = $initialData[0];
//                $targetPropertyName = 'AUs';
//                if (property_exists($frameData, $targetPropertyName) === True)
//                    foreach ($initialData as $frameIndex => $frameData) {
//                        $actionUnits = $frameData->{$targetPropertyName};
//                        $actionUnitsAsFacts = $facialFeatureDetector->convertActionUnitsToFacts($actionUnits,
//                            $frameIndex);
//                        if (isset($facts[$frameIndex]) && count($actionUnitsAsFacts) > 0)
//                            $facts[$frameIndex] = array_merge($facts[$frameIndex], $actionUnitsAsFacts);
//                    }
//            }
        }
        // Сохранение json-файла с результатами конвертации определенных признаков в набор фактов на Object Storage
        $osConnector->saveFileToObjectStorage(
            OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
            $analysisResultModel->id,
            $analysisResultModel->facts_file_name,
            $facts
        );

        return $analysisResultModel->id;
    }
}