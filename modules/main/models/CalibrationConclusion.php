<?php

namespace app\modules\main\models;

/**
 * This is the model class for table "{{%calibration_conclusion}}".
 *
 * @property int $id
 * @property string $text
 *
 * @property FinalResult $finalResult
 */
class CalibrationConclusion extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%calibration_conclusion}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['id', 'text'], 'required'],
            [['id'], 'default', 'value' => null],
            [['id'], 'integer'],
            [['text'], 'string'],
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
            'text' => 'Текст',
        ];
    }

    /**
     * Gets query for [[FinalResult]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFinalResult()
    {
        return $this->hasOne(FinalResult::className(), ['id' => 'id']);
    }
}