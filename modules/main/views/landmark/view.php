<?php

use yii\bootstrap\ButtonDropdown;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Landmark */

$this->title = ($model->landmark_file_name != '') ? $model->landmark_file_name : 'не загружена';
$this->params['breadcrumbs'][] = ['label' => 'Цифровые маски', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="landmark-view">

    <h1>Цифровая маска: <?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->landmark_file_name != '') {
            echo ButtonDropdown::widget([
                'label' => 'Определить признаки',
                'dropdown' => [
                    'items' => [
                        ['label' => 'По сырым точкам', 'url' => '/analysis-result/detection/' .
                            $model->id . '/' . 0],
                        ['label' => 'По нормализованным точкам', 'url' => '/analysis-result/detection/' .
                            $model->id . '/' . 1],
                    ],
                ],
                'options' => ['class' => 'btn btn-success']
            ]);
        } ?>
        <?= Html::a('Посмотреть в редакторе маски',
            'http://84.201.129.65:8080/HRRMaskEditor/MaskEditor.php?landmark_id='. $model->id .
                '&detection_result_id=none',
            ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Обновить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
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
                'attribute' => 'type',
                'label' => 'Цифровая маска получена',
                'value' => ($model->type !== null) ? $model->getType() : null,
            ],
            'question_id',
            [
                'attribute' => 'question_id',
                'label' => 'Текст вопроса',
                'value' => $model->question_id ? $model->question->text : null,
            ],
            'rotation',
            [
                'attribute' => 'mirroring',
                'value' => $model->getMirroring(),
            ],
            [
                'attribute' => 'start_time',
                'value' => $model->getStartTime(),
            ],
            [
                'attribute' => 'finish_time',
                'value' => $model->getFinishTime(),
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