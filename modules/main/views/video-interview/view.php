<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\VideoInterview */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Видеоинтервью', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="video-interview-view">

    <h1>Видеоинтервью: <?= Html::encode($this->title) ?></h1>

    <p>
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
                'attribute' => 'respondent_id',
                'label' => 'Респондент',
                'value' => $model->respondent->name,
            ],
            'name',
            [
                'label' => 'Файл видеоинтервью',
                'value' => Html::a($model->video_file, $model->video_file, ['target' => '_blank']),
                'format' => 'raw'
            ],
            [
                'label' => 'Файл с лицевыми точками',
                'value' => Html::a($model->landmark_file, $model->landmark_file, ['target' => '_blank']),
                'format' => 'raw'
            ],
        ],
    ]) ?>

</div>