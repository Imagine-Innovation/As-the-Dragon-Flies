<?php


/** @var yii\web\View $this */
/** @var common\models\Quest $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Quests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

Hello World
