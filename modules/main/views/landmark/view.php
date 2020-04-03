<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Landmark */

$this->title = ($model->landmark_file_name != '') ? $model->landmark_file_name : 'не загружена';
$this->params['breadcrumbs'][] = ['label' => 'Цифровые маски', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="advanced-landmark-view">

    <h1>Цифровая маска: <?= Html::encode($this->title) ?></h1>

    <p>
        <?= ($model->landmark_file_name != '') ? Html::a('Определить признаки',
            ['/analysis-result/detection/' . $model->id], ['class' => 'btn btn-primary']) : '' ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этот элемент?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            'video_interview_id',
            [
                'attribute' => 'video_interview_id',
                'label' => 'Название файла видеоинтервью',
                'value' => $model->videoInterview->video_file_name,
            ],
            [
                'label' => 'Описание',
                'value' => ($model->description != '') ? $model->description : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Файл с лицевыми точками',
                'value' => ($model->landmark_file_name != '') ? Html::a('скачать',
                    ['/landmark/landmark-file-download/' . $model->id], ['target' => '_blank']) : null,
                'format' => 'raw'
            ],
        ],
    ]) ?>

</div>