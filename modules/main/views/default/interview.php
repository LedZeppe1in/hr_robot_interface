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
/* @var $respondent app\modules\main\models\Respondent */

$this->title = 'Запись интервью';
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_camera_setting'); ?>

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
    // Код респондента
    let respondentCode = '<?= $respondent->name ?>';

    console.log(questionIds);
    console.log(questionTexts);
    console.log(questionMaximumTimes);
    console.log(questionTimes);
    console.log(questionAudioFilePaths);

    // Запуск таймера вопросов
    function startQuestionTimer(previousTime) {
        // Запуск миллисекундомера
        let time = new Date();
        questionTimer = setInterval(function() {
            let milliseconds = new Date().getTime() - time.getTime();
            // Вывод текущего времени таймера в миллисекундах
            document.querySelector("#milliseconds").innerHTML = previousTime + milliseconds;
            // Запоминание текущего времени в миллисекундах
            currentTime = previousTime + milliseconds;
            // Если время завершения вопроса меньше текущего времени
            if (buttonActivationTime <= parseInt(currentTime)) {
                // Активация кнопки следующего вопроса
                nextQuestionButton.disabled = false;
                nextQuestionButton.innerText = "Следующий вопрос";
                nextQuestionButton.classList.remove( "btn-default");
                nextQuestionButton.classList.add( "btn-primary");
                // Если текущий индекс вопроса указывает на последний калибровочный вопрос
                if (questionIndex === 3) {
                    // Скрытие кнопки следующего вопроса
                    nextQuestionButton.style.display = "none";
                    // Отображение кнопки завершения настройки камеры
                    finishCameraSetupButton.style.display = "inline-block";
                }
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
                nextQuestionButton.classList.remove( "btn-primary");
                nextQuestionButton.classList.add( "btn-default");
            }
        }, 1);
    }

    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        let num = 1;
        // Таймер времени для ответа
        let timer;
        // Запоминание времени ответа для первого вопроса
        let answerTime = parseInt(questionMaximumTimes[questionIndex]);
        // Слой с текстом информации о ходе видеоинтервью
        let answerTimeText = document.getElementById("answer-time");
        // Обновление текста информации о ходе видеоинтервью
        answerTimeText.textContent = "Начните интервью!";
        // Слой финальной фразы об ожидании результатов обработки
        let finalText = document.getElementById("final-text");
        // Аудио-плеер
        let audioPlayer = document.getElementById("audio-player");
        // Ресурс аудио-плеера
        let audioSource = document.getElementById("audio-source");
        // Слой с отображением записываемого видео
        let gumVideo = document.getElementById("gum");
        // Поле времени начала вопроса
        let landmarkStartTimeInput = document.getElementById("landmark-start_time");
        // Поле времени окончания вопроса
        let landmarkFinishTimeInput = document.getElementById("landmark-finish_time");

        // Запуск таймера для ответа
        function startAnswerTimer() {
            timer = setInterval(function () {
                answerTime = answerTime - 1000;
                answerTimeText.textContent = "Вопрос №" + num + ". Осталось на ответ: " + msToTime(answerTime, false);
                if (answerTime === 0 && num < 5 && num !== 3)
                    $("#next-question").trigger("click");
                if (answerTime === 0 && num === 3)
                    $("#finish-camera-setup").trigger("click");
                if (answerTime === 0 && num === 5)
                    $("#upload").trigger("click");
            }, 1000);
        }

        // Обработка нажатия кнопки подготовки к интервью
        $("#start-interview").click(function(e) {
            // Открытие модального окна
            $("#cameraSettingForm").modal("show");
            // Отображение кнопки начала записи интервью
            recordButton.style.display = "inline-block";
            // Скрытие кнопки подготовки к интервью
            document.querySelector('button#start-interview').style.display = "none";
        });

        // Обработка закрытия модульного окна с инструкциями по настройке оборудования
        $("#cameraSettingForm").on('hidden.bs.modal', function (e) {
            // Проигрывание аудио-файла с озвучкой не вопроса
            audioSource.src = "/web/audio/interview-preparation-audio.mp3";
            audioPlayer.load();
            audioPlayer.play();
        })

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
            // Запуск таймера вопросов
            startQuestionTimer(0);
            // Запуск таймера для ответа
            startAnswerTimer();
            // Увеличение индекса вопроса
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
                // Если текущий индекс вопроса указывает на последний калибровочный вопрос
                if (questionIndex === 3) {
                    // Скрытие кнопки завершения настройки камеры
                    this.style.display = "none";
                    // Отображение кнопки следующего вопроса
                    nextQuestionButton.style.display = "inline-block";
                    // Запуск таймера вопросов
                    startQuestionTimer(currentTime);
                    // Запуск таймера для ответа
                    startAnswerTimer();
                }
            }
            // Увеличение индекса вопроса
            questionIndex++;
        });

        // Обработка нажатия кнопки завершения интервью (загрузки)
        $("#upload").click(function(e) {
            // Определение времени окончания вопроса
            finishTime = currentTime;
            // Задание значений полей времени начала и окончания вопроса
            landmarkStartTimeInput.value = landmarkFinishTimeInput.value;
            landmarkFinishTimeInput.value = msToTime(finishTime, true);
            // Скрытие слоя текста с временем вопроса и слоев с видео
            answerTimeText.style.display = "none";
            gumVideo.style.display = "none";
            // Отображение слоя с текстом финальной фразы об ожидании результатов обработки
            finalText.style.display = "inline-block";
            // Остановка таймера
            clearInterval(timer);
            // Вызов метода отправки последнего видео ответа на вопрос на сервер
            upload();
        });

        // Обработка нажатия кнопки завершения настройки камеры
        $("#finish-camera-setup").click(function(e) {
            // Определение времени окончания вопроса
            finishTime = currentTime;
            // Задание значений полей времени начала и окончания вопроса
            if (landmarkStartTimeInput.value !== "") {
                landmarkStartTimeInput.value = landmarkFinishTimeInput.value;
                landmarkFinishTimeInput.value = msToTime(finishTime, true);
            } else {
                landmarkStartTimeInput.value = msToTime(0, true);
                landmarkFinishTimeInput.value = msToTime(finishTime, true);
            }
            // Деактивация кнопки завершения настройки камеры
            finishCameraSetupButton.disabled = true;
            finishCameraSetupButton.innerText = "Ожидание ответа...";
            finishCameraSetupButton.classList.remove( "btn-success");
            finishCameraSetupButton.classList.add( "btn-default");
            // Обновление текста информации о ходе видеоинтервью
            answerTimeText.textContent = "Обработка калибровочных вопросов...";
            // Остановка таймера
            clearInterval(timer);
            // Остановка таймера вопросов
            clearInterval(questionTimer);
            // Остановка записи видео и отправка его на сервер
            uploadCalibrationVideo();
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
        <div id="main-recommendations" class="col-md-11 well-sm well" style="font-weight: bold; padding-left: 30px; margin-left: 20px; display: none;">
            <div id="fps-recommendation" style="display: none;">
                &bull; Необходимо сменить оборудование для съемки видео-интервью. Оборудование должно обеспечивать частоту кадров в секунду более 20. Такую функциональность обеспечивают персональный компьютер, планшет и стационарный компьютер. При отсутствии подобного оборудования Вы можете изменить разрешение камеры имеющегося оборудования на 640х480 пикселей.
            </div>
            <div id="focusing-recommendation" style="display: none;">
                &bull; Необходимо сменить оборудование для съемки видео-интервью. Оборудование должно обеспечивать четкое изображение (разрешение камеры должно быть 640х480 пикселей).
            </div>
            <div id="illumination-recommendation" style="display: none;">
                &bull; Необходимо улучшить качество изображения, возможно изменить освещение в помещении - должны отсутствовать узконаправленные на респондента источники света сзади, сверху, сбоку или снизу.
            </div>
            <div id="camera-movements-recommendation" style="display: none;">
                &bull; Необходимо зафиксировать оборудование для съемки.
            </div>
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
                'label' => Yii::t('app', 'Продолжить видео-интервью'),
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
            <?= Button::widget([
                'label' => Yii::t('app', 'Завершить настройку камеры'),
                'options' => [
                    'id' => 'finish-camera-setup',
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
        <video id="recorded" style="display: none" autoplay loop playsinline></video>
    </div>

</div>