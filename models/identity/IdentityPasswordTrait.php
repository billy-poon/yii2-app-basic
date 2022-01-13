<?php

namespace app\models\identity;

trait IdentityPasswordTrait
{
  private $_password;

  /**
   * {@inheritdoc}
   */
  public function fields()
  {
    $result = parent::fields();
    unset($result['password_salt']);
    unset($result['password_hash']);
    if ($this->getPassword() !== null) {
      $result['password'] = 'password';
    }

    return $result;
  }

  public function getPassword()
  {
      return $this->_password;
  }

  /**
   * @return static
   */
  public function setPassword($value, $expired = true)
  {
    $password = $value ?? '';

    $bytes = openssl_random_pseudo_bytes(8);
    $password_salt = bin2hex($bytes);
    $password_hash = static::calcPasswordHash($password, $password_salt);

    \Yii::configure($this, compact('password_salt', 'password_hash'));

    $this->_password = $password;

    $this->password_expired = !!$expired;
    return $this;
  }

  public function validatePassword($password)
  {
    if ($model = $this->identity()) {
      return static::calcPasswordHash(
        $password, $model->password_salt
      ) === $model->password_hash;
    }

    return false;
  }

  public static function calcPasswordHash($password, $salt)
  {
    return sha1("{$password}#{$salt}");
  }
}
