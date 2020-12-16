<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\main\models\User;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Интервью респондентов';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="respondent-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Yii::$app->user->identity->role == User::ROLE_ADMINISTRATOR ?
            Html::a('Создать', ['create'], ['class' => 'btn btn-success']) : null; ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'name',
            'main_respondent_id',
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => Yii::$app->user->identity->role == User::ROLE_ADMINISTRATOR ?
                    '{view} {interview-markup} {update} {delete}' : '{interview-markup}',
                'buttons' => [
                    'interview-markup' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '', ['class' => 'glyphicon glyphicon-film',
                            'title' => 'Разметить интервью']);
                        $url = ['/respondent/interview-markup/' . $model->id];
                        return Html::a($icon, $url);
                    },
                ],
            ],
        ],
    ]); ?>

</div>