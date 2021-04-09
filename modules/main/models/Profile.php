<?php

namespace app\modules\main\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%profile}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $name
 * @property string|null $description
 *
 * @property ProfileSurvey[] $profileSurveys
 * @property Survey[] $surveys
 * @property ProfileKnowledgeBase[] $profileKnowledgeBases
 */
class Profile extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%profile}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
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
        return $this->hasMany(ProfileSurvey::className(), ['profile_id' => 'id']);
    }

    /**
     * Gets query for [[Surveys]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSurveys()
    {
        return $this->hasMany(Survey::className(), ['id' => 'survey_id'])->viaTable('{{%profile_survey}}',
            ['profile_id' => 'id']);
    }

    /**
     * Gets query for [[ProfileKnowledgeBase]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProfileKnowledgeBases()
    {
        return $this->hasMany(ProfileKnowledgeBase::className(), ['profile_id' => 'id']);
    }
}