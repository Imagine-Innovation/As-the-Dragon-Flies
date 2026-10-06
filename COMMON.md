# Common Architectural Assessment & Reference Manual

This document provides a comprehensive technical assessment of the shared core application layer located in `common/` for **As the Dragon Flies**. It is designed to serve as long-term architectural memory and technical reference for future backend, frontend, gameplay, and AI-assisted development tasks.

---

## 1. Executive Summary & Overview

The `common/` directory forms the central foundation of the **As the Dragon Flies** application stack built on the **Yii2 Advanced Application Template**.

### Key Responsibilities & Subsystems
* **Shared Gameplay Core (`common/components/gameplay/`):** Encapsulates turn-based RPG mechanics, quest progression, action and outcome evaluation, character stat calculations, tavern room management, and player chat.
* **Real-Time Event Framework (`common/models/events/` & `common/extensions/EventHandler/`):** Defines real-time WebSocket event payloads (`GameActionEvent`, `NextTurnEvent`, `NextMissionEvent`, `GameOverEvent`, `SendingMessageEvent`) and handles IPC message passing between PHP backend processes and the Node.js / WebSocket event handler server on port `8080`.
* **Centralized State & Session Management (`ContextManager`):** Coordinates user identity, selected player character, active quest state, session UUID, and language preferences across requests and websocket streams.
* **Localization Engine (`LanguageSelector` & `common/messages/`):** Manages multi-language translations ('en' and 'fr') using Yii2's `PhpMessageSource` with a centralized category mapping in `common/config/main.php`.
* **ActiveRecord Data Layer (`common/models/`):** Provides ORM definitions and validation logic for over 90 relational tables encompassing campaign authoring, gameplay state, character equipment, user roles, and system event logging.
* **Shared Web Assets & Theme (`common/web/`):** Hosts the primary system stylesheet (`dragon-lite.css`), custom WYSIWYG editor script (`simple-rich-text.js`), pure ES6 client library (`core-library.js`), icon fonts, and typography.
* **Shared UI Widgets & Text Parsing (`common/widgets/` & `RichTextHelper`):** Offers custom widgets for Markdown parsing (`MarkDown`), WYSIWYG editing (`SimpleRichText`), action button formatting (`ActionButtons`), and AJAX container wrappers.

---

## 2. Directory Structure & File Map

