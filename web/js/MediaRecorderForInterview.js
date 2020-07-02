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

var mediaRecorder;
var recordedBlobs;
var sourceBuffer;

var gumVideo = document.querySelector('video#gum');
var recordedVideo = document.querySelector('video#recorded');

var recordButton = document.querySelector('button#record');
var uploadButton = document.querySelector('button#upload');

recordButton.onclick = toggleRecording;
uploadButton.onclick = upload;

console.log(location.host);
// window.isSecureContext could be used for Chrome
var isSecureOrigin = location.protocol === 'https:' || location.host.includes('localhost');

if (!isSecureOrigin)
 {
  alert('getUserMedia() must be run from a secure origin: HTTPS or localhost.' + '\n\nChanging protocol to HTTPS');
  location.protocol = 'HTTPS';
 }

var constraints = {audio: true,
                   video: {width: 1280,
                           height: 720,
                           frameRate: {min: 24, 
                                       max: 50}}};

navigator.mediaDevices.getUserMedia(constraints).then(successCallback, errorCallback);

function successCallback(stream)
 {
  console.log('getUserMedia() got stream: ', stream);
  window.stream = stream;
  gumVideo.srcObject = stream;
  recordButton.disabled = false;
  recordButton.innerHTML = 'Начать интервью';
 }

function errorCallback(error)
 {
  console.log('navigator.getUserMedia error: ', error);
 }

function handleSourceOpen(event)
 {
  console.log('MediaSource opened');
  sourceBuffer = mediaSource.addSourceBuffer('video/webm; codecs="vp8"');
  console.log('Source buffer: ', sourceBuffer);
 }

function handleDataAvailable(event)
 {
  if (event.data && event.data.size > 0)
   {
    recordedBlobs.push(event.data);
   }
 }

function handleStop(event)
 {
  console.log('Recorder stopped: ', event);
 }

function toggleRecording()
 {
  if (recordButton.textContent === 'Начать интервью')
   {
    startRecording();
   }
  else
   {
    stopRecording();

    recordButton.textContent = 'Начать интервью';
   }
 }

// The nested try blocks will be simplified when Chrome 47 moves to Stable
function startRecording() 
 {
  var options = {mimeType: 'video/webm;codecs=vp9', videoBitsPerSecond: 3800000};
  recordedBlobs = [];
  try
   {
    mediaRecorder = new MediaRecorder(window.stream, options);
   } 
  catch (e0)
   {
    console.log('Unable to create MediaRecorder with options Object: ', options, e0);
    try
     {
      options = {mimeType: 'video/webm;codecs=vp8', bitsPerSecond: 100000};
      mediaRecorder = new MediaRecorder(window.stream, options);
     }
    catch (e1)
     {
      console.log('Unable to create MediaRecorder with options Object: ', options, e1);
      try
       {
        options = {mimeType: 'video/mp4; codecs="avc1.640028"', bitsPerSecond: 100000};
        mediaRecorder = new MediaRecorder(window.stream, options);
       }
      catch (e2)
       {
        alert('MediaRecorder is not supported by this browser.');
        console.error('Exception while creating MediaRecorder:', e2);
        return;
       }
     }
   }

  console.log('Created MediaRecorder', mediaRecorder, 'with options', options);

  recordButton.textContent = 'Остановить интервью';
  recordButton.style.display = "none";

  mediaRecorder.onstop = handleStop;
  mediaRecorder.ondataavailable = handleDataAvailable;
  mediaRecorder.start(10000); // collect 10ms of data
  console.log('MediaRecorder started', mediaRecorder);
 }

function stopRecording()
 {
  mediaRecorder.stop();
  console.log('Recorded Blobs: ', recordedBlobs);
  recordedVideo.controls = true;
 }

function play()
 {
  var superBuffer = new Blob(recordedBlobs, {type: 'video/mp4'});
  recordedVideo.src = window.URL.createObjectURL(superBuffer);
 }

function upload()
 {
  uploadButton.disabled = true;
  uploadButton.innerText = "Ожидание результатов...";

  stopRecording();

  var videoInterviewform = document.getElementById('video-interview-form');

  var blob = new Blob(recordedBlobs, {type: 'video/mp4'});

  var xhr = new XMLHttpRequest();
  var formData = new FormData(videoInterviewform);
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

  xhr.open("POST", '/interview-analysis/' + videoInterviewId);
  xhr.send(formData);
 }

function download()
 {
  var blob = new Blob(recordedBlobs, {type: 'video/mp4'});
  var url = window.URL.createObjectURL(blob);
  var a = document.createElement('a');
  a.style.display = 'none';
  a.href = url;
  a.download = 'test.mp4';
  document.body.appendChild(a);
  a.click();

  setTimeout(function()
              {
               document.body.removeChild(a);
               window.URL.revokeObjectURL(url);
              },
             1000);
 }