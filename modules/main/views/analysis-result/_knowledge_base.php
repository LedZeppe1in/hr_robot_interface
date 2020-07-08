<?php

use yii\helpers\Html;

/* @var $knowledgeBaseModel app\modules\main\models\KnowledgeBase */
/* @var $knowledgeBase app\modules\main\controllers\AnalysisResultController */

?>

<div class="row">
    <div class="col-md-12"><br />
        <p>
            <?= Html::a('Загрузить новую базу знаний', ['/knowledge-base/upload'],
                ['class' => 'btn btn-success']) ?>
            <?= Html::a('Скачать базу знаний', '/knowledge-base/knowledge-base-download/' .
                $knowledgeBaseModel->id, ['class' => 'btn btn-primary']) ?>
        </p>
        <?php echo '<pre>' . $knowledgeBase . '</pre>'; ?>
    </div>
</div>