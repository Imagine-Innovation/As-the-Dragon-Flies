<?php

use frontend\components\BuilderComponent;
use frontend\widgets\AjaxContainer;
use frontend\widgets\BuilderTab;
use frontend\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\PlayerBuilder $model */
$this->title = 'Player builder';
$this->params['breadcrumbs'][] = ['label' => 'Players', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$firstClassQuestion = BuilderComponent::getFirstQuestion('class');
$firstRaceQuestion = BuilderComponent::getFirstQuestion('race');

$tabs = BuilderComponent::CREATE_TABS;
$firstTab = array_key_first($tabs);

$isAdmin = (Yii::$app->user->identity->is_admin === 1);
?>
<div class="card" id="playerBuilder-create">
    <div class="card-body">
        <h4 class="card-title text-decoration"><?= Html::encode($this->title) ?></h4>
        <div class="actions">
            <?=
            Button::widget([
                'mode' => 'icon',
                'id' => 'showBuilderWizardModal-button',
                'icon' => 'bi-magic',
                'tooltip' => 'Character builder wizard',
                'modal' => 'builderWizardModal'
            ])
            ?>
        </div>
        <h6 class="card-subtitle">
            A step-by-step wizard to help you create your player
        </h6>
        <div class="d-none">
            Hidden div to embeb utility tags for PHP/JS communication
            <span id="hiddenWizard-topic"><?= $tabs[$firstTab]['wizard'] ?></span>
            <span id="hiddenWizard-firstQuestion-race"><?= $firstRaceQuestion ?></span>
            <span id="hiddenWizard-firstQuestion-class"><?= $firstClassQuestion ?></span>
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
                            <a role="tab" class="nav-link<?= $tab['anchor'] == $firstTab ? " active" : "" ?>"
                               id="builderTab-<?= $tab['anchor'] ?>-<?= $tab['wizard'] ?>"
                               data-bs-toggle="tab" href="#<?= $tab['anchor'] ?>-tabContent">
                                   <?= $tab['name'] ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content">
                <?php foreach ($tabs as $tab): ?>
                    <div role="tabpanel" id="<?= $tab['anchor'] ?>-tabContent"
                         class="tab-pane <?= $tab['anchor'] == $firstTab ? " active show" : "" ?> fade">
                             <?=
                             BuilderTab::widget([
                                 'player' => $model,
                                 'tabContent' => $tab,
                             ])
                             ?>
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
                <button class="btn btn-warning btn-sm mt-2 w-25" id="nextQuestionButton" type="button"
                        onclick="PlayerBuilder.nextQuestion(); return false;">
                    <i class="bi bi-caret-right"></i> Next question
                </button>
                &nbsp;
                <button class="btn btn-secondary btn-sm mt-2 w-25" type="button" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="builderSaveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Congratulation, your player is almost created</h6>
            </div>
            <div class="modal-body">
                <p>Now, let's dive into the next exciting step: saving your work!</p>
                <p>In the next step, you'll have the opportunity to explore and enhance your player, but race, class, background, and history will remain as you've defined them.</p>
            </div>
            <div class="modal-footer">
                <?=
                Button::widget([
                    'icon' => 'bi-floppy',
                    'title' => 'Save and continue',
                    'id' => 'playerBuilderSaveButton',
                    'callToAction' => true,
                    'style' => 'btn-sm mt-2 w-50',
                ])
                ?>
            </div>
        </div>
    </div>
</div>
