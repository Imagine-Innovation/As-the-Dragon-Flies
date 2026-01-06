<?php
/** @var yii\web\View $this */
/** @var common\models\Player $model */
$this->title = $model->name ?? 'New player';
$this->params['breadcrumbs'][] = ['label' => 'Players', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$cards = ['abilities', 'skills', 'combat-stats', 'attacks'];
$proficiencyBonus = $model->level->proficiency_bonus;
?>
<div class="container">
    <!-- Character Header -->
    <table>
        <tr>
            <td style="min-width: 100px">
                <img src="img/character/<?= $model->avatar ?>" class="avatar">
            </td>
            <td>
                <h3 class="mb-3 text-decoration"><?= $model->name ?></h3>
                <h6><?= $model->description ?></h6>
            </td>
        </tr>
    </table>
    <hr>

    <!-- Main Content -->
    <div class="row">
        <?php
        $cardHeaderClass = "card-header bg-purple text-decoration fw-bold h-100 py-2";
        $div = '<div class="col-12 col-md-6">';

        foreach ($cards as $card) {
            $cardContent = $this->renderFile("@app/views/player/snippets/{$card}.php", [
                'model' => $model,
                'cardHeaderClass' => $cardHeaderClass,
                'proficiencyBonus' => $proficiencyBonus,
            ]);
            echo($div . PHP_EOL);
            echo($cardContent . PHP_EOL);
            echo("</div>" . PHP_EOL);
        }
        ?>
    </div>
</div>
