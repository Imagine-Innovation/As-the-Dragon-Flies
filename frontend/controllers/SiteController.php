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
        $oauthAttributes = $this->_getOAuthUserAttributes($client);

        if (!$oauthAttributes) {
            // Error message already set in _getOAuthUserAttributes if oauth_user_id was missing
            // Or add a generic one if it can fail for other reasons:
            // Yii::$app->session->setFlash('error', 'Could not retrieve user information from ' . ucfirst($client->getId()) . '.');
            return; // Stop further processing
        }

        $provider = $oauthAttributes['provider'];
        $oauthUserId = $oauthAttributes['oauth_user_id'];
        $email = $oauthAttributes['email'];
        $username = $oauthAttributes['username'];

        $user = $this->_findUserByOAuth($provider, $oauthUserId);

        if ($user) {
            $this->_loginUser($user);
            return;
        }

        // User not found by OAuth credentials, try to link or create
        if ($email !== null) {
            $existingUserByEmail = $this->_findUserByEmail($email);
            if ($existingUserByEmail) {
                if ($this->_linkOAuthToUser($existingUserByEmail, $provider, $oauthUserId, $client)) {
                    $this->_loginUser($existingUserByEmail);
                }
                // If linking fails, an error flash is set in _linkOAuthToUser.
                // The AuthAction will typically redirect to auth/login on callback method return.
                return;
            }
        }

        // No existing user by email, or email not provided by OAuth: create a new user
        $newUser = $this->_createNewUserFromOAuth($provider, $oauthUserId, $email, $username, $client);
        if ($newUser) {
            $this->_loginUser($newUser);
        }
        // If user creation fails, an error flash is set in _createNewUserFromOAuth.
        // AuthAction redirects.
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

    /**
     * Extracts relevant user attributes from OAuth client.
     * @param ClientInterface $client
     * @return array|null
     */
    private function _getOAuthUserAttributes(ClientInterface $client): ?array
    {
        $attributes = $client->getUserAttributes();
        $provider = $client->getId();

        $oauthUserId = isset($attributes['id']) ? (string)$attributes['id'] : null;
        if (empty($oauthUserId)) {
            Yii::$app->session->setFlash('error', 'Unable to retrieve OAuth User ID from ' . ucfirst($provider) . '.');
            return null;
        }

        $email = isset($attributes['email']) ? $attributes['email'] : null;
        $username = isset($attributes['login']) ? $attributes['login'] : (isset($attributes['name']) ? $attributes['name'] : null);

        if (empty($username)) {
            // Fallback username generation
            $username = $email ? explode('@', $email)[0] : 'user_' . $provider;
            $username .= '_' . substr($oauthUserId, 0, 5); // Add part of ID for more uniqueness
        }

        // Further ensure username uniqueness or handle conflicts if necessary before returning
        // This basic version just extracts or generates a candidate username.

        return [
            'provider' => $provider,
            'oauth_user_id' => $oauthUserId,
            'email' => $email,
            'username' => $username,
        ];
    }

    /**
     * Finds a user by OAuth provider and ID.
     * @param string $provider
     * @param string $oauthUserId
     * @return User|null
     */
    private function _findUserByOAuth(string $provider, string $oauthUserId): ?User
    {
        return User::findByOAuthCredentials($provider, $oauthUserId);
    }

    /**
     * Finds a user by email.
     * @param string $email
     * @return User|null
     */
    private function _findUserByEmail(string $email): ?User
    {
        return User::findOne(['email' => $email, 'status' => User::STATUS_ACTIVE]);
    }

    /**
     * Links OAuth credentials to an existing user.
     * @param User $user
     * @param string $provider
     * @param string $oauthUserId
     * @param ClientInterface $client
     * @return bool
     */
    private function _linkOAuthToUser(User $user, string $provider, string $oauthUserId, ClientInterface $client): bool
    {
        $user->oauth_provider = $provider;
        $user->oauth_user_id = $oauthUserId;
        if ($user->save()) {
            return true;
        } else {
            Yii::$app->session->setFlash('error', 'Unable to link ' . ucfirst($client->getId()) . ' account. Please try again.');
            // Log errors: Yii::error($user->getErrors());
            return false;
        }
    }

    /**
     * Creates a new user from OAuth attributes.
     * @param string $provider
     * @param string $oauthUserId
     * @param string|null $email
     * @param string $username
     * @param ClientInterface $client
     * @return User|null
     */
    private function _createNewUserFromOAuth(string $provider, string $oauthUserId, ?string $email, string $username, ClientInterface $client): ?User
    {
        $baseUsername = $username;
        $counter = 1;
        // Resolve username conflicts
        while (User::findOne(['username' => $username])) {
            $username = $baseUsername . '_' . substr($oauthUserId, 0, 4) . ($counter > 1 ? '_' . $counter : '');
            if ($counter > 5) { // Limit attempts to avoid infinite loops on extreme edge cases
                 Yii::$app->session->setFlash('error', 'Failed to generate a unique username for ' . ucfirst($client->getId()) . ' account.');
                 return null;
            }
            $counter++;
        }

        $newUser = new User();
        $newUser->oauth_provider = $provider;
        $newUser->oauth_user_id = $oauthUserId;
        $newUser->email = $email;
        $newUser->username = $username;
        $newUser->status = User::STATUS_ACTIVE; // Consider User::STATUS_INACTIVE if email verification is desired and email is present
        $newUser->generateAuthKey();
        // $newUser->setPassword(Yii::$app->security->generateRandomString(12)); // Optional: if local login with password is also desired

        if ($newUser->save()) {
            return $newUser;
        } else {
            Yii::$app->session->setFlash('error', 'Unable to create an account using ' . ucfirst($client->getId()) . '.');
            // Log errors: Yii::error($newUser->getErrors());
            return null;
        }
    }

    /**
     * Logs in the given user.
     * @param User $user
     * @return bool
     */
    private function _loginUser(User $user): bool
    {
        return Yii::$app->user->login($user);
    }
}
