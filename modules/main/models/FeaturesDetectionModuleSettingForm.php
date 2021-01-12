<?php

namespace app\modules\main\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * FeaturesDetectionModuleSettingForm - класс формы для указания настроек модуля определения признаков (МОП).
 */
class FeaturesDetectionModuleSettingForm extends Model
{
    // Режим работы МОП
    const MODE_V2 = 0; // МОП второй (старой) версии (Юрин)
    const MODE_V3 = 1; // МОП новой версии (Столбов)
    const MODE_V4 = 2; // МОП новой версии с параметрами запуска (Столбов)

    // Режим выбора инвариантных точек
    const INVARIANT_POINT_FOR_EYES = 0;
    const INVARIANT_POINT_FOR_NOSE = 1;

    // Номера первой и второй инвариантной точки
    const INVARIANT1_POINT1 = 39; // Инвариантная точка по глазам
    const INVARIANT1_POINT2 = 42; // Инвариантная точка по глазам
    const INVARIANT2_POINT1 = 27; // Инвариантная точка по носу
    const INVARIANT2_POINT2 = 27; // Инвариантная точка по носу

    // Номера точек для расчёта длины справа и слева
    const INVARIANT_LENGTH1_POINT1 = 42;
    const INVARIANT_LENGTH1_POINT2 = 45;
    const INVARIANT_LENGTH2_POINT1 = 36;
    const INVARIANT_LENGTH2_POINT2 = 39;

    // Флаг выбора номеров для первой и второй инвариантной точки
    public $invariantPointFlag;
    // Значения инвариантных точек
    public $firstInvariantPoint;
    public $secondInvariantPoint;
    // Значения точек для расчёта длины справа
    public $invariantRightLengthFirstPoint;
    public $invariantRightLengthSecondPoint;
    // Значения точек для расчёта длины слева
    public $invariantLeftLengthFirstPoint;
    public $invariantLeftLengthSecondPoint;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['invariantPointFlag', 'firstInvariantPoint', 'secondInvariantPoint', 'invariantRightLengthFirstPoint',
                'invariantRightLengthSecondPoint', 'invariantLeftLengthFirstPoint', 'invariantLeftLengthSecondPoint'],
                'safe'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'invariantPointFlag' => 'Выбор инвариантных точек для:',
            'firstInvariantPoint' => 'Номер первой инвариантной точки',
            'secondInvariantPoint' => 'Номер второй инвариантной точки',
            'invariantRightLengthFirstPoint' => 'Номер первой точки для расчёта длины справа',
            'invariantRightLengthSecondPoint' => 'Номер второй точки для расчёта длины справа',
            'invariantLeftLengthFirstPoint' => 'Номера первой точки для расчёта длины слева',
            'invariantLeftLengthSecondPoint' => 'Номера второй точки для расчёта длины слева',
        ];
    }

    /**
     * Получение списка значений для инвариантных точек.
     *
     * @return array - массив всех возможных значений инвариантных точек
     */
    public static function getInvariantPoints()
    {
        return [
            self::INVARIANT_POINT_FOR_EYES => 'глаз',
            self::INVARIANT_POINT_FOR_NOSE => 'носа',
        ];
    }

    /**
     * Получение значения инвариантных точек.
     *
     * @return mixed
     */
    public function getRotateMode()
    {
        return ArrayHelper::getValue(self::getInvariantPoints(), $this->invariantPointFlag);
    }
}