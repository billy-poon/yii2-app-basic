<?php

namespace app\controllers;

trait ApiControllerTrait
{
  /**
   * disable cors for this application
   * external applications should access over rpc
   */
  public $enableCors = false;

  /**
   * {@inheritdoc}
   */
  public function behaviors()
  {
    $result = parent::behaviors();

    // auth
    $result['authenticator'] = [
      'class' => \app\filters\auth\MixedAuth::class,
      'optional' => (array)$this->anonymousActions()
    ];

    // cors
    if ($this->enableCors) {
      list($authenticator, $verbFilter) = [
        @$result['authenticator'],
        @$result['verbFilter']
      ];
      unset($result['authenticator']);
      unset($result['verbFilter']);

      $result['corsFilter'] = [
        // you may need to set `cors` to support expose headers for pagination
        'class' => 'yii\filters\Cors',
      ];

      $verbFilter && ($result['verbFilter'] = $verbFilter);
      $authenticator && ($result['authenticator'] = $authenticator);
    }

    // negotiators
    $result['contentNegotiator']['formats'] = array_merge([
      'text/html' => \yii\web\Response::FORMAT_JSON
    ], $result['contentNegotiator']['formats']);

    $result['paginationNegotiator'] = 'app\filters\PaginationNegotiator';

    return $this->filterBehaviors($result);
  }

  /**
   * The actions can be accessed without credentials.
   *
   * @return array action names.
   */
  public function anonymousActions()
  {
    return [];
  }

  public function filterBehaviors($behaviors)
  {
    return $behaviors;
  }
}
