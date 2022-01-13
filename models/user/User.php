<?php

namespace app\models\user;

/**
 * @property    int $id               int(11)
 * @property string $code             varchar(64)
 * @property string $nick_name        varchar(64)?
 * @property string $password_hash    varchar(64)
 * @property string $password_salt    varchar(64)
 * @property    int $password_expired tinyint(1)
 * @property    int $disabled         tinyint(1)
 * @property string $expire_at        timestamp?
 * @property string $remarks          text?
 * @property    int $create_by        int(11)?
 * @property string $create_at        timestamp
 * @property    int $update_by        int(11)?
 * @property string $update_at        timestamp?
 */
class User extends \app\models\ActiveRecord
{
  public function getExpired()
  {
      if ($expireAt = $this->expire_at) {
          if ($time = strtotime($expireAt)) {
              return $time <= time();
          }
      }

      return false;
  }
}
