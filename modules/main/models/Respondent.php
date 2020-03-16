<?php

namespace app\modules\main\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%respondent}}".
 *
 * @property int $id
 * @property string $name
 *
 * @property VideoInterview[] $videoInterviews
 */
class Respondent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%respondent}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Имя',
        ];
    }

    /**
     * Gets query for [[VideoInterviews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVideoInterviews()
    {
        return $this->hasMany(VideoInterview::className(), ['respondent_id' => 'id']);
    }

    /**
     * Получение списка респондентов.
     * @return array - массив всех респондентов
     */
    public static function getRespondents()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }
}