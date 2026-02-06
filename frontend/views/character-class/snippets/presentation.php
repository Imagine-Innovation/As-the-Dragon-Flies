<?php

/** @var yii\web\View $this */
/** @var common\models\CharacterClass $model */
?>
<div class="table-responsive">
    <table class="table table-dark mb-0">
        <tbody>
            <tr>
                <th scope="row">
                    <img src="img/character/<?= $model->randomImage ?>" alt="<?= $model->name ?>" style="max-width: 120px;">
                </th>

                <td>
                    <div class="card">
                        <div class="card-body">
                            <?= $model->description ?>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
