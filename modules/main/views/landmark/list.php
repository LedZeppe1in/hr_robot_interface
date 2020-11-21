<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Цифровые маски';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="landmark-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Загрузить', ['upload'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute'=>'video_interview_id',
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a($data->video_interview_id,
                        ['video-interview/view', 'id' => $data->video_interview_id]);
                },
            ],
            [
                'attribute'=>'question_id',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->question_id != '') ? Html::a($data->question_id,
                        ['question/view', 'id' => $data->question_id]) : null;
                },
            ],
            [
                'attribute'=>'landmark_file_name',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->landmark_file_name != '') ? $data->landmark_file_name : null;
                },
            ],
            [
                'attribute'=>'description',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->description != '') ? $data->description : null;
                },
            ],
            [
                'attribute'=>'type',
                'label' => 'Цифровая маска получена',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->type !== null) ? $data->getType() : null;
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {update} {mask-editor} {raw-detection} {norm-detection} {delete}',
                'buttons' => [
                    'mask-editor' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-user',
                                'title' => 'Посмотреть в редакторе маски']);
                        $url = 'https://84.201.129.65:8080/HRRMaskEditor/MaskEditor.php?landmark_id='. $model->id .
                            '&detection_result_id=none';
                        return Html::a($icon, $url);
                    },
                    'raw-detection' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '', ['class' => 'glyphicon glyphicon-file',
                            'title' => 'Определение признаков по сырым точкам']);
                        $url = ['/analysis-result/detection/' . $model->id . '/' . 0];
                        return ($model->landmark_file_name != '') ? Html::a($icon, $url) : null;
                    },
                    'norm-detection' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '', ['class' => 'glyphicon glyphicon-save-file',
                            'title' => 'Определение признаков по нормализованным точкам']);
                        $url = ['/analysis-result/detection/' . $model->id . '/' . 1];
                        return ($model->landmark_file_name != '') ? Html::a($icon, $url) : null;
                    },
                ],
            ],
        ],
    ]); ?>

</div>