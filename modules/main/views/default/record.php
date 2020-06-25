<?php

use yii\helpers\Html;
use yii\bootstrap\Button;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'Запись видеоинтервью';
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Подключение js-скрипта -->
<?php $this->registerJsFile('/js/MediaRecorder.js') ?>

<div class="video-interview-record">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Button::widget([
            'label' => Yii::t('app', 'Начать запись'),
            'options' => [
                'id' => 'record',
                'class' => 'btn-success',
                'style' => 'margin:5px'
            ]
        ]); ?>
        <?= Button::widget([
            'label' => Yii::t('app', 'Просмотреть запись'),
            'options' => [
                'id' => 'play',
                'class' => 'btn-primary',
                'style' => 'margin:5px',
                'disabled' => 'disabled'
            ]
        ]); ?>
        <?= Button::widget([
            'label' => Yii::t('app', 'Загрузить'),
            'options' => [
                'id' => 'upload',
                'class' => 'btn-primary',
                'style' => 'margin:5px',
                'disabled' => 'disabled'
            ]
        ]); ?>
        <?= Button::widget([
            'label' => Yii::t('app', 'Скачать'),
            'options' => [
                'id' => 'download',
                'class' => 'btn-primary',
                'style' => 'margin:5px',
                'disabled' => 'disabled'
            ]
        ]); ?>
    </p>

    <video id="gum" autoplay muted playsinline></video>
    <video id="recorded" autoplay loop playsinline></video>

</div>