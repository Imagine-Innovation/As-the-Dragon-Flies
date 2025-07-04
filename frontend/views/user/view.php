<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\User $model */
$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
<?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
<?=
Html::a('Delete', ['delete', 'id' => $model->id], [
    'class' => 'btn btn-danger',
    'data' => [
        'confirm' => 'Are you sure you want to delete this item?',
        'method' => 'post',
    ],
])
?>
    </p>

<?=
DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'username',
        'fullname',
        'auth_key',
        'password_hash',
        'password_reset_token',
        'verification_token',
        'email:email',
        'status',
        'is_admin',
        'is_designer',
        'created_at',
        'updated_at',
        'backend_last_login_at',
        'frontend_last_login_at',
    ],
])
?>

</div>
