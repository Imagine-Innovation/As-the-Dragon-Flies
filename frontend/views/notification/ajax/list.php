<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Notification $models */
?>
<?php foreach ($models as $model): ?>
    <a href="#" class="listview__item">
        <img src="img/character/<?= $model->sender->image->file_name ?>" class="image-thumbnail">
        <div class="listview__content">
            <div class="listview__heading"><?= $model->sender->name ?></div>
            <p><?= Html::encode($model->message) ?></p>
        </div>
    </a>
<?php endforeach; ?>
