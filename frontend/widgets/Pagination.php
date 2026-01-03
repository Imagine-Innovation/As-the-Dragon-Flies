<?php

namespace frontend\widgets;

use yii\base\Widget;

class Pagination extends Widget
{

    public int $limit;
    public int $pageCount;
    public int $page;
    private int $firstPage = 0;
    private int $lastVisiblePage = 0;

    public function run() {
        $maxPages = 3;
        $offset = floor($maxPages / 2);

        $this->firstPage = (int) max(0, $this->page - $offset);
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

    /**
     *
     * @return array<int>
     */
    private function calculatePages(): array {
        $pages = [];
        for ($i = $this->firstPage; $i <= $this->lastVisiblePage; $i++) {
            $pages[] = $i;
        }

        return $pages;
    }
}
