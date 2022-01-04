<?php

namespace app\models;

class ActiveRecord extends \yii\db\ActiveRecord
{
  use ModelTrait;

  public function behaviors()
  {
    $result = parent::behaviors();
    $result['createByBehavior'] = 'app\behaviors\models\CreateByBehavior';
    return $result;
  }

  public function fields()
  {
    $result = parent::fields();
    foreach (static::hiddenFields() as $v) {
      unset($result[$v]);
    }

    return $result;
  }

  public function extraFields()
  {
    return ['hidden_fields' => 'hiddenFields'];
  }

  public static function hiddenFields()
  {
    return ['create_by', 'create_at', 'update_by', 'update_at'];
  }

  public function getHiddenFields()
  {
    return $this->getAttributes(static::hiddenFields());
  }

  public function saveAndRefresh($runValidation = true, $attributeNames = null)
  {
    if ($this->save($runValidation, $attributeNames)) {
      return $this->refresh();
    }

    return false;
  }

  /**
   * @return static
   */
  public static function findModel($condition, $singleton = true, $throw = true)
  {
    try {
      if ($models = static::findByCondition($condition)->limit(2)->all()) {
        if ($singleton && count($models) > 1) {
          if ($throw) {
            throw new ActiveRecord__FindModelException("Multiple records found", $condition);
          }

          return null;
        }

        return $models[0];
      }

      if ($throw) {
        throw new ActiveRecord__FindModelException("Object not found", $condition);
      }

      return null;
    } catch (ActiveRecord__FindModelException $ex) {
      $message = $ex->getMessage();
      if (\Yii::$app instanceof \yii\web\Application) {
        throw new \yii\web\NotFoundHttpException($message);
      } else {
        throw new \yii\console\Exception($message);
      }
    }
  }

  public static function query($q, $query = null)
  {
    static $supportedTypes = ['char', 'string', 'text'];

    $query = $query ?? static::find();
    if (empty($q)) return $query;

    $columns = array_filter(
      static::getTableSchema()->columns,
      function ($x) use ($supportedTypes) {
        return in_array($x->type, $supportedTypes);
      }
    );

    return $query->andWhere(array_merge(
      ['OR'],
      array_map(
        function ($x) use ($q) {
          return ['LIKE', $x, $q];
        },
        array_keys($columns)
      )
    ));
  }

  public function resolveColumns()
  {
    return static::getTableSchema()->columns;
  }
}

class ActiveRecord__FindModelException extends \yii\base\UserException
{
  public function __construct($messagePrefix, $condition)
  {
    $whereClause = static::toWhereString($condition);
    $messagePrefix = $messagePrefix ?: 'Failed to find model';

    parent::__construct("{$messagePrefix}: {$whereClause}");
  }

  static function toWhereString($condition)
  {
    if (is_numeric($condition)) {
      return "#$condition";
    } else if (is_array($condition)) {
      return http_build_query($condition);
    }

    return $condition;
  }
}
