<?php

namespace common\widgets;

use Yii;

class Alert extends \yii\bootstrap5\Widget
{

    /**
     * @var array<string, string> the alert types configuration for the flash messages.
     * This array is setup as $key => $value, where:
     * - key: the name of the session flash variable
     * - value: the bootstrap alert type (i.e. danger, success, info, warning)
     */
    public $alertTypes = [
        'error' => 'alert-danger',
        'danger' => 'alert-danger',
        'success' => 'alert-success',
        'info' => 'alert-info',
        'warning' => 'alert-warning',
    ];

    /**
     * {@inheritdoc}
     * @var array<string, mixed> the options for rendering the close button tag.
     * Array will be passed to [[\yii\bootstrap\Alert::closeButton]].
     */
    public array $closeButton = [];

    /**
     *
     * @param string $type
     * @return string|null
     */
    protected function bootstrapAlertType(string $type = null): ?string
    {
        return match ($type) {
            'error' => 'alert-danger',
            'danger' => 'alert-danger',
            'success' => 'alert-success',
            'info' => 'alert-info',
            'warning' => 'alert-warning',
            default => null
        };
    }

    /**
     *
     * @param string $type
     * @param array<string, string> $flash
     * @return string
     */
    protected function echoAlerts(string $type, array $flash): string
    {
        $bootstrapAlertType = $this->bootstrapAlertType($type);

        if ($bootstrapAlertType === null) {
            return '';
        }

        $alert = '';
        $class = isset($this->options['class']) ? "{$bootstrapAlertType} {$this->options['class']}"
                    : $bootstrapAlertType;

        foreach ((array) $flash as $i => $message) {
            $alert .= \yii\bootstrap5\Alert::widget([
                'body' => $message,
                'closeButton' => $this->closeButton,
                'options' => array_merge($this->options, [
                    'id' => $this->getId() . '-' . $type . '-' . $i,
                    'class' => $class,
                ]),
                    ])
            ;
        }

        return $alert;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();
        // $appendClass = isset($this->options['class']) ? ' ' . $this->options['class'] : '';

        foreach ($flashes as $type => $flash) {
            echo $this->echoAlerts($type, $flash);

            $session->removeFlash($type);
        }
    }
}
