<?php

namespace app\modules\main\models;

use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%video_interview_processing_status}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property int $status
 * @property int $all_runtime
 * @property int $emotion_interpretation_runtime
 * @property int $video_interview_id
 *
 * @property QuestionProcessingStatus[] $questionProcessingStatuses
 * @property VideoInterview $videoInterview
 */
class VideoInterviewProcessingStatus extends \yii\db\ActiveRecord
{
    const STATUS_QUEUE                  = 0; // Статус в очереди
    const STATUS_IN_PROGRESS            = 1; // Статус в процессе
    const STATUS_FINAL_RESULT_FORMATION = 2; // Статус формирования итогового заключения
    const STATUS_COMPLETED              = 3; // Статус завершено
    const STATUS_PARTIALLY_COMPLETED    = 4; // Статус завершено частично
    const STATUS_REJECTION              = 5; // Статус отказ

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%video_interview_processing_status}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['status', 'video_interview_id'], 'required'],
            [['status', 'video_interview_id'], 'integer'],
            [['all_runtime', 'emotion_interpretation_runtime'], 'safe'],
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
            'status' => 'Статус',
            'all_runtime' => 'Время выполнения анализа видео-интервью',
            'emotion_interpretation_runtime' => 'Время формирования итогового заключения',
            'video_interview_id' => 'ID видеоинтервью',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Gets query for [[QuestionProcessingStatuses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestionProcessingStatuses()
    {
        return $this->hasMany(QuestionProcessingStatus::className(), ['video_interview_processing_status_id' => 'id']);
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
     * Получение списка статусов.
     *
     * @return array - массив всех возможных статусов
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_QUEUE => 'в очереди',
            self::STATUS_IN_PROGRESS => 'в процессе',
            self::STATUS_FINAL_RESULT_FORMATION => 'формирование итогового заключения',
            self::STATUS_COMPLETED => 'завершено',
            self::STATUS_PARTIALLY_COMPLETED => 'завершено частично',
            self::STATUS_REJECTION => 'отказ',
        ];
    }

    /**
     * Получение значения статуса.
     *
     * @return mixed
     */
    public function getStatus()
    {
        return ArrayHelper::getValue(self::getStatuses(), $this->status);
    }

    /**
     * Перевод секунд в формат времени (H:m:s).
     *
     * @param $seconds - секунды
     * @return string - строка c временем в формате H:m:s
     */
    public static function formatSeconds($seconds) {
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $seconds = $seconds % 60;
        $minutes = $minutes % 60;
        $format = '%02u:%02u:%02u';
        $time = sprintf($format, $hours, $minutes, $seconds);

        return $time;
    }

    /**
     * Получение времени выполнения анализа видео-интервью.
     *
     * @return string
     */
    public function getAllRuntime()
    {
        return self::formatSeconds($this->all_runtime);
    }

    /**
     * Получение времени формирования итогового заключения.
     *
     * @return string
     */
    public function getEmotionInterpretationRuntime()
    {
        return self::formatSeconds($this->emotion_interpretation_runtime);
    }
}