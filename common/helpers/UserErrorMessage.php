<?php

namespace common\helpers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class UserErrorMessage
{

    /**
     *
     * @template T of \yii\web\Controller
     * @param T $controller
     * @param string $errorLevel
     * @param string $message
     * @param string|null $redirectRoute
     * @return void
     * @throws NotFoundHttpException
     */
    public static function throw(
            Controller $controller,
            string $errorLevel,
            string $message,
            ?string $redirectRoute = null,
    ): void
    {
        if ($errorLevel === 'fatal') {
            throw new NotFoundHttpException($message);
        } else {
            Yii::$app->session->setFlash($errorLevel, $message);

            $route[0] = $redirectRoute ?? 'site/index';
            $controller->redirect($route);
        }
    }
}
