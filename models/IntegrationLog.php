<?php
declare(strict_types=1);
namespace app\models;

use yii\db\ActiveRecord;

final class IntegrationLog extends ActiveRecord
{
    public static function tableName(): string { return '{{%integracao_log}}'; }
}
