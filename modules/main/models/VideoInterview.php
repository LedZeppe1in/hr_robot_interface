<?php

namespace app\modules\main\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%video_interview}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $name
 * @property string $video_file
 * @property string $landmark_file
 * @property int $respondent_id
 *
 * @property AddressedInterview[] $addressedInterviews
 * @property AnalysisResult[] $analysisResults
 * @property Respondent $respondent
 */
class VideoInterview extends \yii\db\ActiveRecord
{
    public $videoInterviewFile; // Файл видео-интервью
    public $landmarkFile;       // Файл с лицевыми точками

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%video_interview}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['respondent_id', 'video_file', 'landmark_file'], 'required'],
            [['respondent_id'], 'integer'],
            [['name', 'video_file', 'landmark_file'], 'string', 'max' => 255],
            [['videoInterviewFile'], 'file', 'extensions' => ['avi', 'mp4'], 'checkExtensionByMimeType' => false],
            [['landmarkFile'], 'file', 'extensions' => 'json', 'checkExtensionByMimeType' => false],
            [['respondent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Respondent::className(),
                'targetAttribute' => ['respondent_id' => 'id']],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
            'name' => 'Название',
            'video_file' => 'Файл видеоинтервью',
            'landmark_file' => 'Файл с лицевыми точками',
            'respondent_id' => 'ID респондента',
            'videoInterviewFile' => 'Файл видеоинтервью',
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
     * Gets query for [[AddressedInterviews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAddressedInterviews()
    {
        return $this->hasMany(AddressedInterview::className(), ['video_interview_id' => 'id']);
    }

    /**
     * Gets query for [[AnalysisResults]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAnalysisResults()
    {
        return $this->hasMany(AnalysisResult::className(), ['video_interview_id' => 'id']);
    }

    /**
     * Gets query for [[Respondent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRespondent()
    {
        return $this->hasOne(Respondent::className(), ['id' => 'respondent_id']);
    }
}