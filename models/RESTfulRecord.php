<?php

namespace app\models;

use yii\helpers\ArrayHelper;

class RESTfulRecord extends ActiveRecord
{
  public function rules()
  {
    static $result;
    if (!$result) $result = static::rulesFromSchema();

    return $result;
  }

  public static function rulesFromSchema()
  {
    static $readonly = ['id', 'create_by', 'create_at', 'update_by', 'update_at'];
    static $validators = [
      'required' => [],
      'string' => [],
      'number' => [],
      'boolean' => [],
      'datetime' => ['$type' => 'date', 'type' => 'datetime', 'format' => 'php:Y-m-d H:i:s'],
      'date' => ['$type' => 'date', 'type' => 'date', 'format' => 'php:Y-m-d'],
      'time' => ['$type' => 'date', 'type' => 'time', 'format' => 'php:H:i:s'],
      'safe' => [],
    ];

    $normalizeType = function($type) {
      static $stringTypes = ['char', 'string', 'text'];
      static $numberTypes = ['smallint', 'integer', 'bigint', 'float', 'decimal', 'timestamp'];

      if (in_array($type, $stringTypes)) return 'string';
      if (in_array($type, $numberTypes)) return 'number';

      return $type;
    };

    $fieldSet = [];
    foreach (static::getTableSchema()->columns as $v) {
      if (in_array($v->name, $readonly)) continue;

      $safe = true;
      if (!$v->allowNull) {
        $safe = false;
        $fieldSet['required'] []= $v;
      };

      $type = $normalizeType($v->type);
      if (array_key_exists($type, $validators)) {
        $fieldSet[$type] []= $v;
      } else if ($safe) {
        $fieldSet['safe'] []= $v;
      }
    }

    $result = [];
    foreach ($fieldSet as $k => $v) {
      $config = $validators[$k];
      $names = ArrayHelper::getColumn($v, 'name');

      $type = $k;
      if (isset($config['$type'])) {
        $type = $config['$type'];
        unset($config['$type']);
      }

      $result []= $config
        ? array_merge([$names, $type], $config)
        : [$names, $type];
    }

    return $result;
  }
}