```
common/
├── components/                       # Shared application components & services
│   ├── gameplay/                     # Core RPG gameplay engines & state managers
│   │   ├── ActionManager.php         # Action availability, eligibility & reply tree evaluation
│   │   ├── BaseManager.php           # Abstract base class for gameplay managers
│   │   ├── ChatManager.php           # Quest & lobby chat message processing
│   │   ├── OutcomeManager.php        # Outcome application (XP, items, damage, status, mission jumps)
│   │   ├── PlayerManager.php         # Player creation, stat updates, AC calculation, ability modifiers
│   │   ├── QuestManager.php          # Quest lifecycle, mission transitions & turn rotation
│   │   └── TavernManager.php         # Tavern table/room creation & player joining/leaving
│   ├── AccessRightsManager.php       # Role-based access control checking (`is_admin`, `is_designer`, `is_player`)
│   ├── AjaxRequest.php               # Standardized AJAX response payload generator
│   ├── AppStatus.php                 # Enum defining quest/session statuses (WAITING, PLAYING, ENDED, TERMINATED)
│   ├── ContextManager.php            # Global application state initializer (User, Player, Quest, Language)
│   ├── LanguageSelector.php          # Language detection, fallback & cookie persistence
│   ├── NarrativeComponent.php        # Narrative/story helper wrapper
│   └── Shopping.php                  # Shop item purchasing & inventory endowment
├── config/
│   ├── bootstrap.php                 # Global path aliases (`@common`, `@frontend`, `@backend`, `@console`)
│   ├── main.php                      # Centralized component configuration (i18n, cache, log, eventHandler, httpclient)
│   ├── params.php                    # Shared configuration parameters
│   └── test.php                      # Test configuration overrides
├── extensions/
│   └── EventHandler/                 # Real-time WebSocket event handler integration
│       ├── EventHandler.php          # PHP socket client to broadcast events to WebSocket server
│       └── Server/                   # Node.js / Socket server implementation
├── helpers/                          # Shared utility & static helper classes
│   ├── ActionButtonsConfig.php       # Action button styling & configuration
│   ├── DateTimeHelper.php            # Date/time formatting & timezone utilities
│   ├── DebugHelper.php               # Debug logging & execution profiling helpers
│   ├── DiceRoller.php                # Dice rolling mechanics (e.g. 1d20+2, 2d6)
│   ├── FileHelper.php                # File system operations & image path resolution
│   ├── FindModelHelper.php           # Safe ActiveRecord find-or-fail retrieval
│   ├── ItemHelper.php                # Equipment, body zone slotting & item attributes
│   ├── JsonHelper.php                # JSON encoding/decoding wrappers with error handling
│   ├── LanguageHelper.php            # Language list & locale utilities
│   ├── MergeHelper.php               # Array merging utilities
│   ├── ModelHelper.php               # Model error aggregation & attribute manipulation
│   ├── PayloadHelper.php             # Event payload builder utilities
│   ├── RichTextHelper.php            # Markdown & custom text tag sanitization / caching
│   ├── SaveHelper.php                # Safe transaction-wrapped model saving
│   ├── SpecialCheckBox.php           # Special form checkbox renderer
│   ├── Status.php                    # Status helper class
│   ├── StoryNeededClass.php          # Story requirements helper
│   ├── StoryPlayers.php              # Story player constraints helper
│   ├── UserErrorMessage.php          # Formatted user error messaging
│   ├── Utilities.php                 # UUID generation, random string generator & general helpers
│   └── WebResourcesHelper.php        # Resource URL resolver
├── messages/                         # Centralized i18n translation catalogs
│   ├── en/                           # English translations (`app.php`, `game.php`, `guest.php`, `lobby.php`, `error.php`)
│   └── fr/                           # French translations (`app.php`, `game.php`, `guest.php`, `lobby.php`, `error.php`)
├── models/                           # Shared ActiveRecord models & Form models
│   ├── events/                       # Real-time game event payload definitions
│   │   ├── Event.php                 # Base abstract event class
│   │   ├── EventFactory.php          # Factory method for creating event instances from arrays/types
│   │   ├── GameActionEvent.php       # Action evaluation event (outcomes, damage, logs)
│   │   ├── GameOverEvent.php         # Quest completion or party wipe event
│   │   ├── NextMissionEvent.php      # Mission transition event
│   │   ├── NextTurnEvent.php         # Turn rotation event
│   │   ├── PlayerJoiningEvent.php    # Player table join event
│   │   ├── PlayerQuittingEvent.php   # Player table leave event
│   │   ├── QuestStartingEvent.php    # Quest start event
│   │   └── SendingMessageEvent.php   # Chat message broadcast event
│   ├── Ability.php                   # D&D Ability scores (STR, DEX, CON, INT, WIS, CHA)
│   ├── Action.php                    # Mission actions & decision nodes
│   ├── Chapter.php                   # Story chapters
│   ├── CharacterClass.php            # RPG Character classes (Fighter, Wizard, Rogue, etc.)
│   ├── Item.php                      # Inventory & equipment items
│   ├── Mission.php                   # Mission steps & encounters
│   ├── Outcome.php                   # Action evaluation outcomes
│   ├── Player.php                    # Player character state & attributes
│   ├── Quest.php                     # Quest game instance
│   ├── QuestLog.php                  # Historical log of turn actions and outcomes
│   ├── QuestProgress.php             # Active quest mission state
│   ├── Race.php                      # RPG Character races (Human, Elf, Dwarf, etc.)
│   ├── Story.php                     # Campaign story definition
│   ├── User.php                      # User account model (`is_admin`, `is_designer`, `is_player`)
│   └── ...                           # (~90 total ActiveRecord models for campaign content & game state)
├── web/                              # Shared CSS, JS, fonts, and images
│   ├── css/
│   │   ├── dev-icons.css             # Developer / UI icon styles
│   │   ├── dragon-lite.css           # Core theme stylesheet (15 design system sections)
│   │   ├── fonts.css                 # Font-face declarations (Berenika, etc.)
│   │   └── icons.css                 # Application icon set
│   └── js/
│       ├── core-library.js           # ES6 Core JS library (`CoreLibrary`, `DOMUtils`, `AjaxUtils`, `ToastManager`, etc.)
│       └── simple-rich-text.js       # WYSIWYG editor class (`SimpleRichTextEditor`)
└── widgets/                          # Custom UI widgets
    ├── ActionButtons.php             # Configurable action button group
    ├── AjaxContainer.php             # Wrapper for AJAX-updated container blocks
    ├── Alert.php                     # Flash alert notification widget
    ├── Button.php                    # Custom styled button widget
    ├── CheckBox.php                  # Custom checkbox control widget
    ├── MarkDown.php                  # Server-side Markdown parser widget with custom tag support
    ├── ModalDesc.php                 # Modal description renderer
    ├── Pagination.php                # Custom styled pagination control
    ├── RecordCount.php               # Data table record counter display
    └── SimpleRichText.php            # Rich text editor widget wrapper
```

