<?php

use common\helpers\WebResourcesHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Notification[] $models */
$imgPath = WebResourcesHelper::imagePath();
?>
<?php foreach ($models as $model): ?>
    <a href="#" class="listview__item">
        <img src="<?= $imgPath ?>/character/<?= $model->initiator->image?->file_name ?>" class="image-thumbnail">
        <div class="listview__content">
            <div class="listview__heading"><?= $model->initiator->name ?></div>
            <p><?= Html::encode($model->message) ?></p>
        </div>
    </a>
<?php endforeach; ?>
