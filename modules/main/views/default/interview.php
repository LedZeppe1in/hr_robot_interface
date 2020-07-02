<?php

use yii\helpers\Html;
use yii\bootstrap\Button;

/* @var $this yii\web\View */
/* @var $videoInterviewModel app\modules\main\models\VideoInterview */
/* @var $gerchikovTestConclusionModel app\modules\main\models\GerchikovTestConclusion */
/* @var $landmarkModels app\modules\main\models\Landmark */
/* @var $questionTexts app\modules\main\controllers\DefaultController */
/* @var $questionTimes app\modules\main\controllers\DefaultController */
/* @var $questionAudioFilePaths app\modules\main\controllers\DefaultController */
/* @var $answerMaxTimes app\modules\main\controllers\DefaultController */

$this->title = 'Запись интервью';
$this->params['breadcrumbs'][] = $this->title;
?>

<script type="text/javascript">
    // CSRF-токен
    let _csrf = '<?= Yii::$app->request->csrfToken ?>';

    // id видеоинтервью
    let videoInterviewId = '<?= $videoInterviewModel->id ?>';

    // Перевод миллисекунд в формат времени
    function msToTime(s, flag) {
        function pad(n, z) {
            z = z || 2;
            return ("00" + n).slice(-z);
        }
        let ms = s % 1000;
        s = (s - ms) / 1000;
        let secs = s % 60;
        s = (s - secs) / 60;
        let mins = s % 60;
        let hrs = (s - mins) / 60;

        if (flag)
            return pad(hrs) + ":" + pad(mins) + ":" + pad(secs) + ":" + pad(ms, 3);
        else
            return pad(hrs) + ":" + pad(mins) + ":" + pad(secs);
    }

    // Продолжительность ответа по времени
    let answerDuration = 5000;
    // Текущее время
    let currentTime = 0;
    // Время начала вопроса
    let startTime = 0;
    // Время завершения вопроса
    let finishTime = 0;
    // Порядковый номер вопроса
    let questionIndex = 0;
    // Массив текстов вопросов
    let questionTexts = [<?php echo '"' . implode('","', $questionTexts) . '"' ?>];
    // Массив с продолжительностями вопросов по времени
    let questionTimes = [<?php echo '"' . implode('","', $questionTimes) . '"' ?>];
    // Массив с путями до аудио-файлов с озвучкой вопросов
    let questionAudioFilePaths = [<?php echo '"' . implode('","', $questionAudioFilePaths) . '"' ?>];
    // Массив с продолжительностями ответов на вопросы по времени
    let answerTimes = [<?php echo '"' . implode('","', $answerMaxTimes) . '"' ?>];

    console.log(questionTexts);
    console.log(questionTimes);
    console.log(questionAudioFilePaths);
    console.log(answerTimes);

    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        let num = 1;
        // Таймер времени ответа
        let timer;
        // Запоминание времени ответа для первого вопроса
        let answerTime = parseInt(answerTimes[questionIndex]) / 6;
        // Слой таймера ответа
        let answerTimeText = document.getElementById("answer-time");
        // Установка таймера по первому времени ответа
        answerTimeText.textContent = "Начните интервью!";
        // Слой текста вопроса
        let currentQuestionText = document.getElementById("current-question-text");
        // Кнопка следующего вопроса
        let nextQuestionButton = document.getElementById("next-question");
        // Кнопка завершения интервью (загрузки)
        let uploadButton = document.getElementById("upload");
        // Аудио-плеер
        let audioPlayer = document.getElementById("audio-player");
        // Ресурс аудио-плеера
        let audioSource = document.getElementById("audio-source");

        // Обработка нажатия кнопки начала записи интервью
        $("#record").click(function(e) {
            // Установка таймера по первому времени ответа
            answerTimeText.textContent = "Вопрос №" + num + ". Осталось на ответ: " + msToTime(answerTime * 1000, false);
            // Отображение кнопки следующего вопроса
            nextQuestionButton.style.display = "inline-block";
            // Отображение текста вопроса
            currentQuestionText.textContent = "Вопрос №" + (questionIndex + 1) + ": " + questionTexts[questionIndex];
            //currentQuestionText.style.display = "inline-block";
            // Задание значения поля времени начала вопроса
            $("#landmark-" + questionIndex + "-start_time").val(msToTime(startTime, true));
            // Определение времени завершения вопроса
            finishTime = parseInt(questionTimes[questionIndex]) + answerDuration;
            // Задание значения поля времени завершения вопроса
            $("#landmark-" + questionIndex + "-finish_time").val(msToTime(finishTime, true));
            // Проигрывание аудио-файла с озвучкой вопроса
            audioSource.src = "/web/audio/" + questionAudioFilePaths[questionIndex];
            audioPlayer.load();
            audioPlayer.play();
            // Запуск миллисекундомера
            const time = new Date();
            setInterval(function() {
                const milliseconds = new Date().getTime() - time.getTime();
                document.querySelector("#milliseconds").innerHTML = milliseconds;
                // Запоминание текущего времени в миллисекундах
                currentTime = milliseconds;
                // Если время завершения вопроса пеньше текущего времени
                if (finishTime <= parseInt(currentTime)) {
                    // Активация кнопки следующего вопроса
                    nextQuestionButton.disabled = false;
                    nextQuestionButton.innerText = "Следующий вопрос";
                    // Если вопросов больше нет
                    if (questionTexts.indexOf(questionTexts[questionIndex]) === -1) {
                        // Скрытие кнопки следующего вопроса
                        nextQuestionButton.style.display = "none";
                        // Отображение завершения интервью (загрузки)
                        uploadButton.style.display = "inline-block";
                    }
                } else {
                    // Деактивация кнопки следующего вопроса
                    nextQuestionButton.disabled = true;
                    nextQuestionButton.innerText = "Ожидание ответа...";
                }
            }, 1);
            // Запуск таймера для ответа
            timer = setInterval(function () {
                let sec = --answerTime;
                let msec = sec * 1000;
                answerTimeText.textContent = "Вопрос №" + num + ". Осталось на ответ: " + msToTime(msec, false);
                if (sec === 0 && num < 10)
                    $("#next-question").trigger("click");
                if (sec === 0 && num === 10)
                    $("#upload").trigger("click");
            }, 1000);
            questionIndex++;
        });

        // Обработка нажатия кнопки следующего вопроса
        $("#next-question").click(function(e) {
            if (questionTexts.indexOf(questionTexts[questionIndex]) !== -1) {
                // Задание текста вопроса
                currentQuestionText.textContent = "Вопрос №" + (questionIndex + 1) + ": " + questionTexts[questionIndex];
                // Определение времени начала вопроса
                startTime = currentTime;
                // Задание значения поля времени начала вопроса
                $("#landmark-" + questionIndex + "-start_time").val(msToTime(startTime, true));
                // Определение времени завершения вопроса
                finishTime = startTime + parseInt(questionTimes[questionIndex]) + answerDuration;
                // Задание значения поля времени завершения вопроса
                $("#landmark-" + questionIndex + "-finish_time").val(msToTime(finishTime, true));
                // Проигрывание аудио-файла с озвучкой вопроса
                audioSource.src = "/web/audio/" + questionAudioFilePaths[questionIndex];
                audioPlayer.load();
                audioPlayer.play();
                // Запоминание времени для текущего ответа
                answerTime = parseInt(answerTimes[questionIndex]) / 6;
                num++;
            }
            questionIndex++;
        });

        // Обработка нажатия кнопки завершения интервью (загрузки)
        $("#upload").click(function(e) {
            // Остановка таймера
            clearInterval(timer);
            answerTimeText.textContent = "Ваши ответы приняты, не закрывайте страницу и ожидайте результат...";
        });
    });
