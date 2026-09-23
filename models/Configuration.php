<?php
declare(strict_types=1);
namespace app\models;

use yii\db\ActiveRecord;

final class Configuration extends ActiveRecord
{
    public static function tableName(): string { return '{{%configuracao}}'; }
    public static function value(string $key): ?string
    {
        $value = self::find()->select('valor')->where(['chave' => $key])->scalar();
        return $value === false || $value === null ? null : (string) $value;
    }
    public function rules(): array
    {
        return [[['chave'], 'required'], [['valor'], 'string'], [['updated_by'], 'integer']];
    }
}
