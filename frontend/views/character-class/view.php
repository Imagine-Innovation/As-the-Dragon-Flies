<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\CharacterClass $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Character Classes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$has_spells = false;

foreach ($model->classProficiencies as $p) {
    if ($p->proficiency->name === "Spell") {
        $has_spells = true;
        break;
    }
}

$tabs = ['init' =>
    [
        'name' => 'Presentation',
        'snippet' => '_presentation',
        'anchor' => 'presentation',
    ],
    [
        'name' => 'Proficiencies',
        'snippet' => '_proficiencies',
        'anchor' => 'proficiencies',
    ],
];
if ($has_spells) {
    $tabs[] = [
            'name' => 'Spells',
            'snippet' => '_spells',
            'anchor' => 'spells',
    ];
}

$first_anchor = $tabs['init']['anchor'];
?>
<header class="content__title">
    <h4><?= Html::encode($model->name) ?> class</h4>
</header>

<div class="card">
    <div class="card-body">
        <div class="tab-container">
            <ul class="nav nav-tabs" role="tablist">
                <?php foreach ($tabs as $tab): ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $tab['anchor'] == $first_anchor ? " active" : "" ?>"
                           data-bs-toggle="tab" href="#<?= $tab['anchor'] ?>" role="tab">
                               <?= $tab['name'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content">
                <?php
                foreach ($tabs as $tab) {
                    if ($tab['anchor'] == $first_anchor) {
                        ?>
                        <div class="tab-pane active fade show" id="<?= $tab['anchor'] ?>" role="tabpanel">
                            <?= $this->renderFile('@app/views/character-class/' . $tab['snippet'] . '.php', ['model' => $model]) ?>
                        </div>
                    <?php } else { ?>
                        <div class="tab-pane fade" id="<?= $tab['anchor'] ?>" role="tabpanel">
                            <?= $this->renderFile('@app/views/character-class/' . $tab['snippet'] . '.php', ['model' => $model]) ?>
                        </div>
                        <?php
                    }   // End if first anchor
                }  // endforeach tabs; 
                ?>
            </div>
        </div>
    </div>
</div>
