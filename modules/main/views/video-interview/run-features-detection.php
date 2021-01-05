<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\VideoInterview */
/* @var $featureStatistics app\modules\main\controllers\VideoInterviewController */
/* @var $summarizedFeatureStatistics app\modules\main\controllers\VideoInterviewController */
/* @var $summarizedFeatureStatisticsFacts app\modules\main\controllers\VideoInterviewController */

$this->title = ($model->video_file_name != '') ? $model->video_file_name : 'не загружено';
$this->params['breadcrumbs'][] = ['label' => 'Видеоинтервью', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="run-features-detection">

    <h1>Результата для видеоинтервью: <?= Html::encode($this->title) ?></h1>

    <pre><?php print_r($featureStatistics); ?></pre>

    <pre><?php print_r($summarizedFeatureStatistics); ?></pre>

    <pre><?php print_r($summarizedFeatureStatisticsFacts); ?></pre>

</div>