---

## 3. Application Architecture & Global Configuration

### 3.1 Centralized Components (`common/config/main.php`)
All shared components are configured in `@common/config/main.php`:
* **`i18n`:** Uses `yii\i18n\PhpMessageSource`. Configured with `app*` wildcard mapping `app`, `app/guest`, `app/lobby`, `app/error`, and `app/game` to file maps in `@common/messages`.
* **`eventHandler`:** Instance of `common\extensions\EventHandler\EventHandler` used to send JSON messages to the WebSocket daemon process.
* **`httpclient`:** Configured `yii\httpclient\Client` for outbound JSON API requests.
* **`cache`:** Configured with `yii\caching\FileCache` by default.
* **`log`:** File target logging runtime errors and warnings to `@runtime/logs/console.log` and websocket communications to `@runtime/logs/websocket.log`.

### 3.2 Context & Session Management (`ContextManager`)
`ContextManager` initializes and maintains runtime session state across HTTP requests and AJAX calls:
```
 +-----------------------------------------------------------------------+
 |                         ContextManager                                |
 +-----------------------------------------------------------------------+
 |  - initContext(?User $user): Sets session user, language, & player    |
 |  - updatePlayerContext(?int $playerId): Sets active player & avatar   |
 |  - updateQuestContext(?int $questId): Sets active quest & status      |
 |  - getContext(): Returns associative array of current session state   |
 +-----------------------------------------------------------------------+
```
Key guarantees enforced by `ContextManager`:
* Automatically hooks into `afterLogin` event on the `user` component in both `frontend` and `backend`.
* Sets `Yii::$app->language` based on user model preference or 30-day language cookie (`LanguageSelector::getLangCookie()`).
* Automatically syncs active player (`playerId`, `playerName`, `avatar`) and active quest (`questId`, `questName`, `inQuest`).
* Generates a unique session UUID (`sessionId`) if not present.

---

## 4. Core Application Components

### 4.1 Access Rights & Authorization (`AccessRightsManager`)
Provides role-based access control checking without heavy RBAC database overhead.
* **Roles:** Checked via user flags (`is_admin`, `is_designer`, `is_player`).
* **Route Validation:** `AccessRightsManager::isRouteAllowed($controller)` evaluates controller routes based on user attributes and application scope (`APP_FRONTEND` vs `APP_BACKEND`).
* **Execution Guard:** Access control behaviors in controllers call `AccessRightsManager` within deferred match closures to prevent premature evaluation during controller instantiation.

### 4.2 Language Selection (`LanguageSelector`)
* **Supported Languages:** `en` (English) and `fr` (French). Default is `en`.
* **Cookie Management:** Sets a `language` cookie valid for 30 days when a user changes language preference.
* **Session & Model Sync:** Updates `user.language` attribute and updates current application `Yii::$app->language`.

### 4.3 Quest Status Enum (`AppStatus`)
Provides explicit status definitions for quests and game sessions:
* `AppStatus::WAITING` (`'waiting'`): Tavern lobby, awaiting players.
* `AppStatus::PLAYING` (`'playing'`): Active gameplay on Virtual Table Top.
* `AppStatus::ENDED` (`'ended'`): Quest completed successfully.
* `AppStatus::TERMINATED` (`'terminated'`): Quest abandoned or failed.

---

## 5. Gameplay Engine & Managers Subsystem (`common/components/gameplay/`)

The gameplay mechanics are segregated into manager classes extending `BaseManager`:

```
                           +------------------+
                           |   BaseManager    |
                           +------------------+
                                    |
     +-----------------+------------+------------+-----------------+
     |                 |                         |                 |
     v                 v                         v                 v
+------------+  +--------------+          +--------------+  +---------------+
|QuestManager|  |ActionManager |          |OutcomeManager|  | PlayerManager |
+------------+  +--------------+          +--------------+  +---------------+
```

### 5.1 QuestManager
* **Role:** Manages high-level quest progression, mission flow, and turn rotation.
* **Mission Transitions:**
  * `moveToNextMission(int $nextMissionId)`: Executes explicit transition to a specific mission branch.
  * `moveToNextDefaultMission()`: Progresses sequentially along the mission order tree.
  * After context updates, automatically advances if the target mission has no remaining valid actions.
* **Turn Lifecycle:** Handles turn rotation (`moveToNextTurn()`), advances turn sequence, updates `QuestProgress`, and logs round counters.

