<?php

use yii\helpers\Html;

/* @var $knowledgeBase app\modules\main\controllers\AnalysisResultController */

?>

<div class="row">
    <div class="col-md-12"><br />
        <p>
            <?= Html::a('Загрузить новую базу знаний', ['/default/knowledge-base-upload'],
                ['class' => 'btn btn-success']) ?>
            <?= Html::a('Скачать базу знаний', ['/default/knowledge-base-download'],
                ['class' => 'btn btn-primary']) ?>
        </p>
        <?php echo '<pre>' . $knowledgeBase . '</pre>'; ?>
    </div>
</div>