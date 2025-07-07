<?php

use frontend\widgets\AjaxContainer;
use frontend\widgets\BuilderTab;
use frontend\components\BuilderTool;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\PlayerBuilder $model */
$this->title = 'Player builder';
$this->params['breadcrumbs'][] = ['label' => 'Players', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$firstClassQuestion = BuilderTool::getFirstQuestion('class');
$firstRaceQuestion = BuilderTool::getFirstQuestion('race');

$tabs = BuilderTool::CREATE_TABS;
$firstTab = array_key_first($tabs);

$isAdmin = (Yii::$app->user->identity->is_admin === 1);
?>
<div class="card" id="playerBuilder-create">
    <div class="card-body">
        <h4 class="card-title text-decoration"><?= Html::encode($this->title) ?></h4>
        <div class="actions">
            <a href="#" id="showBuilderWizardModal-button" class="actions__item" data-bs-toggle="tooltip" title="Character builder wizard" data-placement="bottom">
                <span data-bs-toggle="modal" data-bs-target="#builderWizardModal">
                    <i class="bi bi-magic"></i>
                </span>
            </a>
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
                            <a class="nav-link<?= $tab['anchor'] == $firstTab ? " active" : "" ?>"
                               data-bs-toggle="tab" href="#<?= $tab['anchor'] ?>-tab" role="tab"
                               onclick="PlayerBuilder.initWizard('<?= $tab['wizard'] ?>');">
                                   <?= $tab['name'] ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content">
                <?php foreach ($tabs as $tab): ?>
                    <div class="tab-pane <?= $tab['anchor'] == $firstTab ? "active fade show" : "fade" ?>" id="<?= $tab['anchor'] ?>-tab" role="tabpanel">
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
                <button type="button" class="btn btn-theme btn--icon"
                        onclick="$('#save-button').click();">
                    <i class="bi bi-floppy"></i>
                </button>
            </div>
        </div>
    </div>
</div>
