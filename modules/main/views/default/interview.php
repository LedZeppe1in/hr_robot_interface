<?php

use yii\helpers\Html;
use yii\bootstrap\Button;

/* @var $this yii\web\View */
/* @var $videoInterviewModel app\modules\main\models\VideoInterview */
/* @var $gerchikovTestConclusionModel app\modules\main\models\GerchikovTestConclusion */
/* @var $landmarkModel app\modules\main\models\Landmark */
/* @var $questionIds app\modules\main\controllers\DefaultController */
/* @var $questionTexts app\modules\main\controllers\DefaultController */
/* @var $questionMaximumTimes app\modules\main\controllers\DefaultController */
/* @var $questionTimes app\modules\main\controllers\DefaultController */
/* @var $questionAudioFilePaths app\modules\main\controllers\DefaultController */

$this->title = 'Запись интервью';
$this->params['breadcrumbs'][] = $this->title;
?>

<script type="text/javascript">
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

    // CSRF-токен
    let _csrf = '<?= Yii::$app->request->csrfToken ?>';

    // id видеоинтервью
    let videoInterviewId = '<?= $videoInterviewModel->id ?>';

    // Таймер времени для вопросов
    let questionTimer;
    // Продолжительность ответа по времени
    let answerDuration = 5000;
    // Текущее время
    let currentTime = 0;
    // Время завершения вопроса
    let finishTime = 0;
    // Время активации кнопки следующего вопроса
    let buttonActivationTime = 0;
    // Порядковый номер вопроса
    let questionIndex = 0;
    // Массив id вопросов
    let questionIds = [<?php echo '"' . implode('","', $questionIds) . '"' ?>];
    // Массив текстов вопросов
    let questionTexts = [<?php echo '"' . implode('","', $questionTexts) . '"' ?>];
    // Массив с продолжительностями ответов на вопросы по времени
    let questionMaximumTimes = [<?php echo '"' . implode('","', $questionMaximumTimes) . '"' ?>];
    // Массив с продолжительностями вопросов по времени
    let questionTimes = [<?php echo '"' . implode('","', $questionTimes) . '"' ?>];
    // Массив с путями до аудио-файлов с озвучкой вопросов
    let questionAudioFilePaths = [<?php echo '"' . implode('","', $questionAudioFilePaths) . '"' ?>];

    console.log(questionIds);
    console.log(questionTexts);
    console.log(questionMaximumTimes);
    console.log(questionTimes);
    console.log(questionAudioFilePaths);

    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        let num = 1;
        // Таймер времени для ответа
        let timer;
        // Запоминание времени ответа для первого вопроса
        let answerTime = parseInt(questionMaximumTimes[questionIndex]);
        // Слой таймера ответа
        let answerTimeText = document.getElementById("answer-time");
        // Установка таймера по первому времени ответа
        answerTimeText.textContent = "Начните интервью!";
        // Слой финальной фразы об ожидании результатов обработки
        let finalText = document.getElementById("final-text");
        // Кнопка подготовки к интервью
        let startInterviewButton = document.getElementById("start-interview");
        // Кнопка начала интервью (записи видео)
        let recordButton = document.getElementById("record");
        // Кнопка следующего вопроса
        let nextQuestionButton = document.getElementById("next-question");
        // Кнопка завершения интервью (загрузки)
        let uploadButton = document.getElementById("upload");
        // Аудио-плеер
        let audioPlayer = document.getElementById("audio-player");
        // Ресурс аудио-плеера
        let audioSource = document.getElementById("audio-source");
        // Слой с отображением записываемого видео
        let gumVideo = document.getElementById("gum");
        // Слой с записанным видео
        let recordedVideo = document.getElementById("recorded");
        // Поле времени начала вопроса
        let landmarkStartTimeInput = document.getElementById("landmark-start_time");
        // Поле времени окончания вопроса
        let landmarkFinishTimeInput = document.getElementById("landmark-finish_time");

        // Обработка нажатия кнопки подготовки к интервью
        $("#start-interview").click(function(e) {
            // Отображение кнопки начала записи интервью
            recordButton.style.display = "inline-block";
            // Скрытие кнопки подготовки к интервью
            startInterviewButton.style.display = "none";
            // Проигрывание аудио-файла с озвучкой не вопроса
            audioSource.src = "/web/audio/interview-preparation-audio.mp3";
            audioPlayer.load();
            audioPlayer.play();
        });

        // Обработка нажатия кнопки начала записи интервью
        $("#record").click(function(e) {
            // Установка таймера по первому времени ответа
            answerTimeText.textContent = "Вопрос №" + num + ". Осталось на ответ: " + msToTime(answerTime, false);
            // Отображение кнопки следующего вопроса
            nextQuestionButton.style.display = "inline-block";
            // Определение нового времени активации кнопки следующего вопроса
            buttonActivationTime = parseInt(questionTimes[questionIndex]) + answerDuration;
            // Проигрывание аудио-файла с озвучкой вопроса
            audioSource.src = "/web/audio/" + questionAudioFilePaths[questionIndex];
            audioPlayer.load();
            audioPlayer.play();
            // Запуск миллисекундомера
            const time = new Date();
            questionTimer = setInterval(function() {
                const milliseconds = new Date().getTime() - time.getTime();
                document.querySelector("#milliseconds").innerHTML = milliseconds;
                // Запоминание текущего времени в миллисекундах
                currentTime = milliseconds;
                // Если время завершения вопроса меньше текущего времени
                if (buttonActivationTime <= parseInt(currentTime)) {
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
                answerTime = answerTime - 1000;
                answerTimeText.textContent = "Вопрос №" + num + ". Осталось на ответ: " + msToTime(answerTime, false);
                if (answerTime === 0 && num < 3)
                    $("#next-question").trigger("click");
                if (answerTime === 0 && num === 3)
                    $("#upload").trigger("click");
            }, 1000);
            questionIndex++;
        });

        // Обработка нажатия кнопки следующего вопроса
        $("#next-question").click(function(e) {
            // Определение времени окончания вопроса
            finishTime = currentTime;
            // Задание значений полей времени начала и окончания вопроса
            if (landmarkStartTimeInput.value !== "") {
                landmarkStartTimeInput.value = landmarkFinishTimeInput.value;
                landmarkFinishTimeInput.value = msToTime(finishTime, true);
            } else {
                console.log("TIME: " + msToTime(0, true));
                landmarkStartTimeInput.value = msToTime(0, true);
                landmarkFinishTimeInput.value = msToTime(finishTime, true);
            }
            // Остановка записи видео и отправка его на сервер
            uploadVideo();
        });

        // Обработка нажатия кнопки записи видео ответа на вопрос
        $("#start-record").click(function(e) {
            if (questionTexts.indexOf(questionTexts[questionIndex]) !== -1) {
                // Старт записи нового видео
                startRecording();
                // Определение нового времени активации кнопки следующего вопроса
                buttonActivationTime = finishTime + parseInt(questionTimes[questionIndex]) + answerDuration;
                // Проигрывание аудио-файла с озвучкой вопроса
                audioSource.src = "/web/audio/" + questionAudioFilePaths[questionIndex];
                audioPlayer.load();
                audioPlayer.play();
                // Запоминание времени для текущего ответа
                answerTime = parseInt(questionMaximumTimes[questionIndex]);
                num++;
            }
            questionIndex++;
        });

        // Обработка нажатия кнопки завершения интервью (загрузки)
        $("#upload").click(function(e) {
            // Определение времени окончания вопроса
            finishTime = currentTime;
            // Задание значений полей времени начала и окончания вопроса
            landmarkStartTimeInput.value = landmarkFinishTimeInput.value;
            landmarkFinishTimeInput.value = msToTime(finishTime, true);
            // Вызов метода отправки последнего видео ответа на вопрос на сервер
            upload();
            // Остановка таймера
            clearInterval(timer);
            // Скрытие слоя текста с временем вопроса и слоев с видео
            answerTimeText.style.display = "none";
            gumVideo.style.display = "none";
            recordedVideo.style.display = "none";
            // Отображение слоя с текстом финальной фразы об ожидании результатов обработки
            finalText.style.display = "inline-block";
        });
    });
