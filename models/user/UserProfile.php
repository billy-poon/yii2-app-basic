<?php

namespace app\models\user;

use app\helpers\StringHelper;

/**
 * @property    int $id               int(11)
 * @property    int $uid              int(11)
 * @property string $name             varchar(64)?
 * @property    int $gender           tinyint(1)?
 * @property string $birthday         datetime?
 * @property string $avatar_url       text?
 * @property string $email            varchar(64)?
 * @property    int $email_verified   tinyint(1)
 * @property string $cellphone        varchar(64)?
 * @property    int $cellphone_verified tinyint(1)
 * @property string $signature        text?
 * @property string $remarks          text?
 * @property    int $create_by        int(11)?
 * @property string $create_at        timestamp
 * @property    int $update_by        int(11)?
 * @property string $update_at        timestamp?
 */
class UserProfile extends \app\models\ActiveRecord
{
  public static function findVerified($emailOrCellphone)
  {
    $where = [];
    $code = $emailOrCellphone;
    if (StringHelper::isEmailAddress($code)) {
      $where = ['email' => $code, 'email_verified' => 1];
    } else if (StringHelper::isCellphoneNumber($code)) {
      $where = ['cellphone' => $code, 'cellphone_verified' => 1];
    }

    return !empty($where) ? static::findOne($where) : null;
  }
}
