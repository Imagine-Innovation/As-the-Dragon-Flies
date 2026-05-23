<?php

use common\helpers\Status;
use common\helpers\StoryNeededClass;
use common\helpers\WebResourcesHelper;
use common\widgets\ActionButtons;
use common\widgets\Pagination;
use common\widgets\RecordCount;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Story[] $models */
/** @var int $count: total number of records retrived by the query */
/** @var int $page: current page number */
/** @var int $pageCount: nomber of pages regarding the limit of the query */
/** @var int $limit: nomber of records to be fetched */
?>
<div class="card">
    <div class="card-body">
        <?=
        RecordCount::widget([
            'count' => $count,
            'model' => 'story',
            'adjective' => 'defined',
            'actions' => [
                [
                    'url' => Url::toRoute(['story/create']),
                    'icon' => 'bi-pencil-square',
                    'tooltip' => 'Create a new story',
                ],
            ],
        ])
        ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Picture</th>
                        <th>Name</th>
                        <th>Lang</th>
                        <th class="text-center">Status</th>
                        <th>Players</th>
                        <th>Level</th>
                        <th>Classes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($models as $model):
                        $storyRoot = WebResourcesHelper::storyRootPath($model->id);
                        ?>
                        <tr>
                            <th scope="row">
                                <?php if ($model->image): ?>
                                    <img src="<?= $storyRoot ?>/img/<?= $model->image ?>" class="image-thumbnail">
                                <?php else: ?>
                                    &nbsp;
                                <?php endif; ?>
                            </th>
                            <td>
                                <?= Status::hyperlink($model) ?><br>
                            </td>
                            <td><?= $model->language ?></td>
                            <td class="text-center"><?= Status::label($model->status) ?></td>
                            <td><?= $model->companySize ?></td>
                            <td><?= $model->getRequiredLevels() ?></td>
                            <td><?= StoryNeededClass::classBadge($model); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <?=
        Pagination::widget([
            'page' => $page,
            'pageCount' => $pageCount,
            'limit' => $limit,
        ])
        ?>
        <!-- End Pagination -->
    </div>
</div>
