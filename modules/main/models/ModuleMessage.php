<?php

namespace app\modules\main\models;

use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%module_message}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $message
 * @property int $module_name
 * @property int $question_processing_status_id
 *
 * @property QuestionProcessingStatus $questionProcessingStatus
 */
class ModuleMessage extends \yii\db\ActiveRecord
{
    const IVAN_VIDEO_PROCESSING_MODULE   = 0; // МОВ Ивана
    const ANDREY_VIDEO_PROCESSING_MODULE = 1; // МОВ Андрея
    const FEATURE_DETECTION_MODULE       = 2; // МОП
    const FEATURE_INTERPRETATION_MODULE  = 3; // МИП

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%module_message}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['message', 'question_processing_status_id'], 'required'],
            [['module_name', 'question_processing_status_id'], 'integer'],
            [['message'], 'string'],
            [['question_processing_status_id'], 'exist', 'skipOnError' => true,
                'targetClass' => QuestionProcessingStatus::className(),
                'targetAttribute' => ['question_processing_status_id' => 'id']],
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
            'message' => 'Сообщение',
            'module_name' => 'Название модуля',
            'question_processing_status_id' => 'ID статуса обработки вопроса',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Gets query for [[QuestionProcessingStatus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestionProcessingStatus()
    {
        return $this->hasOne(QuestionProcessingStatus::className(), ['id' => 'question_processing_status_id']);
    }

    /**
     * Получение списка всех названий модулей системы.
     *
     * @return array - массив всех возможных названий модулей системы
     */
    public static function getModuleNames()
    {
        return [
            self::IVAN_VIDEO_PROCESSING_MODULE => 'МОВ Ивана',
            self::ANDREY_VIDEO_PROCESSING_MODULE => 'МОВ Андрея',
            self::FEATURE_DETECTION_MODULE => 'МОП',
            self::FEATURE_INTERPRETATION_MODULE => 'МИП',
        ];
    }

    /**
     * Получение конкретного названия модуля системы.
     *
     * @return mixed
     */
    public function getModuleName()
    {
        return ArrayHelper::getValue(self::getModuleNames(), $this->module_name);
    }
}