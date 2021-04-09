<?php

namespace app\modules\main\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%profile_survey}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property int $survey_id
 * @property int $profile_id
 *
 * @property Profile $profile
 * @property Survey $survey
 */
class ProfileSurvey extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%profile_survey}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['survey_id', 'profile_id'], 'required'],
            [['survey_id', 'profile_id'], 'default', 'value' => null],
            [['survey_id', 'profile_id'], 'integer'],
            [['survey_id', 'profile_id'], 'unique', 'targetAttribute' => ['survey_id', 'profile_id']],
            [['profile_id'], 'exist', 'skipOnError' => true, 'targetClass' => Profile::className(),
                'targetAttribute' => ['profile_id' => 'id']],
            [['survey_id'], 'exist', 'skipOnError' => true, 'targetClass' => Survey::className(),
                'targetAttribute' => ['survey_id' => 'id']],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'survey_id' => 'Survey ID',
            'profile_id' => 'Profile ID',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Gets query for [[Profile]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['id' => 'profile_id']);
    }

    /**
     * Gets query for [[Survey]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSurvey()
    {
        return $this->hasOne(Survey::className(), ['id' => 'survey_id']);
    }
}