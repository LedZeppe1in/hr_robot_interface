<?php

namespace app\modules\main\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%main_respondent}}".
 *
 * @property int $id
 * @property string $code
 *
 * @property Respondent[] $respondents
 * @property SnaResult[] $snaResults
 */
class MainRespondent extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%main_respondent}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['code'], 'required'],
            [['code'], 'string'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
        ];
    }

    /**
     * Gets query for [[Respondents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRespondents()
    {
        return $this->hasMany(Respondent::className(), ['main_respondent_id' => 'id']);
    }

    /**
     * Gets query for [[SnaResults]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSnaResults()
    {
        return $this->hasMany(SnaResult::className(), ['main_respondent_id' => 'id']);
    }

    /**
     * Получение списка кодов респондентов.
     *
     * @return array - массив всех кодов респондентов
     */
    public static function getMainRespondents()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'code');
    }
}
