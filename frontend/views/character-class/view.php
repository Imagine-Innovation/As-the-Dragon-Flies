<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\CharacterClass $model */
/** @var bool $hasSpell */
/** @var array<int, string> $proficiencyHeaders */
/** @var array<int, array<int, array{value: string, is_header: bool}>> $proficiencies */
/** @var array<int, array<int, array{id: int|string, name: string}>> $spellsByLevel */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Character Classes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$tabs = [
    [
        'name' => 'Presentation',
        'snippet' => 'presentation',
        'anchor' => 'presentation',
    ],
    [
        'name' => 'Proficiencies',
        'snippet' => 'proficiencies',
        'anchor' => 'proficiencies',
    ],
];
if ($hasSpell) {
    $tabs[] = [
        'name' => 'Spells',
        'snippet' => 'spells',
        'anchor' => 'spells',
    ];
}

$first_anchor = 'presentation';
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
                        <a class="nav-link<?= ($tab['anchor'] === $first_anchor) ? ' active' : '' ?>"
                           data-bs-toggle="tab" href="#<?= $tab['anchor'] ?>" role="tab">
                               <?= $tab['name'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content">
                <?php foreach ($tabs as $tab): ?>
                    <div class="tab-pane <?= ($tab['anchor'] === $first_anchor) ? 'active fade show' : 'fade' ?>" id="<?= $tab['anchor'] ?>" role="tabpanel">
                        <?=
                        $this->renderFile('@app/views/character-class/snippets/' . $tab['snippet'] . '.php', [
                            'model' => $model,
                            'hasSpell' => $hasSpell,
                            'proficiencyHeaders' => $proficiencyHeaders,
                            'proficiencies' => $proficiencies,
                            'spellsByLevel' => $spellsByLevel,
                        ])
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
