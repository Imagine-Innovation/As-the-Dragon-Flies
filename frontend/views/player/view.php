<?php
/** @var yii\web\View $this */
/** @var common\models\Player $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Players', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$cardHeaderClass = "card-header bg-purple text-decoration fw-bold h-100 py-2";
$cards = ['abilities', 'combat-stats', 'skills', 'attacks', 'equipment', 'features'];
$div = '<div class="col-12 col-md-6 col-xl-4 col-xxl-3">';
?>
<div class="container py-3">
    <!-- Character Header -->
    <table>
        <tr>
            <td>
                <img src="img/characters/<?= $model->avatar ?>" class="avatar">
            </td>
            <td>
                <h3 class="mb-3 text-decoration"><?= $model->name ?></h3>
                <h6><?= $model->description ?></h6>
            </td>
        </tr>
    </table>

    <!-- Main Content -->
    <div class="row g-4">
        <?php
        foreach ($cards as $card) {
            $cardContent = $this->renderFile("@app/views/player/sheet/{$card}.php", [
                'model' => $model,
                'cardHeaderClass' => $cardHeaderClass
            ]);
            echo($div . PHP_EOL);
            echo($cardContent . PHP_EOL);
            echo("</div>" . PHP_EOL);
        }
        ?>
    </div>
</div>
