<?php
declare(strict_types=1);
namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

final class Invoice extends ActiveRecord
{
    public static function tableName(): string { return '{{%nfe}}'; }
    public function getSale(): ActiveQuery { return $this->hasOne(Sale::class, ['id' => 'venda_id']); }
}
