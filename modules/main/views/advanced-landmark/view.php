<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\AdvancedLandmark */

$this->title = ($model->file_name != '') ? $model->file_name : 'не загружено';
$this->params['breadcrumbs'][] = ['label' => 'Модифицированные файлы с лицевыми точками', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="advanced-landmark-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этот файл?',
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
                'label' => 'Файл с лицевыми точками',
                'value' => ($model->file_name != '') ? Html::a('скачать',
                    ['/advanced-landmark/file-download/' . $model->id], ['target' => '_blank']) : null,
                'format' => 'raw'
            ],
        ],
    ]) ?>

</div>