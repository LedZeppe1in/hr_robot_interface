<?php

namespace app\components;

use Yii;
use stdClass;
use Exception;
use vova07\console\ConsoleRunner;
use app\modules\main\models\Landmark;
use app\modules\main\models\Question;
use app\modules\main\models\TopicQuestion;
use app\modules\main\models\AnalysisResult;
use app\modules\main\models\VideoInterview;
use app\modules\main\models\QuestionProcessingStatus;
use app\modules\main\models\VideoInterviewProcessingStatus;
use app\modules\main\models\VideoProcessingModuleSettingForm;

/**
 * AnalysisHelper - класс с общими функциями анализа видео-интервью.
 */
class AnalysisHelper
{
    const OLD_FDM = 0; // Старая версия МОП (Юрин)
    const NEW_FDM = 1; // Новая версия МОП (Столбов)

    const TURN_RIGHT = 0; // Поворот вправо
    const TURN_LEFT  = 1; // Поворот влево

    /**
     * Поиск соответствий между форматами МОП и МИП для основных лицевых признаков.
     *
     * @param $sourceFacePart - название части лица от МОП
     * @param $sourceFeatureName - название признака от МОП
     * @param $sourceValue - значение признака от МОП
     * @return array - массив значений для МИП
     */
    public static function findCorrespondences($sourceFacePart, $sourceFeatureName, $sourceValue)
    {
        // Формирование пустого целевого массива с лицевыми признаками для МИП
        $targetValues = array();
        $targetValues['targetFacePart'] = null;
        $targetValues['featureChangeType'] = null;
        $targetValues['changeDirection'] = null;

        /* Соответствия для лба */
        if ($sourceFacePart == 'brow')
            $targetValues['targetFacePart'] = 'Лоб';
        if ($sourceFeatureName == 'brow_width')
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
        if ($sourceValue == '-')
            $targetValues['changeDirection'] = 'Уменьшение';
        if ($sourceValue == '+')
            $targetValues['changeDirection'] = 'Увеличение';
        /* Соответствия для брови */
        if ($sourceFacePart == 'eyebrow')
            $targetValues['targetFacePart'] = 'Бровь';
        if (($sourceFeatureName == 'left_eyebrow_movement_x') || ($sourceFeatureName == 'left_eyebrow_movement_y')
            || ($sourceFeatureName == 'left_eyebrow_form'))
            $targetValues['targetFacePart'] = 'Левая бровь';
        if (($sourceFeatureName == 'right_eyebrow_movement_x') || ($sourceFeatureName == 'right_eyebrow_movement_y')
            || ($sourceFeatureName == 'right_eyebrow_form'))
            $targetValues['targetFacePart'] = 'Правая бровь';
//        if ((($sourceFeatureName == 'left_eyebrow_movement_x') || ($sourceFeatureName == 'right_eyebrow_movement_x')
//            || ($sourceFeatureName == 'left_eyebrow_movement_y') || ($sourceFeatureName == 'right_eyebrow_movement_y')) &&
//            ($sourceValue == 'none')) {
//            $targetValues['featureChangeType'] = 'Отсутствие типа';
//            $targetValues['changeDirection'] = 'Отсутствие направления';
//        }
        if ((($sourceFeatureName == 'left_eyebrow_form') || ($sourceFeatureName == 'right_eyebrow_form')) && ($sourceValue == 'triangle')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Тругольник';
        }
        if ((($sourceFeatureName == 'left_eyebrow_form') || ($sourceFeatureName == 'right_eyebrow_form')) && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Не определено';
        }
        if ((($sourceFeatureName == 'left_eyebrow_form') || ($sourceFeatureName == 'right_eyebrow_form')) && ($sourceValue == 'line')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Линия';
        }
        if ((($sourceFeatureName == 'left_eyebrow_form') || ($sourceFeatureName == 'right_eyebrow_form')) && ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Дуга вверх';
        }
        if ((($sourceFeatureName == 'left_eyebrow_form') || ($sourceFeatureName == 'right_eyebrow_form')) && ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Дуга вниз';
        }
        if ((($sourceFeatureName == 'left_eyebrow_movement_x') || ($sourceFeatureName == 'right_eyebrow_movement_x')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eyebrow_movement_y') || ($sourceFeatureName == 'right_eyebrow_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eyebrow_movement_x') || ($sourceFeatureName == 'right_eyebrow_movement_x')) &&
            ($sourceValue == 'to center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'К центру';
        }
        if ((($sourceFeatureName == 'left_eyebrow_movement_x') || ($sourceFeatureName == 'right_eyebrow_movement_x')) &&
            ($sourceValue == 'from center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'От центра в стороны';
        }
        if ((($sourceFeatureName == 'left_eyebrow_movement_y') || ($sourceFeatureName == 'right_eyebrow_movement_y')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eyebrow_movement_y') || ($sourceFeatureName == 'right_eyebrow_movement_y')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        if (($sourceFeatureName == 'left_eyebrow_inner_movement_x') || ($sourceFeatureName == 'left_eyebrow_inner_movement_y'))
            $targetValues['targetFacePart'] = 'Внутренний уголок левой брови';
        if (($sourceFeatureName == 'right_eyebrow_inner_movement_x') || ($sourceFeatureName == 'right_eyebrow_inner_movement_y'))
            $targetValues['targetFacePart'] = 'Внутренний уголок правой брови';
//        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_x') ||
//                ($sourceFeatureName == 'right_eyebrow_inner_movement_x') ||
//                ($sourceFeatureName == 'left_eyebrow_inner_movement_y') ||
//                ($sourceFeatureName == 'right_eyebrow_inner_movement_y')) &&
//            ($sourceValue == 'none')) {
//            $targetValues['featureChangeType'] = 'Отсутствие типа';
//            $targetValues['changeDirection'] = 'Отсутствие направления';
//        }
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_x') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_x')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_y') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_x') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_x')) &&
            ($sourceValue == 'to center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'К центру';
        }
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_x') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_x')) &&
            ($sourceValue == 'from center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'От центра в стороны';
        }
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_y') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_y')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eyebrow_inner_movement_y') ||
                ($sourceFeatureName == 'right_eyebrow_inner_movement_y')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
//        if ((($sourceFeatureName == 'left_eyebrow_inner_movement') ||
//                ($sourceFeatureName == 'right_eyebrow_inner_movement')) &&
//            ($sourceValue == 'to center and up')) {
//            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
//            $targetValues['changeDirection'] = 'К центру и вверх';
//        }
//        if (($sourceFeatureName == 'right_eyebrow_inner_movement') && ($sourceValue == 'to center and down')) {
//            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
//            $targetValues['changeDirection'] = 'К центру и вниз';
//        }
        if ($sourceFeatureName == 'left_eyebrow_outer_movement')
            $targetValues['targetFacePart'] = 'Внешний уголок левой брови';
        if ($sourceFeatureName == 'right_eyebrow_outer_movement')
            $targetValues['targetFacePart'] = 'Внешний уголок правой брови';

        if ((($sourceFeatureName == 'left_eyebrow_outer_movement') ||
                ($sourceFeatureName == 'right_eyebrow_outer_movement')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eyebrow_outer_movement') ||
                ($sourceFeatureName == 'right_eyebrow_outer_movement')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eyebrow_outer_movement') ||
                ($sourceFeatureName == 'right_eyebrow_outer_movement')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }

        /* Соответствия для глаз */
        // Глаза
        if ($sourceFeatureName == 'left_eye_width_changing')
            $targetValues['targetFacePart'] = 'Левый глаз';
        if ($sourceFeatureName == 'right_eye_width_changing')
            $targetValues['targetFacePart'] = 'Правый глаз';
        if ((($sourceFeatureName == 'left_eye_width_changing') || ($sourceFeatureName == 'right_eye_width_changing')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_width_changing') || ($sourceFeatureName == 'right_eye_width_changing')) &&
            ($sourceValue == '+')) {
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
            $targetValues['changeDirection'] = 'Увеличение';
        }
        if ((($sourceFeatureName == 'left_eye_width_changing') || ($sourceFeatureName == 'right_eye_width_changing')) &&
            ($sourceValue == '-')) {
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
            $targetValues['changeDirection'] = 'Уменьшение';
        }
        // Нижнии веки
        if (($sourceFeatureName == 'left_eye_lower_eyelid_movement_x') ||
            ($sourceFeatureName == 'left_eye_lower_eyelid_movement_y') ||
            ($sourceFeatureName == 'left_eye_lower_eyelid_movement_d'))
            $targetValues['targetFacePart'] = 'Нижнее веко левого глаза';
        if (($sourceFeatureName == 'right_eye_lower_eyelid_movement_x') ||
            ($sourceFeatureName == 'right_eye_lower_eyelid_movement_y') ||
            ($sourceFeatureName == 'right_eye_lower_eyelid_movement_d'))
            $targetValues['targetFacePart'] = 'Нижнее веко правого глаза';
//        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_x') ||
//                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_x') ||
//                ($sourceFeatureName == 'left_eye_lower_eyelid_movement_y') ||
//                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_y') ||
//                ($sourceFeatureName == 'left_eye_lower_eyelid_movement_d') ||
//                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_d')) &&
//            (($sourceValue == 'none') || ($sourceValue == 'none and none'))) {
//            $targetValues['featureChangeType'] = 'Отсутствие типа';
//            $targetValues['changeDirection'] = 'Отсутствие направления';
//        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_x') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_x')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_x') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_x')) &&
            ($sourceValue == 'to center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'К центру';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_x') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_x')) &&
            ($sourceValue == 'from center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'От центра в стороны';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_y') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_y') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_y')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_y') ||
                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_y')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
//        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_d') ||
//                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_d')) &&
//            ($sourceValue != 'to center and up')) {
//            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
//            $targetValues['changeDirection'] = 'Отсутствие направления';
//        }
//        if ((($sourceFeatureName == 'left_eye_lower_eyelid_movement_d') ||
//                ($sourceFeatureName == 'right_eye_lower_eyelid_movement_d')) &&
//            ($sourceValue == 'to center and up')) {
//            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
//            $targetValues['changeDirection'] = 'К центру и вверх';
//        }
        // Верхнии веки
        if ($sourceFeatureName == 'left_eye_upper_eyelid_movement')
            $targetValues['targetFacePart'] = 'Верхнее веко левого глаза';
        if ($sourceFeatureName == 'right_eye_upper_eyelid_movement')
            $targetValues['targetFacePart'] = 'Верхнее веко правого глаза';
        if ((($sourceFeatureName == 'left_eye_upper_eyelid_movement') ||
                ($sourceFeatureName == 'right_eye_upper_eyelid_movement')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_upper_eyelid_movement') ||
                ($sourceFeatureName == 'right_eye_upper_eyelid_movement')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eye_upper_eyelid_movement') ||
                ($sourceFeatureName == 'right_eye_upper_eyelid_movement')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        // Зрачки
        if (($sourceFeatureName == 'left_eye_pupil_movement_x') || ($sourceFeatureName == 'left_eye_pupil_movement_y')
            || ($sourceFeatureName == 'left_eye_pupil_movement_d'))
            $targetValues['targetFacePart'] = 'Левый зрачок';
        if (($sourceFeatureName == 'right_eye_pupil_movement_x') || ($sourceFeatureName == 'right_eye_pupil_movement_y')
            || ($sourceFeatureName == 'right_eye_pupil_movement_d'))
            $targetValues['targetFacePart'] = 'Правый зрачок';

        if ((($sourceFeatureName == 'left_eye_pupil_movement_d') || ($sourceFeatureName == 'right_eye_pupil_movement_d')) &&
            ($sourceValue == 'none and none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Прямо перед собой';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_x') || ($sourceFeatureName == 'right_eye_pupil_movement_x')) &&
            ($sourceValue == 'left')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Влево';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_x') || ($sourceFeatureName == 'right_eye_pupil_movement_x')) &&
            ($sourceValue == 'right')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Вправо';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_y') || ($sourceFeatureName == 'right_eye_pupil_movement_y')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_y') || ($sourceFeatureName == 'right_eye_pupil_movement_y')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_d') || ($sourceFeatureName == 'right_eye_pupil_movement_d')) &&
            ($sourceValue == 'up and right')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'Вверх и вправо';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_d') || ($sourceFeatureName == 'right_eye_pupil_movement_d')) &&
            ($sourceValue == 'down and right')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'Вниз и вправо';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_d') || ($sourceFeatureName == 'right_eye_pupil_movement_d')) &&
            ($sourceValue == 'up and left')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'Вверх и влево';
        }
        if ((($sourceFeatureName == 'left_eye_pupil_movement_d') || ($sourceFeatureName == 'right_eye_pupil_movement_d')) &&
            ($sourceValue == 'down and left')) {
            $targetValues['featureChangeType'] = 'Изменение положения по диагонали';
            $targetValues['changeDirection'] = 'Вниз и влево';
        }
        // Уголки глаз
        if ($sourceFeatureName == 'left_eye_inner_movement')
            $targetValues['targetFacePart'] = 'Внутренний уголок левого глаза';
        if ($sourceFeatureName == 'right_eye_inner_movement')
            $targetValues['targetFacePart'] = 'Внутренний уголок правого глаза';
        if ((($sourceFeatureName == 'left_eye_inner_movement') || ($sourceFeatureName == 'right_eye_inner_movement')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_inner_movement') || ($sourceFeatureName == 'right_eye_inner_movement')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eye_inner_movement') || ($sourceFeatureName == 'right_eye_inner_movement')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        if ($sourceFeatureName == 'left_eye_outer_movement')
            $targetValues['targetFacePart'] = 'Внешний уголок левого глаза';
        if ($sourceFeatureName == 'right_eye_outer_movement')
            $targetValues['targetFacePart'] = 'Внешний уголок правого глаза';
        if ((($sourceFeatureName == 'left_eye_outer_movement') || ($sourceFeatureName == 'right_eye_outer_movement')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_eye_outer_movement') || ($sourceFeatureName == 'right_eye_outer_movement')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_eye_outer_movement') || ($sourceFeatureName == 'right_eye_outer_movement')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        // Закрытие глаз
        if ($sourceFeatureName == 'left_eye_closed')
            $targetValues['targetFacePart'] = 'Левый глаз';
        if ($sourceFeatureName == 'right_eye_closed')
            $targetValues['targetFacePart'] = 'Правый глаз';
        if ((($sourceFeatureName == 'left_eye_closed') || ($sourceFeatureName == 'right_eye_closed')) &&
            ($sourceValue == 'yes')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Глаз закрыт';
        }
        if ((($sourceFeatureName == 'left_eye_closed') || ($sourceFeatureName == 'right_eye_closed')) &&
            ($sourceValue == 'no')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Глаз открыт';
        }
        // Моргание
        if ($sourceFeatureName == 'left_eye_blink')
            $targetValues['targetFacePart'] = 'Левый глаз';
        if ($sourceFeatureName == 'right_eye_blink')
            $targetValues['targetFacePart'] = 'Правый глаз';
        if ((($sourceFeatureName == 'left_eye_blink') || ($sourceFeatureName == 'right_eye_blink')) &&
            ($sourceValue == 'yes')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Глаз моргает';
        }
        if ((($sourceFeatureName == 'left_eye_blink') || ($sourceFeatureName == 'right_eye_blink')) &&
            ($sourceValue == 'no')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Глаз не моргает';
        }

        /* Соответствия для рта */
        // Размер и форма рта
        if ($sourceFacePart == 'mouth')
            $targetValues['targetFacePart'] = 'Рот';
        if (($sourceFeatureName == 'mouth_form') || ($sourceFeatureName == 'mouth_form2') || ($sourceFeatureName == 'mouth_lips_form')
            || ($sourceFeatureName == 'mouth_lowerlip_form') || ($sourceFeatureName == 'mouth_upperlip_form'))
            $targetValues['targetFacePart'] = 'Рот';
//        if (($sourceFeatureName == 'mouth_lowerlip_form') && ($sourceValue == 'down')) {
//            $targetValues['featureChangeType'] = 'Изменение формы нижней губы';
//            $targetValues['changeDirection'] = 'Дуга вниз';
//        }
//        if (($sourceFeatureName == 'mouth_lowerlip_form') && ($sourceValue == 'none')) {
//            $targetValues['featureChangeType'] = 'Изменение формы нижней губы';
//            $targetValues['changeDirection'] = 'Не определено';
//        }
//        if (($sourceFeatureName == 'mouth_lowerlip_form') && ($sourceValue == 'up')) {
//            $targetValues['featureChangeType'] = 'Изменение формы нижней губы';
//            $targetValues['changeDirection'] = 'Дуга вверх';
//        }
//        if (($sourceFeatureName == 'mouth_upperlip_form') && ($sourceValue == 'down')) {
//            $targetValues['featureChangeType'] = 'Изменение формы верхней губы';
//            $targetValues['changeDirection'] = 'Дуга вниз';
//        }
//        if (($sourceFeatureName == 'mouth_upperlip_form') && ($sourceValue == 'none')) {
//            $targetValues['featureChangeType'] = 'Изменение формы верхней губы';
//            $targetValues['changeDirection'] = 'Не определено';
//        }
//        if (($sourceFeatureName == 'mouth_upperlip_form') && ($sourceValue == 'up')) {
//            $targetValues['featureChangeType'] = 'Изменение формы верхней губы';
//            $targetValues['changeDirection'] = 'Дуга вверх';
//        }
//        if (($sourceFeatureName == 'mouth_lips_form') && ($sourceValue == 'down')) {
//            $targetValues['featureChangeType'] = 'Изменение формы губ';
//            $targetValues['changeDirection'] = 'Дуга вниз';
//        }
//        if (($sourceFeatureName == 'mouth_lips_form') && ($sourceValue == 'none')) {
//            $targetValues['featureChangeType'] = 'Изменение формы губ';
//            $targetValues['changeDirection'] = 'Не определено';
//        }
//        if (($sourceFeatureName == 'mouth_lips_form') && ($sourceValue == 'up')) {
//            $targetValues['featureChangeType'] = 'Изменение формы губ';
//            $targetValues['changeDirection'] = 'Дуга вверх';
//        }
        if (($sourceFeatureName == 'mouth_form2') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Не определено';
        }
        if (($sourceFeatureName == 'mouth_form') && ($sourceValue == 'ellipse')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Овал';
        }
        if (($sourceFeatureName == 'mouth_form') && ($sourceValue == 'rectangle')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Прямоугольник';
        }
        if ($sourceFeatureName == 'mouth_length')
            $targetValues['targetFacePart'] = 'Рот';
        if (($sourceFeatureName == 'mouth_length') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение размера по горизонтали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if (($sourceFeatureName == 'mouth_length') && ($sourceValue == '-')) {
            $targetValues['featureChangeType'] = 'Изменение размера по горизонтали';
            $targetValues['changeDirection'] = 'Уменьшение';
        }
        if (($sourceFeatureName == 'mouth_length') && ($sourceValue == '+')) {
            $targetValues['featureChangeType'] = 'Изменение размера по горизонтали';
            $targetValues['changeDirection'] = 'Увеличение';
        }
        if ($sourceFeatureName == 'mouth_width')
            $targetValues['targetFacePart'] = 'Рот';
        if (($sourceFeatureName == 'mouth_width') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if (($sourceFeatureName == 'mouth_width') && ($sourceValue == '-')) {
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
            $targetValues['changeDirection'] = 'Уменьшение';
        }
        if (($sourceFeatureName == 'mouth_width') && ($sourceValue == '+')) {
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
            $targetValues['changeDirection'] = 'Увеличение';
        }
        if (($sourceFeatureName == 'mouth_width') && ($sourceValue == 'compressed')) {
            $targetValues['featureChangeType'] = 'Изменение размера по вертикали';
            $targetValues['changeDirection'] = 'Сжатие';
        }
        // Уголки рта
        if (($sourceFeatureName == 'left_corner_mouth_movement_x') || ($sourceFeatureName == 'left_corner_mouth_movement_y'))
            $targetValues['targetFacePart'] = 'Левый уголок рта';
        if (($sourceFeatureName == 'right_corner_mouth_movement_x') || ($sourceFeatureName == 'right_corner_mouth_movement_y'))
            $targetValues['targetFacePart'] = 'Правый уголок рта';
//        if ((($sourceFeatureName == 'left_corner_mouth_movement_x') ||
//                ($sourceFeatureName == 'right_corner_mouth_movement_x') ||
//                ($sourceFeatureName == 'left_corner_mouth_movement_y') ||
//                ($sourceFeatureName == 'right_corner_mouth_movement_y')) &&
//            ($sourceValue == 'none')) {
//            $targetValues['featureChangeType'] = 'Отсутствие типа';
//            $targetValues['changeDirection'] = 'Отсутствие направления';
//        }
        if ((($sourceFeatureName == 'left_corner_mouth_movement_x') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_x')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_corner_mouth_movement_x') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_x')) &&
            ($sourceValue == 'from center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'От центра в стороны';
        }
        if ((($sourceFeatureName == 'left_corner_mouth_movement_x') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_x')) &&
            ($sourceValue == 'to center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'К центру';
        }
        if ((($sourceFeatureName == 'left_corner_mouth_movement_y') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_y')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_corner_mouth_movement_y') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_y')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'left_corner_mouth_movement_y') ||
                ($sourceFeatureName == 'right_corner_mouth_movement_y')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        // Губы
        if ($sourceFeatureName == 'mouth_upper_lip_outer_center_movement')
            $targetValues['targetFacePart'] = 'Верхняя губа';
        if ($sourceFeatureName == 'mouth_lower_lip_outer_center_movement')
            $targetValues['targetFacePart'] = 'Нижняя губа';
        if ($sourceFeatureName == 'mouth_upperlip_form')
            $targetValues['targetFacePart'] = 'Верхняя губа';
        if ($sourceFeatureName == 'mouth_lowerlip_form')
            $targetValues['targetFacePart'] = 'Нижняя губа';
        if (($sourceFeatureName == 'mouth_lowerlip_form') && ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Дуга вниз';
        }
        if (($sourceFeatureName == 'mouth_lowerlip_form') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Не определено';
        }
        if (($sourceFeatureName == 'mouth_lowerlip_form') && ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Дуга вверх';
        }
        if (($sourceFeatureName == 'mouth_upperlip_form') && ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Дуга вниз';
        }
        if (($sourceFeatureName == 'mouth_upperlip_form') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Не определено';
        }
        if (($sourceFeatureName == 'mouth_upperlip_form') && ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение формы';
            $targetValues['changeDirection'] = 'Дуга вверх';
        }
        if ((($sourceFeatureName == 'mouth_upper_lip_outer_center_movement') ||
                ($sourceFeatureName == 'mouth_lower_lip_outer_center_movement')) &&
            ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'mouth_upper_lip_outer_center_movement') ||
                ($sourceFeatureName == 'mouth_lower_lip_outer_center_movement')) &&
            ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if ((($sourceFeatureName == 'mouth_upper_lip_outer_center_movement') ||
                ($sourceFeatureName == 'mouth_lower_lip_outer_center_movement')) &&
            ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }

        /* Соответствия для подбородка */
        if ($sourceFacePart == 'chin')
            $targetValues['targetFacePart'] = 'Подбородок';
        if ($sourceFeatureName == 'chin_movement')
            $targetValues['targetFacePart'] = 'Подбородок';
        if (($sourceFeatureName == 'chin_movement') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if (($sourceFeatureName == 'chin_movement') && ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        if (($sourceFeatureName == 'chin_movement') && ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }

        /* Соответствия для носа */
        // Крылья носа
        if (($sourceFacePart == 'nose') || ($sourceFacePart == 'nose_movement') || ($sourceFacePart == 'nose_width_changing'))
            $targetValues['targetFacePart'] = 'Нос';
        if ($sourceFeatureName == 'nose_wing_movement')
            $targetValues['targetFacePart'] = 'Крылья носа';
        if (($sourceFeatureName == 'nose_wing_movement') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if (($sourceFeatureName == 'nose_movement') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if (($sourceFeatureName == 'nose_movement') && ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if (($sourceFeatureName == 'nose_movement') && ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        if (($sourceFeatureName == 'nose_width_changing') && ($sourceValue == 'none')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if (($sourceFeatureName == 'nose_width_changing') && ($sourceValue == '+')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Увеличение';
        }
        if (($sourceFeatureName == 'nose_width_changing') && ($sourceValue == '-')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'Уменьшение';
        }
        if (($sourceFeatureName == 'nose_wing_movement') && ($sourceValue == 'up')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вверх';
        }
        if (($sourceFeatureName == 'nose_wing_movement') && ($sourceValue == 'down')) {
            $targetValues['featureChangeType'] = 'Изменение положения по вертикали';
            $targetValues['changeDirection'] = 'Вниз';
        }
        // Носогубная складка
        if ($sourceFeatureName == 'left_nasolabial_fold_movement')
            $targetValues['targetFacePart'] = 'Левая носогубная складка';
        if ($sourceFeatureName == 'right_nasolabial_fold_movement')
            $targetValues['targetFacePart'] = 'Правая носогубная складка';
        if ((($sourceFeatureName == 'left_nasolabial_fold_movement') ||
                ($sourceFeatureName == 'right_nasolabial_fold_movement')) &&
            ($sourceValue != 'from center')) {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие направления';
        }
        if ((($sourceFeatureName == 'left_nasolabial_fold_movement') ||
                ($sourceFeatureName == 'right_nasolabial_fold_movement')) &&
            ($sourceValue == 'from center')) {
            $targetValues['featureChangeType'] = 'Изменение положения по горизонтали';
            $targetValues['changeDirection'] = 'От центра в стороны';
        }
        // Морщины носа
        if ($sourceFeatureName == 'central_nose_wrinkle_zone')
            $targetValues['targetFacePart'] = 'Центральная зона морщин носа';
        if ($sourceFeatureName == 'left_nose_wrinkle_zone')
            $targetValues['targetFacePart'] = 'Левая зона морщин носа';
        if ($sourceFeatureName == 'right_nose_wrinkle_zone')
            $targetValues['targetFacePart'] = 'Правая зона морщин носа';
        if ((($sourceFeatureName == 'central_nose_wrinkle_zone') || ($sourceFeatureName == 'left_nose_wrinkle_zone') ||
                ($sourceFeatureName == 'right_nose_wrinkle_zone')) && $sourceValue == 'none') {
            $targetValues['featureChangeType'] = 'Отсутствие типа';
            $targetValues['changeDirection'] = 'Отсутствие изменения размера';
        }
        if ((($sourceFeatureName == 'central_nose_wrinkle_zone') || ($sourceFeatureName == 'left_nose_wrinkle_zone') ||
                ($sourceFeatureName == 'right_nose_wrinkle_zone')) && $sourceValue == '+') {
            $targetValues['featureChangeType'] = 'Изменение размера';
            $targetValues['changeDirection'] = 'Увеличение';
        }
        if ((($sourceFeatureName == 'left_nose_wrinkle_zone') || ($sourceFeatureName == 'left_nose_wrinkle_zone') ||
                ($sourceFeatureName == 'right_nose_wrinkle_zone')) && $sourceValue == '-') {
            $targetValues['featureChangeType'] = 'Изменение размера';
            $targetValues['changeDirection'] = 'Уменьшение';
        }

        return $targetValues;
    }

    /**
     * Поиск соответствий между форматами МОП и МИП для признаков общего поведения.
     *
     * @param $sourceFacePart - название части лица от МОП
     * @param $sourceFeatureName - название признака от МОП
     * @param $sourceValue - значение признака от МОП
     * @return array - массив значений для МИП
     */
    public static function findCorrespondencesForBehaviorFeatures($sourceFacePart, $sourceFeatureName, $sourceValue)
    {
        // Формирование пустого целевого массива с лицевыми признаками для МИП
        $targetValues = array();
        $targetValues['targetFacePart'] = null;
        $targetValues['generalNameBehavior'] = null;
        $targetValues['presenceFeature'] = null;

        /* Соответствия для рта */
        // Разговор (речь)
        if ($sourceFacePart == 'mouth')
            $targetValues['targetFacePart'] = 'Рот';
        if ($sourceFeatureName == 'speaking')
            $targetValues['generalNameBehavior'] = 'Речь';
        if ($sourceFeatureName == 'speaking' && $sourceValue == 'yes')
            $targetValues['presenceFeature'] = 'Да';

        /* Соответствия для глаз */
        // Глаза
        if ($sourceFeatureName == 'left_eye_blink')
            $targetValues['targetFacePart'] = 'Левый глаз';
        if ($sourceFeatureName == 'right_eye_blink')
            $targetValues['targetFacePart'] = 'Правый глаз';
        if ((($sourceFeatureName == 'left_eye_blink') || ($sourceFeatureName == 'right_eye_blink')) &&
            ($sourceValue == 'yes')) {
            $targetValues['generalNameBehavior'] = 'Моргание';
            $targetValues['presenceFeature'] = 'Да';
        }

        return $targetValues;
    }

    /**
     * Формирование факта с информацией по кадру.
     *
     * @param $frameIndex - номер кадра
     * @return stdClass - факт с информацией по кадру
     */
    public static function createFactWithFrameInformation($frameIndex)
    {
        $result = new stdClass;
        // Имя шаблона: Параметры сеанса логического вывода
        $result -> {'NameOfTemplate'} = 'T2168';
        // Имя слота: "Номер кадра"
        $result -> {'s975'} = $frameIndex;

        return $result;
    }

    /**
     * Конвертация фразы из текста в факт статистики средних величин интервью.
     *
     * @param $phrase - фраза из текста
     * @return stdClass|null - факт фразы
     */
    public static function convertPhrase($phrase)
    {
        if (is_array($phrase)) {
            $curPhraseFact = new stdClass;
            // Имя шаблона: Статистика средних величин интервью
            $curPhraseFact -> {'NameOfTemplate'} = 'T2167';
            // Имя слота: "Момент времени"
            if (isset($phrase["time"])) $curPhraseFact -> {'s968'} = $phrase["time"];
            // Имя слота: "Текст словосочетания"
            if (isset($phrase["val"])) $curPhraseFact -> {'s969'} = $phrase["val"];
            // Имя слота: "Номер кадра"
            if (isset($phrase["frame"])) $curPhraseFact -> {'s970'} = $phrase["frame"];
            // Имя слота: "Номер конечного кадра"
            if (isset($phrase["endFrame"])) $curPhraseFact -> {'s971'} = $phrase["endFrame"];
            // Имя слота: "Номер начального кадра"
            if (isset($phrase["startFrame"])) $curPhraseFact -> {'s972'} = $phrase["startFrame"];

            return $curPhraseFact;
        }

        return null;
    }

    /**
     * Конвертация последовательности фраз из текста распознанной речи.
     *
     * @param $phraseSequence - последовательность фраз из текста распознанной речи
     * @return array|null - массив фактов фраз
     */
    public static function convertPhraseSequence($phraseSequence)
    {
        if (isset($phraseSequence) && is_array($phraseSequence) && count($phraseSequence) > 0) {
            $result = array();
            foreach ($phraseSequence as $k => $v) {
                $curFact = self::convertPhrase($v);
                if (isset($curFact))
                    $result[] = $curFact;
            }

            return $result;
        }

        return null;
    }

    /**
     * Конвертация фраз из текста распознанной речи в набор фактов.
     *
     * @param $phrases - массив фраз текста распознанной речи
     * @return array|null - массив фактов с фразами
     */
    public static function convertPhrases($phrases)
    {
        if (isset($phrases)) {
            $result = array();
            if (isset($phrases["YesPhrase"]) && is_array($phrases["YesPhrase"])) {
                foreach ($phrases["YesPhrase"] as $k => $v) {
                    $curPhrases = self::convertPhraseSequence($v);
                    if (isset($curPhrases) && count($curPhrases) > 0)
                        $result = array_merge($result, $curPhrases);
                }
            }
            if (isset($phrases["NoPhrase"])) {
                foreach ($phrases["NoPhrase"] as $k=>$v) {
                    $curPhrases = self::convertPhraseSequence($v);
                    if (isset($curPhrases) && count($curPhrases) > 0)
                        $result = array_merge($result, $curPhrases);
                }
            }

            return $result;
        }

        return null;
    }

    /**
     * Конвертация статистики по всему видеоинтервью в два факта: статистика средних величин интервью и
     * статистика стандартных отклонений интервью.
     *
     * @param $statistics - массив со статистикой по всему видеоинтервью
     * @return array|null - массив из двух фактов с общей статистикой по всему видеоинтервью
     */
    public static function convertSummarizedFeatureStatistics($statistics)
    {
        $result = array();
        if (isset($statistics) && is_array($statistics)) {
            $factAver = new stdClass;
            // Имя шаблона: Статистика средних величин интервью
            $factAver -> {'NameOfTemplate'} = 'T2171';

            // Имя слота: "Средний темп речи"
            if (isset($statistics["average_speech_frequency"]) &&
                isset($statistics["average_speech_frequency"]["val"]))
                $factAver -> {'s989'} = $statistics["average_speech_frequency"]["val"];

            // Имя слота: "Среднее число морганий"
            if (isset($statistics["average_eye_blinking_frequency"]) &&
                isset($statistics["average_eye_blinking_frequency"]["val"]))
                $factAver -> {'s991'} = $statistics["average_eye_blinking_frequency"]["val"];

            // Имя слота: "Среднее число опускания уголков губ"
            if (isset($statistics["average_lipcorners_lowering_frequency"]) &&
                isset($statistics["average_lipcorners_lowering_frequency"]["val"]))
                $factAver -> {'s992'} = $statistics["average_lipcorners_lowering_frequency"]["val"];

            // Имя слота: "Среднее число поднятий бровей"
            if (isset($statistics["average_eyebrow_lift_frequency"]) &&
                isset($statistics["average_eyebrow_lift_frequency"]["val"]))
                $factAver -> {'s993'} = $statistics["average_eyebrow_lift_frequency"]["val"];

            // Имя слота: "Среднее число движений носом"
            if (isset($statistics["average_nose_movement_frequency"]) &&
                isset($statistics["average_nose_movement_frequency"]["val"]))
                $factAver -> {'s994'} = $statistics["average_nose_movement_frequency"]["val"];

            // Имя слота: "Среднее число нахмуриваний"
            if (isset($statistics["average_frown_frequency"]) &&
                isset($statistics["average_frown_frequency"]["val"]))
                $factAver -> {'s996'} = $statistics["average_frown_frequency"]["val"];

            // Имя слота: "Среднее время молчания перед ответом"
            if (isset($statistics["average_silence_before_response"]) &&
                isset($statistics["average_silence_before_response"]["val"]))
                $factAver -> {'s1004'} = $statistics["average_silence_before_response"]["val"];

            $result[] = $factAver;

            $factDev = new stdClass;
            // Имя шаблона: Статистика стандартных отклонений интервью
            $factDev -> {'NameOfTemplate'} = 'T2173';

            // Имя слота: "Стандартное отклонение темпа речи"
            if (isset($statistics["deviation_speech_frequency"]) &&
                isset($statistics["deviation_speech_frequency"]["val"]))
                $factDev -> {'s1003'} = $statistics["deviation_speech_frequency"]["val"];

            // Имя слота: "Стандартное отклонение числа морганий"
            if (isset($statistics["deviation_eye_blinking_frequency"]) &&
                isset($statistics["deviation_eye_blinking_frequency"]["val"]))
                $factDev -> {'s1002'} = $statistics["deviation_eye_blinking_frequency"]["val"];

            // Имя слота: "Стандартное отклонение числа опусканий уголков губ"
            if (isset($statistics["deviation_lipcorners_lowering_frequency"]) &&
                isset($statistics["deviation_lipcorners_lowering_frequency"]["val"]))
                $factDev -> {'s997'} = $statistics["deviation_lipcorners_lowering_frequency"]["val"];

            // Имя слота: "Стандартное отклонение числа поднятий бровей"
            if (isset($statistics["deviation_eyebrow_lift_frequency"]) &&
                isset($statistics["deviation_eyebrow_lift_frequency"]["val"]))
                $factDev -> {'s1001'} = $statistics["deviation_eyebrow_lift_frequency"]["val"];

            // Имя слота: "Стандартное отклонение числа движений носом"
            if (isset($statistics["deviation_nose_movement_frequency"]) &&
                isset($statistics["deviation_nose_movement_frequency"]["val"]))
                $factDev -> {'s1000'} = $statistics["deviation_nose_movement_frequency"]["val"];

            // Имя слота: "Стандартное отклонение числа нахмуриваний"
            if (isset($statistics["deviation_frown_frequency"]) &&
                isset($statistics["deviation_frown_frequency"]["val"]))
                $factDev -> {'s998'} = $statistics["deviation_frown_frequency"]["val"];

            // Имя слота: "Стандартное отклонение молчания перед ответом "
            if (isset($statistics["deviation_silence_before_response"]) &&
                isset($statistics["deviation_silence_before_response"]["val"]))
                $factDev -> {'s1005'} = $statistics["deviation_silence_before_response"]["val"];

            $result[] = $factDev;

            return $result;
        }

        return null;
    }

    /**
     * Конвертация статистики по определенным лицевым признакам в факт статистики средних величин вопроса.
     *
     * @param $statistics - статистика определенных лицевых признаков
     * @param $questionText - текст вопроса
     * @return stdClass|null - факт со статистикой по вопросу
     */
    public static function convertFeatureStatistics($statistics, $questionText)
    {
        if (isset($statistics) && is_array($statistics)) {
            $fact = new stdClass;
            // Имя шаблона: Статистика средних величин вопроса
            $fact -> {'NameOfTemplate'} = 'T2172';

            // Имя слота: "Средний темп речи"
            if (isset($statistics["average_speech_frequency"]) &&
                isset($statistics["average_speech_frequency"]["val"]))
                $fact -> {'s989'} = $statistics["average_speech_frequency"]["val"];

            //s990: String @NameOfSlot (Заданный вопрос) - текст вопроса
            $fact -> {'s990'} = $questionText;

            // Имя слота: "Среднее число морганий"
            if (isset($statistics["average_eye_blinking_frequency"]) &&
                isset($statistics["average_eye_blinking_frequency"]["val"]))
                $fact -> {'s991'} = $statistics["average_eye_blinking_frequency"]["val"];

            // Имя слота: "Среднее число опускания уголков губ"
            if (isset($statistics["average_lipcorners_lowering_frequency"]) &&
                isset($statistics["average_lipcorners_lowering_frequency"]["val"]))
                $fact -> {'s992'} = $statistics["average_lipcorners_lowering_frequency"]["val"];

            // Имя слота: "Среднее число поднятий бровей"
            if (isset($statistics["average_eyebrow_lift_frequency"]) &&
                isset($statistics["average_eyebrow_lift_frequency"]["val"]))
                $fact -> {'s993'} = $statistics["average_eyebrow_lift_frequency"]["val"];

            // Имя слота: "Среднее число движений носом"
            if (isset($statistics["average_nose_movement_frequency"]) &&
                isset($statistics["average_nose_movement_frequency"]["val"]))
                $fact -> {'s994'} = $statistics["average_nose_movement_frequency"]["val"];

            // Имя слота: "Среднее число нахмуриваний"
            if (isset($statistics["average_frown_frequency"]) &&
                isset($statistics["average_frown_frequency"]["val"]))
                $fact -> {'s996'} = $statistics["average_frown_frequency"]["val"];

            // Имя слота: "Среднее время молчания перед ответом"
            if (isset($statistics["silence_before_response"]) &&
                isset($statistics["silence_before_response"]["val"]))
                $fact -> {'s1004'} = $statistics["silence_before_response"]["val"];

            return $fact;
        }

        return null;
    }

    /**
     * Преобразование массива с результатами определения признаков в массив фактов.
     *
     * @param $faceData - цифровая маска
     * @param $detectedFeatures - массив обнаруженных признаков
     * @param $questionTime - время на вопрос в миллисекундах
     * @param $questionText - текст вопроса
     * @return array - массив наборов фактов для каждого кадра видео
     */
    public static function convertFeaturesToFacts($faceData, $detectedFeatures, $questionTime, $questionText)
    {
        // Массив для наборов фактов, сформированных для каждого кадра
        $facts = array();
        // Кол-во кадров
        $frameNumber = 0;
        if (isset($detectedFeatures['eye']['left_eye_upper_eyelid_movement']) &&
            is_array($detectedFeatures['eye']['left_eye_upper_eyelid_movement']))
            $frameNumber = count($detectedFeatures['eye']['left_eye_upper_eyelid_movement']);
        // Цикл от 1 до общего-кол-ва кадров
        for ($i = 1; $i < $frameNumber; $i++) {
            // Массив фактов для текущего кадра
            $frameFacts = array();
            // Обход всех определенных лицевых признаков
            foreach ($detectedFeatures as $facePart => $features) {
                if ($features != null)
                    foreach ($features as $featureName => $frames)
                        if (is_array($frames))
                            for ($j = 1; $j < count($frames); $j++) {
                                if (isset($frames[$j]["val"]) && isset($frames[$j]["force"]) && ($i == $j)) {
                                    // Поиск соответствий лицевых признаков
                                    $targetValues = self::findCorrespondences($facePart, $featureName,
                                        $frames[$j]["val"]);
                                    // Если соответсвия лицевых признаков найдены
                                    if ($targetValues['targetFacePart'] != null &&
                                        $targetValues['featureChangeType'] != null &&
                                        $targetValues['changeDirection'] != null) {
                                        // Формирование факта одного лицевого признака для текущего кадра
                                        $faceFeatureFact['NameOfTemplate'] = 'T1986';
                                        $faceFeatureFact['s861'] = $targetValues['targetFacePart'];
                                        $faceFeatureFact['s862'] = $targetValues['featureChangeType'];
                                        $faceFeatureFact['s863'] = $targetValues['changeDirection'];
                                        $faceFeatureFact['s864'] = $frames[$j]["force"];
                                        $faceFeatureFact['s869'] = $j;
                                        $faceFeatureFact['s870'] = $j;
                                        $faceFeatureFact['s871'] = $j; //count($frames);
                                        $faceFeatureFact['s874'] = $j;
                                        // Добавление факта одного лицевого признака для текущего кадра в набор фактов
                                        array_push($frameFacts, $faceFeatureFact);
                                    }
                                }
                                if (isset($frames[$j]["val"]) && $i == $j) {
                                    // Поиск соответствий признаков общего поведения
                                    $targetValues = self::findCorrespondencesForBehaviorFeatures(
                                        $facePart,
                                        $featureName,
                                        $frames[$j]["val"]
                                    );
                                    // Если соответсвия признаков общего поведения найдены
                                    if ($targetValues['targetFacePart'] != null &&
                                        $targetValues['generalNameBehavior'] != null &&
                                        $targetValues['presenceFeature'] != null) {
                                        // Формирование факта одного признака общего поведения для текущего кадра
                                        $generalBehaviorFeatureFact = array();
                                        $generalBehaviorFeatureFact['NameOfTemplate'] = 'T2046';
                                        $generalBehaviorFeatureFact['s908'] = $targetValues['generalNameBehavior'];
                                        $generalBehaviorFeatureFact['s909'] = $j;
                                        $generalBehaviorFeatureFact['s910'] = $j; //count($frames);
                                        $generalBehaviorFeatureFact['s911'] = $j;
                                        $generalBehaviorFeatureFact['s913'] = $targetValues['targetFacePart'];
                                        // Добавление факта одного признака общего поведения для текущего кадра в набор фактов
                                        array_push($frameFacts, $generalBehaviorFeatureFact);
                                    }
                                }
                            }
            }
            if ($i == 1 && $questionTime != null) {
                // Декодирование цифровой маски из json-формата
                $faceData = json_decode($faceData, true);
                $fps = null;
                // Определение FPS
                if (isset($faceData['fps']))
                    $fps = $faceData['fps'];
                if (isset($faceData['FPS']))
                    $fps = $faceData['FPS'];
                // Если существует ключ (индекс) - fps или FPS
                if (isset($faceData['fps']) || isset($faceData['FPS'])) {
                    // Определение времени на вопрос в кадрах
                    $questionTimeInFrames = round(((float)$fps * ($questionTime / 1000)), 0);
                    // Формирование факта одного признака для первого кадра
                    $videoParametersFact['NameOfTemplate'] = 'T2110';
                    $videoParametersFact['s922'] = $fps;
                    $videoParametersFact['s924'] = $questionTimeInFrames;
                    // Добавление факта параметра видео для первого кадра в набор фактов
                    array_push($frameFacts, $videoParametersFact);
                }
            }
            if ($i == 1) {
                // Обход всех определенных лицевых признаков
                foreach ($detectedFeatures as $key => $value) {
                    if ($key == 'feature_statistics') {
                        // Конвертация статистики по видео в факт
                        $featureStatistics = self::convertFeatureStatistics($value, $questionText);
                        // Если факт создан
                        if (isset($featureStatistics))
                            // Добавление факта по статистике для первого кадра в набор фактов
                            array_push($frameFacts, $featureStatistics);
                    }
                    if ($key == 'text') {
                        // Конвертация фраз текста в факт
                        $phrases = self::convertPhrases($value);
                        // Если создан массив с фактами
                        if (isset($phrases) && is_array($phrases))
                            foreach ($phrases as $phrase)
                                // Добавление факта по фразам для первого кадра в набор фактов
                                array_push($frameFacts, $phrase);
                    }
                }
            }
//            if ($i <= $questionTimeInFrames) {
//                // Формирование факта признака общего поведения (слушание) для текущего кадра
//                $generalBehaviorFeatureFact = array();
//                $generalBehaviorFeatureFact['NameOfTemplate'] = 'T2046';
//                $generalBehaviorFeatureFact['s908'] = 'Слушание';
//                $generalBehaviorFeatureFact['s909'] = $i;
//                $generalBehaviorFeatureFact['s910'] = $i; //$frameNumber;
//                $generalBehaviorFeatureFact['s911'] = $i;
//                // Добавление факта одного признака общего поведения (слушание) для текущего кадра в набор фактов
//                array_push($frameFacts, $generalBehaviorFeatureFact);
//            }
            // Добавление в конец набора фактов факта с информацией по кадру
            $frameFacts[] = self::createFactWithFrameInformation($i);
            // Добавление набора фактов для текущего кадра в общий массив фактов
            array_push($facts, $frameFacts);
        }

        return $facts;
    }

    /**
     * Преобразование массива с action units в массив фактов.
     *
     * @param stdClass $actionUnits - массив AUs (action units)
     * @param $frameIndex - номер кадра
     * @return array - массив фактов по AUs
     */
    public static function convertActionUnitsToFacts(stdClass $actionUnits, $frameIndex)
    {
        $replacementTable = array_combine(json_decode('["AU00","AU01","AU02","AU04","AU05","AU06","AU07","AU08","AU09","AU10","AU11","AU12","AU13","AU14","AU15","AU16","AU17","AU18","AU19","AU20","AU21","AU22","AU23","AU24","AU25","AU26","AU27","AU28","AU29","AU30","AU31","AU32","AU33","AU34","AU35","AU36","AU37","AU38","AU39","AU41","AU42","AU43","AU44","AU45","AU46","AU51","AU52","AU53","AU54","AU55","AU","AU56","AU","AU57","AU","AU58","AU","AU","AU","AU61","AU","AU62","AU","AU63","AU64","AU65","AU66","AU","AU69","AU","AU70","AU71","AU72","AU73","AU74","AU40","AU50","AU80","AU81","AU82","AU84","AU85"]'),
            json_decode('["AU0 - Нейтральное лицо","AU1 - Подниматель внутренней части брови","AU2 - Подниматель внешней части брови","AU4 - Опускатель брови","AU5 - Подниматель верхнего века","AU6 - Подниматель щеки","AU7 - Натягиватель века","AU8 - Губы навстречу друг другу","AU9 - Сморщиватель носа","AU10 - Подниматель верхней губы","AU11 - Углубитель носогубной складки","AU12 - Подниматель уголка губы","AU13 - Острый подниматель уголка губы","AU14 - Ямочка","AU15 - Опускатель уголка губы","AU16 - Опускатель нижней губы","AU17 - Подниматель подбородка","AU18 - Сморщиватель губ","AU19 - Показ языка","AU20 - Растягиватель губ","AU21 - Натягиватель шеи","AU22 - Губы воронкой","AU23 - Натягиватель губ","AU24 - Сжиматель губ","AU25 - Губы разведены","AU26 - Челюсть опущена","AU27 - Рот широко открыт","AU28 - Втягивание губ","AU29 - Нижняя челюсть вперёд","AU30 - Челюсть в бок","AU31 - Сжиматель челюстей","AU32 - Покусывание губы","AU33 - Выдувание","AU34 - Раздувание щёк","AU35 - Втягивание щёк","AU36 - Язык высунут","AU37 - Облизывание губ","AU38 - Расширитель ноздрей","AU39 - Суживатель ноздрей","AU41 - Опускатель надпереносья","AU42 - Опускатель внутренней части брови","AU43 - Глаза закрыты","AU44 - Сведение бровей","AU45 - Моргание","AU46 - Подмигивание","AU51 - Поворот головы влево","AU52 - Поворот головы вправо","AU53 - Голова вверх","AU54 - Голова вниз","AU55 - Наклон головы влево","AU M55 - Наклон головы влево","AU56 - Наклон головы вправо","AU M56 - Наклон головы вправо","AU57 - Голова вперёд","AU M57 - Толчок головы вперёд","AU58 - Голова назад","AU M59 - Кивок головой","AU M60 - Голова из стороны в сторону","AU M83 - Голова вверх и в сторону","AU61 - Отведение глаз влево","AU M61 - Глаза влево","AU62 - Отведение глаз вправо","AU M62 - Глаза вправо","AU63 - Глаза вверх","AU64 - Глаза вниз","AU65 - Расходящееся косоглазие","AU66 - Сходящееся косоглазие","AU M68 - Закатывание глаз","AU69 - Глаза на другом человеке","AU M69 - Голова и/или глаза на другом человеке","AU70 - Брови и лоб не видны","AU71 - Глаза не видны","AU72 - Нижняя часть лица не видна","AU73 - Всё лицо не видно","AU74 - Оценивание невозможно","AU40 - Втягивание носом","AU50 - Речь","AU80 - Глотание","AU81 - Жевание","AU82 - Пожатие плечом","AU84 - Движение головой назад и вперёд","AU85 - Кивок головой вверх и вниз"]'));
        $result = array();
        foreach ($actionUnits as $name => $actionUnit) {
            if ($actionUnit->presence === 1) {
                $fact = new stdClass;
                // Имя шаблона: Признаки эмоций (Action units)
                $fact -> {'NameOfTemplate'} = 'T2045';
                // Имя слота: "Название" Описание слота: "Название action unit'а"
                $fact -> {'s900'} = $replacementTable[$name];
                // [Нет данных - пропускаем] Имя слота: "Проявление" Описание слота: "Описание проявления action unit'а:
                // левая часть лица, правая или обе стороны"
                // $fact -> {'s901'} = Null;
                // [+ Преобразование в %] Имя слота: "Интенсивность" Описание слота: ""
                $fact -> {'s902'} = $actionUnit -> intensity * 20;
                // Имя слота: "Номер кадра" Описание слота: ""
                $fact -> {'s903'} = $frameIndex;
                $result[] = $fact;
            };
        }

        return $result;
    }

    /**
     * Преобразование массива с направлениями взгляда в массив фактов.
     *
     * @param stdClass $gazeDirections - массив направлений взгляда
     * @param $frameIndex - номер кадра
     * @return array - массив фактов с направлением взгляда
     */
    public static function convertGazeToFacts(stdClass $gazeDirections, $frameIndex)
    {
        $result = array();
        foreach ($gazeDirections as $name => $gazeDirection) {
            // Направления взгляда по горизонтали
            if ($name === 'horz') {
                // Формирование шаблона факта направления взгляда по горизонтали (левый зрачок) для текущего кадра
                $fact = new stdClass;
                $fact -> {'NameOfTemplate'} = 'T1986';
                $fact -> {'s861'} = 'Левый зрачок';
                $fact -> {'s862'} = 'Изменение положения по горизонтали';
                if ($gazeDirection->direction === 'right')
                    $fact -> {'s863'} = 'Влево';
                if ($gazeDirection->direction === 'left')
                    $fact -> {'s863'} = 'Вправо';
                $fact -> {'s864'} = abs($gazeDirection->intensity);
                $fact -> {'s869'} = $frameIndex;
                $fact -> {'s870'} = $frameIndex;
                $fact -> {'s871'} = $frameIndex;
                $fact -> {'s874'} = $frameIndex;
                $result[] = $fact;
                // Формирование шаблона факта направления взгляда по горизонтали (правый зрачок) для текущего кадра
                $fact = new stdClass;
                $fact -> {'NameOfTemplate'} = 'T1986';
                $fact -> {'s861'} = 'Правый зрачок';
                $fact -> {'s862'} = 'Изменение положения по горизонтали';
                if ($gazeDirection->direction === 'right')
                    $fact -> {'s863'} = 'Влево';
                if ($gazeDirection->direction === 'left')
                    $fact -> {'s863'} = 'Вправо';
                $fact -> {'s864'} = abs($gazeDirection->intensity);
                $fact -> {'s869'} = $frameIndex;
                $fact -> {'s870'} = $frameIndex;
                $fact -> {'s871'} = $frameIndex;
                $fact -> {'s874'} = $frameIndex;
                $result[] = $fact;
            };
            // Направления взгляда по вертикали
            if ($name === 'vert') {
                // Формирование шаблона факта направления взгляда по вертикали (левый зрачок) для текущего кадра
                $fact = new stdClass;
                $fact -> {'NameOfTemplate'} = 'T1986';
                $fact -> {'s861'} = 'Левый зрачок';
                $fact -> {'s862'} = 'Изменение положения по вертикали';
                if ($gazeDirection->direction === 'up')
                    $fact -> {'s863'} = 'Вверх';
                if ($gazeDirection->direction === 'down')
                    $fact -> {'s863'} = 'Вниз';
                $fact -> {'s864'} = abs($gazeDirection->intensity);
                $fact -> {'s869'} = $frameIndex;
                $fact -> {'s870'} = $frameIndex;
                $fact -> {'s871'} = $frameIndex;
                $fact -> {'s874'} = $frameIndex;
                $result[] = $fact;
                // Формирование шаблона факта направления взгляда по вертикали (правый зрачок) для текущего кадра
                $fact = new stdClass;
                $fact -> {'NameOfTemplate'} = 'T1986';
                $fact -> {'s861'} = 'Правый зрачок';
                $fact -> {'s862'} = 'Изменение положения по вертикали';
                if ($gazeDirection->direction === 'up')
                    $fact -> {'s863'} = 'Вверх';
                if ($gazeDirection->direction === 'down')
                    $fact -> {'s863'} = 'Вниз';
                $fact -> {'s864'} = abs($gazeDirection->intensity);
                $fact -> {'s869'} = $frameIndex;
                $fact -> {'s870'} = $frameIndex;
                $fact -> {'s871'} = $frameIndex;
                $fact -> {'s874'} = $frameIndex;
                $result[] = $fact;
            };
        }

        return $result;
    }

    /**
     * Определение поворота головы на основе анализа событий.
     *
     * @param $landmark - цифровая маска, полученная вторым скриптом МОВ Ивана
     * @return int|null - числовое значение поворота головы (0 - поворот вправо, 1 - поворот влево)
     */
    public static function determineTurn($landmark)
    {
        // Если цифровая маска содержит события и получена вторым скриптом МОВ Ивана
        if (strripos($landmark->landmark_file_name, '_ext') !== false) {
            // Количество поворотов головы вправо и влево
            $turnRightNumber = 0;
            $turnLeftNumber = 0;
            // Создание объекта коннектора с Yandex.Cloud Object Storage
            $osConnector = new OSConnector();
            // Получение содержимого json-файла с результатами второго скрипта МОВ Ивана из Object Storage
            $jsonFaceData = $osConnector->getFileContentFromObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $landmark->id,
                $landmark->landmark_file_name
            );
            // Замена в строке некорректных значений для правильного декодирования json-формата
            $jsonFaceData = str_ireplace('NaN','99999', $jsonFaceData);
            // Декодирование json-файла с цифровой маской
            $faceData = json_decode($jsonFaceData, true);
            // Определение кол-ва событий поворотов головы вправо и влево
            foreach ($faceData as $key => $value)
                if (strpos(Trim($key), 'frame_') !== false)
                    if (isset($value['EVENTS']))
                        foreach ($value['EVENTS'] as $event) {
                            if ($event == VideoProcessingModuleSettingForm::TURN_RIGHT_EVENT)
                                $turnRightNumber++;
                            if ($event == VideoProcessingModuleSettingForm::TURN_LEFT_EVENT)
                                $turnLeftNumber++;
                        }
            // Возвращение значения определения поворота головы
            if ($turnRightNumber > $turnLeftNumber)
                return self::TURN_RIGHT;
            if ($turnRightNumber < $turnLeftNumber)
                return self::TURN_LEFT;
        }

        return null;
    }

    /**
     * Определение качества видео на основе коэффициентов.
     *
     * @param $landmark - цифровая маска, полученная первым скриптом МОВ Ивана
     * @return array - массив с оценкой качества видео (true или false) и числовыми коэффициентами
     */
    public static function determineQuality($landmark)
    {
        // Значение FPS
        $fpsValue = true;
        // Показатель качества видео
        $qualityVideo = false;
        // Массив с коэффициентами качества видео
        $videoQualityParameters = array();
        // Если цифровая маска содержит получена первым скриптом МОВ Ивана
        if (strripos($landmark->landmark_file_name, '_ext') === false) {
            // Создание объекта коннектора с Yandex.Cloud Object Storage
            $osConnector = new OSConnector();
            // Получение содержимого json-файла с результатами первого скрипта МОВ Ивана из Object Storage
            $jsonFaceData = $osConnector->getFileContentFromObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $landmark->id,
                $landmark->landmark_file_name
            );
            // Замена в строке некорректных значений для правильного декодирования json-формата
            $jsonFaceData = str_ireplace('NaN','99999', $jsonFaceData);
            // Декодирование json-файла с цифровой маской
            $faceData = json_decode($jsonFaceData, true);
            // Определение качества видео по коэффициентам
            foreach ($faceData as $key => $value) {
                if ($key == 'err_msg')
                    $fpsValue = false;
                //if ($key == 'FPS')
                //    $fpsValue = $value;
                if ($key == 'COEF_QUALITY') {
                    foreach ($value as $coefficient)
                        array_push($videoQualityParameters, $coefficient);
                    if (isset($videoQualityParameters[0]) && isset($videoQualityParameters[1]) &&
                        isset($videoQualityParameters[2]) && isset($videoQualityParameters[3]) &&
                        isset($videoQualityParameters[4])) {
                        // Если видео полность подходит под коэффициенты качества
                        if ($videoQualityParameters[0] > 10 && $videoQualityParameters[1] < 2 &&
                            $videoQualityParameters[2] < 0.5 && $videoQualityParameters[3] < 25 &&
                            $videoQualityParameters[4] > 2)
                            $qualityVideo = true;
                        // Если К2 выше нормы И К1, К3, К4, К5 в норме
                        if ($videoQualityParameters[0] > 13.5 && $videoQualityParameters[1] > 2 &&
                            $videoQualityParameters[2] < 0.3 && $videoQualityParameters[3] < 5 &&
                            $videoQualityParameters[4] > 3)
                            $qualityVideo = true;
                        // Если K4 равен -1 (лицо не в кадре)
                        if ($videoQualityParameters[3] == -1)
                            $qualityVideo = false;
                    }
                }
            }
        }

        return array($fpsValue, $qualityVideo, $videoQualityParameters);
    }

    /**
     * Получение фактов для наклонов, поворотов и кивков головы, и их добавление в общий набор фактов.
     *
     * @param $landmark - цифровая маска, полученная вторым скриптом МОВ Ивана
     * @param $facts - исходный общий набор фактов
     * @return array|null - обновленный набор фактов с фактами событий наклонов, поворотов и кивков головы
     */
    public static function getHeadPositionEventFacts($landmark, $facts)
    {
        // Переменная для хранения обновленного общего набора фактов
        $updateFacts = null;
        // Если цифровая маска содержит события и получена вторым скриптом МОВ Ивана
        if (strripos($landmark->landmark_file_name, '_ext') !== false) {
            // Формирвоание обновленного общего набора фактов на основе исходного общего набора фактов
            $updateFacts = $facts;
            // Создание объекта коннектора с Yandex.Cloud Object Storage
            $osConnector = new OSConnector();
            // Получение содержимого json-файла с результатами второго скрипта МОВ Ивана из Object Storage
            $jsonFaceData = $osConnector->getFileContentFromObjectStorage(
                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $landmark->id,
                $landmark->landmark_file_name
            );
            // Замена в строке некорректных значений для правильного декодирования json-формата
            $jsonFaceData = str_ireplace('NaN','99999', $jsonFaceData);
            // Декодирование json-файла с цифровой маской
            $faceData = json_decode($jsonFaceData, true);
            // Обход фреймов в цифровой маске
            foreach ($faceData as $key => $value)
                if (strpos(Trim($key), 'frame_') !== false)
                    if (isset($value['FRAMES']))
                        // Обход подмассива с номерами кадров
                        foreach ($value['FRAMES'] as $frame)
                            // Обход общего набора фактов
                            foreach ($updateFacts as $frameNumber => $factsForFrame)
                                // Если номера кадров совпадают
                                if ($frame == $frameNumber) {
                                    if (isset($value['EVENTS']) && isset($value['EVENT_VALS'])) {
                                        // Массив для хранения набора фактов признаков общего поведения
                                        $generalBehaviorFeatureFacts = array();
                                        // Обход событий
                                        foreach ($value['EVENTS'] as $eventKey => $event) {
                                            // Обход значений событий
                                            foreach ($value['EVENT_VALS'] as $eventValueKey => $eventValue)
                                                if ($eventKey == $eventValueKey) {
                                                    // Если есть событие наклона головы влево
                                                    if ($event == VideoProcessingModuleSettingForm::TILT_LEFT_EVENT) {
                                                        // Формирование факта о наклоне головы влево
                                                        $generalBehaviorFeatureFact = new stdClass;
                                                        $generalBehaviorFeatureFact->{'NameOfTemplate'} = 'T2046';
                                                        $generalBehaviorFeatureFact->{'s908'} = 'Наклон головы влево';
                                                        $generalBehaviorFeatureFact->{'s909'} = (int)$frame + 1;
                                                        $generalBehaviorFeatureFact->{'s913'} = 'Голова';
                                                        $generalBehaviorFeatureFact->{'s1011'} = $eventValue;
                                                        // Добавление факта о наклоне головы влево в
                                                        // массив фактов признаков общего поведения
                                                        array_push($generalBehaviorFeatureFacts,
                                                            $generalBehaviorFeatureFact);
                                                    }
                                                    // Если есть событие наклона головы вправо
                                                    if ($event == VideoProcessingModuleSettingForm::TILT_RIGHT_EVENT) {
                                                        // Формирование факта о наклоне головы вправо
                                                        $generalBehaviorFeatureFact = new stdClass;
                                                        $generalBehaviorFeatureFact->{'NameOfTemplate'} = 'T2046';
                                                        $generalBehaviorFeatureFact->{'s908'} = 'Наклон головы вправо';
                                                        $generalBehaviorFeatureFact->{'s909'} = (int)$frame + 1;
                                                        $generalBehaviorFeatureFact->{'s913'} = 'Голова';
                                                        $generalBehaviorFeatureFact->{'s1011'} = $eventValue;
                                                        // Добавление факта о наклоне головы вправо в
                                                        // массив фактов признаков общего поведения
                                                        array_push($generalBehaviorFeatureFacts,
                                                            $generalBehaviorFeatureFact);
                                                    }
                                                    // Если есть событие поворота головы влево
                                                    if ($event == VideoProcessingModuleSettingForm::TURN_LEFT_EVENT) {
                                                        // Формирование факта о повороте головы влево
                                                        $generalBehaviorFeatureFact = new stdClass;
                                                        $generalBehaviorFeatureFact->{'NameOfTemplate'} = 'T2046';
                                                        $generalBehaviorFeatureFact->{'s908'} = 'Поворот головы влево';
                                                        $generalBehaviorFeatureFact->{'s909'} = (int)$frame + 1;
                                                        $generalBehaviorFeatureFact->{'s913'} = 'Голова';
                                                        $generalBehaviorFeatureFact->{'s1011'} = $eventValue;
                                                        // Добавление факта о повороте головы влево в
                                                        // массив фактов признаков общего поведения
                                                        array_push($generalBehaviorFeatureFacts,
                                                            $generalBehaviorFeatureFact);
                                                    }
                                                    // Если есть событие поворота головы вправо
                                                    if ($event == VideoProcessingModuleSettingForm::TURN_RIGHT_EVENT) {
                                                        // Формирование факта о повороте головы вправо
                                                        $generalBehaviorFeatureFact = new stdClass;
                                                        $generalBehaviorFeatureFact->{'NameOfTemplate'} = 'T2046';
                                                        $generalBehaviorFeatureFact->{'s908'} = 'Поворот головы вправо';
                                                        $generalBehaviorFeatureFact->{'s909'} = (int)$frame + 1;
                                                        $generalBehaviorFeatureFact->{'s913'} = 'Голова';
                                                        $generalBehaviorFeatureFact->{'s1011'} = $eventValue;
                                                        // Добавление факта о повороте головы вправо в
                                                        // массив фактов признаков общего поведения
                                                        array_push($generalBehaviorFeatureFacts,
                                                            $generalBehaviorFeatureFact);
                                                    }
                                                    // Если есть событие опускания головы вниз
                                                    if ($event == VideoProcessingModuleSettingForm::HEAD_NOD_DOWN_EVENT) {
                                                        // Формирование факта об опускании головы вниз
                                                        $generalBehaviorFeatureFact = new stdClass;
                                                        $generalBehaviorFeatureFact->{'NameOfTemplate'} = 'T2046';
                                                        $generalBehaviorFeatureFact->{'s908'} = 'Опускание головы вниз';
                                                        $generalBehaviorFeatureFact->{'s909'} = (int)$frame + 1;
                                                        $generalBehaviorFeatureFact->{'s913'} = 'Голова';
                                                        $generalBehaviorFeatureFact->{'s1011'} = $eventValue;
                                                        // Добавление факта об опускании головы вниз в
                                                        // массив фактов признаков общего поведения
                                                        array_push($generalBehaviorFeatureFacts,
                                                            $generalBehaviorFeatureFact);
                                                    }
                                                    // Если есть событие поднятия головы вверх
                                                    if ($event == VideoProcessingModuleSettingForm::HEAD_NOD_UP_EVENT) {
                                                        // Формирование факта о поднятии головы вверх
                                                        $generalBehaviorFeatureFact = new stdClass;
                                                        $generalBehaviorFeatureFact->{'NameOfTemplate'} = 'T2046';
                                                        $generalBehaviorFeatureFact->{'s908'} = 'Поднятие головы вверх';
                                                        $generalBehaviorFeatureFact->{'s909'} = (int)$frame + 1;
                                                        $generalBehaviorFeatureFact->{'s913'} = 'Голова';
                                                        $generalBehaviorFeatureFact->{'s1011'} = $eventValue;
                                                        // Добавление факта о поднятии головы вверх в
                                                        // массив фактов признаков общего поведения
                                                        array_push($generalBehaviorFeatureFacts,
                                                            $generalBehaviorFeatureFact);
                                                    }
                                                }
                                            // Если есть событие кивка головы
                                            if (strripos($event, VideoProcessingModuleSettingForm::HAS_NODDED_VERT) !== false) {
                                                // Формирование факта о кивке головы
                                                $generalBehaviorFeatureFact = new stdClass;
                                                $generalBehaviorFeatureFact -> {'NameOfTemplate'} = 'T2046';
                                                $generalBehaviorFeatureFact -> {'s908'} = 'Кивание головой';
                                                $generalBehaviorFeatureFact -> {'s909'} = (int)$frame + 1;
                                                $generalBehaviorFeatureFact -> {'s913'} = 'Голова';
                                                $eventWithCertainty = explode(':', $event);
                                                if (isset($eventWithCertainty[1]))
                                                    $generalBehaviorFeatureFact -> {'s1028'} = $eventWithCertainty[1];
                                                // Добавление факта о кивке головы в
                                                // массив фактов признаков общего поведения
                                                array_push($generalBehaviorFeatureFacts,
                                                    $generalBehaviorFeatureFact);
                                            }
                                            // Если есть событие мотания головы
                                            if (strripos($event, VideoProcessingModuleSettingForm::HAS_NODDED_HORZ) !== false) {
                                                // Формирование факта о мотании головы
                                                $generalBehaviorFeatureFact = new stdClass;
                                                $generalBehaviorFeatureFact -> {'NameOfTemplate'} = 'T2046';
                                                $generalBehaviorFeatureFact -> {'s908'} = 'Мотание головой';
                                                $generalBehaviorFeatureFact -> {'s909'} = (int)$frame + 1;
                                                $generalBehaviorFeatureFact -> {'s913'} = 'Голова';
                                                $eventWithCertainty = explode(':', $event);
                                                if (isset($eventWithCertainty[1]))
                                                    $generalBehaviorFeatureFact -> {'s1028'} = $eventWithCertainty[1];
                                                // Добавление факта о мотании головы в
                                                // массив фактов признаков общего поведения
                                                array_push($generalBehaviorFeatureFacts,
                                                    $generalBehaviorFeatureFact);
                                            }
                                        }
                                        // Добавление фактов признаков общего поведения в общий набор фактов для текущего кадра
                                        foreach ($generalBehaviorFeatureFacts as $generalBehaviorFeatureFact)
                                            array_push($updateFacts[$frameNumber], $generalBehaviorFeatureFact);
                                    }
                                }
        }

        return $updateFacts;
    }

    /**
     * Получение текста распознанной речи на основе анализа видео ответа на вопрос.
     *
     * @param $id - идентификатор видео ответа на вопрос
     * @return bool|string - текст распознанной речи в вопросе
     */
    public static function getRecognizedSpeechText($id)
    {
        // Поиск видео ответа на вопрос по id
        $question = Question::findOne($id);
        // Если есть файл видео
        if ($question->video_file_name != null) {
            // Путь к программе обработки видео от Ивана
            $mainPath = '/home/-Common/-ivan/';
            // Путь к файлу видео
            $videoPath = $mainPath . 'video/';
            // Путь к json-файлу результатов обработки видео
            $jsonResultPath = $mainPath . 'json/';
            // Создание объекта коннектора с Yandex.Cloud Object Storage
            $osConnector = new OSConnector();
            // Сохранение файла видео ответа на вопрос на сервер
            $osConnector->saveFileToServer(
                OSConnector::OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET,
                $question->id,
                $question->video_file_name,
                $videoPath
            );
            // Название json-файла с результатами обработки видео
            $jsonResultFile = 'out_' . $question->id . '_audio.json';
            // Формирование массива с параметрами запуска программы обработки видео
            $parameters['nameVidFilesIn'] = 'video/' . $question->video_file_name;
            $parameters['nameVidFilesOut'] = 'json/out_{}.avi';
            $parameters['nameJsonFilesOut'] = 'json/out_{}.json';
            $parameters['nameAudioFilesOut'] = 'json/out_{}.mp3';
            $parameters['indexesTriagnleStats'] = [[21, 22, 28], [31, 48, 74], [31, 40, 74], [35, 54, 75],
                [35, 47, 75], [27, 35, 42], [27, 31, 39]];
            $parameters['rotate_mode'] = VideoProcessingModuleSettingForm::ROTATE_MODE_ZERO;
            $parameters['enableAutoRotate'] = VideoProcessingModuleSettingForm::AUTO_ROTATE_TRUE;
            $parameters['Mirroring'] = VideoProcessingModuleSettingForm::MIRRORING_FALSE;
            $parameters['AlignMode'] = VideoProcessingModuleSettingForm::ALIGN_MODE_BY_THREE_FACIAL_POINTS;
            $parameters['id'] = $question->id;
            $parameters['landmark_mode'] = VideoProcessingModuleSettingForm::LANDMARK_MODE_FAST;
            $parameters['parameters'] = VideoProcessingModuleSettingForm::PARAMETER_CHECK_VIDEO_PARAMETERS;
            // Формирование json-строки на основе массива с параметрами запуска программы обработки видео
            $jsonParameters = json_encode($parameters, JSON_UNESCAPED_UNICODE);
            // Открытие файла на запись для сохранения параметров запуска программы обработки видео
            $jsonFile = fopen($mainPath . 'test' . $question->id . '.json', 'a');
            // Запись в файл json-строки с параметрами запуска программы обработки видео
            fwrite($jsonFile, str_replace("\\", "", $jsonParameters));
            // Закрытие файла
            fclose($jsonFile);

            // Сообщение об ошибке формирования текста распознанной речи
            $errorMessage = null;
            // json-текст распознанной речи
            $jsonRecognizedSpeechText = null;

            try {
                // Запуск программы обработки видео Ивана
                chdir($mainPath);
                exec('./venv/bin/python ./main_audio.py ./test' . $question->id . '.json');
            } catch (Exception $e) {
                // Сохранение сообщения об ошибке МОВ Ивана
                $errorMessage = 'Ошибка модуля обработки видео Ивана (скрипт распознования речи)! ' . $e->getMessage();
            }

            // Проверка существования json-файл с результатами обработки видео
            if (file_exists($jsonResultPath . $jsonResultFile)) {
                // Получение json-файла с результатами обработки видео в виде текста распознанной речи
                $jsonRecognizedSpeechFile = file_get_contents($jsonResultPath . $jsonResultFile,
                    true);
                // Декодирование json-файла с результатами обработки видео в виде текста распознанной речи
                $recognizedSpeechFile = json_decode($jsonRecognizedSpeechFile, true);
                // Запоминание массива с распознанным текстом в формате json
                foreach ($recognizedSpeechFile as $key => $value)
                    if ($key == 'TEXT' && $value != null)
                        $jsonRecognizedSpeechText = $value;
            }

            // Удаление файла с видеоинтервью
            if (file_exists($videoPath . $question->video_file_name))
                unlink($videoPath . $question->video_file_name);
            // Удаление файла с параметрами запуска программы обработки видео
            if (file_exists($mainPath . 'test' . $question->id . '.json'))
                unlink($mainPath . 'test' . $question->id . '.json');
            // Удаление json-файла с результатами обработки видео программой Ивана
            if (file_exists($jsonResultPath . $jsonResultFile))
                unlink($jsonResultPath . $jsonResultFile);

            // Если нет ошибок при обработке видео и сформирован текст с распознанной черью
            if ($errorMessage == null && $jsonRecognizedSpeechText != null)
                return $jsonRecognizedSpeechText;
        }

        return false;
    }

    /**
     * Получение базового нулевого кадра (нейтрального состояния лица).
     *
     * @param $videoInterviewId - идентификатор видеоинтервью
     * @param $additionalOptions - дополнительные параметры для запуска МОП
     * @return mixed|null - цифровая маска с определенным базовым кадром
     * @return array - массив с определенным базовым кадром или текстом об ошибке
     */
    public static function getBaseFrame($videoInterviewId, $additionalOptions)
    {
        $resultExist = false;
        // Базовый (нудевой) кадр с нейтральным выражением лица
        $baseFrame = null;
        // Поиск всех вопросов для конкретного видеоинтервью
        $questions = Question::find()->where(['video_interview_id' => $videoInterviewId])->all();
        // Обход по всем найденным видео ответов на вопросы
        foreach ($questions as $question) {
            // Поиск темы для вопроса - 27 (калибровочный для камеры)
            $topicQuestion = TopicQuestion::find()->where(['test_question_id' => $question->test_question_id])->one();
            // Если есть видео ответ на калибровочный вопрос (27 - посмотрите в камеру)
            if (!empty($topicQuestion) && $topicQuestion->topic_id == 27) {
                // Поиск цифровой маски, полученной на основе анализа видео ответа на калибровочный вопрос
                $landmark = Landmark::find()
                    ->where(['question_id' => $question->id, 'type' => Landmark::TYPE_LANDMARK_IVAN_MODULE])
                    ->orderBy('id DESC')
                    ->one();
                // Если цифровая маска существует
                if (!empty($landmark)) {
                    // Переменная для хранения цифровой маски, полученной от МОВ Андрея
                    $andreyFaceData = null;
                    // Переменная для хранения текста распознанной речи
                    $recognizedSpeechText = null;
                    // Создание объекта коннектора с Yandex.Cloud Object Storage
                    $osConnector = new OSConnector();
                    // Получение содержимого json-файла с лицевыми точками из Object Storage
                    $faceData = $osConnector->getFileContentFromObjectStorage(
                        OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                        $landmark->id,
                        $landmark->landmark_file_name
                    );
                    // Если у данной цифровой маски есть вопрос
                    if ($landmark->question_id != null) {
                        // Получение текста распознанной речи на основе анализа видео ответа на вопрос
                        $recognizedSpeechText = self::getRecognizedSpeechText($landmark->question_id);
                        // Запоминание идентификатора вопроса
                        $testQuestionId = $landmark->question->testQuestion->id;
                        // Поиск всех цифровых масок для данного интервью
                        $landmarks = Landmark::find()
                            ->where(['video_interview_id' => $landmark->video_interview_id])
                            ->all();
                        // Обход цифровых масок
                        foreach ($landmarks as $currentLandmark)
                            if (isset($currentLandmark->question_id))
                                if ($currentLandmark->question->testQuestion->id == $testQuestionId)
                                    if ($currentLandmark->type == Landmark::TYPE_LANDMARK_ANDREW_MODULE)
                                        // Получение содержимого json-файла с лицевыми точками,
                                        // полученного МОВ Андрея из Object Storage
                                        $andreyFaceData = $osConnector->getFileContentFromObjectStorage(
                                            OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                                            $currentLandmark->id,
                                            $currentLandmark->landmark_file_name
                                        );
                    }
                    // Формирование дополнительных параметров
                    $options = [
                        'mode' => isset($additionalOptions) ? 2 : 1,
                        'invariantPoint1' => null,
                        'invariantPoint2' => null,
                        'useLength' => null,
                        'invariantLength1Point1' => null,
                        'invariantLength1Point2' => null,
                        'invariantLength2Point1' => null,
                        'invariantLength2Point2' => null,
                        'pointsFlag' => VideoInterview::TYPE_NORMALIZED_POINTS,
                        'voiceActingTime' => $landmark->question->testQuestion->time,
                        'skipIrisDetection' => true
                    ];
                    // Если заданы дополнительные параметры
                    if (isset($additionalOptions)) {
                        // Определение дополнительных параметров
                        $options['mode'] = $additionalOptions['mode'];
                        $options['invariantPoint1'] = $additionalOptions['invariantPoint1'];
                        $options['invariantPoint2'] = $additionalOptions['invariantPoint2'];
                        $options['useLength'] = $additionalOptions['useLength'];
                        $options['invariantLength1Point1'] = $additionalOptions['invariantLength1Point1'];
                        $options['invariantLength1Point2'] = $additionalOptions['invariantLength1Point2'];
                        $options['invariantLength2Point1'] = $additionalOptions['invariantLength2Point1'];
                        $options['invariantLength2Point2'] = $additionalOptions['invariantLength2Point2'];
                        // Создание объекта обнаружения лицевых признаков (экспериментальная версия нового МОП)
                        $facialFeatureDetector = new FacialFeatureDetectorExperiment();
                    } else
                        // Создание объекта обнаружения лицевых признаков (стабильная версия нового МОП)
                        $facialFeatureDetector = new FacialFeatureDetector();
                    // Определение нулевого кадра (нейтрального состояния лица) по новому методу МОП
                    list($resultExist, $baseFrame) = $facialFeatureDetector->makeBasicFrameWithSmoothingAndRotating(
                        $faceData,
                        $andreyFaceData,
                        $options,
                        $recognizedSpeechText
                    );
                    // Если базовый кадр определен
                    if ($resultExist && isset($baseFrame))
                        // Сохранение базового кадра в виде json-файла
                        file_put_contents(Yii::$app->basePath . '/web/base-frame-' .
                            $landmark->video_interview_id . '.json', $baseFrame);
                }
            }
        }

        return array($resultExist, $baseFrame);
    }

    /**
     * Преобразование массива с результатами определения признаков по МОВ Андрея (массив AU и
     * массив направлений взгляда) в массив фактов.
     *
     * @param $faceData - цифровая маска
     * @return array - массив наборов фактов для каждого кадра видео
     */
    public static function convertActionUnitsAndGazesToFacts($faceData)
    {
        $facts = array();
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
                // Анализ направления взгляда
                $targetPropertyName = 'gaze';
                if (property_exists($frameData, $targetPropertyName) === True)
                    foreach ($initialData as $frameIndex => $frameData) {
                        $gazeDirections = $frameData->{$targetPropertyName};
                        // Формирование фактов по направлению взгляда
                        $gazeDirectionsAsFacts = self::convertGazeToFacts($gazeDirections, $frameIndex + 1);
                        if (count($gazeDirectionsAsFacts) > 0)
                            $facts[$frameIndex] = $gazeDirectionsAsFacts;
                    }
                // Анализ Action Units
                $targetPropertyName = 'AUs';
                if (property_exists($frameData, $targetPropertyName) === True)
                    foreach ($initialData as $frameIndex => $frameData) {
                        $actionUnits = $frameData->{$targetPropertyName};
                        // Формирование фактов на основе Action Units
                        $actionUnitsAsFacts = self::convertActionUnitsToFacts($actionUnits, $frameIndex);
                        if (count($actionUnitsAsFacts) > 0)
                            foreach ($actionUnitsAsFacts as $actionUnitsAsFact)
                                array_push($facts[$frameIndex], $actionUnitsAsFact);
                    }
            }
        }

        return $facts;
    }

    /**
     * Создание модели результатов анализа и запуск модуля определения признаков.
     *
     * @param $landmark - цифровая маска
     * @param $landmarkProcessingType - тип обработки получаемых цифровых масок (нормализованные или сырые точки)
     * @param $baseFrame - базовый кадр
     * @param $FDMVersion - версия запускаемого МОП
     * @param $additionalOptions - дополнительные параметры для запуска МОП
     * @return array|int - массив с id результатов анализа или сообщением об ошибке
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function getAnalysisResult($landmark, $landmarkProcessingType, $baseFrame,
                                             $FDMVersion, $additionalOptions)
    {
        // Создание модели для результатов определения признаков
        $analysisResultModel = new AnalysisResult();
        $analysisResultModel->landmark_id = $landmark->id;
        $analysisResultModel->detection_result_file_name = 'feature-detection-result.json';
        $analysisResultModel->facts_file_name = 'facts.json';
        $analysisResultModel->description = $landmark->description . ($landmarkProcessingType == 0 ?
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
        // Переменная для хранения цифровой маски, полученной от МОВ Андрея
        $andreyFaceData = null;
        // Массив фактов
        $facts = array();
        // Переменные для хранения результатов анализа
        $resultExist = false;
        $facialFeatures = null;
        // Если вызывается модуль обработки видео Ивана
        if ($landmark->type == Landmark::TYPE_LANDMARK_IVAN_MODULE) {
            // Если указан режим запуска нового МОП
            if ($FDMVersion == self::NEW_FDM) {
                // Если запускается экспериментальная версия нового МОП
                if ($landmarkProcessingType == 3)
                    // Создание объекта обнаружения лицевых признаков (экспериментальная версия нового МОП)
                    $facialFeatureDetector = new FacialFeatureDetectorExperiment();
                else
                    // Создание объекта обнаружения лицевых признаков (стабильная версия нового МОП)
                    $facialFeatureDetector = new FacialFeatureDetector();
                $landmarkProcessingType = 1;
                // Если у данной цифровой маски есть вопрос
                if ($landmark->question_id != null) {
                    // Запоминание идентификатора вопроса
                    $testQuestionId = $landmark->question->testQuestion->id;
                    // Поиск всех цифровых масок для данного интервью
                    $landmarks = Landmark::find()->where(['video_interview_id' => $landmark->video_interview_id])->all();
                    // Обход цифровых масок
                    foreach ($landmarks as $currentLandmark)
                        if (isset($currentLandmark->question_id))
                            if ($currentLandmark->question->testQuestion->id == $testQuestionId)
                                if ($currentLandmark->type == Landmark::TYPE_LANDMARK_ANDREW_MODULE)
                                    // Получение содержимого json-файла с лицевыми точками,
                                    // полученного МОВ Андрея из Object Storage
                                    $andreyFaceData = $osConnector->getFileContentFromObjectStorage(
                                        OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                                        $currentLandmark->id,
                                        $currentLandmark->landmark_file_name
                                    );
                }
                // Формирование дополнительных параметров
                $options = [
                    'mode' => isset($additionalOptions) ? 2 : 1,
                    'invariantPoint1' => null,
                    'invariantPoint2' => null,
                    'useLength' => null,
                    'invariantLength1Point1' => null,
                    'invariantLength1Point2' => null,
                    'invariantLength2Point1' => null,
                    'invariantLength2Point2' => null,
                    'pointsFlag' => $landmarkProcessingType,
                    'voiceActingTime' => $landmark->question->testQuestion->time,
                    'skipIrisDetection' => true
                ];
                // Если заданы дополнительные параметры
                if (isset($additionalOptions)) {
                    // Определение дополнительных параметров
                    $options['mode'] = $additionalOptions['mode'];
                    $options['invariantPoint1'] = $additionalOptions['invariantPoint1'];
                    $options['invariantPoint2'] = $additionalOptions['invariantPoint2'];
                    $options['useLength'] = $additionalOptions['useLength'];
                    $options['invariantLength1Point1'] = $additionalOptions['invariantLength1Point1'];
                    $options['invariantLength1Point2'] = $additionalOptions['invariantLength1Point2'];
                    $options['invariantLength2Point1'] = $additionalOptions['invariantLength2Point1'];
                    $options['invariantLength2Point2'] = $additionalOptions['invariantLength2Point2'];
                    // Обновление описания для данного результата анализа
                    if ($options['useLength'])
                        $analysisResultModel->description .= ' Запущен экспериментальный МОП с параметрами: ' .
                            '1) Номера первой и второй инвариантной точки: ' . $additionalOptions['invariantPoint1'] .
                            ' и ' . $additionalOptions['invariantPoint2'] . '; Номера точек для расчёта длины справа: ' .
                            $additionalOptions['invariantLength1Point1'] . ' и ' .
                            $additionalOptions['invariantLength1Point2'] . '; Номера точек для расчёта длины слева: ' .
                            $additionalOptions['invariantLength2Point1'] . ' и ' .
                            $additionalOptions['invariantLength2Point2'] . '.';
                    else
                        $analysisResultModel->description .= ' Запущен экспериментальный МОП с параметрами: ' .
                            '1) Номера первой и второй инвариантной точки: ' . $additionalOptions['invariantPoint1'] .
                            ' и ' . $additionalOptions['invariantPoint2'] . '; Расчёт длин не используется.';
                    $analysisResultModel->updateAttributes(['description']);
                }
                // Получение текста распознанной речи на основе анализа видео ответа на вопрос
                $recognizedSpeechText = self::getRecognizedSpeechText($landmark->question_id);
                // Выявление признаков для лица по новому методу МОП
                list($resultExist, $facialFeatures) = $facialFeatureDetector->detectFeaturesV3($faceData, $baseFrame, $andreyFaceData,
                    $options, $recognizedSpeechText);
                // Если сформирован результат МОП и есть цифровая маска от Андрея
                if ($resultExist && isset($facialFeatures) && isset($andreyFaceData))
                    // Обход результатов МОП по цифровой маске, полученной МОВ Ивана
                    foreach ($facialFeatures as $key => $value)
                        if ($key == 'feature_statistics') {
                            // Получение статистики по данным Андрея
                            $updatedFacialFeatures = $facialFeatureDetector->detectStatisticsA(
                                $andreyFaceData,
                                $value
                            );
                            // Если статистика сформирована
                            if (isset($updatedFacialFeatures))
                                // Обновление статистики в результате МОП по данным Ивана
                                $facialFeatures['feature_statistics'] = $updatedFacialFeatures;
                        }
            }
            // Если указан режим запуска старого МОП
            if ($FDMVersion == self::OLD_FDM) {
                // Создание объекта обнаружения лицевых признаков (старая версия МОП)
                $facialFeatureDetector = new FacialFeatureDetector();
                // Определение нулевого кадра (нейтрального состояния лица) по старому методу МОП
                $baseFrame = $facialFeatureDetector->detectFeaturesForBasicFrameDetection(
                    $faceData,
                    $landmarkProcessingType
                );
                // Выявление признаков для лица по старому методу МОП
                $facialFeatures = $facialFeatureDetector->detectFeaturesV2(
                    $faceData,
                    $landmarkProcessingType,
                    $baseFrame
                );
                $resultExist = true;
            }
            // Если сформирован результат МОП
            if ($resultExist && isset($facialFeatures)) {
                // Сохранение json-файла с результатами определения признаков на Object Storage
                $osConnector->saveFileToObjectStorage(
                    OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                    $analysisResultModel->id,
                    $analysisResultModel->detection_result_file_name,
                    $facialFeatures
                );
                // Время на вопрос
                $questionTime = null;
                // Текст вопроса
                $questionText = null;
                // Если к цифровой маски привязан вопрос
                if ($landmark->question_id != null) {
                    // Запоминание времени на вопрос и текста вопроса
                    $questionTime = $landmark->question->testQuestion->time;
                    $questionText = $landmark->question->testQuestion->text;
                }
                // Преобразование массива с результатами определения признаков в массив фактов
                $facts = self::convertFeaturesToFacts(
                    $faceData,
                    $facialFeatures,
                    $questionTime,
                    $questionText
                );
                // Если к цифровой маски привязан вопрос
                if ($landmark->question_id != null) {
                    // Поиск темы вопроса
                    $topicQuestion = TopicQuestion::find()
                        ->where(['test_question_id' => $landmark->question->test_question_id])
                        ->one();
                    // Если тема для вопроса найдена
                    if (!empty($topicQuestion)) {
                        // Поиск цифровых масок полученных модулем Ивана для текущего вопроса видеоинтервью
                        $landmarks = Landmark::find()->where([
                            'question_id' => $landmark->question_id,
                            'video_interview_id' => $landmark->video_interview_id,
                            'type' => Landmark::TYPE_LANDMARK_IVAN_MODULE
                        ])->all();
                        // Если цифровые маски найдены
                        if (!empty($landmarks))
                            foreach ($landmarks as $currentLandmark) {
                                // Получение обновленного набора фактов с фактами событий наклонов, поворотов и кивков головы
                                $results = self::getHeadPositionEventFacts($currentLandmark, $facts);
                                // Обновление общего набора фактов на основе полученного результата конвертации событий
                                if (isset($results))
                                    $facts = $results;
                            }
                    }
                }
                // Если получены результаты обработки МОВ Андрея
                if (isset($andreyFaceData)) {
                    // Преобразование результаов обработки МОВ Андрея в набор фактов
                    $andreyFacts = self::convertActionUnitsAndGazesToFacts($andreyFaceData);
                    // Если наборы фактов, сформированных от МОВ Ивана и Андрея, не пустые
                    if (!empty($facts) && !empty($andreyFacts))
                        // Объединение набора фактов, полученных от МОВ Андрея с набором фактов, полученных от МОВ Ивана
                        foreach ($facts as $iKey => $factsForFrame)
                            foreach ($andreyFacts as $aKey => $andreyFactsForFrame)
                                if ($iKey == $aKey)
                                    foreach ($andreyFactsForFrame as $andreyFact)
                                        array_push($facts[$iKey], $andreyFact);
                }
            }
        }
        // Если в json-файле цифровой маски есть данные по Action Units
        if (strpos($faceData, 'AUs') !== false) {
            // Поиск цифровых масок полученных модулем Ивана для текущего вопроса цифровой маски
            $questionLandmarks = Landmark::find()
                ->where(['question_id' => $landmark->question_id, 'type' => Landmark::TYPE_LANDMARK_IVAN_MODULE])
                ->all();
            // Если цифровые маски найдены
            if (!empty($questionLandmarks)) {
                foreach ($questionLandmarks as $questionLandmark) {
                    // Если цифровая маска полученная не вторым скриптом Ивана
                    if (strripos($questionLandmark->landmark_file_name, '_ext') === false) {
                        // Время на вопрос
                        $questionTime = null;
                        // Если к цифровой маски привязан вопрос, то запоминание времени на вопрос
                        if ($questionLandmark->question_id != null)
                            $questionTime = $questionLandmark->question->testQuestion->time;
                        // Если задано время на вопрос
                        if ($questionTime != null) {
                            // Создание объекта коннектора с Yandex.Cloud Object Storage
                            $osConnector = new OSConnector();
                            // Получение содержимого json-файла с лицевыми точками полученных модулем Ивана из Object Storage
                            $jsonFaceDataOnIvanModule = $osConnector->getFileContentFromObjectStorage(
                                OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                                $questionLandmark->id,
                                $questionLandmark->landmark_file_name
                            );
                            // Декодирование цифровой маски из json-формата
                            $faceDataOnIvanModule = json_decode($jsonFaceDataOnIvanModule, true);
                            $fps = null;
                            // Определение FPS
                            if (isset($faceDataOnIvanModule['fps']))
                                $fps = $faceDataOnIvanModule['fps'];
                            if (isset($faceDataOnIvanModule['FPS']))
                                $fps = $faceDataOnIvanModule['FPS'];
                            // Если существует ключ (индекс) - fps или FPS
                            if (isset($faceDataOnIvanModule['fps']) || isset($faceDataOnIvanModule['FPS'])) {
                                // Определение времени на вопрос в кадрах
                                $questionTimeInFrames = round(((float)$fps * ($questionTime / 1000)), 0);
                                // Формирование факта одного признака для первого кадра
                                $videoParametersFact['NameOfTemplate'] = 'T2110';
                                $videoParametersFact['s922'] = $fps;
                                $videoParametersFact['s924'] = $questionTimeInFrames;
                                // Добавление факта параметра видео для первого кадра в набор фактов
                                $facts[0] = [$videoParametersFact];
                            }
                        }
                    }
                }
            }
            // Формирование json-строки
            $faceData = str_replace('{"AUs"', ',{"AUs"', $faceData);
            $faceData = trim($faceData, ',');
            $faceData = '[' . $faceData . ']';
            // Конвертация данных по Action Units в набор фактов
            $initialData = json_decode($faceData);
            if (count($initialData) > 0) {
                $frameData = $initialData[0];
                // Анализ направления взгляда
                $targetPropertyName = 'gaze';
                if (property_exists($frameData, $targetPropertyName) === True)
                    foreach ($initialData as $frameIndex => $frameData) {
                        $gazeDirections = $frameData->{$targetPropertyName};
                        // Формирование фактов по направлению взгляда
                        $gazeDirectionsAsFacts = self::convertGazeToFacts($gazeDirections, $frameIndex + 1);
                        if (count($gazeDirectionsAsFacts) > 0)
                            if ($frameIndex == 0)
                                foreach ($gazeDirectionsAsFacts as $gazeDirectionsAsFact)
                                    array_push($facts[0], $gazeDirectionsAsFact);
                            else
                                $facts[$frameIndex] = $gazeDirectionsAsFacts;
                    }
                // Анализ Action Units
                $targetPropertyName = 'AUs';
                if (property_exists($frameData, $targetPropertyName) === True)
                    foreach ($initialData as $frameIndex => $frameData) {
                        $actionUnits = $frameData->{$targetPropertyName};
                        // Формирование фактов на основе Action Units
                        $actionUnitsAsFacts = self::convertActionUnitsToFacts($actionUnits, $frameIndex);
                        // Добавление в конец набора фактов факта с информацией по кадру
                        $actionUnitsAsFacts[] = self::createFactWithFrameInformation($frameIndex);
                        // Добавление текущего набора фактов к общему набору фактов
                        if (count($actionUnitsAsFacts) > 0)
                            foreach ($actionUnitsAsFacts as $actionUnitsAsFact)
                                array_push($facts[$frameIndex], $actionUnitsAsFact);
                    }
            }
        }
        // Если получены результаты анализа
        if ($resultExist && isset($facialFeatures)) {
            // Сохранение json-файла с результатами конвертации определенных признаков в набор фактов на Object Storage
            $osConnector->saveFileToObjectStorage(
                OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                $analysisResultModel->id,
                $analysisResultModel->facts_file_name,
                $facts
            );

            return array($resultExist, $analysisResultModel->id);
        } else {
            // Удаление записи о результатах анализа из БД
            $analysisResultModel->delete();

            return array($resultExist, $facialFeatures);
        }
    }

    /**
     * Запуск обработки видеоинтервью (обработка всех не калибровочных вопросов средствами МОВ +
     * определение базового кадра на основе первого калибровочного вопроса).
     *
     * @param $videoInterviewId - идентификатор видеоинтервью
     * @return array - массив из значений состояния процесса анализа видеоинтервью (true или false) и
     * существования калибровочного вопроса (true или false)
     */
    public static function runVideoInterviewProcessing($videoInterviewId)
    {
        // Флаг статуса процесса обработки видеоинтеврью
        $videoInterviewInProgress = true;
        // Флаг существования калибровочного вопроса
        $calibrationQuestionExist = false;
        // Поиск всех видео ответов на вопросы для данного видеоинтервью
        $questions = Question::find()->where(['video_interview_id' => $videoInterviewId])->all();
        // Обход всех видео ответов на вопросы
        foreach ($questions as $question) {
            // Поиск темы для вопроса - 27 (калибровочный для камеры)
            $topicQuestion = TopicQuestion::find()->where(['test_question_id' => $question->test_question_id])->one();
            // Если тема для вопроса найдена
            if (!empty($topicQuestion))
                // Если текущий вопрос является калибровочным
                if ($topicQuestion->topic_id == 27)
                    $calibrationQuestionExist = true;
        }
        // Поиск статуса обработки видеоинтервью по id видеоинтервью
        $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::find()
            ->where(['video_interview_id' => $videoInterviewId])
            ->one();
        // Если данное видеоинтервью не находится в обработке
        if ($videoInterviewProcessingStatus->status != VideoInterviewProcessingStatus::STATUS_IN_PROGRESS) {
            $videoInterviewInProgress = false;
            // Если у данного видеоинтервью есть калибровочный вопрос
            if ($calibrationQuestionExist) {
                // Создание объекта запуска консольной команды
                $consoleRunner = new ConsoleRunner(['file' => '@app/yii']);
                // Выполнение команды определения базового кадра в фоновом режиме
                $consoleRunner->run('video-interview-analysis/start-base-frame-detection ' . $videoInterviewId);
            }
        }

        return array($videoInterviewInProgress, $calibrationQuestionExist);
    }

    /**
     * Запуск обработки калибровочных вопросов для выбранного видеоинтервью.
     *
     * @param $videoInterviewId - идентификатор видеоинтервью
     * @return array|null|bool - результат анализа калибровочных вопросов (успешность формирования цифровых масок,
     * значение поворота головы вправо, значение поворота головы влево, качество видео, значения коэффициентов качества)
     */
    public static function runCalibrationQuestionsProcessing($videoInterviewId)
    {
        // Поиск статуса обработки видеоинтервью по id видеоинтервью
        $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::find()
            ->where(['video_interview_id' => $videoInterviewId])
            ->one();
        // Если данное видеоинтервью не находится в обработке
        if (empty($videoInterviewProcessingStatus) ||
            $videoInterviewProcessingStatus->status != VideoInterviewProcessingStatus::STATUS_IN_PROGRESS) {

            // Создание объекта AnalysisHelper
            $analysisHelper = new AnalysisHelper();
            // Удаление всех цифровых масок и связанных с ними результатов анализа для данного видеоинтервью на Object Storage
            $analysisHelper->deleteLandmarksInObjectStorage($videoInterviewId);
            // Поиск всех цифровых масок принадлежащих данному видеоинтервью
            $landmarks = Landmark::find()->where(['video_interview_id' => $videoInterviewId])->all();
            // Удаление всех цифровых масок из БД
            foreach ($landmarks as $landmark)
                $landmark->delete();
            // Если интервью новое
            if (isset($videoInterviewProcessingStatus) === True) {
              // Поиск статуса обработки видеоинтервью по id видеоинтервью
              $questionProcessingStatuses = QuestionProcessingStatus::find()
                  ->where(['video_interview_processing_status_id' => $videoInterviewProcessingStatus->id])
                  ->all();
              // Удаление всех статусов по вопросам у данного видеоинтервью из БД
              foreach ($questionProcessingStatuses as $questionProcessingStatus)
                  $questionProcessingStatus->delete();
            }
            // Поиск всех видео ответов на вопросы для данного видеоинтервью
            $questions = Question::find()->where(['video_interview_id' => $videoInterviewId])->all();

            $IDsOfProcessingQuestions = array();

            // Если есть видео ответов на вопросы для данного видеоинтервью
            if (!empty($questions)) {
                // Обход всех видео ответов на вопросы для данного видеоинтервью
                foreach ($questions as $question) {
                    // Поиск темы для вопроса - Topic 24 (поворот вправо), 25 (поворот влево), 27 (калибровочный для камеры)
                    $topicQuestion = TopicQuestion::find()
                        ->where(['test_question_id' => $question->test_question_id])
                        ->one();
                    // Если тема для вопроса найдена
                    if (!empty($topicQuestion)) {
                        // Если вопросы калибровочные (темы 24, 25 и 27)
                        if ($topicQuestion->topic_id == 24 || $topicQuestion->topic_id == 25 ||
                            $topicQuestion->topic_id == 27) {

                            // Сохраним ID'ы обрабатываемых вопросов
                            $IDsOfProcessingQuestions[] = $question->id;

                            // Создание цифровой маски в БД
                            $landmarkModel = new Landmark();
                            $landmarkModel->start_time = '00:00:00:000';
                            $landmarkModel->finish_time = '12:00:00:000';
                            $landmarkModel->type = Landmark::TYPE_LANDMARK_IVAN_MODULE;
                            $landmarkModel->rotation = Landmark::TYPE_ZERO;
                            $landmarkModel->mirroring = Landmark::TYPE_MIRRORING_FALSE;
                            $landmarkModel->question_id = $question->id;
                            $landmarkModel->video_interview_id = $question->video_interview_id;
                            $landmarkModel->save();
                            // Создание объекта запуска консольной команды
                            $consoleRunner = new ConsoleRunner(['file' => '@app/yii']);
                            // Выполнение команды анализа видео ответа на калибровочный вопрос в фоновом режиме
                            $consoleRunner->run('video-interview-analysis/preparation ' . $question->id . ' ' .
                                $landmarkModel->id . ' ' . $topicQuestion->topic_id);
                            // Задержка выполнения скрипта в 1 секунду
                            sleep(1);
                        }
                    }
                }

                // Пример сохранения отладочной информации: подключение PHP файла
                require_once('/var/www/hr-robot-default.com/public_html/Common/CommonData.php');
                // Пример сохранения отладочной информации: использование
                \TCommonData::SaveDebugInformation('IDsOfProcessingQuestions',
                    var_export($IDsOfProcessingQuestions, True), False);
                $StatusOfVideoInterview = VideoInterviewProcessingStatus::find()
                  ->where(['video_interview_id' => $videoInterviewId])
                  ->one();
                do {
                    $ProcessingFinished = True;
                    $StatusesOfQuestions = QuestionProcessingStatus::find()
                        ->where(['video_interview_processing_status_id' => $StatusOfVideoInterview -> id])
                        ->orderBy(['question_id' => SORT_ASC])
                        ->all();
                    foreach($StatusesOfQuestions as $StatusOfQuestion) {
                        if (in_array($StatusOfQuestion -> question_id, $IDsOfProcessingQuestions) === False)
                            continue;
                  // \TCommonData::SaveDebugInformation('StatusOfQuestion - '.$StatusOfQuestion -> question_id, 
                  //                                    var_export(array($StatusOfQuestion -> status, ($StatusOfQuestion -> status !== 5), $ProcessingFinished), True), 
                  //                                    False);
                        if ($StatusOfQuestion -> status !== QuestionProcessingStatus::STATUS_COMPLETED) {
                            $ProcessingFinished = False;
                            break;
                        }
                    }
                    sleep(2);
                    if ($ProcessingFinished) {
                        // Обновление атрибутов статуса обработки видеоинтервью в БД
                        $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::findOne($StatusOfVideoInterview->id);
                        $videoInterviewProcessingStatus->status = VideoInterviewProcessingStatus::STATUS_COMPLETED;
                        $videoInterviewProcessingStatus->updateAttributes(['status']);
                    }
                } while ($ProcessingFinished === False);
                \TCommonData::SaveDebugInformation('StatusOfQuestion - end', $_SERVER['REMOTE_ADDR'], False);
                $videoInterviewProcessingStatus = $StatusOfVideoInterview;

//                // Ожидание завершения анализа видео по калибровочным вопросам
//                do {
//                    // Задержка выполнения скрипта в 1 секунду
//                    sleep(1);
//                    // Поиск статуса обработки видеоинтервью по id видеоинтервью
//                    $videoInterviewProcessingStatus = VideoInterviewProcessingStatus::find()
//                        ->where(['video_interview_id' => $videoInterviewId])
//                        ->one();
//                } while ($videoInterviewProcessingStatus->status !== VideoInterviewProcessingStatus::STATUS_COMPLETED);

                // Поиск всех статусов обработки видео на вопрос по id статуса обработки видеоинтервью
                $questionProcessingStatuses = QuestionProcessingStatus::find()
                    ->where(['video_interview_processing_status_id' => $videoInterviewProcessingStatus->id])
                    ->orderBy(['question_id' => SORT_ASC])
                    ->all();
                // Массив сформированных цифровых масок МОВ Ивана
                $formedLandmarks = array();
                // Параметры наличия поворота головы вправо и влево
                $turnRight = null;
                $turnLeft = null;
                // Значение FPS
                $fpsValue = 0;
                // Показатель качества видео
                $qualityVideo = false;
                // Массив с коэффициентами качества видео
                $videoQualityParameters = array();
                // Обход всех статусов обработки видео на вопрос
                foreach ($questionProcessingStatuses as $questionProcessingStatus) {
                    // Поиск видео ответа на вопрос по id
                    $question = Question::findOne($questionProcessingStatus->question_id);
                    // Поиск темы вопроса по id вопроса
                    $topicQuestion = TopicQuestion::find()
                        ->where(['test_question_id' => $question->test_question_id])
                        ->one();
                    // Поиск цифровых масок по определенному вопросу
                    $landmarks = Landmark::find()
                        ->where(['question_id' => $questionProcessingStatus->question_id])
                        ->all();
                    // Переменная существования цифровой маски
                    $landmarkExist = false;
                    // Если вопрос калибровочный (сядьте прямо, посмотрите в камеру)
                    if ($topicQuestion->topic_id == 27)
                        if (!empty($landmarks))
                            foreach ($landmarks as $landmark) {
                                $landmarkExist = true;
                                // Определение качества видео
                                list($fpsValue, $qualityVideo, $videoQualityParameters) = self::determineQuality($landmark);
                            }
                    // Если вопрос калибровочный (поверните голову вправо)
                    if ($topicQuestion->topic_id == 24)
                        if (!empty($landmarks))
                            foreach ($landmarks as $landmark)
                                // Если цифровая маска содержит события и получена вторым скриптом МОВ Ивана
                                if (strripos($landmark->landmark_file_name, '_ext') !== false) {
                                    $landmarkExist = true;
                                    // Определение поворота головы, если калибровочный вопрос с темой 24 (поворот головы вправо)
                                    $turnRight = self::determineTurn($landmark);
                                }
                    // Если вопрос калибровочный (поверните голову влево)
                    if ($topicQuestion->topic_id == 25)
                        if (!empty($landmarks))
                            foreach ($landmarks as $landmark)
                                // Если цифровая маска содержит события и получена вторым скриптом МОВ Ивана
                                if (strripos($landmark->landmark_file_name, '_ext') !== false) {
                                    $landmarkExist = true;
                                    // Определение поворота головы, если калибровочный вопрос с темой 25 (поворот головы влево)
                                    $turnLeft = self::determineTurn($landmark);
                                }
                    // Формирование массива получения цифровых масок
                    array_push($formedLandmarks, [$question->test_question_id, $landmarkExist]);
                }

                return array($formedLandmarks, $turnRight, $turnLeft, $fpsValue, $qualityVideo, $videoQualityParameters);
            }

            return null;
        }

        return false;
    }

    /**
     * Удаление видеоинтервью на облачном хранилище Yandex.Cloud Object Storage.
     *
     * @param $videoInterviewId - идентификатор видеоинтервью
     */
    public static function deleteVideoInterviewInObjectStorage($videoInterviewId)
    {
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Поиск видеоинтервью по id
        $videoInterview = VideoInterview::findOne($videoInterviewId);
        // Если у данного видеоинтервью задан видео-файл
        if ($videoInterview->video_file_name != '')
            // Удаление файла видеоинтервью на Object Storage
            $osConnector->removeFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_VIDEO_BUCKET,
                $videoInterview->id,
                $videoInterview->video_file_name
            );
    }

    /**
     * Удаление всех видео ответов на вопросы для данного видеоинтервью на облачном хранилище Yandex.Cloud Object Storage.
     *
     * @param $videoInterviewId - идентификатор видеоинтервью
     */
    public static function deleteQuestionsInObjectStorage($videoInterviewId)
    {
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Поиск вопросов для данного видеоинтервью
        $questions = Question::find()->where(['video_interview_id' => $videoInterviewId])->all();
        // Обход всех найденных вопросов для данного видеоинтервью
        foreach ($questions as $question) {
            // Если у данного вопроса задан видео-файл
            if ($question->video_file_name != '') {
                // Удаление файла видео с ответом на вопрос на Object Storage
                $osConnector->removeFileFromObjectStorage(
                    OSConnector::OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET,
                    $question->id,
                    $question->video_file_name
                );
            }
        }
    }

    /**
     * Удаление видео ответа на вопрос и всех связанных с ним цифровых масок и их результатов анализа на
     * облачном хранилище Yandex.Cloud Object Storage.
     *
     * @param $question - идентификатор видео ответа на вопрос
     */
    public static function deleteQuestionInObjectStorage($question)
    {
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Поиск цифровых масок для данного видео ответа на вопрос
        $landmarks = Landmark::find()->where(['question_id' => $question->id])->all();
        // Обход всех найденных цифровых масок
        foreach ($landmarks as $landmark) {
            // Удаление всех результатов анализа для данной цифровой маски на Object Storage
            self::deleteAnalysisResultsInObjectStorage($landmark->id);
            // Удаление цифровой маски на Object Storage
            self::deleteLandmarkInObjectStorage($landmark);
        }
        // Если у данного вопроса задан видео-файл
        if ($question->video_file_name != '')
            // Удаление файла видео с ответом на вопрос на Object Storage
            $osConnector->removeFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET,
                $question->id,
                $question->video_file_name
            );
    }

    /**
     * Удаление всех цифровых масок и связанных с ними результатов анализа для данного видеоинтервью на
     * облачном хранилище Yandex.Cloud Object Storage.
     *
     * @param $videoInterviewId - идентификатор видеоинтервью
     */
    public static function deleteLandmarksInObjectStorage($videoInterviewId)
    {
        // Поиск цифровых масок для данного видеоинтервью
        $landmarks = Landmark::find()->where(['video_interview_id' => $videoInterviewId])->all();
        // Обход всех найденных цифровых масок
        foreach ($landmarks as $landmark) {
            // Удаление всех результатов анализа для данной цифровой маски на Object Storage
            self::deleteAnalysisResultsInObjectStorage($landmark->id);
            // Удаление цифровой маски на Object Storage
            self::deleteLandmarkInObjectStorage($landmark);
        }
    }

    /**
     * Удаление цифровой маски на облачном хранилище Yandex.Cloud Object Storage.
     *
     * @param $landmark - цифровая маска
     */
    public static function deleteLandmarkInObjectStorage($landmark)
    {
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Если у данной цифровой маски задан json-файл с лэндмарками
        if ($landmark->landmark_file_name != '')
            // Удаление файла с лицевыми точками на Object Storage
            $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $landmark->id, $landmark->landmark_file_name);
        // Если у данной цифровой маски задан видео-файл с нанесенными лэндмарками
        if ($landmark->processed_video_file_name != '')
            // Удаление файла видео с нанесенной цифровой маской на Object Storage
            $osConnector->removeFileFromObjectStorage(OSConnector::OBJECT_STORAGE_LANDMARK_BUCKET,
                $landmark->id, $landmark->processed_video_file_name);
    }

    /**
     * Удаление всех результатов анализа (определения и интерпретации лицевых признаков) для данной цифровой маски на
     * облачном хранилище Yandex.Cloud Object Storage.
     *
     * @param $landmarkId - идентификатор цифровой маски
     */
    public static function deleteAnalysisResultsInObjectStorage($landmarkId)
    {
        // Поиск результатов анализа, проведенных для данной цифровой маски
        $analysisResults = AnalysisResult::find()->where(['landmark_id' => $landmarkId])->all();
        // Обход всех найденных результатов анализа
        foreach ($analysisResults as $analysisResult)
            self::deleteAnalysisResultInObjectStorage($analysisResult);
    }

    /**
     * Удаление результата анализа (определения и интерпретации лицевых признаков) на облачном хранилище
     * Yandex.Cloud Object Storage.
     *
     * @param $analysisResult - результат анализа цифровой маски
     */
    public static function deleteAnalysisResultInObjectStorage($analysisResult)
    {
        // Создание объекта коннектора с Yandex.Cloud Object Storage
        $osConnector = new OSConnector();
        // Если у данного результата анализа задан json-файл с определенными признаками
        if ($analysisResult->detection_result_file_name != '')
            // Удаление файла с результатами определения признаков и фактами на Object Storage
            $osConnector->removeFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                $analysisResult->id,
                $analysisResult->detection_result_file_name
            );
        // Если у данного результата анализа задан json-файл с набором фактов
        if ($analysisResult->facts_file_name != '')
            // Удаление файла с набором фактов на Object Storage
            $osConnector->removeFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_DETECTION_RESULT_BUCKET,
                $analysisResult->id,
                $analysisResult->facts_file_name
            );
        // Если у данного результата анализа задан json-файл с интерпретируемыми признаками
        if ($analysisResult->interpretation_result_file_name != '')
            // Удаление файла с набором фактов на Object Storage
            $osConnector->removeFileFromObjectStorage(
                OSConnector::OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET,
                $analysisResult->id,
                $analysisResult->interpretation_result_file_name
            );
    }
}