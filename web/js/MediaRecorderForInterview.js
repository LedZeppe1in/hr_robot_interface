/*
Copyright 2017 Google Inc.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

'use strict';

// This code is adapted from
// https://rawgit.com/Miguelao/demos/master/mediarecorder.html

var mediaSource = new MediaSource();
mediaSource.addEventListener('sourceopen', handleSourceOpen, false);
var RecoderedVideo = [];

var sourceBuffer;

var gumVideo = document.querySelector('video#gum');
var recordedVideo = document.querySelector('video#recorded');

var recordButton = document.querySelector('button#record');
var playButton = document.querySelector('button#play');
var uploadButton = document.querySelector('button#upload');
var downloadButton = document.querySelector('button#download');

var nextQuestionButton = document.querySelector('button#next-question'); // Кнопка следующего вопроса
var finishCameraSetupButton = document.querySelector('button#finish-camera-setup'); // Кнопка завершения настройки камеры
var startRecordButton = document.querySelector('button#start-record'); // Кнопка старта записи видео

recordButton.onclick = toggleRecording;
//playButton.onclick = play;
//uploadButton.onclick = upload;
//downloadButton.onclick = download;

console.log(location.host);
// window.isSecureContext could be used for Chrome
var isSecureOrigin = location.protocol === 'https:' || location.host.includes('localhost');

if (!isSecureOrigin) {
  alert('getUserMedia() must be run from a secure origin: HTTPS or localhost.' + '\n\nChanging protocol to HTTPS');
  location.protocol = 'HTTPS';
 }

var constraints = {audio: true,
                   video: {width: 1280,
                           height: 720,
                           frameRate: {min: 24,
                                       max: 50}}};

console.log(navigator.mediaDevices.getSupportedConstraints());

navigator.mediaDevices.getUserMedia(constraints).then(successCallback, errorCallback);

function successCallback(stream) {
  console.log('getUserMedia() got stream: ', stream);

  console.log(stream.getVideoTracks()[0].getSettings());

  window.stream = stream;
  gumVideo.srcObject = stream;
  recordButton.disabled = false;
  recordButton.innerHTML = 'Запись';
 }

function errorCallback(error) {
  console.log('navigator.getUserMedia error: ', error);
 }

function handleSourceOpen(event) {
  console.log('MediaSource opened');
  sourceBuffer = mediaSource.addSourceBuffer('video/webm; codecs="vp8"');
  console.log('Source buffer: ', sourceBuffer);
 }

function handleDataAvailable(event) {
  if ((event.data && event.data.size > 0) &&
      (RecoderedVideo.length > 0))
   {
    var ControllerOfMediaRecorder = RecoderedVideo[RecoderedVideo.length - 1];
    ControllerOfMediaRecorder.recordedBlobs.push(event.data);
   }
 }

function handleStop(event) {
  console.log('Recorder stopped: ', event);
  if (questionIndex !== 3) {
      $("#start-record").trigger("click");
  }
 }

function toggleRecording() {
  if (recordButton.textContent === 'Запись')
   {
    startRecording();
   }
  else
   {
    stopRecording();

    recordButton.textContent = 'Запись';
   }
 }

// The nested try blocks will be simplified when Chrome 47 moves to Stable
function startRecording() {
  var ControllerOfMediaRecorder = {mediaRecorder: null,
                                   recordedBlobs: null};
  RecoderedVideo[RecoderedVideo.length] = ControllerOfMediaRecorder;

  var options = {mimeType: 'video/webm;codecs=vp9', videoBitsPerSecond: 3800000};
  ControllerOfMediaRecorder.recordedBlobs = [];
  try
   {
    ControllerOfMediaRecorder.mediaRecorder = new MediaRecorder(window.stream, options);
   }
  catch (e0)
   {
    console.log('Unable to create MediaRecorder with options Object: ', options, e0);
    try
     {
      options = {mimeType: 'video/webm;codecs=vp8', bitsPerSecond: 100000};
      ControllerOfMediaRecorder.mediaRecorder = new MediaRecorder(window.stream, options);
     }
    catch (e1)
     {
      console.log('Unable to create MediaRecorder with options Object: ', options, e1);
      try
       {
        options = {mimeType: 'video/mp4; codecs="avc1.640028"', bitsPerSecond: 100000};
        ControllerOfMediaRecorder.mediaRecorder = new MediaRecorder(window.stream, options);
       }
      catch (e2)
       {
        alert('MediaRecorder is not supported by this browser.');
        console.error('Exception while creating MediaRecorder:', e2);
        return;
       }
     }
   }

  console.log('Created MediaRecorder', ControllerOfMediaRecorder.mediaRecorder, 'with options', options);

  recordButton.textContent = 'Остановить интервью';
  recordButton.style.display = "none";

  ControllerOfMediaRecorder.mediaRecorder.onstop = handleStop;
  ControllerOfMediaRecorder.mediaRecorder.ondataavailable = handleDataAvailable;
  ControllerOfMediaRecorder.mediaRecorder.start(1000); // collect 10ms of data
  console.log('MediaRecorder started', ControllerOfMediaRecorder.mediaRecorder);
 }

function stopRecording() {
  var ControllerOfMediaRecorder = RecoderedVideo[RecoderedVideo.length - 1];
  ControllerOfMediaRecorder.mediaRecorder.stop();
  console.log('Recorded Blobs: ', ControllerOfMediaRecorder.recordedBlobs);
  recordedVideo.controls = true;

  play.Index = 0;
 }

function play() {
  if (play.Index >= RecoderedVideo.length)
   {
    play.Index = 0;
    return;
   }
  else

  var ControllerOfMediaRecorder = RecoderedVideo[play.Index];
  var superBuffer = new Blob(ControllerOfMediaRecorder.recordedBlobs, {type: 'video/mp4'});
  recordedVideo.src = window.URL.createObjectURL(superBuffer);
  play.Index = play.Index + 1;
 }

function upload() {
  // Остановка таймера вопросов
  clearInterval(questionTimer);
  // Скрытие кнопки записи
  uploadButton.style.display = "none";

  stopRecording();

  var landmarkForm = document.getElementById('landmark-form');
  var blob = new Blob(RecoderedVideo[RecoderedVideo.length - 1].recordedBlobs, {type: 'video/mp4'});
  var xhr = new XMLHttpRequest();
  var formData = new FormData(landmarkForm);

  formData.append("FileToUpload", blob, Date.now() + ".mp4");
  formData.append("_csrf", _csrf);

  // отслеживаем процесс отправки
  xhr.upload.onprogress = function(event)
   {
    console.log(`Отправлено ${event.loaded} из ${event.total}`);
   };

  // Ждём завершения: неважно, успешного или нет
  xhr.onloadend = function()
   {
    if (xhr.status == 200)
     {
      console.log("Успех");
      // Отображение финальной фразы
      let finalText = document.getElementById("final-text");
      finalText.textContent = "Спасибо, Ваши ответы приняты!";
     }
    else
     {
      console.log("Ошибка " + this.status);
     }
   }

  xhr.open("POST", '/interview-analysis/' + questionIds[questionIndex - 1]);
  xhr.send(formData);
 }

function uploadVideo() {
    stopRecording();

    var landmarkForm = document.getElementById('landmark-form');
    var blob = new Blob(RecoderedVideo[RecoderedVideo.length - 1].recordedBlobs, {type: 'video/mp4'});
    var xhr = new XMLHttpRequest();
    var formData = new FormData(landmarkForm);

    formData.append("FileToUpload", blob, Date.now() + ".mp4");
    formData.append("_csrf", _csrf);

    // отслеживаем процесс отправки
    xhr.upload.onprogress = function(event)
    {
        console.log(`Отправлено ${event.loaded} из ${event.total}`);
    };

    // Ждём завершения: неважно, успешного или нет
    xhr.onloadend = function()
    {
        if (xhr.status == 200)
        {
            console.log("Успех");
        }
        else
        {
            console.log("Ошибка " + this.status);
        }
    }

    xhr.open("POST", '/interview-analysis/' + questionIds[questionIndex - 1]);
    xhr.send(formData);
}

function uploadCalibrationVideo() {
    stopRecording();

    var landmarkForm = document.getElementById('landmark-form');
    var blob = new Blob(RecoderedVideo[RecoderedVideo.length - 1].recordedBlobs, {type: 'video/mp4'});
    var xhr = new XMLHttpRequest();
    var formData = new FormData(landmarkForm);

    formData.append("FileToUpload", blob, Date.now() + ".mp4");
    formData.append("_csrf", _csrf);

    // отслеживаем процесс отправки
    xhr.upload.onprogress = function(event)
    {
        console.log(`Отправлено ${event.loaded} из ${event.total}`);
    };

    // Ждём завершения: неважно, успешного или нет
    xhr.onloadend = function()
    {
        if (xhr.status == 200)
        {
            console.log("Успех");

            // Получение ответа от сервера
            let response = xhr.responseText;
            response = JSON.parse(response);

            console.log(response.success);
            console.log(response.turnRight);
            console.log(response.turnLeft);

            // Скрытие кнопки завершения настройки камеры
            finishCameraSetupButton.style.display = "none";
            // Слой с текстом информации о ходе видеоинтервью
            let answerTimeText = document.getElementById("answer-time");
            // Если проверка калибровочных вопросов прошла успешно
            if (response.success === true && response.turnRight !== false && response.turnLeft !== false) {
                // Отображение кнопки запуска новой записи видео
                startRecordButton.style.display = "inline-block";
                // Обновление текста информации о ходе видеоинтервью
                answerTimeText.textContent = "Калибровочные вопросы успешно обработаны!";
                // Поле наличия отзеркаливания
                let mirroringField = document.getElementById("landmark-mirroring");
                // Если повороты головы определены верно, то отключение отзеркаливания
                if (response.turnRight === 0 && response.turnLeft === 1)
                    mirroringField.value = 0;
                // Если повороты головы определены с инверсией, то включаем наличие отзеркаливания
                if (response.turnRight === 1 && response.turnLeft === 0)
                    mirroringField.value = 1;
            } else {
                // Скрытие слоя текста с временем вопроса и слоя с видео
                answerTimeText.style.display = "none";
                gumVideo.style.display = "none";
                // Отображение слоя с текстом финальной фразы об ожидании результатов обработки
                let finalText = document.getElementById("final-text");
                finalText.textContent = "Спасибо за ожидание! К сожалению, Ваше видео плохого качества.";
                finalText.style.display = "inline-block";
            }
        }
        else
        {
            console.log("Ошибка " + this.status);
        }
    }

    xhr.open("POST", '/interview-analysis/' + questionIds[questionIndex - 1]);
    xhr.send(formData);
}

function download() {
  var ArrayOfLinks = [];
  var Link;
  var url;
  var a;
  for (var i = 0; i < RecoderedVideo.length; i++)
   {
    url = window.URL.createObjectURL(new Blob(RecoderedVideo[i].recordedBlobs, {type: 'video/mp4'}));
    a = document.createElement('a');
    a.style.display = 'none';
    a.href = url;
    a.download = 'test.mp4';
    document.body.appendChild(a);

    ArrayOfLinks[ArrayOfLinks.length] = {UIControl: a,
                                         URL: url};
   }

  var DelayedExecution = function(IndexOfItemToProcess)
   {
    if (IndexOfItemToProcess == ArrayOfLinks.length)
     {
      return;
     }

    ArrayOfLinks[IndexOfItemToProcess].UIControl.click();
    setTimeout(function()
                {
                 document.body.removeChild(ArrayOfLinks[IndexOfItemToProcess].UIControl);
                 window.URL.revokeObjectURL(ArrayOfLinks[IndexOfItemToProcess].URL);
                 ArrayOfLinks.splice(0, 1);
                 DelayedExecution(IndexOfItemToProcess);
                },
               5000);
   }

  DelayedExecution(0);
 }