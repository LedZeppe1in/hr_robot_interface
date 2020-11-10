<?php

namespace app\modules\main\models;

use yii\helpers\ArrayHelper;
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
 *
 * @property Landmark[] $landmarks
 * @property TestQuestion $testQuestion
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
            [['test_question_id'], 'integer'],
            [['videoFile'], 'file', 'extensions' => ['avi', 'mp4'], 'checkExtensionByMimeType' => false],
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
            'video_file_name' => 'Название файла видео ответа на вопрос',
            'description' => 'Описание',
            'videoFile' => 'Файл c видео ответом на вопрос',
            'test_question_id' => 'ID вопроса опроса',
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

//    /**
//     * Формирование миллисекунд для времени вопроса.
//     *
//     * @param bool $insert
//     * @return bool
//     */
//    public function beforeSave($insert)
//    {
//        if (parent::beforeSave($insert)) {
//            // Получение миллисекунд для времени вопроса
//            $time = explode(":", $this->time);
//            $hour = $time[0] * 60 * 60 * 1000;
//            $minute = $time[1] * 60 * 1000;
//            $second = $time[2] * 1000;
//            $millisecond = $time[3];
//            $this->time = $hour + $minute + $second + $millisecond;
//
//            return parent::beforeSave($insert);
//        }
//
//        return false;
//    }
//
//    /**
//     * Перевод миллисекунд в формат времени (H:m:s:l).
//     *
//     * @param $milliseconds
//     * @return string
//     */
//    public static function formatMilliseconds($milliseconds) {
//        $seconds = floor($milliseconds / 1000);
//        $minutes = floor($seconds / 60);
//        $hours = floor($minutes / 60);
//        $milliseconds = $milliseconds % 1000;
//        $seconds = $seconds % 60;
//        $minutes = $minutes % 60;
//        $format = '%02u:%02u:%02u:%03u';
//        $time = sprintf($format, $hours, $minutes, $seconds, $milliseconds);
//
//        return $time;
//    }
//
//    /**
//     * Получение времени вопроса.
//     *
//     * @return string
//     */
//    public function getTime()
//    {
//        return self::formatMilliseconds($this->time);
//    }
//
//    /**
//     * Получение списка всех типов вопросов.
//     *
//     * @return array - массив всех возможных типов вопросов
//     */
//    public static function getTypes()
//    {
//        return [
//            self::TYPE_CALIBRATION_QUESTION => 'Калибровочный вопрос',
//            self::TYPE_MAIN_QUESTION => 'Основной вопрос',
//            self::TYPE_NOT_QUESTION => 'Не вопрос',
//        ];
//    }
//
//    /**
//     * Получение типа вопроса.
//     *
//     * @return mixed
//     */
//    public function getType()
//    {
//        return ArrayHelper::getValue(self::getTypes(), $this->type);
//    }
//
//    /**
//     * Получение списка вопросов.
//     *
//     * @return array - массив всех вопросов
//     */
//    public static function getQuestions()
//    {
//        return ArrayHelper::map(self::find()->all(), 'id', 'text');
//    }
}