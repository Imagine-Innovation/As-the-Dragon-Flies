<?php

use common\models\User;

/** @var yii\web\View $this */
/** @var common\models\Player[] $models */
$user = User::findOne(['id' => Yii::$app->user->identity->id]);
$initial = null;
$checkedId = null;
if ($user->currentPlayer) {
    $names = explode(' ', $user->currentPlayer->name);
    $initial = $names[0][0] . $names[1][0];
    $checkedId = $user->currentPlayer->id;
}
?>
<?php if ($models): ?>
    <li class="dropdown top-nav__notifications">
        <a class="top-nav position-relative" href="#" data-toggle="dropdown">
            <i class="bi bi-file-earmark-person"></i>
            <?php if ($initial): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= $initial ?>
                </span>
            <?php endif; ?>
        </a>
        <div class="dropdown-menu dropdown-menu-right dropdown-menu--block">
            <div class="dropdown-header">Players</div>

            <div class="listview listview--hover">
                <div class="card">
                    <div class="card-body">
                        <?php foreach ($models as $model): ?>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" class="custom-control-input"
                                       id="player-<?= $model->id ?>" name="player"
                                       <?= $model->id == $checkedId ? "checked" : "" ?>
                                       onclick='PlayerSelector.select(<?= $user->id ?>, <?= $model->id ?>);'
                                       />
                                <label class="custom-control-label" for="player-<?= $model->id ?>"><?= $model->name ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </li>
<?php endif; ?>
