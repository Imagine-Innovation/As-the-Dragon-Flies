<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\CharacterClass $model */
$index = array();
$data = array();
$spell = array();

foreach ($model->spells as $s) {
    $box = $s->spell_level;
    if (!in_array($box, $index, TRUE)) {
        $index[] = $box;
    }

    $spell['id'] = $s->id;
    $spell['name'] = $s->name;
    $data[$box][] = $spell;
}

sort($index);
?>
<div class="card">
    <div class="card-body">
        <div class="container">
            <div class="row g-4">
                <?php foreach ($index as $i): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <h6>Spell level <?= $i ?></h6>
                        <ul>
                            <?php foreach ($data[$i] as $l): ?>
                                <li>
                                    <a href="<?= Url::toRoute(['spell/view', 'id' => $l['id']]) ?>">
                                        <?= $l['name'] ?>
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
