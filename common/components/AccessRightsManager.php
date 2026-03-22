<?php

namespace common\components;

use common\models\AccessCount;
use common\models\AccessRight;
use common\models\User;
use RuntimeException;
use Yii;
use yii\base\Component;
use yii\db\ActiveQuery;
use yii\web\Controller;

/** @template T of \yii\web\Controller */
class AccessRightsManager extends Component
{

    const APP_BACKEND = 'app-backend';
    const APP_FRONTEND = 'app-frontend';

    // --- Strict Accessors (The Level 8/9 "Secret Sauce") ---

    /**
     *
     * @param T $controller
     * @return string
     * @throws RuntimeException
     */
    private static function getAction(Controller $controller): string
    {
        if ($controller->action === null) {
            throw new RuntimeException('AccessRightsManager component error: Action is missing.');
        }
        return $controller->action->id;
    }

    /**
     * Gets all authorized access rights for a user based on their roles and status
     *
     * Algorithm:
     * 1. If user is player:
     *    - Get access rights for players
     *    - If no player selected, filter out player-specific rights
     *    - If not in quest, filter out quest-specific rights
     * 2. If not player:
     *    - Only allow home page access
     * 3. If user is admin:
     *    - Add all admin access rights
     * 4. If user is designer:
     *    - Add all designer access rights
     * 5. Return combined access rights query
     *
     * @param User $user The user to get authorizations for
     * @param bool $hasPlayerSelected Whether the user has selected a player
     * @param bool $inQuest Whether the selected player is in a quest
     * @return ActiveQuery<AccessRight> Query containing all authorized access right IDs
     */
    public static function getAuthorizedIds(
            User $user,
            bool $hasPlayerSelected = false,
            bool $inQuest = false,
    ): ActiveQuery
    {
        // Side effect: Id=1 does not exist. This statement is used as a first
        // query that returns no records so that it can then be used to
        // aggregate queries using UNION.
        $accesRights = AccessRight::find()->select('id')->where(['id' => 1]);
        if ($user->is_player) {
            // Get access rights for players
            $query = AccessRight::find()->select('id')->where(['is_player' => true]);

            if (!$hasPlayerSelected) {
                // Remove options requiring player selection
                $query->andWhere(['has_player' => false]);
            } elseif (!$inQuest) {
                // Remove quest-specific options if not in quest
                $query->andWhere(['in_quest' => false]);
            }
            $accesRights->union($query);
        }

        // Add admin access rights if applicable
        if ($user->is_admin) {
            $query = AccessRight::find()->select('id')->where(['is_admin' => true]);
            $accesRights->union($query);
        }

        // Add designer access rights if applicable
        if ($user->is_designer) {
            $query = AccessRight::find()->select('id')->where(['is_designer' => true]);
            $accesRights->union($query);
        }

        return $accesRights;
    }

    /**
     * Checks if a user is authorized to access a specific route
     *
     * @param T $controller The source controller
     * @return bool True if access is granted, false otherwise
     */
    public static function isRouteAllowed(Controller $controller): bool
    {
        $access = self::checkAccess($controller);

        if ($access['denied']) {
            throw new \yii\web\ForbiddenHttpException($access['reason']);
        }
        return true;
    }

    /**
     * Defines if an action within a route is public, i.e. access can be granted by default
     *
     * @param string $route
     * @param string $action
     * @return bool
     */
    private static function isPublic(string $route, string $action): bool
    {
        $publicSiteActions = [
            'error',
            'login',
            'logout',
            'captcha',
            'index',
            'signup',
            'request-password-reset',
            'reset-password',
            'verify-email',
            'resend-verification-email',
            'colors',
            'icons',
            'fonts',
            'game',
        ];

        // Allow access to site/error, site/login, site/captcha, site/index,...
        // or to any ajax call (side effect, every ajax call actions should be prefixed with 'ajax'
        return $route === 'site' && in_array($action, $publicSiteActions) || strncmp($action, 'ajax', 4) === 0;
    }

    /**
     *
     * @param string $route
     * @param string $action
     * @return AccessRight|null
     */
    private static function getAccessRight(string $route, string $action): ?AccessRight
    {
        $application = \Yii::$app->id;

        self::countAccess($application, $route, $action);

        $accessRightData = Yii::$app
                ->db
                ->createCommand('SELECT * FROM `access_right`'
                        . ' WHERE `application` = :application AND `route` = :route AND `action` = :action', [
                    ':application' => $application,
                    ':route' => $route,
                    ':action' => $action,
                        ])
                ->queryOne();

        return $accessRightData ? new AccessRight($accessRightData) : null;
    }

