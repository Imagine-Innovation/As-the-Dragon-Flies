<?php

/**
 * UserErrorMessage Helper Class
 * 
 * This class provides helper methods for common error message handling
 *
 * @package common\helpers
 * @author François Gros
 * @version 1.0
 * @since 2024-11-01
 */

namespace common\helpers;

use Yii;
use yii\web\NotFoundHttpException;

class UserErrorMessage {

    public static function throw($controller, $errorLevel, $message, $redirectRoute = null) {
        if ($errorLevel === 'fatal') {
            throw new NotFoundHttpException($message);
        } else {
            Yii::$app->session->setFlash($errorLevel, $message);
            
            $route[0] = $redirectRoute ?? 'site/index';
            $controller->redirect($route);
        }
    }
}
