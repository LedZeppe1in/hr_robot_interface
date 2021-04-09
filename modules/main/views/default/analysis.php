<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\VideoInterview */
/* @var $landmarkModels app\modules\main\models\Landmark */
/* @var $questions app\modules\main\controllers\DefaultController */

$this->title = 'Загрузка видеоинтервью';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="video-interview-analysis">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'landmarkModels' => $landmarkModels,
        'questions' => $questions
    ]) ?>

</div>