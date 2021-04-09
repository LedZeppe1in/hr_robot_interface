<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\VideoInterview */
/* @var $landmarkModels app\modules\main\models\Landmark */
/* @var $questions app\modules\main\controllers\VideoInterviewController */

$this->title = 'Формирование цифровой маски для ' . $model->video_file_name;
$this->params['breadcrumbs'][] = ['label' => 'Видеоинтервью', 'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => ($model->video_file_name != '') ? $model->video_file_name :
    'не загружено', 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Формирование цифровой маски';
?>

<div class="get-landmarks">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form_landmarks', [
        'model' => $model,
        'landmarkModels' => $landmarkModels,
        'questions' => $questions
    ]) ?>

</div>