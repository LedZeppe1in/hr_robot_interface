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
 * @property int $time
 * @property string $audio_file_name
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
    const CREATE_QUESTION_SCENARIO   = 'create-question'; // Сценарий создания нового вопроса

    const TYPE_SIMPLE_QUESTION       = 0; // Простой вопрос
    const TYPE_COMPOUND_QUESTION     = 1; // Составной вопрос
    const TYPE_CALIBRATION_QUESTION  = 2; // Калибровочный вопрос
    const TYPE_NOT_QUESTION          = 3; // Не вопрос

    public $audioFile; // Файл с аудио-дорожкой озвучки вопроса

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
            [['audioFile'], 'required', 'on' => self::CREATE_QUESTION_SCENARIO],
            [['text', 'maximum_time', 'time'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['text', 'audio_file_name', 'description'], 'string'],
            [['type'], 'integer'],
            [['maximum_time', 'time'], 'safe'],
            [['audioFile'], 'file', 'extensions' => ['mp3', 'wav'], 'checkExtensionByMimeType' => false],
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
            'name' => 'Название',
            'text' => 'Текст',
            'type' => 'Тип',
            'maximum_time' => 'Максимальное время на ответ',
            'time' => 'Время вопроса',
            'audio_file_name' => 'Название файла с озвучкой вопроса',
            'description' => 'Описание',
            'audioFile' => 'Файл озвучки вопроса',
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
     * Формирование миллисекунд для времени вопроса опроса и максимального времени на ответ.
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Получение миллисекунд для максимального времени на ответ
            $time = explode(":", $this->maximum_time);
            $hour = $time[0] * 60 * 60 * 1000;
            $minute = $time[1] * 60 * 1000;
            $second = $time[2] * 1000;
            $millisecond = $time[3];
            $this->maximum_time = $hour + $minute + $second + $millisecond;
            // Получение миллисекунд для времени вопроса опроса
            $time = explode(":", $this->time);
            $hour = $time[0] * 60 * 60 * 1000;
            $minute = $time[1] * 60 * 1000;
            $second = $time[2] * 1000;
            $millisecond = $time[3];
            $this->time = $hour + $minute + $second + $millisecond;

            return parent::beforeSave($insert);
        }

        return false;
    }

    /**
     * Перевод миллисекунд в формат времени (H:m:s:l).
     *
     * @param $milliseconds
     * @return string
     */
    public static function formatMilliseconds($milliseconds) {
        $seconds = floor($milliseconds / 1000);
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $milliseconds = $milliseconds % 1000;
        $seconds = $seconds % 60;
        $minutes = $minutes % 60;
        $format = '%02u:%02u:%02u:%03u';
        $time = sprintf($format, $hours, $minutes, $seconds, $milliseconds);

        return $time;
    }

    /**
     * Получение максимального времени на ответ.
     *
     * @return string
     */
    public function getMaximumTime()
    {
        return self::formatMilliseconds($this->maximum_time);
    }

    /**
     * Получение времени вопроса опроса.
     *
     * @return string
     */
    public function getTime()
    {
        return self::formatMilliseconds($this->time);
    }

    /**
     * Получение списка всех типов вопросов.
     *
     * @return array - массив всех возможных типов вопросов
     */
    public static function getTypes()
    {
        return [
            self::TYPE_SIMPLE_QUESTION => 'Простой вопрос',
            self::TYPE_COMPOUND_QUESTION => 'Составной вопрос',
            self::TYPE_CALIBRATION_QUESTION => 'Калибровочный вопрос',
            self::TYPE_NOT_QUESTION => 'Не вопрос',
        ];
    }

    /**
     * Получение типа вопроса.
     *
     * @return mixed
     */
    public function getType()
    {
        return ArrayHelper::getValue(self::getTypes(), $this->type);
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