### 5.2 ActionManager
* **Role:** Resolves available actions for players during their turn.
* **Action Eligibility:** Evaluates requirement checks (stat requirements, item conditions, class checks).
* **Dialogue Processing:** Resolves dialogue options (`Reply` trees) for 'talk' actions and formats conversation blocks for client UI rendering.

### 5.3 OutcomeManager
* **Role:** Applies outcomes when an action is executed or evaluated.
* **State Modifications:**
  * Grants experience (`xp`) and gold/coins.
  * Awards or removes items (`Item` endowment via `PlayerItem`).
  * Applies damage or healing to player HP (`current_hp`).
  * Triggers mission jumps (`next_mission_id`).
* **Logging & Events:** Stores `dialogLog` into `QuestLog` and triggers `GameActionEvent` broadcast via `EventHandler`.

### 5.4 PlayerManager
* **Role:** Character attribute calculations, equipment modifiers, and combat formulas.
* **Key Mechanics:**
  * `calcAbilityModifier(int $score)`: Computes standard D&D ability modifier `floor(($score - 10) / 2)`.
  * `updateArmorClass(Player $player)`: Calculates total Armor Class (AC) based on equipped chest armor, helmet, shield, and DEX modifier, saving the result to `player.armor_class`.
  * Merged player ability helpers (`getAbilitiesAndSavingThrow`, `getPlayerWeaponProperties`, `EMPTY_ABILITIES`).

### 5.5 TavernManager & ChatManager
* **TavernManager:** Controls table creation in the tavern lobby, player joins/leaves, and quest initiation.
* **ChatManager:** Handles quest and lobby chat message creation and triggers `SendingMessageEvent` broadcasts.

---

## 6. Real-Time Event Architecture

Real-time synchronization across clients is powered by event payloads in `common/models/events/` dispatched via `EventHandler`.

### 6.1 Event Flow
```
 [ PHP Gameplay Manager ]
          |
          v (Instantiates Event subclass, e.g. GameActionEvent)
  [ GameActionEvent::process() ]
          |
          +---> Creates QuestLog entry (localized via story language)
          |
          v
  [ EventHandler::send(Event $event) ]
          |
          v (JSON payload over TCP socket)
  [ WebSocket Server (Port 8080) ]
          |
          v (Broadcasts to connected WebSocket clients)
  [ NotificationClient (Frontend JS) ]
```

### 6.2 Key Event Types
| Event Model Class | Broadcast Type | Key Payload Fields | Client Action / Handler |
| :--- | :--- | :--- | :--- |
| `GameActionEvent` | `'game-action'` | `questId`, `playerId`, `outcomes`, `hpChange`, `itemChange` | Updates party status, triggers equipment refresh if items changed |
| `NextTurnEvent` | `'next-turn'` | `questId`, `currentPlayerId`, `turnNumber` | Triggers `vtt.refreshTurn()` |
| `NextMissionEvent` | `'next-mission'` | `questId`, `missionId`, `missionName` | Triggers `vtt.refreshMission()` |
| `GameOverEvent` | `'game-over'` | `questId`, `status`, `summary` | Redirects to quest summary screen |
| `SendingMessageEvent` | `'new-message'` | `questId`, `senderName`, `message` | Appends new chat line in chat feed |
| `PlayerJoiningEvent` / `PlayerQuittingEvent` | `'player-joined'` / `'player-quit'` | `questId`, `playerName` | Refreshes lobby/VTT party list |

---

## 7. Shared Data Layer & ActiveRecord Models

The `common/models/` directory contains over 90 ActiveRecord models categorized into functional domain groups:

### 7.1 Core Domain Categories
1. **User & Identity:** `User` (`is_admin`, `is_designer`, `is_player`, `language`), `LoginForm`, `AccessRight`, `AccessLog`.
2. **Campaign & Story Content:** `Story`, `Chapter`, `Mission`, `Action`, `Reply`, `Outcome`, `Passage`, `Dialog`.
3. **Character Rules & Compendium:** `Race`, `CharacterClass`, `Ability`, `Skill`, `Spell`, `Item`, `Armor`, `Weapon`, `Background`, `Feature`, `Proficiency`.
4. **Gameplay & Quest Instance:** `Quest`, `QuestPlayer`, `QuestProgress`, `QuestTurn`, `QuestLog`, `QuestSession`.
5. **Player Character State:** `Player`, `PlayerAbility`, `PlayerItem`, `PlayerBody`, `PlayerSpell`, `PlayerSkill`, `PlayerCoin`.

---

## 8. Shared Helpers & Utilities (`common/helpers/`)

