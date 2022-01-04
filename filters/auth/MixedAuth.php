<?php

namespace app\filters\auth;

class MixedAuth extends \yii\filters\auth\CompositeAuth
{
  public $authMethods = [
    'yii\filters\auth\HttpBearerAuth',
    '\yii\filters\auth\QueryParamAuth',
  ];

  public function authenticate($user, $request, $response)
  {
    if ($result = parent::authenticate($user, $request, $response)) {
      return $result;
    }

    return $user->identity;
  }
}
