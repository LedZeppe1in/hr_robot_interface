<?php

namespace app\components;

use Aws\Sdk;
use Aws\S3\Exception\S3Exception;

/**
 * Class OSConnector - класс для взаимодействия с хранилищем Object Storage на Yandex.Cloud.
 */
class OSConnector
{
    // Название бакета для файлов видеоинтервью в Object Storage на Yandex.Cloud
    const OBJECT_STORAGE_VIDEO_BUCKET = 'videointerviews';
    // Название бакета для json-файлов результатов определения признаков в Object Storage на Yandex.Cloud
    const OBJECT_STORAGE_DETECTION_RESULT_BUCKET = 'detectionresults';
    // Название бакета для json-файлов результатов интерпретации признаков в Object Storage на Yandex.Cloud
    const OBJECT_STORAGE_INTERPRETATION_RESULT_BUCKET = 'interpretationresults';
    // Ключ для севрвисного аккаунта (hrrrobotuserforobjectstorage) в Object Storage на Yandex.Cloud
    const OBJECT_STORAGE_KEY    = 'IZnZSrNDYYbkZRDyAtZ9';
    // Шифр для севрвисного аккаунта (hrrrobotuserforobjectstorage) в Object Storage на Yandex.Cloud
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
     * @param $bucketName - название бакета (videointerviews, detectionresults или jsonfiles)
     * @param $path - название папки в бакете (соответствует id записи из БД)
     * @param $fileName - имя файла (без пути)
     * @param $file - файл
     */
    public function saveFileToObjectStorage($bucketName, $path, $fileName, $file)
    {
        $sdk = new Sdk($this->sharedConfig);
        $s3Client = $sdk->createS3();
        try {
            $s3Client->putObject([
                'Bucket' => $bucketName,
                'Key' => $path . '/' . $fileName,
                'Body' => (is_array($file)) ? json_encode($file, true) : fopen($file, 'r'),
            ]);
        } catch (S3Exception $e) {
            echo "При загрузке файла произошла ошибка.\n";
        }
    }

    /**
     * Удаление объекта файла из Object Storage на Yandex.Cloud.
     *
     * @param $bucketName - название бакета (videointerviews, detectionresults или jsonfiles)
     * @param $path - название папки в бакете (соответствует id записи из БД)
     * @param $fileName - имя файла (без пути)
     */
    public function removeFileToObjectStorage($bucketName, $path, $fileName)
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
     * @param $bucketName - название бакета (videointerviews, detectionresults или jsonfiles)
     * @param $path - название папки в бакете (соответствует id записи из БД)
     * @param $fileName - имя файла (без пути
     * @return bool|mixed - содержимое объекта файла
     */
    public function getFileContentToObjectStorage($bucketName, $path, $fileName)
    {
        $sdk = new Sdk($this->sharedConfig);
        $s3Client = $sdk->createS3();
        try {
            $result = $s3Client->getObject([
                'Bucket' => $bucketName,
                'Key' => $path . '/' . $fileName,
            ]);

            return $result["Body"];
        } catch (S3Exception $e) {
            echo "При получении файла произошла ошибка.\n";
        }

        return false;
    }

    /**
     * Скачивание объекта файла из Object Storage на Yandex.Cloud.
     *
     * @param $bucketName - название бакета (videointerviews, detectionresults или jsonfiles)
     * @param $path - название папки в бакете (соответствует id записи из БД)
     * @param $fileName - имя файла (без пути
     * @return mixed - файл с Object Storage
     */
    public function downloadFileToObjectStorage($bucketName, $path, $fileName)
    {
        $sdk = new Sdk($this->sharedConfig);
        $s3Client = $sdk->createS3();
        try {
            $result = $s3Client->getObject([
                'Bucket' => $bucketName,
                'Key' => $path . '/' . $fileName,
            ]);
            // Установка типа контента при скачивании файла
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
    }
}