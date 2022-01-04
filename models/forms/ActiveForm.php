<?php

namespace app\models\forms;

class ActiveForm extends \app\models\Form
{
  private $_modelClass;
  public function getModelClass()
  {
    return $this->_modelClass;
  }

  public function setModelClass($value)
  {
    $this->_modelClass = $value;
  }

  public function init()
  {
    parent::init();
    if ($this->modelClass === null) {
      throw new \yii\base\InvalidConfigException('The "modelClass" property must be set.');
    }
  }

  public function findModel($condition)
  {
    $modelClass = $this->modelClass;
    return $modelClass::findModel($condition);
  }
}
