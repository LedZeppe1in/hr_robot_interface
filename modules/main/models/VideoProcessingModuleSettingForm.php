<?php

namespace app\modules\main\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * VideoProcessingModuleSettingForm - класс формы для определения настроек модуля обработки видео Ивана и Андрея.
 */
class VideoProcessingModuleSettingForm extends Model
{
    // Режим поворота изображения
    const ROTATE_MODE_ZERO                    = 0; // Поворот на 0 градусов
    const ROTATE_MODE_NINETY                  = 1; // Поворот на 90 градусов
    const ROTATE_MODE_ONE_HUNDRED_EIGHTY      = 2; // Поворот на 180 градусов
    const ROTATE_MODE_TWO_HUNDRED_AND_SEVENTY = 3; // Поворот на 270 градусов

    // Режим автоматического покадрового разворота головы
    const AUTO_ROTATE_TRUE  = true;  // Включение режима
    const AUTO_ROTATE_FALSE = false; // Выключение режима

    // Режим зеркального отображения
    const MIRRORING_FALSE = false; // Отзеркаливания нет
    const MIRRORING_TRUE  = true;  // Отзеркаливание есть

    // Режим выравнивания изображения
    const ALIGN_MODE_BY_THREE_FACIAL_POINTS = 0; // По трем точкам лица [39, 42, 33]
    const ALIGN_MODE_BY_FOUR_FACIAL_POINTS  = 1; // По четырем точкам лица [39, 42, 11, 5]

    // Режим построения лэндмарков
    const LANDMARK_MODE_FAST        = 0; // Быстрый 2D-режим
    const LANDMARK_MODE_FIRST_SLOW  = 1; // Медленный 2D-режим
    const LANDMARK_MODE_SECOND_SLOW = 2; // Медленный 3D-режим

    // Параметр работы основного модуля обработки видео
    const PARAMETER_NONE                   = 'None';                   // По-умолчанию (определение всех параметров видео и поиск лэндмарков)
    const PARAMETER_CHECK_ALL_VIDEO_DATA   = 'CheckAllDataOfVideo';    // Определение всех параметров видео и поиск лэндмарков
    const PARAMETER_CHECK_VIDEO_DATA       = 'CheckDataOfVideo';       // Поиск лэндмарков (если FPS задана, то не определять его)
    const PARAMETER_CHECK_VIDEO_PARAMETERS = 'CheckParametersOfVideo'; // Определение параметров видео без поиска лэндмарков

    // Флаг запуска второго скрипта МОВ Ивана
    const ENABLE_SECOND_SCRIPT_FALSE = false; // Не запускать второй скрипт МОВ Ивана
    const ENABLE_SECOND_SCRIPT_TRUE  = true;  // Запустить второй скрипт МОВ Ивана

    const TILT_LEFT_EVENT       = 'TiltLeft';      // Наклон влево
    const TILT_RIGHT_EVENT      = 'TiltRight';     // Наклон вправо
    const TURN_LEFT_EVENT       = 'TurnLeft';      // Поворот влево
    const TURN_RIGHT_EVENT      = 'TurnRight';     // Поворот вправо
    const CLOSE_LEFT_EYE_EVENT  = 'CloseLeftEye';  // Левый глаз закрыт
    const CLOSE_RIGHT_EYE_EVENT = 'CloseRightEye'; // Правый глаз закрыт
    const GAZE_LEFT_EVENT       = 'GazeLeft';      // Взгляд слева
    const GAZE_RIGHT_EVENT      = 'GazeRight';     // Взгляд справа
    const GAZE_TOP_EVENT        = 'GazeTop';       // Взгляд сверху
    const GAZE_BOTTOM_EVENT     = 'GazeBottom';    // Взгляд снизу
    const OPEN_MOUTH_EVENT      = 'OpenMouth';     // Рот открыт
    const HEAD_NOD_DOWN_EVENT   = 'HeadNodDown';   // Наклон назад
    const HEAD_NOD_UP_EVENT     = 'HeadNodUp';     // Наклон вперед

