<?php

namespace app\modules\v1\controllers;

class DefaultController extends \app\controllers\ApiController
{
  public function anonymousActions()
  {
    return ['*'];
  }

  public function actionIndex()
  {
    if (YII_ENV_DEV) {
      $array = $GLOBALS;
      unset($array['GLOBALS']);

      return $array;
    }

    return ['$_REQUEST' => $_REQUEST];
  }

  public function actionHello($name = 'World')
  {
    return "Hello, {$name}!";
  }
}
