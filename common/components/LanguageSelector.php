<?php

namespace common\components;

use Yii;
use yii\base\BootstrapInterface;

class LanguageSelector implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $session = $app->session;
        $language = $session->get('language');
        if ($language !== null) {
            $app->language = $language;
        } else {
            $user = $app->user->identity;
            if ($user !== null && isset($user->language)) {
                $app->language = $user->language;
                $session->set('language', $user->language);
            }
        }
    }
}
