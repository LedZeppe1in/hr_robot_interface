<?php

namespace app\modules\main\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%final_result}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string|null $description
 * @property int $video_interview_id
 *
 * @property FinalConclusion $finalConclusion
 * @property VideoInterview $videoInterview
 * @property GerchikovTestConclusion $gerchikovTestConclusion
 */
class FinalResult extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%final_result}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['video_interview_id'], 'required'],
            [['video_interview_id'], 'default', 'value' => null],
            [['video_interview_id'], 'integer'],
            [['description'], 'string'],
            [['video_interview_id'], 'exist', 'skipOnError' => true, 'targetClass' => VideoInterview::className(),
                'targetAttribute' => ['video_interview_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
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
            'description' => 'Описание',
            'video_interview_id' => 'ID видеоинтервью',
        ];
    }

    /**
     * Gets query for [[FinalConclusion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFinalConclusion()
    {
        return $this->hasOne(FinalConclusion::className(), ['id' => 'id']);
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

    /**
     * Gets query for [[GerchikovTestConclusion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGerchikovTestConclusion()
    {
        return $this->hasOne(GerchikovTestConclusion::className(), ['id' => 'id']);
    }
}