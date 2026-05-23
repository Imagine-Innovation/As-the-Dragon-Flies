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
        if ($language === null) {
            $user = $app->user->identity;
            if ($user !== null && isset($user->language)) {
                $language = $user->language;
            }
        }

        if ($language !== null) {
            $supportedLanguages = ['en', 'fr'];
            if (in_array($language, $supportedLanguages, true)) {
                $app->language = $language;
                $session->set('language', $language);
            } else {
                $app->language = $app->sourceLanguage;
                $session->set('language', $app->sourceLanguage);
            }
        }
    }
}
