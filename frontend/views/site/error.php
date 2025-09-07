<?php
/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */

/** @var Exception $exception */
use yii\helpers\Html;

//$previousException = $exception->getPrevious();

$this->title = "Oups, something went wrong";

$envVars = ['hasPlayerSelected',
    'playerId',
    'playerName',
    'avatar',
    'inQuest',
    'questId',
    'questName'
];
?>
<?php if ($exception->statusCode === 404): ?>
    <section class="error">
        <div class="error__inner">
            <h1>Rhoooo</h1>
            <h2>You little rascal!</h2>
            <h2><?= Html::encode($message) ?></h2>
            <h2>That's not right!</h2>
        </div>
    </section>
<?php else: ?>
    <section class="error">
        <div class="error__inner">
            <h1><?= Html::encode($exception->getName()) ?></h1>
            <h2>Don't tell me you're trying to cheat the system!</h2>
            <p><?= Html::encode($message) ?></p>
        </div>
    </section>
<?php endif; ?>
