<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $jsonFileForm app\modules\main\models\JsonFileForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Определение признаков';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="main-default-face-feature-detection">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin([
                'id'=>'import-excel-file-form',
                'options' => ['enctype' => 'multipart/form-data']
            ]); ?>

            <?= $form->errorSummary($jsonFileForm); ?>

            <?= $form->field($jsonFileForm, 'jsonFile')->fileInput() ?>

            <div class="form-group">
                <?= Html::submitButton('<span class="glyphicon glyphicon-import"></span> Загрузить',
                    ['class' => 'btn btn-success', 'name'=>'import-excel-file-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>