    /**
     *
     * @param string $application
     * @param string $route
     * @param string $action
     * @return bool
     */
    private static function countAccess(string $application, string $route, string $action): bool
    {
        $accessCount = AccessCount::findOne(['application' => $application, 'route' => $route, 'action' => $action]);

        if ($accessCount) {
            $accessCount->calls = $accessCount->calls + 1;
        } else {
            $accessCount = new AccessCount(['application' => $application, 'route' => $route, 'action' => $action]);
        }

        return $accessCount->save(false);
    }

    /**
     *
     * @param string $route
     * @param string $action
     * @param string $grantedMessage
     * @param string $deniedMessage
     * @return array{denied: bool, severity: string, reason: string}
     */
    private static function manageDefaultAccess(string $route, string $action, string $grantedMessage, string $deniedMessage): array
    {
        if (self::isPublic($route, $action)) {
            return self::logAccess(null, false, 'success', "{$grantedMessage} by default to public route");
        }

        return self::logAccess(null, true, 'error', "{$deniedMessage} by default: no specific AccessRight found");
    }

    /**
     * Checks that the requested action is authorised within the controller
     *
     * @param T $controller
     * @return array{
     *     denied: bool,
     *     severity: string,
     *     reason: string
     * }
     */
    private static function checkAccess(Controller $controller): array
    {
        $route = $controller->id;
        $action = self::getAction($controller);
        $grantedMessage = "[{$route}/{$action}] Access granted";
        $deniedMessage = "[{$route}/{$action}] Access denied";

        /** @var User|null $user */
        $user = Yii::$app->user->identity;
        $application = \Yii::$app->id;

        // Blanket backend restriction: only is_admin or is_designer
        if ($application === self::APP_BACKEND && $user !== null && !$user->is_admin && !$user->is_designer) {
            return self::logAccess(null, true, 'error', "{$deniedMessage}: Invalid user role for logging in to the backend application");
        }

        // Get access rights for the route
        $accessRight = self::getAccessRight($route, $action);

        // If no access rights defined, grant access to public "route/action"
        if (!$accessRight) {
            return self::manageDefaultAccess($route, $action, $grantedMessage, $deniedMessage);
        }

        // Grant access if user is admin and route allows admin access
        if (($user->is_admin ?? false) && $accessRight->is_admin) {
            return self::logAccess($accessRight->id, false, 'success', "{$grantedMessage} for Admin role");
        }

        // Grant access if user is designer and route allows designer access
        if (($user->is_designer ?? false) && $accessRight->is_designer) {
            return self::logAccess($accessRight->id, false, 'success', "{$grantedMessage} for Designer role");
        }

        // Check player-specific access conditions
        if (($user->is_player ?? false) && $accessRight->is_player) {
            $playerAccess = self::checkPlayerAccess($accessRight);
            return self::logAccess($accessRight->id, $playerAccess['denied'], $playerAccess['severity'],
                            "[{$route}/{$action}] {$playerAccess['reason']}");
        }

        // Deny access by default
        return self::logAccess($accessRight->id, true, 'fatal', $deniedMessage);
    }

    /**
     * Check player-specific access conditions
     *
     * @param \common\models\AccessRight $accessRight
     * @return array{
     *     denied: bool,
     *     severity: string,
     *     reason: string
     * }
     */
    private static function checkPlayerAccess(AccessRight $accessRight): array
    {
        // Deny if player selection is required but none selected
        $hasPlayerSelected = Yii::$app->session->get('hasPlayerSelected') ?? false;

        if (!$hasPlayerSelected && $accessRight->has_player) {
            return [
                'denied' => true,
                'severity' => 'error',
                'reason' => 'No player have been selected',
            ];
        }

        // Deny if quest participation is required but player is not in a quest
        $inQuest = Yii::$app->session->get('inQuest') ?? false;
        $playerName = Yii::$app->session->get('playerName') ?? 'Unknown';
        if (!$inQuest && $accessRight->in_quest) {
            return [
                'denied' => true,
                'severity' => 'error',
                'reason' => "Player {$playerName} is not engaged in any quest",
            ];
        }
        return [
            'denied' => false,
            'severity' => 'success',
            'reason' => 'Access granted' . ($playerName ? " for {$playerName}" : ''),
        ];
    }

