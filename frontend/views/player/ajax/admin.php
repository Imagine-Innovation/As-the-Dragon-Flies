<?php

use common\helpers\Status;
use common\helpers\Utilities;
use frontend\widgets\ActionButtons;
use frontend\widgets\Pagination;
use frontend\widgets\RecordCount;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var frontend\models\PlayerBuilder[] $models */
/** @var int $count: total number of records retrived by the query */
/** @var int $page: current page number */
/** @var int $pageCount: nomber of pages regarding the limit of the query */
/** @var int $limit: nomber of records to be fetched */
$isAdmin = Yii::$app->user->identity->is_admin;
$currentUserId = Yii::$app->user->id;

if (Yii::$app->user->identity->is_player) {
    $recordCountWidget = RecordCount::widget([
        'count' => $count,
        'model' => 'player',
        'adjective' => 'defined',
        'actions' => [
            [
                'url' => Url::toRoute(['player/builder']),
                'icon' => 'bi-person-add',
                'tooltip' => 'Create a new player',
            ],
        ],
    ]);
} else {
    $recordCountWidget = RecordCount::widget([
        'count' => $count,
        'model' => 'player',
        'adjective' => 'defined',
    ]);
}
?>
<div class="card">
    <div class="card-body">
        <?= $recordCountWidget ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Avatar</th>
                        <th>Name</th>
                        <?php if ($isAdmin): ?>
                            <th>User</th>
                        <?php endif; ?>
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
                        <?php $isOwner = $model->user->id === $currentUserId; ?>
                        <tr>
                            <th scope="row">
                                <img src="img/character/<?= $model->avatar ?>" class="image-thumbnail">
                            </th>
                            <td>
                                <?php if ($isOwner): ?>
                                    <?= Status::hyperlink($model) ?>
                                <?php else: ?>
                                    <?= Utilities::encode(empty($model->name) ? 'Unknown' : $model->name) ?>
                            <?php endif; ?><br>
                            </td>
    <?php if ($isAdmin): ?>
                                <td>
                                    <a href="<?= Url::toRoute(['user/view', 'id' => $model->user->id]) ?>">
        <?= Utilities::encode($model->user->username) ?>
                                    </a>
                                </td>
    <?php endif; ?>
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
                            'isOwner' => $isOwner,
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
