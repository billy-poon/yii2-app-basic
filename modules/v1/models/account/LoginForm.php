<?php

namespace app\modules\v1\models\account;

use app\models\identity\User;

class LoginForm extends \app\models\Form
{
  public $username;
  public $password;
  public $remember_me;

  public function rules()
  {
    return [
      [['username', 'password'], 'required'],
      ['password', 'validatePassword'],
      ['remember_me', 'boolean'],
    ];
  }

  public function validatePassword($attribute, $params)
  {
    if (!$this->hasErrors()) {
      if ($identity = $this->getUser()) {
        if ($identity->validatePassword($this->password)) {
          $duration = $this->remember_me ? 7 * 24 * 3600 : 0;
          if (\Yii::$app->user->login($identity, $duration)) {
            return;
          }
        }
      }

      $this->addError($attribute, 'Incorrect username or password');
    }
  }

  private $_user = false;
  /**
   * @return User
   */
  public function getUser()
  {
    if ($this->_user === false) {
      $this->_user = User::findByUsername($this->username);
    }

    return $this->_user;
  }

  public function work()
  {
    return $this->getUser();
  }
}
