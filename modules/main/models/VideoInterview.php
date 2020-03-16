<?php

namespace app\modules\main\models;

use Yii;

/**
 * This is the model class for table "{{%video_interview}}".
 *
 * @property int $id
 * @property int $respondent_id
 * @property string $name
 * @property string $video_file
 * @property string $landmark_file
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
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%video_interview}}';
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'respondent_id' => 'ID респондента',
            'name' => 'Название',
            'video_file' => 'Файл видеоинтервью',
            'landmark_file' => 'Файл с лицевыми точками',
            'videoInterviewFile' => 'Файл видеоинтервью',
            'landmarkFile' => 'Файл с лицевыми точками',
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