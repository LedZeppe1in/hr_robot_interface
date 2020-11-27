<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\bootstrap\Nav;
use yii\helpers\Html;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="Content-Type" content="text/html">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>

    <div class="wrap">
        <?php
        NavBar::begin([
            'brandLabel' => Yii::$app->name,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-left'],
            'encodeLabels' => false,
            'items' => array_filter([
                !Yii::$app->user->isGuest ? ['label' => 'Данные', 'url' => '#',
                    'items' => [
                        ['label' => 'Респонденты', 'url' => ['/respondent/list']],
                        ['label' => 'Заказчики', 'url' => ['/customer/list']],
                        ['label' => 'Видеоинтервью', 'url' => ['/video-interview/list']],
                        ['label' => 'Вопросы', 'url' => ['/test-question/list']],
                        ['label' => 'Видео на вопросы', 'url' => ['/question/list']],
                        ['label' => 'Цифровые маски', 'url' => ['/landmark/list']],
                        ['label' => 'Базы знаний', 'url' => ['/knowledge-base/list']],
                        '<li class="divider"></li>',
                        ['label' => 'Тестовый запуск', 'url' => '/test'],
                    ],
                ] : false,
                !Yii::$app->user->isGuest ? ['label' => 'Обработка', 'url' => '#',
                    'items' => [
                        ['label' => 'Состояние обработки видеоинтервью',
                            'url' => ['/video-interview-processing-status/list']],
                        ['label' => 'Состояние обработки видео по вопросам',
                            'url' => ['/question-processing-status/list']],
                    ],
                ] : false,
                !Yii::$app->user->isGuest ? ['label' => 'Результаты', 'url' => '#',
                    'items' => [
                        ['label' => 'Результаты определения и интерпретации признаков',
                            'url' => ['/analysis-result/list']],
                        ['label' => 'Результаты определения признаков', 'url' => ['/detection-result/list']],
                        ['label' => 'Результаты интерпретации признаков', 'url' => ['/interpretation-result/list']],
                        '<li class="divider"></li>',
                        ['label' => 'Итоговые заключения по тесту мотивации к труду',
                            'url' => ['/gerchikov-test-conclusion/list']],
                        ['label' => 'Итоговые заключения по видеоинтервью',
                            'url' => ['/final-conclusion/list']],
                    ],
                ] : false,
                !Yii::$app->user->isGuest ? ['label' => 'Редактор цифровой маски', 'url' => '#',
                    'items' => [
                        ['label' => 'Редактор цифровой маски (Иван)',
                            'url' => 'https://84.201.129.65:8080/HRRMaskEditor/MaskDrawIvan.html'],
                        ['label' => 'Редактор цифровой маски (Андрей)',
                            'url' => 'https://84.201.129.65:8080/HRRMaskEditor/MaskDrawAndr.html'],
                        ['label' => 'Сравнение алгоритмов Иван и Андрей',
                            'url' => 'https://84.201.129.65:8080/HRRMaskEditor/MaskDrawIvanAndAndrey.html'],
                        ['label' => 'Редактор цифровой маски отфильтрованное (Андрей)',
                            'url' => 'https://84.201.129.65:8080/HRRMaskEditor/MaskDrawAndr3.html'],
                        ['label' => 'Оценка цифровой маски',
                            'url' => 'https://84.201.129.65:8080/HRRMaskEditor/MaskWithResult.html'],
                    ],
                ] : false,
                !Yii::$app->user->isGuest ? ['label' => 'Редактор тестов', 'url' => '#',
                    'items' => [
                        ['label' => 'Редактор опросов',
                            'url' => 'https://84.201.129.65:8080/HRRMaskEditor/Sandbox/PollEditor.php'],
                        ['label' => 'Генератор теста мотивации к труду',
                            'url' => 'https://84.201.129.65:8080/HRRTester/GenerateR1Test.php'],
                    ],
                ] : false,
            ]),
        ]);

        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],
            'encodeLabels' => false,
            'items' => [
                Yii::$app->user->isGuest ? ['label' => '<span class="glyphicon glyphicon-log-in"></span> Вход',
                    'url' => ['/main/default/sing-in']] : ['label' => '<span class="glyphicon glyphicon-user"></span> Аккаунт',
                    'url' => ['#'], 'items' => [
                        ['label' => '<span class="glyphicon glyphicon-cog"></span> Настройки',
                            'url' => '#'],
                        ['label' => '<span class="glyphicon glyphicon-log-out"></span> Выход' .
                            ' (' . Yii::$app->user->identity->username . ')', 'url' => ['/main/default/sing-out'],
                                'linkOptions' => ['data-method' => 'post']]
                    ],
                ],
            ],
        ]);

        NavBar::end();
        ?>

        <div class="container">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="pull-left"><?= ' &copy; ' . date('Y') . ' HR Robot Team' ?></p>
            <p class="pull-right">
                Разработано
                <a href="mailto:DorodnyxNikita@gmail.com"><?= Yii::$app->params['adminEmail'] ?></a>
            </p>
        </div>
    </footer>

    <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>