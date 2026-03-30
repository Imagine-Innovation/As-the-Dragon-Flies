<?php

use common\helpers\Status;
use common\helpers\Utilities;
use common\helpers\WebResourcesHelper;
use common\widgets\ActionButtons;
use common\widgets\Pagination;
use common\widgets\RecordCount;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Player[] $models */
/** @var int $count: total number of records retrived by the query */
/** @var int $page: current page number */
/** @var int $pageCount: nomber of pages regarding the limit of the query */
/** @var int $limit: nomber of records to be fetched */
$imgPath = WebResourcesHelper::imagePath();
?>
<div class="card">
    <div class="card-body">
        <?=
        RecordCount::widget([
            'count' => $count,
            'model' => 'player',
            'adjective' => 'defined',
        ])
        ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Avatar</th>
                        <th>Name</th>
                        <th>User</th>
                        <th class="text-center">Status</th>
                        <th>Level</th>
                        <th>Race</th>
                        <th>Class</th>
                        <th>Background</th>
                        <th>Alignment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <th scope="row">
                                <img src="<?= $imgPath ?>/character/<?= $model->avatar ?>" class="image-thumbnail">
                            </th>
                            <td>
                                <?= Status::hyperlink($model) ?><br>
                            </td>
                            <td>
                                <a href="<?= Url::toRoute(['user/view', 'id' => $model->user->id]) ?>">
                                    <?= Utilities::encode($model->user->username) ?>
                                </a>
                            </td>
                            <td class="text-center"><?= Status::icon($model->status) ?></td>
                            <td><?= $model->level->name ?></td>
                            <td><?= $model->race->name ?></td>
                            <td><?= $model->class->name ?></td>
                            <td><?= $model->background->name ?></td>
                            <td><?= $model->alignment->name ?? 'Unkown' ?></td>
                            <td>
                                <?=
                                ActionButtons::widget([
                                    'model' => $model,
                                    'mode' => 'table',
                                    'isOwner' => true,
                                ])
                                ?>
                            </td>
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
