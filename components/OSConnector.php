<?php

namespace app\components;

use Aws\Sdk;
use Aws\S3\Exception\S3Exception;

/**
 * Class OSConnector - класс для взаимодействия с хранилищем Object Storage на Yandex.Cloud.
 */
class OSConnector
{
    // Название бакета для файла базы знаний в Object Storage на Yandex.Cloud
    const OBJECT_STORAGE_KNOWLEDGE_BASE_BUCKET        = 'knowledgebase';
    // Название бакета для файлов видеоинтервью в Object Storage на Yandex.Cloud
    const OBJECT_STORAGE_VIDEO_BUCKET                 = 'videointerviews';
    // Название бакета для файлов видео ответов на вопросы в Object Storage на Yandex.Cloud
    const OBJECT_STORAGE_QUESTION_ANSWER_VIDEO_BUCKET = 'questionanswervideo';
    // Название бакета для файлов с озвучкой вопросов в Object Storage на Yandex.Cloud
    const OBJECT_STORAGE_AUDIO_BUCKET                 = 'questionvoiceactings';
    // Название бакета для json-файлов цифровых масок в Object Storage на Yandex.Cloud
    const OBJECT_STORAGE_LANDMARK_BUCKET              = 'landmarks';
    // Название бакета для json-файлов результатов определения признаков в Object Storage на Yandex.Cloud
    const OBJECT_STORAGE_DETECTION_RESULT_BUCKET      = 'detectionresults';
    // Название бакета для json-файлов результатов интерпретации признаков в Object Storage на Yandex.Cloud
    const OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET = 'interpretationresults';

    // Ключ и шифр для севрвисного аккаунта (hrrrobotuserforobjectstorage) в Object Storage на Yandex.Cloud
    const OBJECT_STORAGE_KEY    = 'IZnZSrNDYYbkZRDyAtZ9';
    const OBJECT_STORAGE_SECRET = 'EsbUgm4uGMnBtwc5bTqBsfbhSgnesPQrX6YGVAHH';

    // Настройки для подключения Object Storage на Yandex.Cloud
    const AWS_ACCESS_REGION   = 'us-east-1';                       // Название региона
    const AWS_ACCESS_ENDPOINT = 'http://storage.yandexcloud.net/'; // Название конечной точки
    const AWS_ACCESS_VERSION  = 'latest';                          // Название версии

    // Конфигурация подключения к Object Storage на Yandex.Cloud
    protected $sharedConfig = [
        'credentials' => [
            'key'     => self::OBJECT_STORAGE_KEY,
            'secret'  => self::OBJECT_STORAGE_SECRET,
        ],
        'region'   => self::AWS_ACCESS_REGION,
        'endpoint' => self::AWS_ACCESS_ENDPOINT,
        'version'  => self::AWS_ACCESS_VERSION,
    ];

    /**
     * Сохранение объекта файла в Object Storage на Yandex.Cloud.
     *
     * @param $bucketName - название бакета
     * @param $path - название папки в бакете (соответствует id записи из БД)
     * @param $fileName - имя файла с расширением без пути
     * @param $file - содержимое файла
     */
    public function saveFileToObjectStorage($bucketName, $path, $fileName, $file)
    {
        $sdk = new Sdk($this->sharedConfig);
        $s3Client = $sdk->createS3();
        try {
            $content = $file;
            // Если пришел массив
            if (is_array($file))
                $content = json_encode($file, JSON_UNESCAPED_UNICODE);
            // Если пришел не json-текст, а файл
            //if (is_string($file) && !is_array(json_decode($file, true)))
            else
                if (file_exists($file))
                    $content = fopen($file, 'r');
            $s3Client->putObject([
                'Bucket' => $bucketName,
                'Key' => ($path != null) ? $path . '/' . $fileName : $fileName,
                'Body' => $content,
            ]);
        } catch (S3Exception $e) {
            echo "При сохранении файла произошла ошибка.\n";
        }
    }

    /**
     * Удаление объекта файла из Object Storage на Yandex.Cloud.
     *
     * @param $bucketName - название бакета
     * @param $path - название папки в бакете (соответствует id записи из БД)
     * @param $fileName - имя файла с расширением без пути
     */
    public function removeFileFromObjectStorage($bucketName, $path, $fileName)
    {
        $sdk = new Sdk($this->sharedConfig);
        $s3Client = $sdk->createS3();
        try {
            $s3Client->deleteObject([
                'Bucket' => $bucketName,
                'Key' => $path . '/' . $fileName,
            ]);
        } catch (S3Exception $e) {
            echo "При удалении файла произошла ошибка.\n";
        }
    }

    /**
     * Получение содержимого объекта файла из Object Storage на Yandex.Cloud.
     *
     * @param $bucketName - название бакета
     * @param $path - название папки в бакете (соответствует id записи из БД)
     * @param $fileName - имя файла с расширением без пути
     * @return bool|mixed - содержимое объекта файла
     */
    public function getFileContentFromObjectStorage($bucketName, $path, $fileName)
    {
        $sdk = new Sdk($this->sharedConfig);
        $s3Client = $sdk->createS3();
        try {
            $result = $s3Client->getObject([
                'Bucket' => $bucketName,
                'Key' => ($path != null) ? $path . '/' . $fileName : $fileName,
            ]);

            return $result["Body"];
        } catch (S3Exception $e) {
            echo "При получении содержимого файла произошла ошибка.\n";
            echo $fileName;
        }

        return false;
    }

    /**
     * Скачивание объекта файла из Object Storage на Yandex.Cloud.
     *
     * @param $bucketName - название бакета
     * @param $path - название папки в бакете (соответствует id записи из БД)
     * @param $fileName - имя файла с расширением без пути
     * @return mixed - файл с Object Storage
     */
    public function downloadFileFromObjectStorage($bucketName, $path, $fileName)
    {
        $sdk = new Sdk($this->sharedConfig);
        $s3Client = $sdk->createS3();
        try {
            $result = $s3Client->getObject([
                'Bucket' => $bucketName,
                'Key' => ($path != null) ? $path . '/' . $fileName : $fileName,
            ]);
            // Установка типа контента при скачивании файла
            header('Access-Control-Allow-Origin: *'); // Реализация кроссдоменных запросов XMLHTTPRequest
            header('Content-Description: File Transfer');
            header("Content-Type: {$result['ContentType']}");
            header('Content-Disposition: attachment; filename=' . $fileName);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            // Отправление файла в браузер для скачивания
            return $result["Body"];
        } catch (S3Exception $e) {
            echo "При скачивании файла произошла ошибка.\n";
        }

        return false;
    }

    /**
     * Сохранение объекта файла из Object Storage на сервер Yandex.Cloud.
     *
     * @param $bucketName - название бакета
     * @param $path - название папки в бакете (соответствует id записи из БД)
     * @param $fileName - имя файла с расширением без пути
     * @param $serverPath - название папки на сервере
     * @return bool|mixed - файл с Object Storage
     */
    public function saveFileToServer($bucketName, $path, $fileName, $serverPath)
    {
        $sdk = new Sdk($this->sharedConfig);
        $s3Client = $sdk->createS3();
        try {
            $result = $s3Client->getObject([
                'Bucket' => $bucketName,
                'Key' => ($path != null) ? $path . '/' . $fileName : $fileName,
                'Range' => 1000000000000,
                'SaveAs' => $serverPath . $fileName
            ]);

            return $result["Body"];
        } catch (S3Exception $e) {
            echo "При сохранении файла на сервер произошла ошибка.\n";
            echo $fileName;
        }

        return false;
    }
}