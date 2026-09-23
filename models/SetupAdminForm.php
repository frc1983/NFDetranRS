<?php
declare(strict_types=1);
namespace app\models;

use yii\base\Model;

final class SetupAdminForm extends Model
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $passwordConfirmation = '';

    public function rules(): array
    {
        return [
            [['name', 'email', 'password', 'passwordConfirmation'], 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::class],
            ['name', 'string', 'min' => 3, 'max' => 150],
            ['password', 'string', 'min' => 12, 'max' => 128],
            ['passwordConfirmation', 'compare', 'compareAttribute' => 'password'],
        ];
    }
}
