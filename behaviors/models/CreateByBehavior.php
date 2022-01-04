<?php

namespace app\behaviors\models;

use yii\db\BaseActiveRecord;
use yii\web\Application as WebApp;
use yii\console\Application as ConsoleApp;

class CreateByBehavior extends \yii\base\Behavior
{
  /**
   * {@inheritdoc}
   */
  public function events()
  {
    return [
      BaseActiveRecord::EVENT_BEFORE_INSERT => 'onBeforeInsert',
    ];
  }

  public function onBeforeInsert($event)
  {
    if ($event->isValid) {
      $model = $event->sender;
      if ($model->hasAttribute('create_by')) {
        $create_by = null;
        $app = \Yii::$app;
        if ($app instanceof WebApp) {
          if ($user = $app->user->identity) {
            $create_by = $user->getId();
          }
        } else if ($app instanceof ConsoleApp) {
          $create_by = -1;
        }

        if ($create_by !== null) {
          $model->create_by = $create_by;
        }
      }
    }
  }
}
