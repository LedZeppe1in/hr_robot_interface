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
 * @property int $type
 * @property string $processed_video_file_name
 * @property int $video_interview_id
 * @property int $question_id
 *
 * @property VideoInterview $videoInterview
 * @property Question $question
 */
class Landmark extends \yii\db\ActiveRecord
{
    const UPLOAD_LANDMARK_SCENARIO = 'upload-landmark'; // Сценарий загрузки новой цифровой маски

    const TYPE_ZERO                    = 0; // Поворот на 0 градусов
    const TYPE_NINETY                  = 1; // Поворот на 90 градусов
    const TYPE_ONE_HUNDRED_EIGHTY      = 2; // Поворот на 180 градусов
    const TYPE_TWO_HUNDRED_AND_SEVENTY = 3; // Поворот на 270 градусов

    const TYPE_MIRRORING_TRUE  = true;  // Отзеркаливание есть
    const TYPE_MIRRORING_FALSE = false; // Отзеркаливания нет

    const TYPE_LANDMARK_IVAN_MODULE   = 0; // Цифровая маска получена от программы Ивана
    const TYPE_LANDMARK_ANDREW_MODULE = 1; // Цифровая маска получена от программы Андрея

    public $landmarkFile;   // Файл с лицевыми точками
    public $testQuestionId; // Идентификатор вопроса

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
            [['landmarkFile', 'testQuestionId'], 'required', 'on' => self::UPLOAD_LANDMARK_SCENARIO],
            [['start_time', 'finish_time', 'video_interview_id'], 'required'],
            [['video_interview_id'], 'integer'],
            [['landmark_file_name', 'processed_video_file_name', 'description'], 'string'],
            [['rotation', 'mirroring', 'start_time', 'finish_time', 'type'], 'safe'],
            [['landmarkFile'], 'file', 'extensions' => 'json', 'checkExtensionByMimeType' => false],
            [['video_interview_id'], 'exist', 'skipOnError' => true, 'targetClass' => VideoInterview::className(),
                'targetAttribute' => ['video_interview_id' => 'id']],
            [['question_id'], 'exist', 'skipOnError' => true, 'targetClass' => Question::className(),
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
            'created_at' => 'Создана',
            'updated_at' => 'Обновлена',
            'landmark_file_name' => 'Название файла с лицевыми точками',
            'description' => 'Описание',
            'rotation' => 'Поворот (градусы)',
            'mirroring' => 'Наличие отзеркаливания',
            'start_time' => 'Время начала нарезки',
            'finish_time' => 'Время окончания нарезки',
            'type' => 'Тип',
            'processed_video_file_name' => 'Название файла видео с лицевыми точками',
            'video_interview_id' => 'ID видеоинтервью',
            'question_id' => 'ID видео на вопрос',
            'landmarkFile' => 'Файл с лицевыми точками',
            'testQuestionId' => 'ID вопроса',
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
     * @param $milliseconds - миллисекунды
     * @return string - строка c временем в формате H:m:s:l
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
     * Получение списка типов градусов для поворота.
     *
     * @return array - массив всех возможных типов градусов поворотов
     */
    public static function getRotationTypes()
    {
        return [
            self::TYPE_ZERO => 0,
            self::TYPE_NINETY => 90,
            self::TYPE_ONE_HUNDRED_EIGHTY => 180,
            self::TYPE_TWO_HUNDRED_AND_SEVENTY => 270
        ];
    }

    /**
     * Получение списка значений для отзеркаливания.
     *
     * @return array - массив всех возможных значений для отзеркаливания
     */
    public static function getMirroringValues()
    {
        return [
            self::TYPE_MIRRORING_FALSE => 'Нет',
            self::TYPE_MIRRORING_TRUE => 'Да',
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

    /**
     * Получение списка значений для типов цифровых масок.
     *
     * @return array - массив всех возможных типов цифровых масок
     */
    public static function getTypes()
    {
        return [
            self::TYPE_LANDMARK_IVAN_MODULE => 'От Ивана',
            self::TYPE_LANDMARK_ANDREW_MODULE => 'От Андрея',
        ];
    }

    /**
     * Получение значения типа цифровой маски.
     *
     * @return mixed
     */
    public function getType()
    {
        return ArrayHelper::getValue(self::getTypes(), $this->type);
    }
}