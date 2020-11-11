<?php

namespace app\modules\main\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%question}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $video_file_name
 * @property string $description
 * @property int $test_question_id
 * @property int $video_interview_id
 *
 * @property Landmark[] $landmarks
 * @property TestQuestion $testQuestion
 * @property VideoInterview $videoInterview
 */
class Question extends \yii\db\ActiveRecord
{
    public $videoFile; // Файл c частью видео-интервью (видео на вопрос)

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%question}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['videoFile'], 'required'],
            [['video_file_name', 'description'], 'string'],
            [['test_question_id', 'video_interview_id'], 'integer'],
            [['videoFile'], 'file', 'extensions' => ['avi', 'mp4'], 'checkExtensionByMimeType' => false],
            [['test_question_id'], 'exist', 'skipOnError' => true, 'targetClass' => TestQuestion::className(),
                'targetAttribute' => ['test_question_id' => 'id']],
            [['video_interview_id'], 'exist', 'skipOnError' => true, 'targetClass' => VideoInterview::className(),
                'targetAttribute' => ['video_interview_id' => 'id']],
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
            'video_file_name' => 'Название файла видео с ответом на вопрос',
            'description' => 'Описание',
            'videoFile' => 'Файл c видео ответом на вопрос',
            'test_question_id' => 'ID вопроса опроса',
            'video_interview_id' => 'ID полного видеоинтервью',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Gets query for [[Landmarks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLandmarks()
    {
        return $this->hasMany(Landmark::className(), ['question_id' => 'id']);
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
     * Gets query for [[VideoInterview]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVideoInterview()
    {
        return $this->hasOne(VideoInterview::className(), ['id' => 'video_interview_id']);
    }
}