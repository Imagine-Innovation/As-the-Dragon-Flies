<?php

use common\models\User;
use common\helpers\Status;
use common\helpers\CheckBox;
use frontend\widgets\Pagination;
use frontend\widgets\RecordCount;
use frontend\widgets\ActionButtons;

/** @var yii\web\View $this */
/** @var common\models\User $models */
/** @var int $count: total number of records retrived by the query */
/** @var int $page: current page number */
/** @var int $pageCount: nomber of pages regarding the limit of the query */
/** @var int $limit: nomber of records to be fetched */
$tooltip = [];
$icons = [];

$tooltip[User::STATUS_DELETED] = 'Deleted';
$tooltip[User::STATUS_INACTIVE] = 'Inactive';
$tooltip[User::STATUS_ACTIVE] = 'Active';

$icons[User::STATUS_DELETED] = 'bi-trash';
$icons[User::STATUS_INACTIVE] = 'bi-question';
$icons[User::STATUS_ACTIVE] = 'bi-check-lg';
$icon = '<i class="bi bi-check-lg h5"></i>';
?>
<div class="card">
    <div class="card-body">
        <?=
        RecordCount::widget([
            'count' => $count,
            'model' => 'user',
            'adjective' => 'registered',
        ])
        ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>User name</th>
                        <th>Fullname</th>
                        <th>email</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Admin</th>
                        <th class="text-center">Designer</th>
                        <th class="text-center">Player</th>
                        <th>Created at</th>
                        <th>Last login at</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <th scope="row"><?= Status::hyperlink($model, 'username') ?></th>
                            <td><?= $model->fullname ?></td>
                            <td><?= $model->email ?></td>
                            <td class="text-center"><?= Status::icon($model->status) ?></td>
                            <td class="text-center"><?= CheckBox::setUserRole($model, 'admin') ?></td>
                            <td class="text-center"><?= CheckBox::setUserRole($model, 'designer') ?></td>
                            <td class="text-center"><?= CheckBox::setUserRole($model, 'player') ?></td>
                            <td><?= Yii::$app->formatter->asDateTime($model->created_at, 'dd/MM/yyyy HH:mm') ?></td>
                            <td><?= Yii::$app->formatter->asDateTime($model->frontend_last_login_at, 'dd/MM/yyyy HH:mm') ?></td>
                            <td>
                                <?=
                                ActionButtons::widget([
                                    'model' => $model,
                                    'mode' => 'table'
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
