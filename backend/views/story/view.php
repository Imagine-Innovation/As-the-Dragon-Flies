<?php

use common\helpers\StoryNeededClass;
use common\helpers\WebResourcesHelper;
use common\widgets\Button;
use common\widgets\MarkDown;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Story $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$storyRoot = WebResourcesHelper::storyRootPath($model->id);
?>
<div class="container">

    <div class="card mb-3">
        <div class="actions">
            <?=
            Button::widget([
                'mode' => 'icon',
                'url' => Url::toRoute(['story/update', 'id' => $model->id]),
                'icon' => 'dnd-spell',
                'tooltip' => 'Edit story',
            ])
            ?>
        </div>
        <div class="row g-0 d-flex"> <!-- Add d-flex to the row -->
            <div class="col-md-4 d-flex align-items-stretch"> <!-- Add d-flex and align-items-stretch -->
                <img src="<?= $storyRoot ?>/img/<?= $model->image ?>" class="img-fluid object-fit-cover rounded-start w-100" alt="<?= $model->name ?>">
            </div>
            <div class="col-md-8 text-decoration d-flex flex-column"> <!-- Add d-flex and flex-column -->
                <div class="card-header">
                    <h3 class="card-title"><?= $model->name ?></h3>
                </div>
                <div class="card-body flex-grow-1"> <!-- Add flex-grow-1 -->
                    <div class="card-text"><?= MarkDown::widget(['content' => $model->description]) ?></div>
                    <br>
                    <?= StoryNeededClass::classList($model); ?>
                    <p>
                        <span class="badge badge-info"><?= $model->getRequiredLevels() ?></span>
                        <span class="badge badge-info"><?= $model->companySize ?></span>
                    </p>
                    <?php foreach ($model->chapters as $chapter): ?>
                        <p>
                            <a href="<?= Url::toRoute(['chapter/view', 'id' => $chapter->id]) ?>">
                                Chapter <?= $chapter->chapter_number ?> - <?= $chapter->name ?>
                            </a>
                        </p>
                    <?php endforeach; ?>
                    <?php if ($model->tags): ?>
                        <div class="listview__attrs">
                            Tags:
                            <?php foreach ($model->tags as $tag): ?>
                                <span><?= $tag->name ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <?=
                    Button::widget([
                        'url' => Url::toRoute(['chapter/create', 'storyId' => $model->id]),
                        'icon' => 'dnd-scroll',
                        'title' => 'Add a chapter',
                        'isCta' => true,
                    ])
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