* **`RichTextHelper`:** Sanitizes and processes formatted text content. Integrates caching for rendered Markdown.
* **`DiceRoller`:** Parses D&D dice notation (e.g. `1d20+3`, `2d6`) and generates random rolls using secure random integer generation.
* **`ItemHelper`:** Provides helper logic for equipment slotting, item sorting by category, and body zone constraints.
* **`SaveHelper`:** Encapsulates model saving within database transactions, logging validation errors on failure.
* **`Utilities`:** System utility functions including `newUUID()`, random string generation, and string formatting.
* **`FindModelHelper`:** Generic helper to load ActiveRecord instances by ID, throwing `NotFoundHttpException` when missing.

---

## 9. Custom Widgets & UI Extensions (`common/widgets/`)

### 9.1 `MarkDown` Widget (`common/widgets/MarkDown.php`)
Provides server-side Markdown parsing with custom RPG syntax extensions:
* **Scroll Block (`§§`):** Lines starting with `§§` toggle a `<div class="scroll">` container block.
* **Scroll Text (`++`):** Lines starting with `++` are wrapped in `<p class="text-scroll">`.
* **Dwarvish Text (`--`):** Lines starting with `--` are wrapped in `<p class="text-dwarvish">`.
* **Placeholder Replacement:** Intercepts arbitrary non-standard widget configuration properties and replaces `{placeholder}` tokens in text with HTML-escaped values.
* **XSS Prevention:** Calls `htmlspecialchars` prior to parsing markdown tags.

### 9.2 `SimpleRichText` Widget (`common/widgets/SimpleRichText.php`)
Provides the toolbar and HTML layout for the custom WYSIWYG editor, binding with `SimpleRichTextEditor` (`common/web/js/simple-rich-text.js`). Includes responsive `flex-wrap` layout for toolbar buttons.

---

## 10. Shared Web Assets & Design System (`common/web/`)

### 10.1 Primary Stylesheet: `common/web/css/dragon-lite.css`
Main theme stylesheet structured into 15 logical design system sections according to `/DESIGN.md`:
1. `ROOT` (Variables), 2. `TYPOGRAPHY`, 3. `BASE`, 4. `TABLES`, 5. `FORMS`, 6. `CARDS`, 7. `NAV_COMPONENTS`, 8. `BREADCRUMB_PAGINATION_BADGES`, 9. `MODALS`, 10. `LIST_VIEWS`, 11. `LAYOUT`, 12. `UTILITIES`, 13. `ANIMATIONS`, 14. `APP_SPECIFIC`, 15. `PRINT`.

### 10.2 Pure ES6 Client Core Library: `common/web/js/core-library.js`
Foundation client library written in ES6+ without jQuery dependencies:
* `CoreLibrary`: Configures base CSRF tokens and AJAX URLs.
* `DOMUtils`: Safe DOM helpers (`exists()`, `getParam()`).
* `AjaxUtils`: Standard wrapper over native `fetch` API.
* `ToastManager`: Integrates with Bootstrap 5 Toast components.
* `LanguageManager`: Manages client-side language switching.

---

## 11. Localization & i18n Architecture (`common/messages/`)

* **Centralized Configuration:** Managed in `@common/config/main.php` under `app*`.
* **Categories:**
  * `'app'`: General UI strings, action buttons, system labels.
  * `'app/guest'`: Authentication, landing page, signin/signup.
  * `'app/lobby'`: Tavern lobby, room listing, player waiting room.
  * `'app/game'`: VTT interface, combat terms, dice outcomes, quest logs.
  * `'app/error'`: HTTP and system error messages.
* **Language Support:** Complete translations provided for `en` (English) and `fr` (French).

---

## 12. Development & Testing Directives for Common Code

When making changes to files in `common/`, adhere strictly to the following guidelines:

1. **Transaction Safety:** Use `SaveHelper::save()` or explicit DB transactions when updating multiple models across gameplay managers (e.g., updating player HP, adding items, and logging quest progress).
2. **Localization Category Compliance:** Place new translation messages in the appropriate category file (`app.php`, `game.php`, `guest.php`, `lobby.php`). Ensure all user-facing strings use `Yii::t('app/...', '...')`.
3. **Pure JS Standard for Shared Scripts:** Shared JavaScript files in `common/web/js/` must strictly use standard ES6+ pure JavaScript (no jQuery dependencies).
4. **Markdown Syntax Parsing:** Always use `common\widgets\MarkDown::widget()` to render user-generated or story content containing custom tags (`++`, `--`, `§§`).
5. **Static Analysis & Unit Testing:** Before committing changes in `common/`, run PHPStan (`vendor/bin/phpstan analyse`) and Codeception unit tests (`vendor/bin/codecept run unit`) to ensure zero regressions.
