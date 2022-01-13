<?php

namespace app\commands;

use yii\helpers\Json;
use app\helpers\StringHelper;

use app\models\LoginForm;
use app\models\identity\User;

class UserController extends Controller
{
  static function createRandomPassword()
  {
    return StringHelper::random(8);
  }

  public function actionIndex()
  {
    $query = User::find()->orderBy('id desc');

    $widths = [4, 16, 8, 19];
    $printLine = function($array) use($widths) {
      $values = array_map(
        fn($i, $w, $v) => str_pad($v, $w),
        array_keys($widths),
        array_values($widths),
        $array
      );
      echo '| ', implode(' | ',  $values), ' |', "\n";
    };

    $printSeporator = function() use($widths) {
      $value = '+' . implode('+', array_map(
        fn($x) => str_repeat('-', $x + 2),
        $widths
      )) . '+';
      echo $value, "\n";
    };

    $fields = ['id', 'code', 'disabled', 'expire_at'];
    $printLine($fields);

    $printSeporator();

    foreach ($query->each() as $v) {
      $data = $v->getAttributes($fields);
      $printLine(array_values($data));
    }
  }

  public function actionCreate($code, $password = '')
  {
    $password = $password ?: static::createRandomPassword();

    $model = new User(compact('code'));
    $model->setPassword($password);

    $model->saveAndRefresh();
    return $model;
  }

  public function actionPassword($id_or_code, $password = '')
  {
    $model = $this->findModel($id_or_code);
    $password = $password ?: static::createRandomPassword();
    $model->setPassword($password);

    $model->saveAndRefresh();
    return $model;
  }

  public function actionQuery($q)
  {
    return User::query($q)->orderBy('id desc');
  }

  public function actionUpdate($id_or_code)
  {
    global $argv;
    $args = $argv;
    $argc = count($args);
    $index = array_search('--', $args);
    if ($index === false) {
      fwrite(STDERR, "Error: No -- found in command line.\n");
      return;
    }

    // display usage
    $usage = function($error) use($args, $index) {
      if ($index !== null) {
        $args = array_slice($args, 0, $index);
      }

      $columns = User::getTableSchema()->columns;
      unset($columns['id']);
      unset($columns['password_salt']);
      unset($columns['password_hash']);
      $columns['password'] = (object)(['name' => 'password', 'type' => 'string']);
      usort($columns, fn($x, $y) => strcmp($x->name, $y->name));

      $fields = array_map(
        function($x) {
          $name = str_pad($x->name, 16, ' ', STR_PAD_RIGHT);
          return "\t{$name} ({$x->type})";
        },
        $columns
      );


      fwrite(STDERR, 'Error: ' . $error . "\n\n");

      fwrite(STDERR, 'Usage: ' . implode(' ', $args) . " [options] -- <field=value> ...\n\n");

      fwrite(STDERR, "Available options:\n");
      fwrite(STDERR, "\tRun `{$args[0]} help {$args[1]}` for details.\n\n" );

      fwrite(STDERR, "Available fields:\n");
      fwrite(STDERR, implode("\n", $fields));
      fwrite(STDERR, "\n");
    };

    if ($argc <= $index + 1)  return $usage('no (field=value) specified.');

    // pre-set model properties
    $model = $this->findModel($id_or_code);
    for ($i = $index + 1; $i < $argc; ++$i) {
      $parts = explode('=', $args[$i], 2);
      if (count($parts) !== 2)  return $usage("invalid (field=value) expression `{$args[$i]}`");

      list($field, $value) = $parts;
      if (!$model->hasAttribute($field)) return $usage("invalid field name `{$field}`");
      $model->$field = $value;
    }

    // ask to confirm current operation
    $separator = "---- JSON ----";
    echo "Updating model {$model->code}#{$model->id}:\n{$separator}\n";
    echo Json::encode($model, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
    echo "{$separator}\n";

    if ($this->interactive) {
      if (!$this->confirm("\nApply the update?")) {
        return "\nAborted!";
      }
    }

    echo "\n";
    $model->saveAndRefresh();
    return $model;
  }

  public function actionLogin($username, $password)
  {
    try {
      \Yii::$app->attachBehavior('webUser',
        \app\behaviors\console\WebUserBehavior::class
      );

      $form = new LoginForm(compact('username', 'password'));
      if ($form->login()) {
        return $form->getUser();
      }

      return $form;
    } finally {
      \Yii::$app->detachBehavior('webUser');
    }
  }

  public function actionAccessToken($token)
  {
    return User::findIdentityByAccessToken($token);
  }

  public function findModel($id_or_code)
  {
    $where = is_numeric($id_or_code) ? $id_or_code : ['code' => $id_or_code];
    return User::findModel($where);
  }
}
