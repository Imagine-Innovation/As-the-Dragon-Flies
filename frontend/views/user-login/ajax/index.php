<?php

use yii\helpers\Html;
use yii\helpers\Url;
use frontend\widgets\Pagination;
use frontend\widgets\RecordCount;

/** @var yii\web\View $this */
/** @var common\models\UserLogin $models */
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
            'model' => 'login',
            'adjective' => 'logged',
        ])
        ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>User name</th>
                        <th>Application</th>
                        <th>Login at</th>
                        <th>logout at</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <?php $user = $model->getUser()->one(); ?>
                            <th scope="row">
                                <a href="<?= Url::toRoute(['user/view', 'id' => $model->user_id]) ?>">
                                    <?= Html::encode($user->username) ?>
                                </a>
                            </th>
                            <td><?= $model->application ?></td>
                            <td><?= Yii::$app->formatter->asDateTime($model->login_at, 'dd/MM/yyyy HH:mm') ?></td>
                            <td><?= Yii::$app->formatter->asDateTime($model->logout_at, 'dd/MM/yyyy HH:mm') ?></td>
                            <td><?= $model->ip_address ?></td>
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
