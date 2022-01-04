<?php

namespace app\bootstraps;

class SessionNegotiator implements \yii\base\BootstrapInterface
{
  public $sessionlessParam = 'sessionless';
  public $sessionlessHeaders = ['Authorization'];
  public $sessionlessModules = [];

  public function getRequest()
  {
    return \Yii::$app->getRequest();
  }

  public function disabledByHeader()
  {
    if (!empty($headers = (array)$this->sessionlessHeaders)) {
      $requestHeaders = $this->getRequest()->getHeaders();
      foreach ($headers as $v) {
        if ($requestHeaders->has($v)) {
          return true;
        }
      }
    }

    return false;
  }

  public function disabledByQueryParam()
  {
    if (!empty($param = $this->sessionlessParam)) {
      return $this->getRequest()->getQueryParam($param) === '1';
    }

    return false;
  }

  public function disabledByModule()
  {
    if (!empty($modules = (array)$this->sessionlessModules)) {
      list($route) = $this->getRequest()->resolve();
      list($module) = explode('/', $route ?? '');

      return in_array($module, $modules);
    }

    return false;
  }

  public function disabled()
  {
    return $this->disabledByQueryParam() ||
      $this->disabledByHeader() ||
      $this->disabledByModule();
  }

  public function bootstrap($app)
  {
    if ($this->disabled()) {
      $user = $app->user;
      $user->loginUrl = null;
      $user->enableSession = false;
      $user->enableAutoLogin = false;

      if (YII_DEBUG) {
        header('YII_SESSION: disabled');
      }
    }
  }
}
