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
            'items' => [
                ['label' => 'Данные', 'url' => '#',
                    'items' => [
                        ['label' => 'Респонденты', 'url' => ['/respondent/list']],
                        ['label' => 'Заказчики', 'url' => ['/customer/list']],
                        ['label' => 'Видеоинтервью', 'url' => ['/video-interview/list']],
                        ['label' => 'Цифровые маски', 'url' => ['/landmark/list']],
                        ['label' => 'Вопросы', 'url' => ['/question/list']],
                        ['label' => 'Базы знаний', 'url' => ['/knowledge-base/list']],
                        ['label' => 'Тестовый запуск', 'url' => '/test'],
                    ],
                ],
                ['label' => 'Результаты анализа', 'url' => '#',
                    'items' => [
                        ['label' => 'Результаты определения и интерпретации признаков',
                            'url' => ['/analysis-result/list']],
                        ['label' => 'Результаты определения признаков', 'url' => ['/detection-result/list']],
                        ['label' => 'Результаты интерпретации признаков', 'url' => ['/interpretation-result/list']],
                    ],
                ],
                ['label' => 'Итоговые результаты', 'url' => '#',
                    'items' => [
                        ['label' => 'Итоговые заключения по тесту мотивации к труду',
                            'url' => ['/gerchikov-test-conclusion/list']],
                        ['label' => 'Итоговые заключения по видеоинтервью',
                            'url' => ['/final-conclusion/list']],
                    ],
                ],
                ['label' => 'Редактор цифровой маски', 'url' => '#',
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
                ],
                ['label' => 'Редактор тестов', 'url' => '#',
                    'items' => [
                        ['label' => 'Редактор простых вопросов',
                            'url' => 'https://84.201.129.65:8080/HRRTester/PollQuestionEditor.html'],
                        ['label' => 'Редактор составных вопросов',
                            'url' => 'https://84.201.129.65:8080/HRRTester/PollQuestionBunchEditor.html'],
                        ['label' => 'Список файлов с вопросами',
                            'url' => 'https://84.201.129.65:8080/HRRTester/Polls/'],
                        ['label' => 'Отображение опроса',
                            'url' => 'https://84.201.129.65:8080/HRRTester/GenerateR1Test.php?pollmodel=someTest.json'],
                        ['label' => 'Генератор теста мотивации к труду',
                            'url' => 'https://84.201.129.65:8080/HRRTester/GenerateR1Test.php'],
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