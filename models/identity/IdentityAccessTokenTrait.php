<?php

namespace app\models\identity;

use app\helpers\OpenSSLHelper;

trait IdentityAccessTokenTrait
{
  private $_accessToken = '';

  /**
   * {@inheritdoc}
   */
  public function extraFields()
  {
    $result = parent::extraFields();
    $result['access_token'] = 'accessToken';

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public static function findIdentityByAccessToken($token, $type = null)
  {
    if ($cipher = hex2bin($token)) {
      if ($data = OpenSSLHelper::instance()->decrypt_public($cipher)) {
        list($expireAt, $id, $authKey) = explode('#', $data, 3);
        if ($expireAt > time()) {
          if ($model = static::findIdentity($id)) {
            if ($model->validateAuthKey($authKey)) {
              if ($result = $model->identity()) {
                $result->_accessToken = $token;
                return $result;
              }
            }
          }
        }
      }
    }

    return null;
  }

  public function getAccessToken($duration = 30 * 24 * 3600, $renew = false)
  {
    if ($renew || $this->_accessToken === null) {
      $data = [
        time() + $duration,
        $this->getId(),
        $this->getAuthKey()
      ];
      $cipher = OpenSSLHelper::instance()->encrypt_private(implode('#', $data));
      $this->_accessToken = bin2hex($cipher);
    }

    return $this->_accessToken;
  }
}
