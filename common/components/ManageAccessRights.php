<?php

namespace common\components;

use common\components\AppStatus;
use common\models\AccessCount;
use common\models\AccessRight;
use common\models\ActionButton;
use common\models\User;
use RuntimeException;
use Yii;
use yii\base\Component;
use yii\db\ActiveQuery;
use yii\web\Controller;

class ManageAccessRights extends Component
{
// --- Strict Accessors (The Level 8/9 "Secret Sauce") ---

    /**
     *
     * @param Controller $controller
     * @return string
     * @throws RuntimeException
     */
    private static function getAction(Controller $controller): string {
        if ($controller->action === null) {
            throw new RuntimeException("ManageAccessRights component error: Action is missing.");
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
    public static function getAuthorizedIds(User $user, bool $hasPlayerSelected = false, bool $inQuest = false): ActiveQuery {

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
     * @param \yii\web\Controller $controller The source controller
     * @param string $redirect route url to redirect avec throwing an error
     * @return bool True if access is granted, false otherwise
     */
    public static function isRouteAllowed(Controller $controller, string $redirect = null): bool {
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
    private static function isPublic(string $route, string $action): bool {
        $publicSiteActions = ['error', 'login', 'captcha', 'index', 'signup', 'error',
            'colors', 'icons', 'fonts', 'game',
            'request-password-reset', 'reset-password', 'verify-email', 'resend-verification-email'];

        // Allow access to site/error, site/login, site/captcha, site/index,...
        // or to any ajax call (side effect, every ajax call actions should be prefixed with 'ajax'
        return (
                ($route === 'site' && in_array($action, $publicSiteActions)) ||
                strncmp($action, 'ajax', 4) === 0
        );
    }

    private static function getAccessRight(string $route, string $action): ?AccessRight {
        $accessRightData = Yii::$app->db->createCommand(
                        "SELECT * FROM `access_right` WHERE `route` = :route AND `action` = :action",
                        [':route' => $route, ':action' => $action]
                )->queryOne();

        return $accessRightData ? new AccessRight($accessRightData) : null;
    }

    private static function countAccess(string $route, string $action): bool {
        $accessCount = AccessCount::findOne(['route' => $route, 'action' => $action]);

        if ($accessCount) {
            $accessCount->calls = $accessCount->calls + 1;
        } else {
            $accessCount = new AccessCount(['route' => $route, 'action' => $action]);
        }

        return $accessCount->save(false);
    }

    /**
     * Checks that the requested action is authorised within the controller
     *
     * @param \yii\web\Controller $controller
     * @return array{
     *     denied: bool,
     *     severity: string,
     *     reason: string
     * }
     */
    private static function checkAccess(Controller $controller): array {

        $route = $controller->id;
        $action = self::getAction($controller);

        self::countAccess($route, $action);

        if (self::isPublic($route, $action)) {
            return self::logAccess(null, false, 'success', "[{$route}/{$action}] Access granted by default to public route");
        }
        // Get access rights for the route
        //$accessRight = AccessRight::findOne(['route' => $route, 'action' => $action]);
        $accessRight = self::getAccessRight($route, $action);

        // If no access rights defined, grant access to public "route/action"
        if (!$accessRight) {
            return self::logAccess(null, true, 'error', "[{$route}/{$action}] Access denied by default: no specific AccessRight found");
        }

        $user = Yii::$app->session->get('user') ?? Yii::$app->user->identity;

        // Grant access if user is admin and route allows admin access
        if ($user->is_admin && $accessRight->is_admin) {
            return self::logAccess($accessRight->id, false, 'success', "[{$route}/{$action}] Access granted for Admin role");
        }

        // Grant access if user is designer and route allows designer access
        if ($user->is_designer && $accessRight->is_designer) {
            return self::logAccess($accessRight->id, false, 'success', "[{$route}/{$action}] Access granted for Designer role");
        }

        // Check player-specific access conditions
        if ($user->is_player && $accessRight->is_player) {
            $playerAccess = self::checkPlayerAccess($accessRight);
            return self::logAccess($accessRight->id, $playerAccess['denied'], $playerAccess['severity'], "[{$route}/{$action}] {$playerAccess['reason']}");
        }

        // Deny access by default
        return self::logAccess($accessRight->id, true, 'fatal', "[{$route}/{$action}] Access denied");
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
    private static function checkPlayerAccess(AccessRight $accessRight): array {
        // Deny if player selection is required but none selected
        $hasPlayerSelected = Yii::$app->session->get('hasPlayerSelected') ?? false;

        if (!$hasPlayerSelected && $accessRight->has_player) {
            return [
                'denied' => true, 'severity' => 'error',
                'reason' => 'No player have been selected'
            ];
        }

        // Deny if quest participation is required but player is not in a quest
        $inQuest = Yii::$app->session->get('inQuest');
        $playerName = Yii::$app->session->get('playerName');
        if (!$inQuest && $accessRight->in_quest) {
            return [
                'denied' => true, 'severity' => 'error',
                'reason' => "Player {$playerName} is not engaged in any quest"
            ];
        }
        return [
            'denied' => false, 'severity' => 'success',
            'reason' => 'Access granted' . ($playerName ? ' for ' . $playerName : '')
        ];
    }

    /**
     * Log the access to the database.
     *
     * The initial code was:
     *
     *   $userLog = new UserLog([
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
     *   if (!$userLog->save(false)) {
     *       throw new \Exception(implode("<br />", ArrayHelper::getColumn($userLog->errors, 0, false)));
     *   }
     * This previously generated three SQL SELECT queries before the INSERT statement.
     * For optimization purposes, given that this logging function is called for each HTTP query,
     * we switched to a more optimized low-level query to perform only the INSERT.
     *
     * @param int|null $accessRightId
     * @param bool $denied
     * @param string $severity
     * @param string $reason
     * @return array{
     *     denied: bool,
     *     severity: string,
     *     reason: string
     * }
     * @throws \Exception
     */
    private static function logAccess(int|null $accessRightId, bool $denied, string $severity, string $reason): array {
        $user = Yii::$app->session->get('user') ?? Yii::$app->user->identity;
        //$playerId = Yii::$app->session->get('playerId');
        //$questId = Yii::$app->session->get('questId');
        if (!$user) {
            return ['denied' => false, 'severity' => 'none', 'reason' => 'User is not logged in'];
        }
        if ($user->current_player_id) {
            $player = Yii::$app->db->createCommand(
                            "SELECT * FROM `player` WHERE `id` = :playerId",
                            [':playerId' => $user->current_player_id]
                    )->queryOne();
            $playerId = $player['id'];
            $questId = $player['quest_id'];
        } else {
            $playerId = null;
            $questId = null;
        }

        $sql = "INSERT INTO `user_log` (`user_id`, `access_right_id`, `player_id`, `quest_id`, `ip_address`, `action_at`, `denied`, `reason`)"
                . " VALUES (:user_id, :access_right_id, :player_id, :quest_id, :ip_address, :action_at, :denied, :reason)";

        $values = [
            ':user_id' => $user->id,
            ':access_right_id' => $accessRightId,
            ':player_id' => $playerId,
            ':quest_id' => $questId,
            ':ip_address' => Yii::$app->getRequest()->getUserIP(),
            ':action_at' => time(),
            ':denied' => $denied ? 1 : 0,
            ':reason' => $reason,
        ];
        try {
            Yii::$app->db->createCommand($sql, $values)->execute();
        } catch (\Exception $e) {
            Yii::debug($e);
            $errorMessage = "Error: " . $e->getMessage() . "<br />Stack Trace:<br />" . nl2br($e->getTraceAsString());
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
     * @param array<string, mixed> $action An associative array containing the action details and requirements.
     * @param string $modelName The name of the model to check against the action's allowed models.
     * @param bool $isOwner Indicates whether the user is the owner of the resource.
     * @param string $mode The mode in which the action is being performed: table or view.
     * @return bool True if the action is allowed, false otherwise.
     */
    public static function isActionButtonAllowed(array $action, string $modelName, bool $isOwner, string $mode): bool {
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
     * @return list<array<string, string>>
     */
    public static function selectActionButtons(string $route, string $action = 'index', int $status = AppStatus::ACTIVE->value): array {

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
