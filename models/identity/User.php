<?php

namespace app\models\identity;

use yii\web\IdentityInterface;

use app\models\user\UserProfile;

class User extends \app\models\user\User implements IdentityInterface
{
  use IdentityTrait,
      IdentityPasswordTrait,
      IdentityAccessTokenTrait {
    IdentityPasswordTrait::validatePassword insteadof IdentityTrait;
    IdentityAccessTokenTrait::findIdentityByAccessToken insteadof IdentityTrait;
  }

  public static function findByUsername($username, $tryProfile = true)
  {
    if (!$result = static::findOne(['code' => $username])) {
      if ($tryProfile) {
        if ($profile = UserProfile::findVerified($username)) {
          $result = static::findOne($profile->uid);
        }
      }
    }

    return $result;
  }

  public function identity()
  {
    if (!$this->disabled) {
      $expireAt = $this->expire_at;
      if (!$expireAt || strtotime($expireAt) > time()) {
        $this->_accessToken = null;
        return $this;
      }
    }

    return null;
  }

  public function getAuthKeyData()
  {
    $result = $this->getAttributes([
      'code',
      'password_hash', 'password_expired',
      'disabled', 'expire_at',
    ]);
    $result []= \Yii::$app->request->cookieValidationKey;

    return $result;
  }
}
