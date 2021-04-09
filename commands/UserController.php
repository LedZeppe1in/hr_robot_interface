<?php

namespace app\commands;

use yii\helpers\Console;
use yii\console\Controller;
use app\modules\main\models\User;

/**
 * UserController - реализует консольные команды для работы с пользователями.
 * @package app\commands
 */
class UserController extends Controller
{
    /**
     * Инициализация команд.
     */
    public function actionIndex()
    {
        echo 'yii user/create-default-users' . PHP_EOL;
    }

    /**
     * Команда создания пользователей (администратора и психолога) по умолчанию.
     */
    public function actionCreateDefaultUsers()
    {
        // Создание пользователя администратора в БД
        $model = new User();
        $model->username = 'admin';
        $model->setPassword('admin098123');
        $model->role = User::ROLE_ADMINISTRATOR;
        $model->status = User::STATUS_ACTIVE;
        $this->log($model->save());
        // Создание первого пользователя психолога в БД
        $model = new User();
        $model->username = 'psycho';
        $model->setPassword('psycho_12345');
        $model->role = User::ROLE_PSYCHOLOGIST;
        $model->status = User::STATUS_ACTIVE;
        $this->log($model->save());
        // Создание второго пользователя психолога в БД
        $model = new User();
        $model->username = 'psycho2';
        $model->setPassword('psycho2_45835');
        $model->role = User::ROLE_PSYCHOLOGIST;
        $model->status = User::STATUS_ACTIVE;
        $this->log($model->save());
        // Создание третьего пользователя психолога в БД
        $model = new User();
        $model->username = 'psycho3';
        $model->setPassword('psycho3_09857');
        $model->role = User::ROLE_PSYCHOLOGIST;
        $model->status = User::STATUS_ACTIVE;
        $this->log($model->save());
        // Создание четвертого пользователя психолога в БД
        $model = new User();
        $model->username = 'psycho4';
        $model->setPassword('psycho4_38563');
        $model->role = User::ROLE_PSYCHOLOGIST;
        $model->status = User::STATUS_ACTIVE;
        $this->log($model->save());
        // Создание пятого пользователя психолога в БД
        $model = new User();
        $model->username = 'psycho5';
        $model->setPassword('psycho5_55992');
        $model->role = User::ROLE_PSYCHOLOGIST;
        $model->status = User::STATUS_ACTIVE;
        $this->log($model->save());
    }

    /**
     * Вывод сообщений на экран (консоль)
     * @param bool $success
     */
    private function log($success)
    {
        if ($success) {
            $this->stdout('Success!', Console::FG_GREEN, Console::BOLD);
        } else {
            $this->stderr('Error!', Console::FG_RED, Console::BOLD);
        }
        echo PHP_EOL;
    }
}