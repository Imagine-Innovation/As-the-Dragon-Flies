<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $topic */
/** @var string $property */
/** @var common\models\WizardQuestion $model */
?>
<p><?= Html::encode($model->question) ?></p>
<?php
foreach ($model->wizardAnswers as $a):
    $onclick = $a->next_question_id ?
            "PlayerBuilder.setNextStep('question', {$a->next_question_id})" :
            "PlayerBuilder.setNextStep('{$topic}', {$a->$property})";
    ?>
    <div class="custom-control custom-radio mb-2">
        <input type="radio" id="answer<?= $a->id ?>" name="answers<?= $model->id ?>" class="custom-control-input"
               onclick="<?= $onclick ?>;">
        <label class="custom-control-label" for="answer<?= $a->id ?>">
            <?= Html::encode($a->answer) ?>
        </label>
    </div>
<?php endforeach; ?>
