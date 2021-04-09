<?php

namespace app\modules\main\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%profile_knowledge_base}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $profile_id
 * @property int|null $first_level_knowledge_base_id
 * @property int|null $second_level_knowledge_base_id
 *
 * @property Profile $profile
 * @property KnowledgeBase $firstLevelKnowledgeBase
 * @property KnowledgeBase $secondLevelKnowledgeBase
 */
class ProfileKnowledgeBase extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%profile_knowledge_base}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['profile_id', 'first_level_knowledge_base_id', 'second_level_knowledge_base_id'],
                'default', 'value' => null],
            [['profile_id', 'first_level_knowledge_base_id', 'second_level_knowledge_base_id'], 'integer'],
            [['profile_id'], 'exist', 'skipOnError' => true, 'targetClass' => Profile::className(),
                'targetAttribute' => ['profile_id' => 'id']],
            [['first_level_knowledge_base_id'], 'exist', 'skipOnError' => true,
                'targetClass' => KnowledgeBase::className(),
                'targetAttribute' => ['first_level_knowledge_base_id' => 'id']],
            [['second_level_knowledge_base_id'], 'exist', 'skipOnError' => true,
                'targetClass' => KnowledgeBase::className(),
                'targetAttribute' => ['second_level_knowledge_base_id' => 'id']],
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
            'profile_id' => 'ID профиля',
            'first_level_knowledge_base_id' => 'ID базы знаний для интерпретации первого уровня',
            'second_level_knowledge_base_id' => 'ID базы знаний для интерпретации второго уровня',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Gets query for [[Profile]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['id' => 'profile_id']);
    }

    /**
     * Gets query for [[FirstLevelKnowledgeBase]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFirstLevelKnowledgeBase()
    {
        return $this->hasOne(KnowledgeBase::className(), ['id' => 'first_level_knowledge_base_id']);
    }

    /**
     * Gets query for [[SecondLevelKnowledgeBase]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSecondLevelKnowledgeBase()
    {
        return $this->hasOne(KnowledgeBase::className(), ['id' => 'second_level_knowledge_base_id']);
    }
}