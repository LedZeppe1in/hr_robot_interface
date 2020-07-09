<?php

namespace app\components;

/**
 * Class DBConnector - класс для взаимодействия с базой данных.
 */
class DBConnector
{
    // Настройки подключения к БД на сервере Yandex.Cloud
    protected $host = 'rc1a-cxj2zyrrqtga2084.mdb.yandexcloud.net';
    protected $port = 6432;
    protected $userName = 'u-2';
    protected $password = 'MXh;dod_22892h_u3_4748@';
    protected $dbName = 'D1';

    /**
     * Подключение к БД.
     *
     * @return bool
     */
    public function connect()
    {
        // Подключение
        $connection = pg_connect("
            host=$this->host
            dbname=$this->dbName
            port=$this->port
            user=$this->userName
            password=$this->password
        ");
        // Проверка подключения
        if (!$connection)
            die("Не удалось открыть соединение с сервером базы данных!");
        else
            return $connection;
    }

    /**
     * Закрытие подключения к БД.
     *
     * @param $connection - соединение с БД
     */
    public function close($connection)
    {
        if (!pg_close($connection))
            echo("Не удалось завершить соединение с базой!");
    }

    /**
     * Поиск записей в таблице "hrrobot_video_interview" с не пустым полем "video_file_name".
     *
     * @param $connection - соединение с БД
     * @return resource - выборка (строки) из таблицы "hrrobot_video_interview"
     */
    public function getVideoInterviews($connection)
    {
        // SQL-запрос
        $sql = 'SELECT *
            FROM hrrobot_video_interview
            WHERE video_file_name IS NOT NULL';
        // Выполнение SQL-запроса
        $result = pg_query($connection, $sql) or die("Ошибка в запросе: " .
            iconv('UTF-8', 'CP1251', $sql) . " " . pg_last_error($connection));

        return $result;
    }

    /**
     * Поиск записи в таблице "hrrobot_video_interview" по идентификатору.
     *
     * @param $connection - соединение с БД
     * @param $id - идентификатор видеоинтервью (PK)
     * @return resource - запись из таблицы "hrrobot_video_interview"
     */
    public function getVideoInterview($connection, $id)
    {
        // SQL-запрос
        $sql = "SELECT *
            FROM hrrobot_video_interview
            WHERE id = '$id'";
        // Выполнение SQL-запроса
        $result = pg_query($connection, $sql) or die("Ошибка в запросе: " .
            iconv('UTF-8', 'CP1251', $sql) . " " . pg_last_error($connection));

        return $result;
    }

    /**
     * Поиск записей в таблице "hrrobot_landmark" с не пустым полем "landmark_file_name".
     *
     * @param $connection - соединение с БД
     * @return resource - выборка (строки) из таблицы "hrrobot_landmark"
     */
    public function getLandmarks($connection)
    {
        // SQL-запрос
        $sql = 'SELECT *
            FROM hrrobot_landmark
            WHERE landmark_file_name IS NOT NULL';
        // Выполнение SQL-запроса
        $result = pg_query($connection, $sql) or die("Ошибка в запросе: " .
            iconv('UTF-8', 'CP1251', $sql) . " " . pg_last_error($connection));

        return $result;
    }

    /**
     * Поиск записи в таблице "hrrobot_landmark" по идентификатору.
     *
     * @param $connection - соединение с БД
     * @param $id - идентификатор цифровой маски (PK)
     * @return resource - запись из таблицы "hrrobot_landmark"
     */
    public function getLandmark($connection, $id)
    {
        // SQL-запрос
        $sql = "SELECT *
            FROM hrrobot_landmark
            WHERE id = '$id'";
        // Выполнение SQL-запроса
        $result = pg_query($connection, $sql) or die("Ошибка в запросе: " .
            iconv('UTF-8', 'CP1251', $sql) . " " . pg_last_error($connection));

        return $result;
    }

    /**
     * Добавление новой записи в таблицу "Цифровая маска" (hrrobot_landmark).
     *
     * @param $connection - соединение с БД
     * @param $fileName - название json-файла с лицевыми точками сохраняемого на Object Storage
     * @param $description - описание цифровой маски
     * @param $videoInterviewId - идентификатор видеоинтервью (дочернего ключа, FK) из таблицы "hrrobot_video_interview")
     * @return resource - результата запроса
     */
    public function insertLandmark($connection, $fileName, $description, $videoInterviewId)
    {
        // Получение текущего времени
        $currentTime = time();
        // SQL-запрос
        $sql = 'INSERT INTO hrrobot_landmark (created_at, updated_at, file_name, description, video_interview_id) 
            VALUES ($1, $1, $2, $3, $4)';
        // Выполнение SQL-запроса
        $result = pg_query_params($connection, $sql, array($currentTime, $fileName, $description, $videoInterviewId)) or
            die("Ошибка в запросе: " . iconv('UTF-8', 'CP1251', $sql) . " " . pg_last_error($connection));

        return $result;
    }

    /**
     * Обновление таблицы "hrrobot_landmark".
     *
     * @param $connection - соединение с БД
     * @param $id - идентификатор цифровой маски (PK)
     * @param $fileName - название json-файла с лицевыми точками,
     * сохраняемого на Object Storage (с указанием расширения файла)
     * @param $description - описание цифровой маски
     * @param $videoInterviewId - обновляемое значение для поля идентификатора видеоинтервью (дочернего ключа, FK)
     * @return resource - результата запроса
     */
    public function updateLandmark($connection, $id, $fileName, $description, $videoInterviewId)
    {
        // Получение текущего времени
        $currentTime = time();
        // SQL-запрос
        $sql = 'UPDATE hrrobot_landmark
            SET updated_at = $2, file_name = $3, description = $4, video_interview_id = $5
            WHERE id = $1';
        // Выполнение SQL-запроса
        $result = pg_query_params(
            $connection,
            $sql,
            array($id, $currentTime, $fileName, $description, $videoInterviewId)
        ) or die("Ошибка в запросе: " . iconv('UTF-8', 'CP1251', $sql) . " " . pg_last_error($connection));

        return $result;
    }

    /**
     * Обновление таблицы "hrrobot_analysis_result" - обновление поля с названием файла результатов интерпретации.
     *
     * @param $connection - соединение с БД
     * @param $id - идентификатор результата анализа (PK)
     * @param $interpretationResultFileName - название json-файла с результатами интерпретации,
     * сохраняемого на Object Storage (с указанием расширения файла)
     * @return resource - результат запроса
     */
    public function updateAnalysisResult($connection, $id, $interpretationResultFileName)
    {
        // Получение текущего времени
        $currentTime = time();
        // SQL-запрос
        $sql = 'UPDATE hrrobot_analysis_result
            SET updated_at = $2, interpretation_result_file_name = $3
            WHERE id = $1';
        // Выполнение SQL-запроса
        $result = pg_query_params($connection, $sql, array($id, $currentTime, $interpretationResultFileName)) or
            die("Ошибка в запросе: " . iconv('UTF-8', 'CP1251', $sql) . " " . pg_last_error($connection));

        return $result;
    }

    /**
     * Обновление таблицы "hrrobot_final_conclusion" - обновление поля с итоговым заключением по видео-интервью.
     *
     * @param $connection - соединение с БД
     * @param $id - идентификатор итогового заключения по видео-интервью (PK)
     * @param $conclusionText - текст с итоговым заключением по видео-интервью,
     * @return resource - результат запроса
     */
    public function updateFinalConclusion($connection, $id, $conclusionText)
    {
        // SQL-запрос
        $sql = 'UPDATE hrrobot_final_conclusion
            SET conclusion = $2
            WHERE id = $1';
        // Выполнение SQL-запроса
        $result = pg_query_params($connection, $sql, array($id, $conclusionText)) or
        die("Ошибка в запросе: " . iconv('UTF-8', 'CP1251', $sql) . " " . pg_last_error($connection));

        return $result;
    }
}