<?php
declare(strict_types=1);
namespace app\models;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

final class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName(): string { return '{{%usuario}}'; }
    public static function findIdentity($id): ?self { return self::findOne(['id' => $id, 'status' => 'ativo']); }
    public static function findIdentityByAccessToken($token, $type = null): ?self { return null; }
    public static function findByEmail(string $email): ?self { return self::findOne(['email' => mb_strtolower(trim($email)), 'status' => 'ativo']); }
    public function getId() { return $this->getPrimaryKey(); }
    public function getAuthKey(): string { return (string) $this->auth_key; }
    public function validateAuthKey($key): bool { return hash_equals($this->getAuthKey(), (string) $key); }
    public function validatePassword(string $password): bool { return \Yii::$app->security->validatePassword($password, (string) $this->password_hash); }
    public function setPassword(string $password): void { $this->password_hash = \Yii::$app->security->generatePasswordHash($password); }
    public function generateAuthKey(): void { $this->auth_key = \Yii::$app->security->generateRandomString(64); }

    public function rules(): array
    {
        return [
            [['nome', 'email', 'password_hash', 'auth_key'], 'required'],
            ['email', 'email'],
            ['email', 'unique'],
            ['status', 'in', 'range' => ['ativo', 'inativo', 'bloqueado']],
            [['nome'], 'string', 'max' => 150],
            [['email'], 'string', 'max' => 190],
        ];
    }
}
