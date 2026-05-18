<?php

use common\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $players */
/** @var int $selectedPlayerId */
/** @var int $userId */
?>
<li class="dropdown top-nav__notifications">
    <a class="top-nav position-relative" href="#" data-bs-toggle="dropdown">
        <i class="bi bi-file-earmark-person"></i>
        <div id="currentPlayerBadge"></div>
    </a>
    <div class="dropdown-menu dropdown-menu-right dropdown-menu--block">
        <div class="dropdown-header">
            Current Player

            <div class="actions">
                <?=
    Button::widget([
        'mode' => 'icon',
        'url' => Url::toRoute(['site/index']),
        'icon' => 'bi-file-earmark-person',
    ])
?>
            </div>
        </div>

        <div class="listview listview--hover">
            <div class="card">
                <div class="card-body">

                    <?php foreach ($players as $player): ?>
                        <?php if ($player['id'] === $selectedPlayerId): ?>
                            <div class="mb-2">
                                <i class="bi bi-person-check text-primary mr-2"></i>
                                <span data-bs-toggle="tooltip" title="<?= ucfirst($player['tooltip']) ?>" data-placement="bottom">
                                    <?= $player['name'] ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <div class="mt-3">
                        <?=
    Button::widget([
        'url' => Url::toRoute(['site/index']),
        'icon' => 'bi-arrow-left-circle',
        'title' => 'Back to Lobby',
        'style' => 'btn-sm w-100',
    ])
?>
                    </div>

                    <div class="d-none">
                        <span id="hiddenSelectedPlayerId"><?= $selectedPlayerId ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</li>
