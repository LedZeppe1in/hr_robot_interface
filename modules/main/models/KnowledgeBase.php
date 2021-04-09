<?php

namespace app\modules\main\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%knowledge_base}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $name
 * @property string $knowledge_base_file_name
 * @property string $description
 *
 * @property ProfileKnowledgeBase[] $firstLevelProfileKnowledgeBases
 * @property ProfileKnowledgeBase[] $secondLevelProfileKnowledgeBases
 */
class KnowledgeBase extends \yii\db\ActiveRecord
{
    const UPLOAD_KNOWLEDGE_BASE_SCENARIO = 'upload-knowledge-base'; // Сценарий загрузки новой базы знаний

    public $knowledgeBaseFile; // Файл базы знаний

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%knowledge_base}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['knowledgeBaseFile'], 'required', 'on' => self::UPLOAD_KNOWLEDGE_BASE_SCENARIO],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['knowledge_base_file_name', 'description'], 'string'],
            [['knowledgeBaseFile'], 'file', 'extensions' => 'txt', 'checkExtensionByMimeType' => false],
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
            'name' => 'Название',
            'knowledge_base_file_name' => 'Название файла базы знаний',
            'description' => 'Описание',
            'knowledgeBaseFile' => 'Файл с кодом базы знаний',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Gets query for [[ProfileKnowledgeBase]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFirstLevelProfileKnowledgeBases()
    {
        return $this->hasMany(ProfileKnowledgeBase::className(), ['first_level_knowledge_base_id' => 'id']);
    }

    /**
     * Gets query for [[ProfileKnowledgeBase]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSecondLevelProfileKnowledgeBases()
    {
        return $this->hasMany(ProfileKnowledgeBase::className(), ['second_level_knowledge_base_id' => 'id']);
    }
}