<?php

use common\components\AppStatus;
use common\helpers\DateTimeHelper;
use common\helpers\WebResourcesHelper;
use common\widgets\Button;
use common\widgets\MarkDown;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Quest $quest */
/** @var common\models\QuestLog[] $logs */
$this->title = $quest->name;
$this->params['breadcrumbs'][] = ['label' => 'Quests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$status = AppStatus::from($quest->status);
$storyRoot = WebResourcesHelper::storyRootPath($quest->story_id);
$storyImage = $quest->image ?? $quest->story?->image;

$chapterName = "";
$missionName = "";
$chapterNumber = 0;
$missionNumber = 0;

// An action illustration is only displayed on the first attempt of a run of
// consecutive attempts with the same outcome; attempting any other action in
// between resets the counter.
$lastActionName = null;
$shownOutcomeImages = [];
?>
<style>
    @media print {
        .navbar,
        nav,
        .breadcrumb,
        footer,
        .no-print {
            display: none !important;
        }

        #questHistory {
            display: block !important;
        }

        #questLogs {
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }
    }
</style>
<div class="quest-summary">

    <container id='questHistory' class="row">
        <section id="questLogs" class="col-12 col-xl-8">
            <div class="card mb-4 text-decoration">
                <div class="card-header"<?php if (!empty($storyImage)): ?> style="background-image: url('<?= $storyRoot ?>/img/<?= $storyImage ?>'); background-size: cover; background-position: center; max-height: 400px;"<?php endif; ?>>
                    <h2 class="mb-0"><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="card-body">
                    <?= MarkDown::widget(['content' => $quest->description]) ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                        if ($log->chapterLog->name !== $chapterName) {
                            $chapterName = $log->chapterLog->name;
                            $chapterNumber += 1;
                            $missionNumber = 0;
                            $name = MarkDown::widget(['content' => "{$chapterNumber}. {$chapterName}"]);
                            $description = MarkDown::widget(['content' => $log->chapterLog->description]);
                            echo "<h3 class=\"text-warning\" style=\"clear: both;\">{$name}</h3>\n";
                            $chapterIllustration = $log->chapterLog->image ?? null;
                            if (!empty($chapterIllustration)) {
                                echo "<img class=\"float-end mb-3 ms-3\" src=\"{$storyRoot}/img/{$chapterIllustration}\" alt=\"" . Html::encode($chapterName ?? '') . "\" style=\"max-width: 300px;\">\n";
                            }
                            echo "<p class=\"text-muted\">{$description}</p>\n";
                        }

                        if ($log->missionLog->name !== $missionName) {
                            $missionName = $log->missionLog->name;
                            $missionNumber += 1;
                            $name = MarkDown::widget(['content' => "{$chapterNumber}.{$missionNumber}. {$missionName}"]);
                            $description = MarkDown::widget(['content' => $log->missionLog->description]);
                            echo "<h4 class=\"text-warning\" style=\"clear: both;\">{$name}</h4>\n";
                            $missionIllustration = $log->missionLog->image ?? null;
                            if (!empty($missionIllustration)) {
                                echo "<img class=\"float-end mb-3 ms-3\" src=\"{$storyRoot}/img/{$missionIllustration}\" alt=\"" . Html::encode($missionName ?? '') . "\" style=\"max-width: 300px;\">\n";
                            }
                            echo "<p class=\"text-muted\">{$description}</p>\n";
                        }

                        $actionName = "Round {$log->round} - {$log->action_name}";
                        $description = MarkDown::widget(['content' => $log->action_description]);

                        if ($log->action_name !== $lastActionName) {
                            $lastActionName = $log->action_name;
                            $shownOutcomeImages = [];
                        }
                        ?>
                        <article>
                            <span class="badge bg-warning text-dark"><?= $actionName ?></span><p class="text-muted"><?= $description ?></p>

                            <?php if ($log->result !== null): ?>
                                <p><i class="bi dnd-d20"></i> <?= $log->result ?></p>
                            <?php endif; ?>

                            <?php
                            $results = json_decode($log->description) ?? [];
                            foreach ($results as $result):
                                if ($result->name === null || trim($result->name) === '') {
                                    continue;
                                }
                                $outcomeIllustration = $result->image ?? null;
                                $illustration = '';
                                if (!empty($outcomeIllustration) && !in_array($outcomeIllustration, $shownOutcomeImages, true)) {
                                    $shownOutcomeImages[] = $outcomeIllustration;
                                    $illustration = "<img class=\"float-end mb-3 ms-3\" src=\"{$storyRoot}/img/{$outcomeIllustration}\" alt=\"" . Html::encode($result->name) . "\" style=\"max-width: 300px;\">\n";
                                }
                                ?>
                                <div class="clearfix">
                                    <?= $illustration ?>
                                    <?= MarkDown::widget(['content' => "###### {$result->name}"]) ?>
                                    <div class="text-muted">
                                        <?= MarkDown::widget(['content' => $result->description ?? '']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </article>
                        <hr class="border border-warning w-90">
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="questContext" class="col-12 col-xl-4 no-print">
            <div class="row">
                <div class="col-12 col-md-6 col-xl-12 col-3xl-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Participants</h5>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($quest->allPlayers as $player): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= Html::encode($player->name ?? '') ?>
                                    <span class="badge bg-secondary"><?= Html::encode($player->class->name ?? 'Adventurer') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-12 col-3xl-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Quest Details</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Story:</strong> <?= Html::encode($quest->story->name) ?></p>
                            <p><strong>Status:</strong>
                                <span class="badge bg-<?= $quest->status === AppStatus::COMPLETED->value ? 'success' : 'danger' ?>">
                                    <?= Html::encode($status->getLabel()) ?>
                                </span>
                            </p>
                            <p><strong>Started at:</strong> <?= Yii::$app->formatter->asDatetime($quest->started_at) ?></p>
                            <p><strong>Completed at:</strong> <?= Yii::$app->formatter->asDatetime($quest->completed_at) ?></p>
                            <p><strong>Elapsed time:</strong> <?= DateTimeHelper::elapsedTime($quest->started_at ?? time(), $quest->completed_at) ?></p>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <?=
                        Button::widget([
                            'icon' => 'bi-printer',
                            'title' => 'Print',
                            'onclick' => 'window.print(); return false;',
                        ])
                        ?>
                        <?= Html::a('Back to Stories', ['story/index'], ['class' => 'btn btn-primary']) ?>
                    </div>
                </div>
            </div>
        </section>
    </container>

</div>
