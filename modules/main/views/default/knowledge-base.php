<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $knowledgeBase app\modules\main\controllers\DefaultController */

$this->title = 'База знаний';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="knowledge-base-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="body-content">

        <h4><b>База знаний</b> — это формализованное представление знаний в виде модели продукций для решения различных
            задач, в частности, в контексте разрабатываемой системы — задачи определения аномалий в эмоциональном
            состоянии респондента.
        </h4>

        <p>
            <?= Html::a('Загрузить базу знаний', ['/default/knowledge-base-upload'],
                ['class' => 'btn btn-success']) ?>
            <?= Html::a('Скачать базу знаний', ['/default/knowledge-base-download'],
                ['class' => 'btn btn-primary']) ?>
        </p>

        <div class="row">
            <div class="col-md-12">
                <h4><b>Код базы знаний:</b></h4>
                <?php echo '<pre>' . $knowledgeBase . '</pre>'; ?>
            </div>
        </div>

    </div>
</div>