    public $rotateMode;               // Режим поворота изображения
    public $enableAutoRotate;         // Режим автоматического покадрового разворота головы
    public $mirroring;                // Режим зеркального отображения
    public $alignMode;                // Режим выравнивания изображения
    public $landmarkMode;             // Режим построения лэндмарков
    public $videoProcessingParameter; // Параметр работы основного модуля обработки видео
    public $enableSecondScript;       // Флаг запуска второго скрипта МОВ Ивана

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['rotateMode', 'enableAutoRotate', 'mirroring', 'alignMode', 'landmarkMode',
                'videoProcessingParameter', 'enableSecondScript'], 'safe'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'rotateMode' => 'Режим поворота изображения (градусы)',
            'enableAutoRotate' => 'Режим автоматического покадрового разворота головы',
            'mirroring' => 'Режим зеркального отображения',
            'alignMode' => 'Режим выравнивания изображения',
            'landmarkMode' => 'Режим построения лэндмарков',
            'videoProcessingParameter' => 'Параметр обработки видео',
            'enableSecondScript' => 'Запуск второго скрипта МОВ Ивана',
        ];
    }

    /**
     * Получение списка значений для режимов поворота изображения.
     *
     * @return array - массив всех возможных значений режимов поворота изображения
     */
    public static function getRotateModes()
    {
        return [
            self::ROTATE_MODE_ZERO => 0,
            self::ROTATE_MODE_NINETY => 90,
            self::ROTATE_MODE_ONE_HUNDRED_EIGHTY => 180,
            self::ROTATE_MODE_TWO_HUNDRED_AND_SEVENTY => 270,
        ];
    }

    /**
     * Получение значения режима поворота изображения.
     *
     * @return mixed
     */
    public function getRotateMode()
    {
        return ArrayHelper::getValue(self::getRotateModes(), $this->rotateMode);
    }

    /**
     * Получение списка значений для режимов автоматического покадрового разворота головы.
     *
     * @return array - массив всех возможных значений режимов автоматического покадрового разворота головы
     */
    public static function getAutoRotates()
    {
        return [
            self::AUTO_ROTATE_TRUE => 'Включить',
            self::AUTO_ROTATE_FALSE => 'Выключить',
        ];
    }

    /**
     * Получение значения режима автоматического покадрового разворота головы.
     *
     * @return mixed
     */
    public function getAutoRotate()
    {
        return ArrayHelper::getValue(self::getAutoRotates(), $this->enableAutoRotate);
    }

    /**
     * Получение списка значений для режимов зеркального отображения.
     *
     * @return array - массив всех возможных значений режимов зеркального отображения
     */
    public static function getMirroringModes()
    {
        return [
            self::MIRRORING_FALSE => 'Нет',
            self::MIRRORING_TRUE => 'Есть',
        ];
    }

    /**
     * Получение значения режима зеркального отображения.
     *
     * @return mixed
     */
    public function getMirroring()
    {
        return ArrayHelper::getValue(self::getMirroringModes(), $this->mirroring);
    }

    /**
     * Получение списка значений для режимов выравнивания изображения.
     *
     * @return array - массив всех возможных значений режимов выравнивания изображения
     */
    public static function getAlignModes()
    {
        return [
            self::ALIGN_MODE_BY_THREE_FACIAL_POINTS => 'По трем точкам лица [39, 42, 33]',
            self::ALIGN_MODE_BY_FOUR_FACIAL_POINTS => 'По четырем точкам лица [39, 42, 11, 5]',
        ];
    }

    /**
     * Получение значения режима выравнивания изображения.
     *
     * @return mixed
     */
    public function getAlignMode()
    {
        return ArrayHelper::getValue(self::getAlignModes(), $this->alignMode);
    }

    /**
     * Получение списка значений для режимов построения лэндмарков.
     *
     * @return array - массив всех возможных значений режимов построения лэндмарков
     */
    public static function getLandmarkModes()
    {
        return [
            self::LANDMARK_MODE_FAST => 'Быстрый 2D-режим',
            self::LANDMARK_MODE_FIRST_SLOW => 'Медленный 2D-режим',
            self::LANDMARK_MODE_SECOND_SLOW => 'Медленный 3D-режим',
        ];
    }

    /**
     * Получение значения режима построения лэндмарков.
     *
     * @return mixed
     */
    public function getLandmarkMode()
    {
        return ArrayHelper::getValue(self::getLandmarkModes(), $this->landmarkMode);
    }

    /**
     * Получение списка значений для параметров обработки видео.
     *
     * @return array - массив всех возможных значений параметров обработки видео
     */
    public static function getParameterValues()
    {
        return [
            self::PARAMETER_NONE => 'По-умолчанию (определение всех параметров видео и поиск лэндмарков)',
            self::PARAMETER_CHECK_ALL_VIDEO_DATA => 'Определение всех параметров видео и поиск лэндмарков',
            self::PARAMETER_CHECK_VIDEO_DATA => 'Поиск лэндмарков (если FPS задана, то не определять его)',
            self::PARAMETER_CHECK_VIDEO_PARAMETERS => 'Определение параметров видео без поиска лэндмарков',
        ];
    }

    /**
     * Получение значения параметра обработки видео.
     *
     * @return mixed
     */
    public function getParameterValue()
    {
        return ArrayHelper::getValue(self::getParameterValues(), $this->videoProcessingParameter);
    }

    /**
     * Получение списка значений для флага запуска второго скрипта МОВ Ивана.
     *
     * @return array - массив всех возможных значений флагов запуска второго скрипта МОВ Ивана
     */
    public static function getSecondScriptFlags()
    {
        return [
            self::ENABLE_SECOND_SCRIPT_FALSE => 'Нет',
            self::ENABLE_SECOND_SCRIPT_TRUE => 'Да',
        ];
    }

    /**
     * Получение значения флага запуска второго скрипта МОВ Ивана.
     *
     * @return mixed
     */
    public function getSecondScriptFlag()
    {
        return ArrayHelper::getValue(self::getSecondScriptFlags(), $this->enableSecondScript);
    }
}