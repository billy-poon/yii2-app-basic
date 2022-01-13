<?php

namespace app\behaviors\console;

class WebUserBehavior extends WebBehavior
{
  public function getUser()
  {
    static $result = false;
    if ($result === false) {
      $result = new WebUserBehavior__WebUser();
    }

    return $result;
  }
}

class WebUserBehavior__WebUser
{
  public function login()
  {
    return true;
  }
}
