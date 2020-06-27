<?php

namespace app\modules\main\models;

use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%test_question}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string|null $name
 * @property string $text
 * @property int $type
 * @property int $maximum_time
 * @property string|null $description
 *
 * @property Answer[] $answers
 * @property CompoundQuestionRelation[] $compoundQuestionRelations
 * @property CompoundQuestionRelation[] $compoundQuestionRelations0
 * @property Question[] $questions
 * @property SurveyQuestion[] $surveyQuestions
 * @property Survey[] $surveys
 * @property TestQuestionRelationToEvaluatedQuestion[] $testQuestionRelationToEvaluatedQuestions
 * @property EvaluatedQuestion[] $evaluatedQuestions
 * @property TopicQuestion[] $topicQuestions
 * @property Topic[] $topics
 */
class TestQuestion extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%test_question}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['text', 'maximum_time'], 'required'],
            [['type', 'maximum_time'], 'default', 'value' => null],
            [['type', 'maximum_time'], 'integer'],
            [['text', 'description'], 'string'],
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
            'text' => 'Text',
            'type' => 'Type',
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
     * Gets query for [[Answers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAnswers()
    {
        return $this->hasMany(Answer::className(), ['test_question_id' => 'id']);
    }

    /**
     * Gets query for [[CompoundQuestionRelations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompoundQuestionRelations()
    {
        return $this->hasMany(CompoundQuestionRelation::className(), ['parent_test_question_id' => 'id']);
    }

    /**
     * Gets query for [[CompoundQuestionRelations0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompoundQuestionRelations0()
    {
        return $this->hasMany(CompoundQuestionRelation::className(), ['child_test_question_id' => 'id']);
    }

    /**
     * Gets query for [[Questions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestions()
    {
        return $this->hasMany(Question::className(), ['test_question_id' => 'id']);
    }

    /**
     * Gets query for [[SurveyQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSurveyQuestions()
    {
        return $this->hasMany(SurveyQuestion::className(), ['test_question_id' => 'id']);
    }

    /**
     * Gets query for [[Surveys]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSurveys()
    {
        return $this->hasMany(Survey::className(), ['id' => 'survey_id'])->viaTable('{{%survey_question}}',
            ['test_question_id' => 'id']);
    }

    /**
     * Gets query for [[TestQuestionRelationToEvaluatedQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestQuestionRelationToEvaluatedQuestions()
    {
        return $this->hasMany(TestQuestionRelationToEvaluatedQuestion::className(), ['test_question_id' => 'id']);
    }

    /**
     * Gets query for [[EvaluatedQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEvaluatedQuestions()
    {
        return $this->hasMany(EvaluatedQuestion::className(), ['id' => 'evaluated_question_id'])
            ->viaTable('{{%test_question_relation_to_evaluated_question}}', ['test_question_id' => 'id']);
    }

    /**
     * Gets query for [[TopicQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTopicQuestions()
    {
        return $this->hasMany(TopicQuestion::className(), ['test_question_id' => 'id']);
    }

    /**
     * Gets query for [[Topics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTopics()
    {
        return $this->hasMany(Topic::className(), ['id' => 'topic_id'])->viaTable('{{%topic_question}}',
            ['test_question_id' => 'id']);
    }

    /**
     * Получение списка вопросов опросов.
     *
     * @return array - массив всех вопросов опросов
     */
    public static function getTestQuestions()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'text');
    }
}
