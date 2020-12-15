<?php

namespace app\modules\main\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%topic}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $name
 * @property string|null $description
 *
 * @property TopicQuestion[] $topicQuestions
 * @property TestQuestion[] $testQuestions
 */
class Topic extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%topic}}';
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
     * Gets query for [[TopicQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTopicQuestions()
    {
        return $this->hasMany(TopicQuestion::className(), ['topic_id' => 'id']);
    }

    /**
     * Gets query for [[TestQuestions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTestQuestions()
    {
        return $this->hasMany(TestQuestion::className(), ['id' => 'test_question_id'])
            ->viaTable('{{%topic_question}}', ['topic_id' => 'id']);
    }
}