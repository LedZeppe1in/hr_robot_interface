<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\VideoInterview */
/* @var $recognizedSpeechText app\modules\main\controllers\VideoInterviewController */

$this->title = ($model->video_file_name != '') ? $model->video_file_name : 'не загружено';
$this->params['breadcrumbs'][] = ['label' => 'Видео на вопросы', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="recognized-speech-text">

    <h1>Текст распознанной речи в видео ответе на вопрос: <?= Html::encode($this->title) ?></h1>

    <pre><?php print_r($recognizedSpeechText); ?></pre>

</div>