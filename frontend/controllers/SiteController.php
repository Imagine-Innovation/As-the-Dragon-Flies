<?php

namespace frontend\controllers;

use common\components\ContextManager;
use common\components\ManageAccessRights;
use common\helpers\Utilities;
use common\models\LoginForm;
use common\models\Player;
use common\models\UserLogin;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\models\ImageUploadForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Site controller
 */
class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'ajax-toast', 'websocket', 'send-message'],
                        //'allow' => ManageAccessRights::isRouteAllowed($this),
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions() {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    private function getLastPlayer(array &$players, int $state): ?Player {
        if ($state === 1) {
            return $players[0] ?? null;
        }
        return null;
    }

    private function getOtherPlayers(array &$players, int $state, int|null $currentPlayerId): ?array {
        switch ($state) {
            case 1:
                return array_slice($players, 1, 2);
            case 2:
            case 3:
                if (!$currentPlayerId) {
                    return array_slice($players, 0, 3);
                }
                $otherPlayers = [];
                foreach ($players as $player) {
                    if ($player->id !== $currentPlayerId) {
                        $otherPlayers[] = $player;
                    }
                }
                // We need 3 other players, making a total of 4 cards
                return array_slice($otherPlayers, 0, 3);
        }
        return null;
    }

    /**
     * Defines in which state is the user:
     *  0   Starting the game, no player defined
     *  1   At least on player is defined, but none is selected
     *  2   A player is selected, but he is not engaged in a quest
     *  3   A player is selected ans currently engaged in a quest
     *
     * @param Player[] $players
     * @param Player|null $player
     * @param bool $inQuest
     * @return int
     */
    private function getCurrentState(array &$players, Player|null &$player, bool $inQuest): int {

        if (count($players) === 0) {
            $state = 0;
        } elseif ($player !== null) {
            $state = $inQuest ? 3 : 2;
        } else {
            $state = 1;
        }
        return $state;
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex() {
        if (Yii::$app->user->isGuest) {
            return $this->render('guest');
        }
        ContextManager::initContext();

        // Get players sorted by creation date
        $user = Yii::$app->user->identity;

        if ($user->is_admin) {
            return $this->render('admin');
        }

        $playersQuery = $user->getPlayers()->orderBy(['created_at' => SORT_DESC]);
        $players = $playersQuery->all();
        $player = $user->current_player_id ? $user->currentPlayer : null;
        $inQuest = Yii::$app->session->get('questId') !== null;

        $state = $this->getCurrentState($players, $player, $inQuest);

        $viewParameters = [
            'player' => $player,
            'lastPlayer' => $this->getLastPlayer($players, $state),
            'otherPlayers' => $this->getOtherPlayers($players, $state, $player?->id),
        ];
        return $this->render('lobby', [
                    'state' => $state,
                    'viewParameters' => $viewParameters
        ]);
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIcons() {
        ManageAccessRights::isRouteAllowed($this);
        return $this->render('icons');
    }

    public function actionFonts() {
        ManageAccessRights::isRouteAllowed($this);
        return $this->render('fonts');
    }

    public function actionGame() {
        ManageAccessRights::isRouteAllowed($this);
        return $this->render('game');
    }

    public function actionColors() {
        ManageAccessRights::isRouteAllowed($this);
        return $this->render('colors');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin() {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $user = Yii::$app->user->identity;
            $login_at = time();
            $user->frontend_last_login_at = $login_at;
            if ($user->save()) {
                $log = new UserLogin([
                    'user_id' => $user->id,
                    'application' => 'frontend',
                    'login_at' => $login_at,
                    'ip_address' => Yii::$app->getRequest()->getUserIP()
                ]);
                $log->save();
            } else {
                throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($user->errors, 0, false)));
            }
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
                    'model' => $model,
        ]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout() {
        $user = Yii::$app->user->identity;
        $ipAddress = Yii::$app->getRequest()->getUserIP();

        UserLogin::updateAll(
                ['logout_at' => time()],
                [
                    'user_id' => $user->id,
                    'application' => 'frontend',
                    'login_at' => $user->frontend_last_login_at,
                    'ip_address' => $ipAddress
                ]
        );
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact() {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
                    'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout() {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup() {
        $model = new SignupForm();
        $this->layout = 'blank';

        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
                    'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset() {
        $model = new PasswordResetRequestForm();
        $this->layout = 'blank';
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }

        return $this->render('requestPasswordResetToken', [
                    'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token) {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
                    'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token) {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if (($user = $model->verifyEmail()) && Yii::$app->user->login($user)) {
            Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail() {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
                    'model' => $model
        ]);
    }

    public function actionAjaxToast() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        // Retrieve the item ID from the POST data and find the item
        $messageHeader = Yii::$app->request->post('messageHeader');
        $message = Yii::$app->request->post('message');
        $severity = Yii::$app->request->post('severity');

        $UUID = Utilities::newUUID();
        return [
            'error' => false,
            'msg' => '',
            'UUID' => $UUID,
            'content' => $this->renderPartial('ajax/toast', [
                'UUID' => $UUID,
                'messageHeader' => $messageHeader,
                'message' => $message,
                'severity' => $severity,
            ]),
        ];
    }

    public function actionImageUpload() {
        $model = new ImageUploadForm();

        if ($model->load(Yii::$app->request->post())) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            if ($model->upload()) {
                // file is uploaded successfully
                Yii::debug("*** Debug *** actionImageUpload ---> file is uploaded successfully !!!!", __METHOD__);
                return;
            }
            Yii::debug("*** Debug *** actionImageUpload ---> POST, but upload failed !!!!", __METHOD__);
        }

        return $this->render('image-upload', [
                    'model' => $model,
        ]);
    }

    /**
     * WebSocket test page
     */
    public function actionWebsocket() {
        return $this->render('websocket');
    }

    /**
     * Send a message to a player via WebSocket
     */
    public function actionSendMessage() {
        if (Yii::$app->request->isAjax) {
            $playerId = Yii::$app->request->post('playerId');
            $message = Yii::$app->request->post('message');

            // In a real application, you would need a way to communicate with
            // the running WebSocket server. This could be through a shared
            // database, Redis, or another messaging system.
            // For now, we'll just log the request
            Yii::info("Request to send message to player {$playerId}: {$message}", 'websocket');

            return $this->asJson([
                        'success' => true,
                        'message' => "Message would be sent to {$playerId}"
            ]);
        }

        return $this->asJson(['success' => false, 'message' => 'Invalid request']);
    }
}
