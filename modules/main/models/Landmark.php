<?php

namespace app\modules\main\models;

use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%landmark}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $landmark_file_name
 * @property string $description
 * @property int $rotation
 * @property bool $mirroring
 * @property int $start_time
 * @property int $finish_time
 * @property int $video_interview_id
 * @property int $question_id
 *
 * @property VideoInterview $videoInterview
 * @property Question $question
 */
class Landmark extends \yii\db\ActiveRecord
{
    const TRUE_MIRRORING = true;   // Отзеркаливание есть
    const FALSE_MIRRORING = false; // Отзеркаливания нет

    public $landmarkFile; // Файл с лицевыми точками
    public $questionText; // Текст вопроса

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%landmark}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['video_interview_id', 'start_time', 'finish_time', 'questionText'], 'required'],
            [['video_interview_id'], 'integer'],
            [['landmark_file_name', 'description', 'questionText'], 'string',],
            [['start_time', 'finish_time'], 'safe'],
            [['landmarkFile'], 'file', 'extensions' => 'json', 'checkExtensionByMimeType' => false],
            [['video_interview_id'], 'exist', 'skipOnError' => true, 'targetClass' => VideoInterview::className(),
                'targetAttribute' => ['video_interview_id' => 'id']],
            [['question_id'], 'exist', 'skipOnError' => true, 'targetClass' => VideoInterview::className(),
                'targetAttribute' => ['question_id' => 'id']],
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
            'landmark_file_name' => 'Название файла с лицевыми точками',
            'description' => 'Описание',
            'rotation' => 'Поворот (градусы)',
            'mirroring' => 'Наличие отзеркаливания',
            'start_time' => 'Время начала нарезки',
            'finish_time' => 'Время окончания нарезки',
            'video_interview_id' => 'ID видеоинтервью',
            'question_id' => 'ID вопроса',
            'landmarkFile' => 'Файл с лицевыми точками',
            'questionText' => 'Текст вопроса',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
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

    /**
     * Gets query for [[Question]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestion()
    {
        return $this->hasOne(Question::className(), ['id' => 'question_id']);
    }

    /**
     * Формирование миллисекунд для времени начала и окончания нарезки.
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Получение миллисекунд для стартового времени
            $startTime = explode(":", $this->start_time);
            $startHour = $startTime[0] * 60 * 60 * 1000;
            $startMinute = $startTime[1] * 60 * 1000;
            $startSecond = $startTime[2] * 1000;
            $startMillisecond = $startTime[3];
            $this->start_time = $startHour + $startMinute + $startSecond + $startMillisecond;
            // Получение миллисекунд для времени окончания
            $finishTime = explode(":", $this->finish_time);
            $finishHour = $finishTime[0] * 60 * 60 * 1000;
            $finishMinute = $finishTime[1] * 60 * 1000;
            $finishSecond = $finishTime[2] * 1000;
            $finishMillisecond = $finishTime[3];
            $this->finish_time = $finishHour + $finishMinute + $finishSecond + $finishMillisecond;

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
     * Получение времени начала нарезки по миллисекундам.
     *
     * @return string
     */
    public function getStartTime()
    {
        return self::formatMilliseconds($this->start_time);
    }

    /**
     * Получение времени окончания нарезки по миллисекундам.
     *
     * @return string
     */
    public function getFinishTime()
    {
        return self::formatMilliseconds($this->finish_time);
    }

    /**
     * Получение списка значений для отзеркаливания.
     *
     * @return array - массив всех возможных значений для отзеркаливания
     */
    public static function getMirroringValues()
    {
        return [
            self::TRUE_MIRRORING => 'Да',
            self::FALSE_MIRRORING => 'Нет',
        ];
    }

    /**
     * Получение значения отзеркаливания.
     *
     * @return mixed
     */
    public function getMirroring()
    {
        return ArrayHelper::getValue(self::getMirroringValues(), $this->mirroring);
    }
}