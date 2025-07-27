<?php

namespace common\components;

use common\components\AppStatus;
use common\models\AccessRight;
use common\models\UserLog;
use common\helpers\UserErrorMessage;
use Yii;
use yii\base\Component;

class ManageAccessRights extends Component {

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
     * @param bool $hasPlayer Whether the user has selected a player
     * @param bool $inQuest Whether the selected player is in a quest
     * @return ActiveQuery Query containing all authorized access right IDs
     */
    public static function getAuthorizedIds($user, $hasPlayer = false, $inQuest = false) {

        $accesRights = AccessRight::find()->select('id')->where(['id' => 1]);
        if ($user->is_player) {
            // Get access rights for players
            $query = AccessRight::find()->select('id')->where(['is_player' => true]);

            if (!$hasPlayer) {
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
     * @param yii\web\Controller $controller The source controller
     * @param string $redirect route url to redirect avec throwing an error
     * @return bool True if access is granted, false otherwise
     */
    public static function isRouteAllowed($controller, $redirect = null) {
        $access = self::checkAccess($controller);

        if ($access['denied']) {
            return UserErrorMessage::throw($controller, $access['severity'], $access['reason'], $redirect);
        }
        return true;
    }

    /**
     * Defines if an action within a route is public, i.e. access can be granted by default
     *
     * @param sting $route
     * @param string $action
     * @return bool
     */
    private static function isPublic(string $route, string $action): bool {
        $publicSiteActions = ['error', 'login', 'captcha', 'index', 'signup', 'colors', 'icons', 'fonts', 'game',
            'request-password-reset', 'reset-password', 'verify-email', 'resend-verification-email'];
        // Allow access to site/error, site/login, site/captcha, site/index,...
        // or to any ajax call (side effect, every ajax call actions should be prefixed with 'ajax'
        return (
                ($route === 'site' && in_array($action, $publicSiteActions)) ||
                substr($action, 0, 4) === 'ajax'
        );
    }

    /**
     * Checks that the requested action is authorised within the controller
     *
     * @param yii\web\Controller $controller
     * @return array
     */
    private static function checkAccess($controller) {

        $route = $controller->id;
        $action = $controller->action->id;

        if (self::isPublic($route, $action)) {
            return self::logAccess(null, false, 'success', "Access granted by default to public route [{$route}/{$action}]");
        }
        // Get access rights for the route
        $accessRight = AccessRight::findOne(['route' => $route, 'action' => $action]);

        // If no access rights defined, grant access to public "route/action"
        if (!$accessRight) {
            return self::logAccess(null, true, 'error', "Access denied by default for [{$route}/{$action}]: no specific AccessRight found");
        }

        $user = Yii::$app->session->get('user') ?? Yii::$app->user->identity;

        // Grant access if user is admin and route allows admin access
        if ($user->is_admin && $accessRight->is_admin) {
            return self::logAccess($accessRight->id, false, 'success', 'Access granted for Admin role');
        }

        // Grant access if user is designer and route allows designer access
        if ($user->is_designer && $accessRight->is_designer) {
            return self::logAccess($accessRight->id, false, 'success', 'Access granted for Designer role');
        }

        // Check player-specific access conditions
        if ($user->is_player && $accessRight->is_player) {
            return self::checkPlayerAccess($accessRight);
        }

        // Deny access by default
        return self::logAccess($accessRight->id, true, 'fatal', 'Access denied');
    }

    /**
     * Check player-specific access conditions
     *
     * @param common\models\AccessRight $accessRight
     * @return array
     */
    private static function checkPlayerAccess($accessRight) {
        // Deny if player selection is required but none selected
        $hasPlayer = Yii::$app->session->get('hasPlayer');
        if (!$hasPlayer && $accessRight->has_player) {
            return self::logAccess($accessRight->id, true, 'error', 'No players have been selected');
        }

        // Deny if quest participation is required but player is not in a quest
        $inQuest = Yii::$app->session->get('inQuest');
        $player = Yii::$app->session->get('currentPlayer');
        if (!$inQuest && $accessRight->in_quest) {
            return self::logAccess($accessRight->id, true, 'error', 'Player ' . $player->name . ' is not engaged in any quest');
        }
        return self::logAccess($accessRight->id, false, 'success', 'Access granted' . ($player ? ' for ' . $player->name : ''));
    }

    /**
     * Log the access to the database
     *
     * @param number $accessRightId
     * @param boolean $denied
     * @param string $severity
     * @param string $reason
     * @return array
     */
    private static function logAccess($accessRightId, $denied, $severity, $reason) {
        //if ($accessRightId) {
        $sessionUser = Yii::$app->session->get('user');
        $user = $sessionUser ?? Yii::$app->user->identity;
        $questId = Yii::$app->session->get('questId');

        $userLog = new UserLog([
            'user_id' => $user->id,
            'access_right_id' => $accessRightId,
            'player_id' => $user->current_player_id,
            'quest_id' => $questId,
            'ip_address' => Yii::$app->getRequest()->getUserIP(),
            'action_at' => time(),
            'denied' => $denied ? 1 : 0,
            'reason' => $reason,
        ]);
        $userLog->save();
        //}

        return [
            'denied' => $denied,
            'severity' => $severity,
            'reason' => $reason
        ];
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
     * @param array $action An associative array containing the action details and requirements.
     * @param string $modelName The name of the model to check against the action's allowed models.
     * @param bool $isOwner Indicates whether the user is the owner of the resource.
     * @param string $mode The mode in which the action is being performed: table or view.
     * @return bool True if the action is allowed, false otherwise.
     */
    public static function isActionButtonAllowed($action, $modelName, $isOwner, $mode) {
        $user = Yii::$app->session->get('user');

        // Check if the model name is allowed for the action
        if (!in_array($modelName, $action['modelName'])) {
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
     * Return the list of action button allowed for the specific route and action
     *
     * @param string $route
     * @param string $action
     * @param int $status
     * @return array type
     */
    public static function selectActionButtons(string $route, string $action = 'index', int $status = AppStatus::ACTIVE->value) {

        $actionButtons = ActionButton::find()
                        ->select(['action_button.icon', 'action_button.route', 'action_button.action', 'action_button.tooltip'])
                        ->innerJoin('access_right_action_button', 'action_button.id = access_right_action_button.action_button_id')
                        ->innerJoin('access_right', 'access_right.id = access_right_action_button.access_right_id')
                        ->where([
                            'access_right.route' => $route,
                            'access_right.action' => $action,
                            'access_right_action_button.status' => $status
                        ])
                        ->asArray()->all();

        $buttons = [];
        foreach ($actionButtons as $actionButton) {
            $buttons[] = [
                'icon' => $actionButton['icon'],
                'route' => $actionButton['route'] ?? $route,
                'action' => $actionButton['action'],
                'tooltip' => $actionButton['tooltip']
            ];
        }

        return $buttons;
    }

    /**
     * Check if the attribute is valid
     *
     * @param string $attribute
     * @return bool
     */
    public static function isValidAttribute(string $attribute): bool {
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
