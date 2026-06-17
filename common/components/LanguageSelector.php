<?php

namespace common\components;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;

class LanguageSelector implements BootstrapInterface
{

    const DEFAULT_LANGUAGE = 'en';
    const SUPPORTED_LANGUAGES = ['en', 'fr'];

    /**
     *
     * @param Application $app
     * @return string
     */
    private function getLanguage(Application $app): string
    {
        $language = $app->session->get('language');
        if ($language !== null) {
            return $language;
        }

        $user = $app->user->identity;
        if ($user !== null && isset($user->language)) {
            return $user->language;
        }

        return self::DEFAULT_LANGUAGE;
    }

    /**
     *
     * @param mixed $app
     * @return void
     */
    public function bootstrap(mixed $app): void
    {
        /** @var Application $application */
        $application = $app;
        $session = $application->session;
        $language = $this->getLanguage($application);

        if (!in_array($language, self::SUPPORTED_LANGUAGES, true)) {
            $language = self::DEFAULT_LANGUAGE;
        }
        $application->language = $language;
        $session->set('language', $language);
    }
}
