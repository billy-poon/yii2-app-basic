<?php

namespace app\models;

/**
 * @property mixed $result
 * @property-read \yii\web\IdentityInterface $identity
 */
class Form extends \yii\base\Model
{
  use ModelTrait;

  public function rules()
  {
    $prepend = [];
    if ($requiredFields = $this->requiredFields()) {
      $prepend[] = [$requiredFields, 'required'];
    }

    $append = [];
    if ($safeFields = $this->safeFields()) {
      $append[] = [$safeFields, 'safe'];
    }

    $extra = $this->extraRules() ?? [];

    return array_merge($prepend, $extra, $append);
  }

  protected function requiredFields()
  {
    return [];
  }

  protected function safeFields()
  {
    return $this->attributes();
  }

  protected function extraRules()
  {
    return [];
  }

  public function bodyParamsOnly()
  {
    return false;
  }

  public function tryLoad($data, $strict = false)
  {
    if (!$this->load($data) && $strict) {
      throw new \yii\web\BadRequestHttpException("No valid input data provided.");
    }

    return true;
  }

  public function tryLoadParams($strict = false)
  {
    $req = \Yii::$app->request;
    if (empty($data = $req->bodyParams) && !$this->bodyParamsOnly()) {
      $data = $req->queryParams;
    }

    return $this->tryLoad($data, $strict);
  }

  public function tryLoadArgs($strict = false)
  {
    $array = debug_backtrace();
    array_shift($array);

    $stack = $array[0];
    $method = new \ReflectionMethod($stack['class'], $stack['function']);

    $params = array_map(
      function ($x) {
        return $x->getName();
      },
      $method->getParameters()
    );
    $args = $stack['args'];

    $data = array_combine($params, $args);

    return $this->tryLoad($data, $strict);
  }

  protected function createActiveDataProvider($query, array $config = [])
  {
    return \Yii::createObject(
      array_merge(
        ['class' => 'yii\data\ActiveDataProvider'],
        compact('query'),
        $config
      )
    );
  }

  public function getIdentity($throw = true)
  {
    if ($result = \Yii::$app->user->identity) {
      return $result;
    }

    if ($throw) {
      throw new \yii\web\UnauthorizedHttpException('Login required.');
    }

    return null;
  }

  private $_result = null;
  public function setResult($val)
  {
    return $this->_result = $val;
  }

  public function getResult()
  {
    return $this->_result;
  }

  public function run()
  {
    if ($this->validate()) {
      $this->setResult($this->work());
    }

    return !$this->hasErrors();
  }

  public function work()
  {
    return null;
  }
}
