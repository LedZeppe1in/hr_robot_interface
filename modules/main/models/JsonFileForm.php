<?php

namespace app\modules\main\models;

use yii\base\Model;

/**
 * Class JsonFileForm.
 */
class JsonFileForm extends Model
{
    public $jsonFile;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return array(
            array(['jsonFile'], 'required'),
            array(['jsonFile'], 'file', 'extensions'=>'json', 'checkExtensionByMimeType'=>false),
        );
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return array(
            'jsonFile' => 'Файл в формате JSON',
        );
    }
}