<?php
declare(strict_types=1);
namespace app\models;

use Yii;
use yii\base\Model;

final class LoginForm extends Model
{
    public string $email = '';
    public string $password = '';
    public bool $rememberMe = false;
    private ?User $user = null;

    public function rules(): array
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword(string $attribute): void
    {
        if (!$this->hasErrors() && (!$this->getUser() || !$this->getUser()->validatePassword($this->password))) {
            $this->addError($attribute, 'E-mail ou senha invalidos.');
        }
    }

    public function login(): bool
    {
        return $this->validate() && Yii::$app->user->login($this->getUser(), $this->rememberMe ? 86400 * 7 : 0);
    }

    private function getUser(): ?User
    {
        return $this->user ??= User::findByEmail($this->email);
    }
}
