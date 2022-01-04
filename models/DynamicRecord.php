<?php

namespace app\models;

class DynamicRecord extends ActiveRecord
{
  public static function tableName()
  {
    throw new \yii\base\InvalidConfigException('Should be overwritten in sub-class.');
  }

  public function rules()
  {
    static $readonlyFields = [
      "id", "create_by", "create_at", "update_by", "update_at"
    ];

    $attrs = $this->attributes();
    $safeAttrs = array_intersect($attrs, array_diff($attrs, $readonlyFields));
    return [
      [$safeAttrs, "safe"]
    ];
  }

  public static function createActiveClass($tableName)
  {
    if (!preg_match('|^(\{\{%)?\w+(\}\})?$|', $tableName)) {
      throw new \yii\base\InvalidArgumentException('Invalid table name.');
    }

    $fn = uniqid();
    $baseClass = static::class;

    $code = sprintf('
      function __create_anonymous_class_%s__() {
        $classObj = new class extends \\%s {
          public static $CODE = null;
          public static function tableName()
          { return "%s"; }
        };

        return get_class($classObj);
      }

      return __create_anonymous_class_%s__();
    ', $fn, $baseClass, $tableName, $fn);

    $result = eval($code);
    $result::$CODE = $code;
    return $result;
  }
}
