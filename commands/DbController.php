<?php

namespace app\commands;

use app\models\DynamicRecord;
use yii\db\ColumnSchema;

class DbController extends Controller
{
  public function getDb()
  {
    return \Yii::$app->getDb();
  }

  public function actionIndex()
  {
    static $sqlMap = [
      'mysql' => 'show tables',
      'sqlsrv' => 'select name from sys.tables order by name'
    ];

    @list($dsnType) = explode(':', $this->getDb()->dsn, 2);
    if (!$sql = @$sqlMap[$dsnType]) {
      throw new \Exception("Unsupported dsn: $dsnType");
    }

    $data = array_map(
      fn($x) => array_values($x)[0],
      $this->getDb()
        ->createCommand($sql)
        ->query()
        ->readAll()
    );

    return implode("\n", $data);
  }

  public function actionModelProps($table)
  {
    $tableName = $this->tableName($table);
    $modelClass = DynamicRecord::createActiveClass($tableName);

    /** @var ColumnSchema[] */
    $columns = $modelClass::getTableSchema()->columns;
    return implode("\n", array_map(
      function($x) {
        static $typeMap = ['integer' => 'int', 'boolean' => 'bool', 'double' => 'float'];
        $name = str_pad($x->name, 16, ' ', STR_PAD_RIGHT);
        $phpType = str_pad(@$typeMap[$x->phpType] ?: $x->phpType, 6, ' ', STR_PAD_LEFT);
        $dbType = $x->dbType . ($x->allowNull ? '?' : '');
        return " * @property {$phpType} \${$name} {$dbType}";
      },
      $columns
    ));
  }

  private function tableName($table)
  {
    if ($prefix = $this->getDb()->tablePrefix) {
      if ($prefix !== substr($table, 0, strlen($prefix))) {
        return $prefix . $table;
      }
    }

    return $table;
  }
}
