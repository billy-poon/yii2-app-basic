<?php

namespace app\filters;

class PaginationNegotiator extends \yii\base\ActionFilter
{
  public $paginationParam = 'pagination';

  public $defaultPageSize = 50;
  public $pageSizeLimit = [1, 100];

  public function disabledByQueryParam()
  {
    if (!empty($param = $this->paginationParam)) {
      return $this->getRequest()->getQueryParam($param) === '0';
    }

    return false;
  }

  public function afterAction($action, $result)
  {
    if ($result instanceof \yii\data\BaseDataProvider) {
      $pagination = $result->getPagination();
      if ($pagination !== false) {
        if ($this->disabledByQueryParam()) {
          $result->setPagination(false);
        } else if ($pagination instanceof \yii\data\Pagination) {
          $pagination->pageSizeLimit = $this->pageSizeLimit;
          $pagination->defaultPageSize = $this->defaultPageSize;
        }
      }
    }

    return $result;
  }
}
