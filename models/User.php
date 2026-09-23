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
    public function getId() { return $this->getPrimaryKey(); }
    public function getAuthKey(): string { return (string) $this->auth_key; }
    public function validateAuthKey($key): bool { return hash_equals($this->getAuthKey(), (string) $key); }
}
