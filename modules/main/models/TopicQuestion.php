<?php

namespace app\modules\main\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%topic_question}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property int $topic_id
 * @property int $test_question_id
 *
 * @property TestQuestion $testQuestion
 * @property Topic $topic
 */
class TopicQuestion extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%topic_question}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['topic_id', 'test_question_id'], 'required'],
            [['topic_id', 'test_question_id'], 'default', 'value' => null],
            [['topic_id', 'test_question_id'], 'integer'],
            [['topic_id', 'test_question_id'], 'unique', 'targetAttribute' => ['topic_id', 'test_question_id']],
            [['test_question_id'], 'exist', 'skipOnError' => true, 'targetClass' => TestQuestion::className(),
                'targetAttribute' => ['test_question_id' => 'id']],
            [['topic_id'], 'exist', 'skipOnError' => true, 'targetClass' => Topic::className(),
                'targetAttribute' => ['topic_id' => 'id']],
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
            'topic_id' => 'Topic ID',
            'test_question_id' => 'Test Question ID',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
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

    /**
     * Gets query for [[Topic]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTopic()
    {
        return $this->hasOne(Topic::className(), ['id' => 'topic_id']);
    }
}