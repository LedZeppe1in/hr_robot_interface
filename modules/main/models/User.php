<?php

namespace app\modules\main\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $username
 * @property string $password_hash
 * @property int $role
 * @property int $status
 * @property string $full_name
 * @property string|null $email
 */
class User extends \yii\db\ActiveRecord
{
    // Роли пользователей
    const ROLE_ADMINISTRATOR = 0; // Администратор
    const ROLE_PSYCHOLOGIST  = 1; // Психолог

    // Статус пользователей
    const STATUS_ACTIVE   = 0; // Активный
    const STATUS_INACTIVE = 1; // Неактивный

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['username', 'password_hash', 'role', 'status'], 'required'],
            [['role', 'status'], 'integer'],
            [['username', 'password_hash', 'full_name', 'email'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлен',
            'username' => 'Имя',
            'password_hash' => 'Хэш пароля',
            'role' => 'Роль',
            'status' => 'Статус',
            'full_name' => 'ФИО',
            'email' => 'Электронная почта',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Установка пароля.
     *
     * @param $password
     * @throws \yii\base\Exception
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Получение списка названий всех ролей пользователей.
     *
     * @return array - массив всех возможных названий ролей пользователей
     */
    public static function getRoles()
    {
        return [
            self::ROLE_ADMINISTRATOR => 'Администратор',
            self::ROLE_PSYCHOLOGIST => 'Психолог',
        ];
    }

    /**
     * Получение названия роли пользователя.
     *
     * @return mixed
     */
    public function getRoleName()
    {
        return ArrayHelper::getValue(self::getRoles(), $this->role);
    }

    /**
     * Получение списка названий всех статусов пользователей.
     *
     * @return array - массив всех возможных названий статусов пользователей
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Активный',
            self::STATUS_INACTIVE => 'Неактивный',
        ];
    }

    /**
     * Получение названия статуса пользователя.
     *
     * @return mixed
     */
    public function getStatusName()
    {
        return ArrayHelper::getValue(self::getStatuses(), $this->status);
    }
}