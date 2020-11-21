<?php

namespace app\modules\main\models;

use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%question_processing_status}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property int $status
 * @property int $ivan_video_analysis_runtime
 * @property int $andrey_video_analysis_runtime
 * @property int $feature_detection_runtime
 * @property int $feature_interpretation_runtime
 * @property int $question_id
 * @property int $video_interview_processing_status_id
 *
 * @property ModuleMessage[] $moduleMessages
 * @property Question $question
 * @property VideoInterviewProcessingStatus $videoInterviewProcessingStatus
 */
class QuestionProcessingStatus extends \yii\db\ActiveRecord
{
    const STATUS_QUEUE                                      = 0; // Статус в очереди
    const STATUS_IVAN_VIDEO_PROCESSING_MODULE_IN_PROGRESS   = 1; // Статус в работе МОВ Ивана
    const STATUS_ANDREY_VIDEO_PROCESSING_MODULE_IN_PROGRESS = 2; // Статус в работе МОВ Андрея
    const STATUS_FEATURE_DEFINITION_MODULE_IN_PROGRESS      = 3; // Статус в работе МОП
    const STATUS_FEATURE_INTERPRETATION_MODULE_IN_PROGRESS  = 4; // Статус в работе МИП
    const STATUS_COMPLETED                                  = 5; // Статус завершено
    const STATUS_REJECTION                                  = 6; // Статус отказ

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%question_processing_status}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['status', 'question_id', 'video_interview_processing_status_id'], 'required'],
            [['status', 'question_id', 'video_interview_processing_status_id'], 'integer'],
            [['ivan_video_analysis_runtime', 'andrey_video_analysis_runtime', 'feature_detection_runtime',
                'feature_interpretation_runtime'], 'safe'],
            [['question_id'], 'exist', 'skipOnError' => true, 'targetClass' => Question::className(),
                'targetAttribute' => ['question_id' => 'id']],
            [['video_interview_processing_status_id'], 'exist', 'skipOnError' => true,
                'targetClass' => VideoInterviewProcessingStatus::className(),
                'targetAttribute' => ['video_interview_processing_status_id' => 'id']],
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
            'ivan_video_analysis_runtime' => 'Время выполнения МОВ Ивана',
            'andrey_video_analysis_runtime' => 'Время выполнения МОВ Андрея',
            'feature_detection_runtime' => 'Время выполнения МОП',
            'feature_interpretation_runtime' => 'Время выполнения МИП',
            'question_id' => 'ID видео на вопрос',
            'video_interview_processing_status_id' => 'ID статуса обработки видеоинтервью',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Gets query for [[ModuleMessages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getModuleMessages()
    {
        return $this->hasMany(ModuleMessage::className(), ['question_processing_status_id' => 'id']);
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
     * Gets query for [[VideoInterviewProcessingStatus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVideoInterviewProcessingStatus()
    {
        return $this->hasOne(VideoInterviewProcessingStatus::className(),
            ['id' => 'video_interview_processing_status_id']);
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
            self::STATUS_IVAN_VIDEO_PROCESSING_MODULE_IN_PROGRESS => 'в работе МОВ Ивана',
            self::STATUS_ANDREY_VIDEO_PROCESSING_MODULE_IN_PROGRESS => 'в работе МОВ Андрея',
            self::STATUS_FEATURE_DEFINITION_MODULE_IN_PROGRESS => 'в работе МОП',
            self::STATUS_FEATURE_INTERPRETATION_MODULE_IN_PROGRESS => 'в работе МИП',
            self::STATUS_COMPLETED => 'завершено',
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
     * Получение времени выполнения МОВ Ивана.
     *
     * @return string
     */
    public function getIvanVideoAnalysisRuntime()
    {
        return self::formatSeconds($this->ivan_video_analysis_runtime);
    }

    /**
     * Получение времени выполнения МОВ Андрея.
     *
     * @return string
     */
    public function getAndreyVideoAnalysisRuntime()
    {
        return self::formatSeconds($this->andrey_video_analysis_runtime);
    }

    /**
     * Получение времени выполнения МОП.
     *
     * @return string
     */
    public function getFeatureDetectionRuntime()
    {
        return self::formatSeconds($this->feature_detection_runtime);
    }

    /**
     * Получение времени выполнения МИП.
     *
     * @return string
     */
    public function getFeatureInterpretationRuntime()
    {
        return self::formatSeconds($this->feature_interpretation_runtime);
    }
}