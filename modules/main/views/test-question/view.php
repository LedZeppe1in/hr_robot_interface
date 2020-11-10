<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\TestQuestion */

$this->title = 'Вопрос опроса №' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Вопросы опроса', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="test-question-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Обновить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этот вопрос опроса?',
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
            'name',
            'text:ntext',
            [
                'attribute' => 'type',
                'label' => 'Тип',
                'value' => ($model->type !== null) ? $model->getType() : null,
            ],
            [
                'attribute' => 'maximum_time',
                'value' => ($model->maximum_time !== null) ? $model->getMaximumTime() : null,
            ],
            [
                'attribute' => 'time',
                'value' => ($model->time !== null) ? $model->getTime() : null,
            ],
            [
                'label' => 'Описание',
                'value' => ($model->description != '') ? $model->description : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Название файла с озвучкой вопроса',
                'value' => ($model->audio_file_name != '') ? $model->audio_file_name : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Файл озвучки вопроса',
                'value' => ($model->audio_file_name != '') ? Html::a('скачать',
                    ['/question/audio-file-download/' . $model->id], ['target' => '_blank']) : null,
                'format' => 'raw'
            ],
        ],
    ]) ?>

</div>