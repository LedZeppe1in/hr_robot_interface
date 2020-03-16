<?php

namespace app\modules\main\models;

use Yii;

/**
 * This is the model class for table "{{%addressed_interview}}".
 *
 * @property int $id
 * @property int $video_interview_id
 * @property int $customer_id
 *
 * @property Customer $customer
 * @property VideoInterview $videoInterview
 */
class AddressedInterview extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%addressed_interview}}';
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'video_interview_id' => 'ID видео-интервью',
            'customer_id' => 'ID заказчика',
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