<?php

namespace app\models\tree;

interface TreeNodeInterface
{
  public function getDepth();

  public function getParent();
  public function setParent(TreeNodeInterface $node = null);

  public function getChildren();
  public function appendChild(TreeNodeInterface $node);

  public function each($bfs = false);

  public function print($formatter, $depth = 0, $prepend = '', $isLastNode = true, $level = 0);
}
