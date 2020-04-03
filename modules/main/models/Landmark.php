<?php

namespace app\modules\main\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%landmark}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $landmark_file_name
 * @property string $description
 * @property int $video_interview_id
 *
 * @property VideoInterview $videoInterview
 */
class Landmark extends \yii\db\ActiveRecord
{
    public $landmarkFile; // Файл с лицевыми точками

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%landmark}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['video_interview_id'], 'required'],
            [['video_interview_id'], 'integer'],
            [['landmark_file_name', 'description'], 'string',],
            [['landmarkFile'], 'file', 'extensions' => 'json', 'checkExtensionByMimeType' => false],
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
            'landmark_file_name' => 'Название файла с лицевыми точками',
            'description' => 'Описание',
            'video_interview_id' => 'ID видеоинтервью',
            'landmarkFile' => 'Файл с лицевыми точками',
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