<?php

namespace app\models\forms;

class ActiveSearchForm extends ActiveForm
{
  public $q;

  public function __construct($modelClass)
  {
    $this->modelClass = $modelClass;
  }

  public function work()
  {
    $modelClass = $this->modelClass;
    $query = $this->q
      ? $modelClass::query($this->q)
      : $modelClass::find();

    return $this->createDataProvider($query);
  }
}
