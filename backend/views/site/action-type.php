<?php

use common\models\ActionType;

/** @var \yii\web\View $this */
$this->title = 'Action type icons';
$this->params['breadcrumbs'][] = $this->title;

$actionTypes = ActionType::find()
        ->with('skills')
//        ->asArray(true)
        ->all();
?>
<div class="container">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th>Action type</th>
                    <th>Description</th>
                    <th>Icon name</th>
                    <th>Skills</th>
                    <th>Icon</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($actionTypes as $actionType):
                    $skills = [];
                    foreach ($actionType->skills as $skill) {
                        $skills[] = $skill->name;
                    }
                    ?>
                    <tr>
                        <th scope="row"><?= $actionType->name ?></th>
                        <td><?= $actionType->description ?></td>
                        <td><?= $actionType->icon ?></td>
                        <td><?= implode(', ', $skills) ?></td>
                        <td><i class="bi <?= $actionType->icon ?>"></i></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
