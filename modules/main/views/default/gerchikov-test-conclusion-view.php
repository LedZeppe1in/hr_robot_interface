<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\GerchikovTestConclusion */

$this->title = 'Результаты обработки опроса мотивации к труду по профилю «Кассир»';
?>

<div class="gerchikov-test-conclusion-view">

    <h1>Ваши результаты:</h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'accept_level',
            'instrumental_motivation',
            'professional_motivation',
            'patriot_motivation',
            'master_motivation',
            'avoid_motivation',
        ],
    ]) ?>

</div>