<?php

use common\components\AppStatus;
use frontend\widgets\AjaxContainer;
use frontend\widgets\BuilderOnclick;
use frontend\widgets\BuilderTab;
use frontend\components\BuilderTool;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\PlayerBuilder $model */
$this->title = 'Player builder';
$this->params['breadcrumbs'][] = ['label' => 'Players', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$firstAlignmentQuestion = BuilderTool::getFirstQuestion('alignment');

$tabs = BuilderTool::UPDATE_TABS;
$firstTab = array_key_first($tabs);

$isAdmin = (Yii::$app->user->identity->is_admin === 1);
?>
<div class="card" id="playerBuilder-update">
    <div class="card-body">
        <h4 class="card-title text-decoration"><?= Html::encode($this->title) ?></h4>
        <div class="actions">
            <a href="#" class="actions__item" data-bs-toggle="tooltip" title="Save" data-placement="bottom">
                <span onclick="$('#save-button').click();">
                    <i class="bi bi-floppy"></i>
                </span>
            </a>
        </div>
        <h6 class="card-subtitle">
            You've chosen to be a <?= $model->race->name ?> <?= $model->class->name ?> with a <?= $model->background->name ?> background, now you just need to fine-tune your characteristics before embarking on new adventures.
        </h6>
        <div class="d-none">
            Hidden div to embeb utility tags for PHP/JS communication
            <span id="hiddenWizard-topic"><?= $tabs[$firstTab]['wizard'] ?></span>
            <span id="hiddenWizard-firstQuestion-alignment"><?= $firstAlignmentQuestion ?></span>
            <span id="hiddenWizard-nextQuestion-Model"></span>
            <span id="hiddenWizard-nextQuestion-Id"></span>
            <span id="hiddenAgeTable"></span>
            <span id="hiddenPlayerId"><?= $model->id ?></span>
        </div>

        <div class="progress" style="height: 20px;">
            <div class="progress-bar bg-secondary" role="progressbar" id="builderProgressBar"
                 style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
        <div class="tab-container">
            <ul class="nav nav-tabs" role="tablist">
                <?php foreach ($tabs as $tab): ?>
                    <?php if (($isAdmin && $tab['admin']) || !$tab['admin']): ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $tab['anchor'] == $firstTab ? " active" : "" ?>"
                               data-bs-toggle="tab" href="#<?= $tab['anchor'] ?>-tab" role="tab"
                               <?= BuilderOnclick::widget(['player' => $model, 'wizard' => $tab['wizard'], 'onclick' => $tab['onclick']]) ?>                               >
                                   <?= $tab['name'] ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content">
                <?php foreach ($tabs as $tab): ?>
                    <div class="tab-pane <?= $tab['anchor'] == $firstTab ? "active fade show" : "fade" ?>" id="<?= $tab['anchor'] ?>-tab" role="tabpanel">
                        <?= $tab['onclick'] ?? BuilderTab::widget(['player' => $model, 'tabContent' => $tab]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="builderWizardModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Answer these questions to help you find the right player</h6>
            </div>
            <?=
            AjaxContainer::widget([
                'name' => 'ajaxBuilderWizardQA',
                'options' => [
                    'class' => 'modal-body',
                ],
            ])
            ?>
            <div class="modal-footer">
                <button type="button" class="btn btn-theme btn--icon" id="nextQuestionButton"
                        onclick="PlayerBuilder.nextQuestion(); return false;">
                    <i class="bi bi-caret-right"></i>
                </button>
                &nbsp;
                <button type="button" class="btn btn-theme btn--icon" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="builderEquipmentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Select one of these items</h6>
            </div>
            <?=
            AjaxContainer::widget([
                'name' => 'ajaxCategoryItems',
                'options' => [
                    'class' => 'modal-body',
                ],
            ])
            ?>
            <div class="modal-footer">
                <button type="button" class="btn btn-theme btn--icon" id="exitEquipmentModal-button">
                    <i class="bi bi-check"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="builderValidateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Let's go on an adventure</h6>
            </div>
            <div class="modal-body">
                <p class="text-muted">Your player is now completely created.</p>
                <p class="text-muted">To continue and launch him on new adventures, you need to validate him.</p>
                <p class="text-muted">Please note: once validated, it will no longer be possible to modify the player's characteristics.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning btn-sm mt-2 w-50" type="button"
                        onclick="PlayerBuilder.setProperty('status', <?= AppStatus::ACTIVE->value ?>);$('#save-button').click();">
                    <i class="bi bi-check"></i> Save & Play
                </button>
            </div>
        </div>
    </div>
</div>
