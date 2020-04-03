<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $knowledgeBaseFileForm app\modules\main\models\KnowledgeBaseFileForm */

$this->title = 'Загрузка базы знаний';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="knowledge-base-download">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin([
                'id'=>'import-excel-file-form',
                'options' => ['enctype' => 'multipart/form-data']
            ]); ?>

            <?= $form->errorSummary($knowledgeBaseFileForm); ?>

            <?= $form->field($knowledgeBaseFileForm, 'knowledgeBaseFile')->fileInput() ?>

            <div class="form-group">
                <?= Html::submitButton('Загрузить', ['class' => 'btn btn-success',
                    'name'=>'import-knowledge-base-file-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>