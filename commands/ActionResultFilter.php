<?php

namespace app\commands;

use app\behaviors\console\WebRequestBehavior;
use app\behaviors\console\WebResponseBehavior;
use yii\data\ActiveDataProvider;

class ActionResultFilter extends \yii\base\ActionFilter
{
  public function afterAction($action, $result)
  {
    if (is_string($result)) {
      static::printString($result);
    } else if ($result !== null) {
      static::printJson($result);
    }
  }

  static function printString($data)
  {
    echo $data, "\n";
  }

  static function printJson($data)
  {
    $serializer = new ActionResultFilter__Serializer();
    if ($data instanceof \yii\db\QueryInterface) {
      $data = new ActiveDataProvider([
        'query' => $data,
        'pagination' => [
          'params' => $serializer->request->resolveOptions(),
        ],
      ]);
    }

    $result = $serializer->serialize($data);

    echo \yii\helpers\Json::encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
  }
}

class ActionResultFilter__Serializer extends \yii\rest\Serializer
{
  public function init()
  {
    parent::init();

    $methodName = '__webBehavior';
    if (!$this->request->hasMethod($methodName)) {
      $this->request->attachBehavior($methodName, WebRequestBehavior::class);
    }

    if (!$this->response->hasMethod($methodName)) {
      $this->response->attachBehavior($methodName, WebResponseBehavior::class);
    }
  }

  /**
   * @param \yii\data\Pagination $pagination
   */
  protected function addPaginationHeaders($pagination)
  {
    $page = implode('/', [
      $pagination->getPage() + 1,
      $pagination->getPageCount(),
    ]);
    $total = implode('/', [
      $pagination->getPageSize(),
      $pagination->totalCount,
    ]);

    $this->response->printMessage("Pagination: {$page} @ {$total}");
  }
}
