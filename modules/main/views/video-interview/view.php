<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\VideoInterview */

$this->title = ($model->video_file_name != '') ? $model->video_file_name : 'не загружено';
$this->params['breadcrumbs'][] = ['label' => 'Видеоинтервью', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="video-interview-view">

    <h1>Видеоинтервью: <?= Html::encode($this->title) ?></h1>

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
            [
                'attribute' => 'respondent_id',
                'label' => 'Респондент',
                'value' => $model->respondent->name,
            ],
            [
                'label' => 'Описание',
                'value' => ($model->description != '') ? $model->description : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Файл видеоинтервью',
                'value' => ($model->video_file_name != '') ? Html::a('скачать',
                    ['/video-interview/video-download/' . $model->id], ['target' => '_blank']) : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Файл с лицевыми точками',
                'value' => ($model->landmark_file_name != '') ? Html::a('скачать',
                    ['/video-interview/landmark-download/' . $model->id], ['target' => '_blank']) : null,
                'format' => 'raw'
            ],
        ],
    ]) ?>

</div>