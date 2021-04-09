<?php

namespace app\modules\main\models;

use Yii;

/**
 * This is the model class for table "{{%survey_question}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property int $index
 * @property int $survey_id
 * @property int $test_question_id
 *
 * @property Survey $survey
 * @property TestQuestion $testQuestion
 */
class SurveyQuestion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%survey_question}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'survey_id', 'test_question_id'], 'required'],
            [['created_at', 'updated_at', 'index', 'survey_id', 'test_question_id'], 'default', 'value' => null],
            [['created_at', 'updated_at', 'index', 'survey_id', 'test_question_id'], 'integer'],
            [['survey_id', 'test_question_id'], 'unique', 'targetAttribute' => ['survey_id', 'test_question_id']],
            [['survey_id'], 'exist', 'skipOnError' => true, 'targetClass' => Survey::className(), 'targetAttribute' => ['survey_id' => 'id']],
            [['test_question_id'], 'exist', 'skipOnError' => true, 'targetClass' => TestQuestion::className(), 'targetAttribute' => ['test_question_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'index' => 'Index',
            'survey_id' => 'Survey ID',
            'test_question_id' => 'Test Question ID',
        ];
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

    /**
     * Gets query for [[TestQuestion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestQuestion()
    {
        return $this->hasOne(TestQuestion::className(), ['id' => 'test_question_id']);
    }
}
