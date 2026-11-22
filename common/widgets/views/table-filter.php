<?php

use yii\helpers\Html;

/** @var array<string, string> $items value => label pairs to filter on */
/** @var bool $multipleValues whether several values can be selected at the same time */
/** @var bool $searchString whether the search form is displayed */
/** @var string $filterName name of the filter inputs */
/** @var string $searchId id and name of the search input */
/** @var string $searchPlaceholder placeholder of the search input */
/** @var string $onLoad JS call executed when a filter value changes */
$filterType = $multipleValues ? 'checkbox' : 'radio';
$comboBoxValues = count($items) > 3;
?>
<div class="d-flex align-items-center flex-wrap gap-3 mb-2">
<?php if ($items !== []): ?>
    <div>
    <?php if ($comboBoxValues): ?>
        <select id="<?= Html::encode($filterName) ?>" name="<?= Html::encode($filterName) ?>"
                class="form-select"<?= $multipleValues ? ' multiple' : '' ?>
                onchange='<?= $onLoad ?>'>
            <?php if (!$multipleValues): ?>
                <option value="" selected><?= Html::encode(\Yii::t('app', 'All')) ?></option>
            <?php endif; ?>
            <?php foreach ($items as $value => $label): ?>
                <option value="<?= Html::encode((string) $value) ?>"><?= Html::encode($label) ?></option>
            <?php endforeach; ?>
        </select>
    <?php else: ?>
        <?php foreach ($items as $value => $label): ?>
            <div class="custom-control custom-<?= $filterType ?> custom-control-inline mb-2">
                <input type="<?= $filterType ?>" id="<?= Html::encode($filterName) ?>-<?= Html::encode((string) $value) ?>"
                       name="<?= Html::encode($filterName) ?>"
                       value="<?= Html::encode((string) $value) ?>"
                       class="custom-control-input"
                       onchange='<?= $onLoad ?>'>
                <label class="custom-control-label"
                       for="<?= Html::encode($filterName) ?>-<?= Html::encode((string) $value) ?>"><?= Html::encode($label) ?></label>
            </div>
<?php endforeach; ?>
    <?php endif; ?>
    </div>
<?php endif; ?>
<?php if ($searchString): ?>
    <div class="input-group ms-auto" style="max-width: 20rem;">
        <input type="text" id="<?= Html::encode($searchId) ?>" name="<?= Html::encode($searchId) ?>" class="form-control"
               placeholder="<?= Html::encode($searchPlaceholder) ?>"
               onkeydown='if (event.key === "Enter") { event.preventDefault(); <?= $onLoad ?> }'>
        <button type="button" class="btn btn-outline-secondary"
                onclick='<?= $onLoad ?>'>
            <i class="bi bi-search"></i>
        </button>
    </div>
<?php endif; ?>
</div>
