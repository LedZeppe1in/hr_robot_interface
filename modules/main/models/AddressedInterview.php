<?php

namespace app\modules\main\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%addressed_interview}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property int $video_interview_id
 * @property int $customer_id
 *
 * @property Customer $customer
 * @property VideoInterview $videoInterview
 */
class AddressedInterview extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%addressed_interview}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['video_interview_id', 'customer_id'], 'required'],
            [['video_interview_id', 'customer_id'], 'integer'],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(),
                'targetAttribute' => ['customer_id' => 'id']],
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
            'video_interview_id' => 'ID видео-интервью',
            'customer_id' => 'ID заказчика',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
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