    /**
     * Log the access to the database.
     *
     * The initial code was:
     *
     *   $accessLog = new AccesLog([
     *       'user_id' => $user->id,
     *       'access_right_id' => $accessRightId,
     *       'player_id' => $playerId,
     *       'quest_id' => $questId,
     *       'ip_address' => Yii::$app->getRequest()->getUserIP(),
     *       'action_at' => time(),
     *       'denied' => $denied ? 1 : 0,
     *       'reason' => $reason,
     *   ]);
     *   // "false" parameter to skip validation and save directly
     *   // and avoid 7 query at each acces right evaluation
     *   if (!$accessLog->save(false)) {
     *       throw new \Exception(implode("<br />", ArrayHelper::getColumn($accessLog->errors, 0, false)));
     *   }
     * This previously generated three SQL SELECT queries before the INSERT statement.
     * For optimization purposes, given that this logging function is called for each HTTP query,
     * we switched to a more optimized low-level query to perform only the INSERT.
     *
     * @param int|null $accessRightId
     * @param bool $denied
     * @param string $severity
     * @param string $reason
     * @return array{denied: bool, severity: string, reason: string}
     * @throws \Exception
     */
    private static function logAccess(?int $accessRightId, bool $denied, string $severity, string $reason): array
    {
        /** @var User|null $user */
        $user = Yii::$app->user->identity;
        if (!$user) {
            return ['denied' => false, 'severity' => 'none', 'reason' => 'User is not logged in'];
        }
        $playerId = null;
        $questId = null;
        if ($user->current_player_id) {
            $player = Yii::$app->db->createCommand('SELECT * FROM `player` WHERE `id` = :playerId', [
                        ':playerId' => $user->current_player_id,
                    ])
                    ->queryOne();
            if ($player) {
                $playerId = $player['id'];
                $questId = $player['quest_id'];
            }
        }

        $sql = 'INSERT INTO `access_log` (`user_id`, `access_right_id`, `player_id`, `quest_id`, `ip_address`, `action_at`, `application`, `denied`, `reason`)'
                . ' VALUES (:user_id, :access_right_id, :player_id, :quest_id, :ip_address, :action_at, :application, :denied, :reason)';

        $values = [
            ':user_id' => $user->id,
            ':access_right_id' => $accessRightId,
            ':player_id' => $playerId,
            ':quest_id' => $questId,
            ':ip_address' => Yii::$app->getRequest()->getUserIP(),
            ':action_at' => time(),
            ':application' => \Yii::$app->id,
            ':denied' => $denied ? 1 : 0,
            ':reason' => $reason,
        ];
        try {
            Yii::$app->db->createCommand($sql, $values)->execute();
        } catch (\Exception $e) {
            Yii::debug($e);
            $errorMessage = "Error: {$e->getMessage()}<br />Stack Trace:<br />" . nl2br($e->getTraceAsString());
            throw new \Exception($errorMessage);
        }

        return ['denied' => $denied, 'severity' => $severity, 'reason' => $reason];
    }

    /**
     * Determines if a given action is allowed based on user roles and ownership.
     *
     * This function checks whether a specific action is allowed for a user,
     * taking into account the user's administrative privileges, ownership
     * status, and the context of the model and mode.
     *
     * The function performs the following checks:
     * 1. Checks if the model name associated with the action matches the given model name.
     * 2. Checks if the action is permitted for the specified mode.
     * 3. Checks if the action requires administrative privileges and if the user is an admin.
     * 4. Checks if the action requires ownership and if the user is the owner.
     *
     * @param array{
     *      tooltip: string,
     *      route: string,
     *      verb: string,
     *      mode: string,
     *      icon: string,
     *      admin: bool,
     *      player: bool,
     *      owner: bool,
     *      controllers: array<string>,
     *      table: bool,
     *      view: bool
     * } $action An associative array containing the action details and requirements.
     * @param string $controller The name of the model to check against the action's allowed models.
     * @param bool $isOwner Indicates whether the user is the owner of the resource.
     * @param string $mode The mode in which the action is being performed: table or view.
     * @return bool True if the action is allowed, false otherwise.
     */
    public static function isActionButtonAllowed(array $action, string $controller, bool $isOwner, string $mode): bool
    {
        //$user = Yii::$app->session->get('user');
        /** @var User|null $user */
        $user = Yii::$app->user->identity;

        if ($user === null) {
            return false;
        }

        // Check if the model name is allowed for the action
        if (!in_array($controller, $action['controllers'])) {
            return false;
        }

        // Check if the action is permitted for the specified mode
        if (!$action[$mode]) {
            return false;
        }

        // Check if the action requires administrative privileges and if the user is an admin
        if ($action['admin'] && !$user->is_admin) {
            return false;
        }

        // Check if the action requires to be a player and if the user is a payer
        if ($action['player'] && !$user->is_player) {
            return false;
        }

        // Check if the action requires ownership and if the user is the owner
        if ($action['owner'] && !$isOwner) {
            return false;
        }

        // If all checks pass, the action is allowed
        return true;
    }

    /**
     * Check if the attribute is valid
     *
     * @param string $attribute
     * @return bool
     */
    public static function isValidAttribute(string $attribute): bool
    {
        $allowedAttributes = [
            'is_admin',
            'is_designer',
            'is_player',
            'has_player',
            'in_quest',
        ];

        return in_array($attribute, $allowedAttributes);
    }
}
