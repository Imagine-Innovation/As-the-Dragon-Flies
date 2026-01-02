<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\CharacterClass $model */
$index = array();
$data = array();
$spell = array();

foreach ($model->spells as $s) {
    $spellLevel = $s->spell_level;
    if (!in_array($spellLevel, $index, TRUE)) {
        $index[] = $spellLevel;
    }

    $spell['id'] = $s->id;
    $spell['name'] = $s->name;
    $data[$spellLevel][] = $spell;
}

sort($index);
?>
<div class="card">
    <div class="card-body">
        <div class="container">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5 row-cols-3xl-10 g-4">
                <?php foreach ($index as $i): ?>
                    <div class="col">
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
