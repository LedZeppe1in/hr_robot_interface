<?php

namespace app\modules\main\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%analysis_result}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $detection_result_file_name
 * @property string $facts_file_name
 * @property string $interpretation_result_file_name
 * @property string $description
 * @property int $landmark_id
 *
 * @property Landmark $landmark
 */
class AnalysisResult extends \yii\db\ActiveRecord
{
    public $landmarkName; // Название файла с лицевыми точками

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%analysis_result}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['landmark_id'], 'required'],
            [['landmark_id'], 'integer'],
            [['detection_result_file_name', 'facts_file_name',
                'interpretation_result_file_name', 'description'], 'string'],
            [['landmark_id'], 'exist', 'skipOnError' => true, 'targetClass' => Landmark::className(),
                'targetAttribute' => ['landmark_id' => 'id']],
            [['landmarkName'], 'safe'],
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
            'detection_result_file_name' => 'Название файла результатов определения признаков',
            'facts_file_name' => 'Название файла набора фактов',
            'interpretation_result_file_name' => 'Название файла результатов интерпретации признаков',
            'description' => 'Описание',
            'landmark_id' => 'ID цифровой маски',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Gets query for [[Landmark]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLandmark()
    {
        return $this->hasOne(Landmark::className(), ['id' => 'landmark_id']);
    }
}