<?php

namespace app\behaviors;

use yii\db\BaseActiveRecord;

class PinyinBehavior extends \yii\base\Behavior
{
  public $config;

  // https://github.com/overtrue/pinyin
  // composer require "overtrue/pinyin"
  public static function pinyin($string, $abbr = false, $name = false)
  {
    static $pinyin = null;
    if (!$pinyin) {
      $pinyin = new \Overtrue\Pinyin\Pinyin();
    }

    $options = PINYIN_KEEP_NUMBER | PINYIN_KEEP_ENGLISH | PINYIN_UMLAUT_V;

    if ($name) {
      $result = $pinyin->name($string, $options);
      if ($abbr) {
        return implode('', array_map(
          function ($x) {
            return $x[0];
          },
          $result
        ));
      }
    } else if ($abbr) {
      return $pinyin->abbr($string, $options);
    } else {
      return $pinyin->permalink($string, $options);
    }
  }

  public static function getDefaultConfig($owner)
  {
    static $configMap = [];

    if ($owner instanceof \yii\db\BaseActiveRecord) {
      $ownerClass = $owner::className();
      if (!$result = @$configMap[$ownerClass]) {
        $fields = array_keys($ownerClass::getTableSchema()->columns);

        $result = [];
        foreach ($fields as $v) {
          if (preg_match('/^(\w+)_pinyin$/', $v, $matches)) {
            $from = $matches[1];
            if (in_array($from, $fields)) {
              $result[$v] = compact('from');
            }
          }
        }

        if ($result) {
          $configMap[$ownerClass] = $result;
        }
      }

      if ($result) return $result;
    }

    throw new \yii\base\InvalidConfigException('Faild to detect pinyin config automatically.');
  }

  public function events()
  {
    return [
      BaseActiveRecord::EVENT_BEFORE_INSERT => 'onBeforeSave',
      BaseActiveRecord::EVENT_BEFORE_UPDATE => 'onBeforeSave',
    ];
  }

  public function onBeforeSave($event)
  {
    if ($event->isValid) {
      $model = $event->sender;
      $config = $this->config ?? static::getDefaultConfig($model);
      if (!is_array($config)) {
        throw new \yii\base\InvalidConfigException("The config value must be an array.");
      }

      foreach ($config as $k => $v) {
        $config = PinyinBehavior_Config::create($k, $v);
        list($field, $value) = $config->toPair($model);
        $model->$field = $value;
      }
    }
  }
}

class PinyinBehavior_Config extends \yii\base\BaseObject
{
  public $field;
  public $from;
  public $fetcher;

  public $is_abbr = true;
  public $is_name = false;

  public static function create($key, $value)
  {
    $array = [];
    if (is_array($value)) {
      $array = $value;
    } else if (is_string($value)) {
      $array = ['from' => $value];
    } else if (is_callable($value)) {
      $array = ['fetcher' => $value];
    }

    if (!@$array['field']) {
      if (is_string($key)) {
        $array['field'] = $key;
      } else {
        throw new \yii\base\InvalidConfigException('The "field" value must be set.');
      }
    }

    return new static($array);
  }

  public function toPair($model)
  {
    $string = '';
    if ($fetcher = $this->fetcher) {
      $string = call_user_func($fetcher, $model, $this);
    } else if ($from = $this->from) {
      $string = $model->$from;
    }

    $pinyin = $string ? PinyinBehavior::pinyin(
      $string, $this->is_abbr, $this->is_name
    ) : null;

    return [$this->field, $pinyin];
  }
}
