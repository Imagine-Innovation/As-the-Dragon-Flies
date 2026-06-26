<?php

namespace common\components;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;

class LanguageSelector implements BootstrapInterface
{

    const DEFAULT_LANGUAGE = 'en';
    const SUPPORTED_LANGUAGES = [
        'en' => 'English',
        'fr' => 'Français',
    ];

    /**
     *
     * @param Application $app
     * @return string
     */
    private function getLanguage(Application $app): string
    {
        $language = $app->request->cookies->getValue('language');
        if ($language !== null) {
            return $language;
        }

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

        if (!array_key_exists($language, self::SUPPORTED_LANGUAGES)) {
            $language = self::DEFAULT_LANGUAGE;
        }

        // Synchronize cookie with user profile if authenticated
        $user = $application->user->identity;
        if ($user !== null && isset($user->language) && $user->language !== $language) {
            $user->language = $language;
            \common\helpers\SaveHelper::save($user);
        }

        $application->language = $language;
        $session->set('language', $language);
    }
}
