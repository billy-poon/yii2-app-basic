<?php

call_user_func(function () {
  $existsFile = function ($file, $yes = true, $no = false) {
    return is_file(__DIR__ . '/../runtime/' . $file) ? $yes : $no;
  };

  defined('YII_ENV') or define('YII_ENV', $existsFile('.prod', 'prod', 'dev'));
  defined('YII_DEBUG') or define('YII_DEBUG', $existsFile('.debug'));

  if (YII_DEBUG) {
    header('YII_DEBUG: yes');
    header('YII_ENV: ' . YII_ENV);
  }
});
