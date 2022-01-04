<?php

namespace app\models;

trait ModelTrait
{
  public function formName()
  {
    return '';
  }

  private $_extraAttributes = [];

  /**
   * Get value of cached attribute
   * @param string $key key of the attribute
   * @param mixed|Callable $callable
   *  callback function to get value if not found, no-callable as default value.
   */
  protected function getExtraAttribute($key, $callable = null)
  {
    if (isset($this->_extraAttributes[$key]) || array_key_exists($key, $this->_extraAttributes)) {
      return $this->_extraAttributes[$key];
    }

    if (is_callable($callable)) {
      return $this->_extraAttributes[$key] = call_user_func($callable, $this, $key);
    }

    return $callable;
  }

  protected function setExtraAttribute($key, $value)
  {
    return $this->_extraAttributes[$key] = $value;
  }

  protected function unsetExtraAttribute($key)
  {
    unset($this->_extraAttributes[$key]);
  }
}
