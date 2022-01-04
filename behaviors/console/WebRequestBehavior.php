<?php

namespace app\behaviors\console;

/**
 * Add some \yii\web\Request actions to \yii\console\Request
 * Usage: add following config to @app/config/console.php
 * ```
 * ...
 * 'components' => [
 *     'request' => [
 *         'as webRequest' => 'app\behaviors\console\WebRequestBehavior',
 *     ],
 *   ...
 * ],
 * ...
 * ```
 */
class WebRequestBehavior extends WebBehavior
{
  public $config;

  public function init()
  {
    if ($this->config === null) {
      $config = require __DIR__ . '/../../config/web.php';
      return $this->config = @$config['components']['request'] ?? [];
    }
  }

  public function getCookieValidationKey()
  {
    return @$this->config['cookieValidationKey'];
  }

  public function getIsHead()
  {
    return false;
  }

  private $_options;
  public function resolveOptions()
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

      foreach ($options as &$v) {
        if (count($v) === 1) {
          $v = $v[0];
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
      return $options[$name];
    }

    return $defaultValue;
  }
}
