<?php

namespace app\modules\main\models;

use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%survey}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $name
 * @property int $maximum_time
 * @property string|null $description
 *
 * @property ProfileSurvey[] $profileSurveys
 * @property Profile[] $profiles
 * @property SurveyQuestion[] $surveyQuestions
 * @property TestQuestion[] $testQuestions
 */
class Survey extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%survey}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'maximum_time'], 'required'],
            [['maximum_time'], 'default', 'value' => null],
            [['maximum_time'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'maximum_time' => 'Maximum Time',
            'description' => 'Description',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Gets query for [[ProfileSurveys]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProfileSurveys()
    {
        return $this->hasMany(ProfileSurvey::className(), ['survey_id' => 'id']);
    }

    /**
     * Gets query for [[Profiles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProfiles()
    {
        return $this->hasMany(Profile::className(), ['id' => 'profile_id'])->viaTable('{{%profile_survey}}',
            ['survey_id' => 'id']);
    }

    /**
     * Gets query for [[SurveyQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSurveyQuestions()
    {
        return $this->hasMany(SurveyQuestion::className(), ['survey_id' => 'id']);
    }

    /**
     * Gets query for [[TestQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestQuestions()
    {
        return $this->hasMany(TestQuestion::className(), ['id' => 'test_question_id'])
            ->viaTable('{{%survey_question}}', ['survey_id' => 'id']);
    }

    /**
     * Получение списка опросов.
     *
     * @return array - массив всех опросов
     */
    public static function getSurveys()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }
}