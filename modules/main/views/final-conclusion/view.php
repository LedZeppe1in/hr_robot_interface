<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\FinalConclusion */

$this->title = 'Итоговое заключение по видеоинтервью №' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Итоговые заключения по видеоинтервью', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="final-video-interview-conclusion-view">

    <h1><?= Html::encode($this->title) ?></h1>

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
                'attribute' => 'id',
                'label' => 'Создано',
                'value' => $model->finalResult->created_at,
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'id',
                'label' => 'Обновлено',
                'value' => $model->finalResult->updated_at,
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'id',
                'label' => 'ID видеоинтервью',
                'format' => 'raw',
                'value' => Html::a($model->finalResult->video_interview_id,
                    ['video-interview/view', 'id' => $model->finalResult->video_interview_id]),
            ],
            [
                'attribute' => 'id',
                'label' => 'Название файла видеоинтервью',
                'value' => $model->finalResult->videoInterview->video_file_name,
            ],
            [
                'attribute' => 'conclusion',
                'format' => 'raw',
            ],
        ],
    ]) ?>

</div>