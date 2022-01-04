<?php

namespace app\models\tree;

trait TreeNodeTrait //implements TreeNodeInterface
{
  public function getDepth()
  {
    if ($parent = $this->getParent()) {
      return $parent->getDepth() + 1;
    }

    return 0;
  }

  private $_parent;
  public function getParent()
  {
    return $this->_parent;
  }

  public function setParent(TreeNodeInterface $node = null)
  {
    $this->_parent = $node;
    return $this;
  }

  private $_children = [];
  public function getChildren()
  {
    return $this->_children;
  }

  public function empty()
  {
    $this->_children = [];
    return $this;
  }

  public function appendChild(TreeNodeInterface $node)
  {
    $node->setParent($this);
    $this->_children []= $node;

    return $this;
  }

  public function appendChildren(array $nodes)
  {
    foreach ($nodes as $v) {
      $this->appendChild($v);
    }

    return $this;
  }

  public function each($bfs = false)
  {
    if ($bfs) {
      yield from $this->bfsEach();
    } else {
      yield from $this->dfsEach();
    }
  }

  protected function bfsEach()
  {
    if ($items = $this->getChildren()) {
      $queue = $items;
      while(count($queue)) {
        $node = array_shift($queue);
        yield $node;

        if ($children = $node->getChildren()) {
          array_push($queue, ...$children);
        }
      }
    }
  }

  protected function dfsEach()
  {
    if ($items = $this->getChildren()) {
      foreach ($items as $v) {
        yield $v;
        yield from $v->each(false);
      }
    }
  }

  public function __toString()
  {
    throw new \yii\base\InvalidConfigException('Should be implemented in class.');
  }

  public function print($formatter, $depth = 0, $prepend = '', $isLastNode = true, $level = 0)
  {
    if (!is_callable($formatter)) {
      $formatter = [$this, '__toString'];
    }

    $label = $formatter($this);
    $handle = $level > 0
      ? ($isLastNode ? '└── ' : '├── ')
      : '';
    echo $prepend, $handle, $label, "\n";

    // 超出指定深度时不再打印子节点
    if ($depth > 0 && $level + 1 > $depth) return;

    $children = $this->getChildren();
    if ($count = count($children)) {
      $prepend .= $level > 0
        ? ($isLastNode ? '    ' : '│   ')
        : '';
      foreach ($children as $k => $v) {
        $v->print($formatter, $depth, $prepend, $k === $count - 1, $level + 1);
      }
    }
  }
}
