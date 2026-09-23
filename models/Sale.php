<?php
declare(strict_types=1);
namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

final class Sale extends ActiveRecord
{
    public static function tableName(): string { return '{{%venda}}'; }
    public function getItems(): ActiveQuery { return $this->hasMany(SaleItem::class, ['venda_id' => 'id']); }
    public function getInvoices(): ActiveQuery { return $this->hasMany(Invoice::class, ['venda_id' => 'id']); }
}
