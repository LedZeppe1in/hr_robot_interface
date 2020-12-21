<?php

namespace app\components;

use stdClass;
use app\modules\main\models\Landmark;
use app\modules\main\models\Question;
use app\modules\main\models\AnalysisResult;
use app\modules\main\models\VideoInterview;
use app\modules\main\models\VideoProcessingModuleSettingForm;

/**
 * AnalysisHelper - класс с общими функциями анализа видео-интервью.
 */
class AnalysisHelper
{
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
     * @return stdClass
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
     * Преобразование массива с результатами определения признаков в массив фактов.
     *
     * @param $faceData - цифровая маска
     * @param $detectedFeatures - массив обнаруженных признаков
     * @param $questionTime - время на вопрос в миллисекундах
     * @return array - массив наборов фактов для кадого кадра видеоинтервью
     */
    public static function convertFeaturesToFacts($faceData, $detectedFeatures, $questionTime)
    {
        // Массив для наборов фактов, сформированных для каждого кадра
        $facts = array();
        // Время на вопрос в кадрах
        $questionTimeInFrames = 0;
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
                // Если существует ключ (индекс) - FPS
                if (isset($faceData['fps'])) {
                    // Определение времени на вопрос в кадрах
                    $questionTimeInFrames = round(((float)$faceData['fps'] * ($questionTime / 1000)), 0);
                    // Формирование факта одного признака для первого кадра
                    $videoParametersFact['NameOfTemplate'] = 'T2110';
                    $videoParametersFact['s922'] = $faceData['fps'];
                    $videoParametersFact['s924'] = $questionTimeInFrames;
                    // Добавление факта параметра видео для первого кадра в набор фактов
                    array_push($frameFacts, $videoParametersFact);
                }
            }
            if ($i <= $questionTimeInFrames) {
                // Формирование факта признака общего поведения (слушание) для текущего кадра
                $generalBehaviorFeatureFact = array();
                $generalBehaviorFeatureFact['NameOfTemplate'] = 'T2046';
                $generalBehaviorFeatureFact['s908'] = 'Слушание';
                $generalBehaviorFeatureFact['s909'] = $i;
                $generalBehaviorFeatureFact['s910'] = $i; //$frameNumber;
                $generalBehaviorFeatureFact['s911'] = $i;
                // Добавление факта одного признака общего поведения (слушание) для текущего кадра в набор фактов
                array_push($frameFacts, $generalBehaviorFeatureFact);
            }
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
     * @return array - массив факта
     */
    public static function convertActionUnitsToFacts(stdClass $actionUnits, $frameIndex)
    {
        $replacementTable = array_combine(json_decode('["AU00","AU01","AU02","AU04","AU05","AU06","AU07","AU08","AU09","AU10","AU11","AU12","AU13","AU14","AU15","AU16","AU17","AU18","AU19","AU20","AU21","AU22","AU23","AU24","AU25","AU26","AU27","AU28","AU29","AU30","AU31","AU32","AU33","AU34","AU35","AU36","AU37","AU38","AU39","AU41","AU42","AU43","AU44","AU45","AU46","AU51","AU52","AU53","AU54","AU55","AU","AU56","AU","AU57","AU","AU58","AU","AU","AU","AU61","AU","AU62","AU","AU63","AU64","AU65","AU66","AU","AU69","AU","AU70","AU71","AU72","AU73","AU74","AU40","AU50","AU80","AU81","AU82","AU84","AU85"]'),
            json_decode('["AU0 - Нейтральное лицо","AU1 - Подниматель внутренней части брови","AU2 - Подниматель внешней части брови","AU4 - Опускатель брови","AU5 - Подниматель верхнего века","AU6 - Подниматель щеки","AU7 - Натягиватель века","AU8 - Губы навстречу друг другу","AU9 - Сморщиватель носа","AU10 - Подниматель верхней губы","AU11 - Углубитель носогубной складки","AU12 - Подниматель уголка губы","AU13 - Острый подниматель уголка губы","AU14 - Ямочка","AU15 - Опускатель уголка губы","AU16 - Опускатель нижней губы","AU17 - Подниматель подбородка","AU18 - Сморщиватель губ","AU19 - Показ языка","AU20 - Растягиватель губ","AU21 - Натягиватель шеи","AU22 - Губы воронкой","AU23 - Натягиватель губ","AU24 - Сжиматель губ","AU25 - Губы разведены","AU26 - Челюсть опущена","AU27 - Рот широко открыт","AU28 - Втягивание губ","AU29 - Нижняя челюсть вперёд","AU30 - Челюсть в бок","AU31 - Сжиматель челюстей","AU32 - Покусывание губы","AU33 - Выдувание","AU34 - Раздувание щёк","AU35 - Втягивание щёк","AU36 - Язык высунут","AU37 - Облизывание губ","AU38 - Расширитель ноздрей","AU39 - Суживатель ноздрей","AU41 - Опускатель надпереносья","AU42 - Опускатель внутренней части брови","AU43 - Глаза закрыты","AU44 - Сведение бровей","AU45 - Моргание","AU46 - Подмигивание","AU51 - Поворот головы влево","AU52 - Поворот головы вправо","AU53 - Голова вверх","AU54 - Голова вниз","AU55 - Наклон головы влево","AU M55 - Наклон головы влево","AU56 - Наклон головы вправо","AU M56 - Наклон головы вправо","AU57 - Голова вперёд","AU M57 - Толчок головы вперёд","AU58 - Голова назад","AU M59 - Кивок головой","AU M60 - Голова из стороны в сторону","AU M83 - Голова вверх и в сторону","AU61 - Отведение глаз влево","AU M61 - Глаза влево","AU62 - Отведение глаз вправо","AU M62 - Глаза вправо","AU63 - Глаза вверх","AU64 - Глаза вниз","AU65 - Расходящееся косоглазие","AU66 - Сходящееся косоглазие","AU M68 - Закатывание глаз","AU69 - Глаза на другом человеке","AU M69 - Голова и/или глаза на другом человеке","AU70 - Брови и лоб не видны","AU71 - Глаза не видны","AU72 - Нижняя часть лица не видна","AU73 - Всё лицо не видно","AU74 - Оценивание невозможно","AU40 - Втягивание носом","AU50 - Речь","AU80 - Глотание","AU81 - Жевание","AU82 - Пожатие плечом","AU84 - Движение головой назад и вперёд","AU85 - Кивок головой вверх и вниз"]'));
        $result = array();
        foreach ($actionUnits as $name => $actionUnit) {
            if ($actionUnit -> presence === 1) {
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
        // Добавление в конец набора фактов факта с информацией по кадру
        $result[] = self::createFactWithFrameInformation($frameIndex);

        return $result;
    }

    /**
     * Определение поворота головы на основе анализа событий.
     *
     * @param $landmark - цифровая маска
     * @return bool|int
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
            // Получение содержимого json-файла с лицевыми точками из Object Storage
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

        return false;
    }

    /**
     * Получение базового нулевого кадра (нейтрального состояния лица).
     *
     * @param $videoInterviewId - идентификатор видеоинтервью
     * @return mixed|string|null
     */
    public static function getBaseFrame($videoInterviewId)
    {
        // Поиск всех вопросов для конкретного видеоинтервью
        $questions = Question::find()->where(['video_interview_id' => $videoInterviewId])->all();
        // Обход по всем найденным видео ответов на вопросы
        foreach ($questions as $question) {
            // Если есть видео ответ на калибровочный вопрос (27 - посмотрите в камеру)
            if ($question->test_question_id == 27) {
                // Поиск цифровой маски, полученной на основе анализа видео ответа на калибровочный вопрос
                $landmark = Landmark::find()->where(['question_id' => $question->id])->one();
                // Если цифровая маска существует
                if (!empty($landmark)) {
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
                    $basicFrame = $facialFeatureDetector->makeBasicFrameWithSmoothingAndRotating(
                        $faceData,
                        VideoInterview::TYPE_NORMALIZED_POINTS
                    );

                    return $basicFrame;
                }
            }
        }

        return '';
    }

    /**
     * Создание модели результатов анализа и запуск модуля определения признаков.
     *
     * @param $landmark - цифровая маска
     * @param $processingType - тип обработки получаемых цифровых масок (нормализованные или сырые точки)
     * @param $basicFrame - базовый кадр
     * @return int - id результатов анализа
     */
    public static function getAnalysisResult($landmark, $processingType, $basicFrame)
    {
        // Создание модели для результатов определения признаков
        $analysisResultModel = new AnalysisResult();
        $analysisResultModel->landmark_id = $landmark->id;
        $analysisResultModel->detection_result_file_name = 'feature-detection-result.json';
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
            // Если явно указан режим запуска нового МОП
            if ($processingType == 2) {
                $processingType = 1;
                // Определение нулевого кадра (нейтрального состояния лица) по новому методу МОП
                $basicFrame = $facialFeatureDetector->makeBasicFrameWithSmoothingAndRotating(
                    $faceData,
                    $processingType
                );
            }
            // Если базовый кадр не определен
            if ($basicFrame == '')
                // Определение нулевого кадра (нейтрального состояния лица) по старому методу МОП
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
            $facts = self::convertFeaturesToFacts(
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
                        $actionUnitsAsFacts = self::convertActionUnitsToFacts($actionUnits,
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
//                        $actionUnitsAsFacts = self::convertActionUnitsToFacts($actionUnits,
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