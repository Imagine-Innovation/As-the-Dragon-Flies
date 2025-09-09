<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Character Classes';
$this->params['breadcrumbs'][] = $this->title;
$models = $dataProvider->getModels();
?>

<h3><?= Html::encode($this->title) ?></h3>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Name</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <th scope="row">
                                <img src="img/character/<?= $model->randomImage ?>" alt="<?= $model->name ?>" style="width: 100px;">
                            </th>
                            <td>
                                <a href="<?= Url::toRoute(['character-class/view', 'id' => $model->id]) ?>">
                                    <?= $model->name ?>
                                </a>
                            </td>
                            <td>
                                <p><?= $model->description ?></p>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
