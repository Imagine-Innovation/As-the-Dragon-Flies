<?php
/** @var yii\web\View $this */
/** @var int $limit: number of records to be fetched */
/** @var int $pageCount: nomber of pages regarding the limit of the query */
/** @var int $lastVisiblePage The last page number */
/** @var int $page: current page number */
/** @var int[] $pages List of the page numbers to be displayed in the cleats */
$limits = [3, 5, 10, 20, 50];

/**
 * Number of page numbers of digits (i.e. cleats) to be displayed in the Pagination nav
 */
?>
<p class="text-center">page <?= $page + 1 ?> out of <?= $pageCount ?></p>
<nav>
    <ul class="pagination justify-content-center">
        <li class="page-item pagination-first<?= ($page === 0) ? ' disabled' : '' ?>">
            <a class="page-link"<?= ($page === 0) ? '' : ' href="#top" onclick="TableManager.loadGenericAjaxTable(0); return false;"' ?>>
                <i class="bi bi-chevron-bar-left"></i>
            </a>
        </li>
        <li class="page-item pagination-first<?= ($page === 0) ? ' disabled' : '' ?>">
            <a class="page-link"<?= ($page === 0) ? '' : ' href="#top" onclick="TableManager.loadGenericAjaxTable(' . $page - 1 . '); return false;"' ?>>
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
        <?php foreach ($pages as $p): ?>
            <li class="page-item<?= ($page === $p) ? ' active' : '' ?>">
                <a class="page-link"<?= ($page === $p) ? '' : ' href="#top" onclick="TableManager.loadGenericAjaxTable(' . $p . '); return false;"' ?>>
                    <?= $p + 1 ?>
                </a>
            </li>
        <?php endforeach; ?>
        <li class="page-item pagination-next<?= ($page === $lastVisiblePage) ? ' disabled' : '' ?>">
            <a class="page-link"<?= ($page === $lastVisiblePage) ? '' : ' href="#top" onclick="TableManager.loadGenericAjaxTable(' . $page + 1 . '); return false;"' ?>>
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
        <li class="page-item pagination-last<?= ($page === $pageCount) - 1 ? ' disabled' : '' ?>">
            <a class="page-link"<?= ($page === $lastVisiblePage) ? '' : ' href="#top" onclick="TableManager.loadGenericAjaxTable(' . $pageCount - 1 . '); return false;"' ?>>
                <i class="bi bi-chevron-bar-right"></i>
            </a>
        </li>
    </ul>
</nav>
<div class="dropdown">
    <button class="btn btn-theme dropdown-toggle dropdown-menu-right" data-bs-toggle="dropdown">Display <?= $limit ?> lines per page</button>
    <div class="dropdown-menu">
        <?php foreach ($limits as $l): ?>
            <?php if ($l === $limit): ?>
                <a href="#" class="dropdown-item disabled"><?= $l ?> lines</a>
            <?php else: ?>
                <a href="#" class="dropdown-item" onclick="TableManager.setLimit(<?= $l ?>); return false;"><?= $l ?> lines</a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