</script>

<!-- Подключение js-скрипта -->
<?php $this->registerJsFile('/js/MediaRecorderForInterview.js') ?>

<div class="interview">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div id="answer-time" class="col-md-4 well-sm well" style="font-weight: bold; padding-left: 30px; margin-left: 20px;"></div>
        <div id="final-text" class="col-md-11 well-sm well" style="font-weight: bold; padding-left: 30px; margin-left: 20px; display: none">
            Пожалуйста подождите. Отправка Ваших ответов может занять некоторое время.
        </div>
        <div class="col-md-2">
            <?= Button::widget([
                'label' => Yii::t('app', 'Начать интервью'),
                'options' => [
                    'id' => 'start-interview',
                    'class' => 'btn-success',
                    'style' => 'margin:5px',
                ]
            ]); ?>
            <?= Button::widget([
                'label' => Yii::t('app', 'Подготовка камеры...'),
                'options' => [
                    'id' => 'record',
                    'class' => 'btn-success',
                    'disabled' => 'disabled',
                    'style' => 'margin:5px; display:none'
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
                'label' => Yii::t('app', 'Старт записи'),
                'options' => [
                    'id' => 'start-record',
                    'class' => 'btn-success',
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
    </div>

    <div id="milliseconds" style="display: none">0</div>

    <audio id="audio-player" style="display: none" controls>
        <source id="audio-source" src="" type="audio/mpeg">
    </audio>

    <?= $this->render('_interview_form', [
        'videoInterviewModel' => $videoInterviewModel,
        'landmarkModel' => $landmarkModel
    ]) ?>

    <div class="row">
        <video id="gum" class="col-sm-12" autoplay muted playsinline></video>
        <video id="recorded" autoplay loop playsinline></video>
    </div>

</div>