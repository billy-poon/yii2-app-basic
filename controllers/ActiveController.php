<?php

namespace app\controllers;

abstract class ActiveController extends \yii\rest\ActiveController
{
  use ApiControllerTrait;

  public $readonly = true;

  public function actions()
  {
    $result = parent::actions();
    if ($this->readonly) {
      return array_reduce(
        ['view', 'index', 'options'],
        function($res, $x) use($result) {
          $res[$x] = $result[$x];
          return $res;
        }
      );
    }
    return $result;
  }

  public function actionSearch()
  {
    $form = new \app\models\forms\ActiveSearchForm($this->modelClass);
    if ($form->tryLoadParams() && $form->run()) {
      return $form->result;
    }

    return $form;
  }
}
