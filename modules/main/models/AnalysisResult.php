<?php

namespace app\modules\main\models;

use Yii;

/**
 * This is the model class for table "{{%analysis_result}}".
 *
 * @property int $id
 * @property int $video_interview_id
 * @property string|null $feature_detection_result
 * @property string|null $feature_interpretation_result
 *
 * @property VideoInterview $videoInterview
 */
class AnalysisResult extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%analysis_result}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['video_interview_id'], 'required'],
            [['video_interview_id'], 'integer'],
            [['feature_detection_result', 'feature_interpretation_result'], 'string'],
            [['video_interview_id'], 'exist', 'skipOnError' => true, 'targetClass' => VideoInterview::className(),
                'targetAttribute' => ['video_interview_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'video_interview_id' => 'ID видео-интервью',
            'feature_detection_result' => 'Описание результатов определения признаков',
            'feature_interpretation_result' => 'Описание результатов интерпретации признаков',
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