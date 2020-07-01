<?php

namespace app\modules\main\models;

/**
 * This is the model class for table "{{%final_conclusion}}".
 *
 * @property int $id
 * @property string|null $conclusion
 *
 * @property FinalResult $id0
 */
class FinalConclusion extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%final_conclusion}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'default', 'value' => null],
            [['id'], 'integer'],
            [['conclusion'], 'string'],
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
            'conclusion' => 'Заключение',
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
}