<?php

namespace app\modules\main\models;

/**
 * This is the model class for table "{{%gerchikov_test_conclusion}}".
 *
 * @property int $id
 * @property int|null $accept_test
 * @property float|null $accept_level
 * @property int|null $instrumental_motivation
 * @property int|null $professional_motivation
 * @property int|null $patriot_motivation
 * @property int|null $master_motivation
 * @property int|null $avoid_motivation
 * @property string|null $description
 *
 * @property FinalResult $id0
 */
class GerchikovTestConclusion extends \yii\db\ActiveRecord
{
    const TYPE_FAILED_PROFILE  = 0;  // Не прошел по профилю
    const TYPE_PASSED          = 1;  // Прошел
    const TYPE_NOT_ANSWER      = 2;  // Не прошел, так как не ответил на достаточное количество вопросов

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%gerchikov_test_conclusion}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'accept_test', 'instrumental_motivation', 'professional_motivation', 'patriot_motivation',
                'master_motivation', 'avoid_motivation'], 'default', 'value' => null],
            [['id', 'accept_test', 'instrumental_motivation', 'professional_motivation', 'patriot_motivation',
                'master_motivation', 'avoid_motivation'], 'integer'],
            [['accept_level'], 'number'],
            [['description'], 'string'],
            [['id'], 'unique'],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => FinalResult::className(),
                'targetAttribute' => ['id' => 'id']],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accept_test' => 'Решение о принятии',
            'accept_level' => 'Рейтинг',
            'instrumental_motivation' => 'Инструментальная мотивация',
            'professional_motivation' => 'Профессиональная мотивация',
            'patriot_motivation' => 'Патриотическая мотивация',
            'master_motivation' => 'Хозяйская мотивация',
            'avoid_motivation' => 'Избегательная мотивация',
            'description' => 'Описание',
        ];
    }

    /**
     * Gets query for [[Id0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(FinalResult::className(), ['id' => 'id']);
    }

    /**
     * Получение списка значений решений по тесту Герчикова.
     *
     * @return array - массив всех возможных значений решений по тесту Герчикова
     */
    public static function getAcceptTestDecisionValues()
    {
        return [
            self::TYPE_FAILED_PROFILE => 'Тест по профилю не пройден',
            self::TYPE_PASSED => 'Тест пройден успешно',
            self::TYPE_NOT_ANSWER => 'Тест не пройден, так как респондент не ответил на достаточное количество вопросов',
        ];
    }
}