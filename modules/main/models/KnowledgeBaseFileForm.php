<?php

namespace app\modules\main\models;

use yii\base\Model;

/**
 * Class KnowledgeBaseFileForm.
 * @package app\modules\main\models
 */
class KnowledgeBaseFileForm extends Model
{
    public $knowledgeBaseFile;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return array(
            array(['knowledgeBaseFile'], 'required'),
            array(['knowledgeBaseFile'], 'file', 'extensions'=>'txt', 'checkExtensionByMimeType'=>false),
        );
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return array(
            'knowledgeBaseFile' => 'Файл в формате TXT',
        );
    }
}