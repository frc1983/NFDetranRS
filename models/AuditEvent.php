<?php
declare(strict_types=1);
namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

final class AuditEvent extends ActiveRecord
{
    public static function tableName(): string { return '{{%auditoria_usuario}}'; }
    public function getUser(): ActiveQuery { return $this->hasOne(User::class, ['id' => 'usuario_id']); }
}
