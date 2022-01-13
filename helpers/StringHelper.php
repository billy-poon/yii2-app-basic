<?php

namespace app\helpers;

class StringHelper extends \yii\helpers\StringHelper
{
  public static function random($length, $alphabet = null)
  {
    $alphabet = $alphabet ??
      '0123456789' .
      'abcdefghijklmnopqrstuvwxyz' .
      'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
      // https://stackoverflow.com/questions/15783701/which-characters-need-to-be-escaped-when-using-bash#27817504
      '%+-.:=@_';

    $charCount = strlen($alphabet);

    $array = [];
    $bytes = openssl_random_pseudo_bytes($length);
    foreach (unpack('C*', $bytes) as $v) {
        $array []= $alphabet[$v % $charCount];
    }

    return implode($array);
  }

  // https://github.com/cdoco/common-regex#邮箱
  public static function isEmailAddress(&$string)
  {
    if (preg_match('/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/', $string)) {
      $string = strtolower($string);
      return true;
    }

    return false;
  }

  // https://github.com/cdoco/common-regex#电话
  public static function isCellphoneNumber(&$string)
  {
    if (preg_match('/^(\+?86)?(1(3|4|5|6|7|8|9)\d{9})$/', $string, $matches)) {
      $string = $matches[2];
      return true;
    }

    return false;
  }
}
