<?php

namespace frontend\widgets;

/**
 * Description of Pagination
 *
 * @author franc
 */
use yii\base\Widget;

class Pagination extends Widget {

    public $limit;
    public $pageCount;
    public $page;
    
    private $firstPage = 0;
    private $lastVisiblePage = 0;

    public function run() {
        $maxPages = 3;
        $offset = floor($maxPages / 2);

        $this->firstPage = max(0, $this->page - $offset);
        $tmpLastPage = $this->firstPage + $maxPages - 1;
        $this->lastVisiblePage = min($this->pageCount - 1, $tmpLastPage);

        return $this->render('pagination', [
                    'limit' => $this->limit,
                    'pageCount' => $this->pageCount,
                    'lastVisiblePage' => $this->lastVisiblePage,
                    'page' => $this->page,
                    'pages' => $this->calculatePages(),
        ]);
    }

    private function calculatePages() {
        $pages = [];
        for ($i = $this->firstPage; $i <= $this->lastVisiblePage; $i++) {
            $pages[] = $i;
        }

        return $pages;
    }
}
