<?php

use yii\helpers\Html;
use yii\web\HttpException;

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var \Throwable $exception */

$this->title = 'Oups, something went wrong';

$statusCode = $exception instanceof HttpException ? $exception->statusCode : 500;
$errorName = method_exists($exception, 'getName') ? $exception->getName() : $name;
?>
<?php if ($statusCode === 404): ?>
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
            <h1><?= Html::encode($errorName) ?></h1>
            <h2>Don't tell me you're trying to cheat the system!</h2>
            <p><?= Html::encode($message) ?></p>
        </div>
    </section>
<?php endif;
