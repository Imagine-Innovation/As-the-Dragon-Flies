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
    const COOKIE_NAME = 'currentLang';
    const COOKIE_LIFETIME = 24 * 3600 * 30; // 30 days

    /**
     *
     * @param Application $app
     * @return string
     */
    private function getLanguage(Application $app): string
    {
        $langCookie = self::getLangCookie();
        if ($langCookie !== null) {
            return $langCookie;
        }

        $user = $app->user->identity;
        if ($user !== null && isset($user->language)) {
            return $user->language;
        }

        return self::DEFAULT_LANGUAGE;
    }

    /**
     *
     * @param string $lang
     * @return void
     */
    public static function setLangCookie(string $lang = 'en'): void
    {
        $cookie = new \yii\web\Cookie([
            'name' => self::COOKIE_NAME,
            'value' => $lang,
            'expire' => time() + self::COOKIE_LIFETIME, // 30 days
        ]);
        Yii::$app->response->cookies->add($cookie);
    }

    /**
     *
     * @return string|null
     */
    public static function getLangCookie(): ?string
    {
        return Yii::$app->request->cookies->getValue(self::COOKIE_NAME);
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