</script>

<!-- Подключение js-скрипта -->
<?php $this->registerJsFile('/js/MediaRecorderForInterview.js') ?>

<div class="interview">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-2">
                <?= Button::widget([
                    'label' => Yii::t('app', 'Подготовка камеры...'),
                    'options' => [
                        'id' => 'record',
                        'class' => 'btn-success',
                        'style' => 'margin:5px',
                        'disabled' => 'disabled'
                    ]
                ]); ?>
                <?= Button::widget([
                    'label' => Yii::t('app', 'Следующий вопрос'),
                    'options' => [
                        'id' => 'next-question',
                        'class' => 'btn-primary',
                        'disabled' => 'disabled',
                        'style' => 'margin:5px; display:none'
                    ]
                ]); ?>
                <?= Button::widget([
                    'label' => Yii::t('app', 'Завершить интервью'),
                    'options' => [
                        'id' => 'upload',
                        'class' => 'btn-success',
                        'style' => 'margin:5px; display:none'
                    ]
                ]); ?>
        </div>
        <div id="answer-time" class="col-md-4 well-sm well" style="font-weight: bold; padding-left: 30px; margin-left: 20px;"></div>
    </div>

    <div id="milliseconds" style="display: none">0</div>
    <div id="current-question-text" class="well" style="display: none"></div>

    <audio id="audio-player" style="display: none" controls>
        <source id="audio-source" src="" type="audio/mpeg">
    </audio>

    <?= $this->render('_interview_form', [
        'videoInterviewModel' => $videoInterviewModel,
        'landmarkModels' => $landmarkModels,
        'questionTexts' => $questionTexts
    ]) ?>

    <div class="row">
        <video id="gum" class="col-sm-12" autoplay muted playsinline></video>
        <video id="recorded" autoplay loop playsinline></video>
    </div>

</div>