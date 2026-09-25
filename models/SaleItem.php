<?php
declare(strict_types=1);
namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

final class SaleItem extends ActiveRecord
{
    public static function tableName(): string { return '{{%venda_item}}'; }
    public function getStockItem(): ActiveQuery { return $this->hasOne(StockItem::class, ['id' => 'estoque_gid_id']); }
}
