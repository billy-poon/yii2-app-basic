<?php

namespace app\models\identity;

use yii\base\InvalidCallException;

trait IdentityTrait
{
  /**
   * @return static
   */
  public static function findIdentity($id)
  {
    if (is_numeric($id)) {
      if ($model = static::findOne($id)) {
        return $model->identity();
      }
    }

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthKey()
  {
    $array = (array)$this->getAuthKeyData();
    $array[] = \Yii::$app->request->cookieValidationKey;

    return sha1(implode('|', $array));
  }

  /**
   * {@inheritdoc}
   */
  public function validateAuthKey($authKey)
  {
    return $this->getAuthKey() === $authKey;
  }

  /**
   * {@inheritdoc}
   */
  public static function findIdentityByAccessToken($token, $type = null)
  {
    throw new InvalidCallException("Not implemented.");
  }

  public function identity()
  {
    throw new InvalidCallException("Not implemented.");
  }

  public function getAuthKeyData()
  {
    throw new InvalidCallException("Not implemented.");
  }

  public static function findByUsername($username)
  {
    throw new InvalidCallException("Not implemented.");
  }

  public function validatePassword($password)
  {
    throw new InvalidCallException("Not implemented.");
  }
}
