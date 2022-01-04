<?php

namespace app\behaviors\console;

/**
 * Add some \yii\web\Response actions to \yii\console\Response
 * Usage: add following config to @app/config/console.php
 * ```
 * ...
 * 'components' => [
 *     'response' => [
 *         'as webResponse' => 'app\behaviors\console\WebResponseBehavior',
 *     ],
 *   ...
 * ],
 * ...
 * ```
 */
class WebResponseBehavior extends WebBehavior
{
  public $config;

  public function init()
  {
    if ($this->config === null) {
      $config = require __DIR__ . '/../../config/web.php';
      return $this->config = @$config['components']['response'] ?? [];
    }
  }

  public function printMessage($message)
  {
    fwrite(STDERR, '---- '. $message . ' ----' . "\n\n");
  }

  public function setStatusCode($value, $text = null)
  {
    $statusText = $text ?? @\yii\web\Response::$httpStatuses[$value];
    $this->printMessage("Status Code: {$value} ${statusText}");
  }

  public function getIsHead()
  {
    return false;
  }

  private $_options;
  protected function resolveOptions()
  {
    if ($this->_options === null) {
      global $argv;

      $options = [];
      if ($index = array_search('--', $argv)) {
        foreach (array_slice($argv, $index + 1) as $v) {
          @list($key, $value) = explode('=', $v, 2);
          if ($value === null) $value = true;
          $options[$key] []= $value;
        }
      }

      $this->_options = $options;
    }

    return $this->_options;
  }

  public function get($name = null, $defaultValue = null)
  {
    $options = $this->resolveOptions();
    if (isset($options[$name])) {
      $values = $options[$name];
      if (count($values) === 1) {
        return $values[0];
      }

      return $values;
    }

    return $defaultValue;
  }
}
