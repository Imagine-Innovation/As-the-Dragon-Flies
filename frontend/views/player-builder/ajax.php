<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\PlayerBuilder $models */
/** @var int $count: total number of records retrived by the query */
/** @var int $page: current page number */
/** @var int $pageCount: nomber of pages regarding the limit of the query */
/** @var int $limit: nomber of records to be fetched */
?>
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-decoration">List of the players you have defined so far</h4>
            <div class="actions">
                <a href="<?= Url::toRoute(['player/builder']) ?>" class="actions__item position-relative">
                    <span data-bs-toggle="tooltip" title="Create a new player" data-placement="bottom"
                          <i class="bi bi-journal-plus"></i>
                    </span>
                </a>
            </div>
            <div class="row g-4">
                <?php foreach ($models as $player): ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
                        <?= $this->renderFile('@app/views/player-builder/_card.php', ['player' => $player]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
