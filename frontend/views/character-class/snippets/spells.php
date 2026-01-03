<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\CharacterClass $model */
/** @var array<int, array<int, array{id: int|string, name: string}>> $spellsByLevel */
?>
<div class="card">
    <div class="card-body">
        <div class="container">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5 row-cols-3xl-10 g-4">
                <?php foreach ($spellsByLevel as $level => $spells): ?>
                    <div class="col">
                        <h6><?= $level === 0 ? 'Cantrips' : Html::encode("Spell level {$level}") ?></h6>
                        <ul>
                            <?php foreach ($spells as $spell): ?>
                                <li>
                                    <a href="<?= Url::toRoute(['spell/view', 'id' => $spell['id']]) ?>">
                                        <?= $spell['name'] ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
