<?php

namespace app\modules\main\models;

use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%respondent}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $name
 * @property string $main_respondent_id
 *
 * @property VideoInterview[] $videoInterviews
 * @property MainRespondent $mainRespondent
 */
class Respondent extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%respondent}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'main_respondent_id'], 'required'],
            [['name'], 'string', 'max' => 255],
            ['name', 'unique', 'message' => 'Такое имя (код) респондента уже существует!'],
            [['main_respondent_id'], 'integer'],
            [['main_respondent_id'], 'exist', 'skipOnError' => true, 'targetClass' => MainRespondent::className(),
                'targetAttribute' => ['main_respondent_id' => 'id']],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлен',
            'name' => 'Имя',
            'main_respondent_id' => 'ID респондента',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
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
     * Gets query for [[MainRespondent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMainRespondent()
    {
        return $this->hasOne(MainRespondent::className(), ['id' => 'main_respondent_id']);
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