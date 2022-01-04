<?php

namespace app\helpers;

class ArrayHelper extends \yii\helpers\ArrayHelper
{
  public static function find($array, callable $predicate)
  {
    $pair = static::findPair($array, $predicate);
    if ($pair !== null) {
      return $pair[1];
    }

    return null;
  }

  public static function findKey($array, callable $predicate)
  {
    $pair = static::findPair($array, $predicate);
    if ($pair !== null) {
      return $pair[0];
    }

    return null;
  }

  public static function findPair($array, callable $predicate)
  {
    foreach ($array as $k => $v) {
      if ($predicate($v, $k)) {
        return [$k, $v];
      }
    }

    return null;
  }

  public static function some($array, callable $predicate)
  {
    return static::findPair($array, $predicate) !== null;
  }

  public static function every($array, callable $predicate)
  {
    $negative = fn($v, $k) => !$predicate($v, $k);
    return static::findPair($array, $negative) === null;
  }

  public static function filterMap($array, callable $transofrm, $keepKeys = true)
  {
    $result = [];
    foreach ($array as $k => $v) {
      if (!$item = $transofrm($v, $k)) continue;
      if ($keepKeys) {
        $result[$k] = $item;
      } else {
        $result []= $item;
      }
    }

    return $result;
  }

  public static function pick($array, array $keys)
  {
    $result = [];
    foreach ($keys as $k => $v) {
      $key = is_string($k) ? $k : $v;
      $value = is_callable($v) ? $v($array, $k) : $array[$key];
      $result[$key] = $value;
    }

    return $result;
  }
}
