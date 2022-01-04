<?php

namespace app\commands;

class Controller extends \yii\console\Controller
{
  public function behaviors()
  {
    $result = parent::behaviors();
    $result['actionResultFilter'] = ActionResultFilter::class;
    return $result;
  }
}
