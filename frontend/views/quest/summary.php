<?php

use common\components\AppStatus;
use common\helpers\DateTimeHelper;
use common\widgets\MarkDown;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Quest $quest */
/** @var common\models\QuestLog[] $logs */
$this->title = $quest->name;
$this->params['breadcrumbs'][] = ['label' => 'Quests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$status = AppStatus::from($quest->status);

$chapterName = "";
$missionName = "";
$chapterNumber = 0;
$missionNumber = 0;
?>
<div class="quest-summary">

    <container id='questHistory' class="row">
        <section id="questLogs" class="col-12 col-xl-8">
            <div class="card mb-4 text-decoration">
                <div class="card-header">
                    <h2 class="mb-0"><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="card-body">
                    <?= MarkDown::widget(['content' => $quest->description]) ?>
                    <?php foreach ($logs as $log): ?>
                        <article>
                            <?php
                            if ($log->chapter_name !== $chapterName) {
                                $chapterName = $log->chapter_name;
                                $chapterNumber += 1;
                                $missionNumber = 0;
                                $name = MarkDown::widget(['content' => "{$chapterNumber}. {$chapterName}"]);
                                $description = MarkDown::widget(['content' => $log->chapter_description]);
                                echo "<h3 class=\"text-warning\">{$name}</h3>\n<p class=\"text-muted\">{$description}</p>\n";
                            }
                            if ($log->mission_name !== $missionName) {
                                $missionName = $log->mission_name;
                                $missionNumber += 1;
                                $name = MarkDown::widget(['content' => "{$chapterNumber}.{$missionNumber}. {$missionName}"]);
                                $description = MarkDown::widget(['content' => $log->mission_description]);
                                echo "<h4 class=\"text-warning\">{$name}</h4>\n<p class=\"text-muted\">{$description}</p>\n";
                            }
                            $actionName = "Round {$log->round} - {$log->action_name}";
                            $description = MarkDown::widget(['content' => $log->action_description]);
                            echo "<span class=\"badge bg-warning text-dark\">{$actionName}</span><p class=\"text-muted\">{$description}</p>\n";

                            if ($log->result !== null) {
                                echo "<p><i class=\"bi dnd-d20\"></i> {$log->result}</p>\n";
                            }

                            $results = json_decode($log->description);
                            foreach ($results as $result) {
                                if ($result->name !== null && trim($result->name) !== '') {
                                    echo MarkDown::widget(['content' => "###### {$result->name}"]);
                                    $description = MarkDown::widget(['content' => $result->description]);
                                    echo "<div class=\"text-muted\">\n{$description}\n</div>\n";
                                }
                            }
                            ?>
                        </article>
                        <hr class="border border-warning w-90">
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="questContext" class="col-12 col-xl-4">
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
                        <?= Html::a('Back to Stories', ['story/index'], ['class' => 'btn btn-primary']) ?>
                    </div>
                </div>
            </div>
        </section>
    </container>

</div>
