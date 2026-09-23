<?php
declare(strict_types=1);
namespace app\models;

use yii\db\ActiveRecord;

final class SaleItem extends ActiveRecord
{
    public static function tableName(): string { return '{{%venda_item}}'; }
}
