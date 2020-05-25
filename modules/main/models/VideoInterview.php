<?php

namespace app\modules\main\models;

use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%video_interview}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $video_file_name
 * @property string $description
 * @property int $respondent_id
 *
 * @property AddressedInterview[] $addressedInterviews
 * @property Landmark[] $landmarks
 * @property Respondent $respondent
 */
class VideoInterview extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;

    const VIDEO_INTERVIEW_ANALYSIS_SCENARIO = 'login'; // Сценарий анализа видео-интервью

    const TYPE_ZERO                    = 0;   // Поворот на 0 градусов
    const TYPE_NINETY                  = 90;  // Поворот на 90 градусов
    const TYPE_ONE_HUNDRED_EIGHTY      = 180; // Поворот на 180 градусов
    const TYPE_TWO_HUNDRED_AND_SEVENTY = 270; // Поворот на 270 градусов

    const TYPE_MIRRORING_TRUE  = true;  // Отзеркаливание есть
    const TYPE_MIRRORING_FALSE = false; // Отзеркаливания нет

    public $videoInterviewFile; // Файл видео-интервью
    public $rotationParameter;  // Параметр поворота
    public $mirroringParameter; // Параметр наличия отзеркаливания

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%video_interview}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['videoInterviewFile'], 'required', 'on' => self::VIDEO_INTERVIEW_ANALYSIS_SCENARIO],
            [['respondent_id'], 'required'],
            [['respondent_id'], 'integer'],
            [['video_file_name', 'description'], 'string'],
            [['rotationParameter', 'mirroringParameter'], 'safe'],
            [['videoInterviewFile'], 'file', 'extensions' => ['avi', 'mp4'], 'checkExtensionByMimeType' => false],
            [['respondent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Respondent::className(),
                'targetAttribute' => ['respondent_id' => 'id']],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
            'video_file_name' => 'Название файла видеоинтервью',
            'description' => 'Описание',
            'respondent_id' => 'ID респондента',
            'videoInterviewFile' => 'Файл видеоинтервью',
            'rotationParameter' => 'Поворот (градусы)',
            'mirroringParameter' => 'Наличие отзеркаливания',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Gets query for [[AddressedInterviews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAddressedInterviews()
    {
        return $this->hasMany(AddressedInterview::className(), ['video_interview_id' => 'id']);
    }

    /**
     * Gets query for [[Landmarks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLandmarks()
    {
        return $this->hasMany(Landmark::className(), ['video_interview_id' => 'id']);
    }

    /**
     * Gets query for [[Respondent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRespondent()
    {
        return $this->hasOne(Respondent::className(), ['id' => 'respondent_id']);
    }

    /**
     * Получение списка видеоинтервью.
     *
     * @return array - массив всех видеоинтервью
     */
    public static function getVideoInterviews()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'id');
    }

    /**
     * Получение списка типов градусов для поворота.
     *
     * @return array - массив всех возможных типов градусов поворотов
     */
    public static function getRotationTypes()
    {
        return [
            self::TYPE_ZERO => self::TYPE_ZERO,
            self::TYPE_NINETY => self::TYPE_NINETY,
            self::TYPE_ONE_HUNDRED_EIGHTY => self::TYPE_ONE_HUNDRED_EIGHTY,
            self::TYPE_TWO_HUNDRED_AND_SEVENTY => self::TYPE_TWO_HUNDRED_AND_SEVENTY,
        ];
    }

    /**
     * Получение списка типов наличия отзеркаливания.
     *
     * @return array - массив всех возможных типов наличия отзеркаливания
     */
    public static function getMirroringTypes()
    {
        return [
            self::TYPE_MIRRORING_FALSE => 'Нет',
            self::TYPE_MIRRORING_TRUE => 'Да',
        ];
    }
}