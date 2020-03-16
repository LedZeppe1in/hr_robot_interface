<?php

namespace app\modules\main\models;

use Yii;

/**
 * This is the model class for table "{{%video_interview}}".
 *
 * @property int $id
 * @property string $name
 * @property int $respondent_id
 * @property string|null $description
 *
 * @property AddressedInterview[] $addressedInterviews
 * @property AnalysisResult[] $analysisResults
 * @property Respondent $respondent
 */
class VideoInterview extends \yii\db\ActiveRecord
{
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
            [['name', 'respondent_id'], 'required'],
            [['respondent_id'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
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
            'name' => 'Название',
            'respondent_id' => 'ID респондента',
            'description' => 'Описание',
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