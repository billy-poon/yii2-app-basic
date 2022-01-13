<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\account\LoginForm;

class AccountController extends \app\controllers\ApiController
{
  public function anonymousActions()
  {
    return '*';
  }

  protected function verbs()
  {
    return [
      'login' => ['POST'],
      'logout' => ['POST'],
    ];
  }

  public function actionLogin()
  {
    $form = new LoginForm();
    if ($form->tryLoadParams() && $form->run()) {
      return $form->result;
    }

    return $form;
  }

  public function actionMy()
  {
    $user = \Yii::$app->user;
    if (!$user->isGuest) {
      return $user->identity;
    }

    $this->response->setStatusCode(401);
    return null;
}

  public function actionLogout()
  {
    return \Yii::$app->user->logout();
  }
}
