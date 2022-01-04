<?php

namespace app\models\tree;

class Tree extends TreeNode
{
  public $printSeparator = '';

  public static function create(array $items, callable $parentKeyGetter)
  {
    $nodes = array_map(
      function($x) {
        return $x instanceof TreeNodeInterface
          ? $x : new TreeNode($x);
      },
      $items
    );

    $iitems = $items;
    foreach ($iitems as $k => $v) {
      // unset($items[$k]);
      $pkey = call_user_func($parentKeyGetter, $v, $k);
      // echo "$pkey,";
      if ($pkey !== null) {
        $nodes[$pkey]->appendChild($nodes[$k]);
      }
    }

    $result = new static();
    foreach ($nodes as $v) {
      if (!$v->getParent()) {
        $result->appendChild($v);
      }
    }

    return $result;
  }

  public function getNodes()
  {
    return $this->getChildren();
  }

  public function appendChild(TreeNodeInterface $node)
  {
    parent::appendChild($node);
    $node->setParent();
  }

  public function fields()
  {
    return ['children'];
  }

  public function print($formatter, $depth = 0, $prepend = '', $isLastNode = true, $level = 0)
  {
    $children = $this->getChildren();
    if ($count = count($children)) {
      foreach ($children as $k => $v) {
        $v->print($formatter, $depth, $prepend, $isLastNode, $level);
        if ($k < $count - 1) {
          echo $this->printSeparator;
        }
      }
    }
  }
}
