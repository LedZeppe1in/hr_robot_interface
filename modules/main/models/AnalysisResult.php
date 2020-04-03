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
 * @property int $video_interview_id
 *
 * @property VideoInterview $videoInterview
 */
class AnalysisResult extends \yii\db\ActiveRecord
{
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
            [['video_interview_id'], 'required'],
            [['video_interview_id'], 'integer'],
            [['detection_result_file_name', 'facts_file_name', 'interpretation_result_file_name'], 'string'],
            [['description'], 'string', 'max' => 600],
            [['video_interview_id'], 'exist', 'skipOnError' => true, 'targetClass' => VideoInterview::className(),
                'targetAttribute' => ['video_interview_id' => 'id']],
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
            'video_interview_id' => 'ID видеоинтервью',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Gets query for [[VideoInterview]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVideoInterview()
    {
        return $this->hasOne(VideoInterview::className(), ['id' => 'video_interview_id']);
    }
}