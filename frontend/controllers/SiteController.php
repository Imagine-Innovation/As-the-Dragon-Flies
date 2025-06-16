<?php

namespace frontend\controllers;

use common\models\User;
use Yii;
use yii\authclient\ClientInterface;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use common\models\LoginForm;
use common\components\ManageAccessRights;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use common\helpers\Utilities;
use frontend\models\ImageUploadForm;
use yii\web\UploadedFile;

/**
 * Site controller
 */
class SiteController extends Controller {

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup', 'auth'],
                'rules' => [
                    [
                        'actions' => ['signup', 'auth'], // Add 'auth' here
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
            'auth' => [
                'class' => 'yii\\authclient\\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    public function onAuthSuccess(ClientInterface $client)
    {
        $attributes = $client->getUserAttributes();
        $provider = $client->getId();
        // Ensure $oauthUserId is a string. Some providers might return integer.
        $oauthUserId = isset($attributes['id']) ? (string)$attributes['id'] : null;
        // Email might not be provided by all OAuth providers or might be empty
        $email = isset($attributes['email']) ? $attributes['email'] : null;
        // Username: try 'login' for GitHub, then 'name', then a default or error
        $username = isset($attributes['login']) ? $attributes['login'] : (isset($attributes['name']) ? $attributes['name'] : null);

        if (empty($oauthUserId)) {
            Yii::$app->session->setFlash('error', 'Unable to retrieve OAuth User ID.');
            return; // Or redirect, or handle error appropriately
        }

        if (empty($username)) {
            // Generate a unique username if not provided or if default is not suitable
            // For now, we'll use a placeholder or derive from email if possible
            $username = $email ? explode('@', $email)[0] . '_' . $provider : 'user_' . $provider . '_' . $oauthUserId;
        }


        /* @var $user User */
        $user = User::findByOAuthCredentials($provider, $oauthUserId);

        if ($user) {
            // User found, log them in
            Yii::$app->user->login($user);
        } else {
            // User not found, check if email exists (if provided by OAuth)
            if ($email !== null) {
                $user = User::findOne(['email' => $email, 'status' => User::STATUS_ACTIVE]);
                if ($user) {
                    // Email exists, link OAuth account
                    $user->oauth_provider = $provider;
                    $user->oauth_user_id = $oauthUserId;
                    if ($user->save()) {
                        Yii::$app->user->login($user);
                    } else {
                        Yii::$app->session->setFlash('error', 'Unable to link ' . ucfirst($provider) . ' account.');
                        // Log errors: Yii::error($user->getErrors());
                    }
                    return; // Exit after attempting to link
                }
            }

            // New user, or user with non-matching email: create an account
            // Check for username conflicts before creating a new user
            if (User::findOne(['username' => $username])) {
                // Username exists, generate a unique one or prompt user
                $username = $username . '_' . substr($oauthUserId, 0, 4); // Simple uniqueness
                if (User::findOne(['username' => $username])) {
                     Yii::$app->session->setFlash('error', 'Username ' . $username . ' already exists. Please choose a different one or contact support if this is your account.');
                     return; // Or redirect to a form where user can choose a username
                }
            }

            $newUser = new User();
            $newUser->oauth_provider = $provider;
            $newUser->oauth_user_id = $oauthUserId;
            $newUser->email = $email; // Can be null if not provided
            $newUser->username = $username;
            $newUser->status = User::STATUS_ACTIVE; // Or User::STATUS_INACTIVE if email verification is needed and email is provided
            $newUser->generateAuthKey();
            // $newUser->setPassword(Yii::$app->security->generateRandomString(12)); // Only if local login is also desired

            if ($newUser->save()) {
                Yii::$app->user->login($newUser);
            } else {
                Yii::$app->session->setFlash('error', 'Unable to create an account using ' . ucfirst($provider) . '.');
                // Log errors: Yii::error($newUser->getErrors());
            }
        }
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex() {
        ManageAccessRights::updateSession();

        return $this->render('index');
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
            'content' => $this->renderPartial('ajax-toast', [
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
