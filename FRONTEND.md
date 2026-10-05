# Frontend Architectural Assessment & Reference Manual

This document provides a comprehensive technical assessment of the frontend application in **As the Dragon Flies**. It is designed to serve as long-term architectural memory and reference for future development, maintenance, and AI-assisted tasks.

---

## 1. Executive Summary & Overview

The frontend application (`frontend/`) is a turn-based online web-based RPG (Dungeons & Dragons style) built on top of the **Yii2 Advanced Application Template**.

### Key System Capabilities
* **Virtual Table Top (VTT):** Turn-based interactive game view (`r=game/view`) providing real-time gameplay, mission tracking, action evaluation, and dynamic dialogue rendering.
* **Real-time Synchronization:** WebSocket-based event broadcasting via `NotificationClient` and `EventHandler` server on port `8080` (or configured port).
* **Interactive Equipment & SVG Body Paperdoll:** Dynamic equipment visualizer (`EquipmentHandler`) with clickable SVG body zones (head, chest, hands) updating in real-time.
* **Character Builder:** Multi-step wizard (`r=player-builder/*`) allowing character creation, race/class selection, ability generation, skills, and equipment endowment.
* **Shop & Cart System:** Shop interface (`r=player-cart/*`) for purchasing items and managing inventory.
* **Localization & Theme System:** Full English and French localization via `LanguageSelector` and unified dark fantasy theme styling defined in `common/web/css/dragon-lite.css` and `frontend/web/css/vtt.css`.

---

## 2. Directory Structure & File Map

```
frontend/
├── assets/
│   └── AppAsset.php                  # Primary AssetBundle registering CSS/JS dependencies
├── config/
│   ├── bootstrap.php                 # App bootstrap script
│   ├── main.php                      # Application configuration (components, assetManager, handlers)
│   ├── params.php                    # Frontend specific parameters (e.g., eventHandlerWebSocketPort)
│   └── test.php                      # Codeception test configuration
├── controllers/
│   ├── GameController.php            # VTT gameplay AJAX endpoints, mission/action transitions, dialogue
│   ├── ItemController.php            # Item detail views and image management
│   ├── PlayerBuilderController.php   # Character creation wizard and AJAX steps
│   ├── PlayerCartController.php      # Shop catalog and purchasing cart
│   ├── PlayerController.php          # Character sheet, stats, and history
│   ├── PlayerItemController.php      # Equipment management and body zone equipping/disarming
│   ├── QuestController.php           # Quest lobby, tavern, summary, and chat messages
│   ├── SiteController.php            # Authentication (login/signup), lobby dashboard, guest page
│   ├── StoryController.php           # Story catalog and overview
│   ├── UserController.php             # User profile and language selection
│   └── WizardController.php          # Alternative wizard steps
├── helpers/
│   └── Caligraphy.php                # Font helper functions
├── models/                           # Form models (Signup, Login, Password Reset, Image Upload)
├── tests/                            # Codeception test suites (unit, functional, acceptance)
├── views/
│   ├── game/                         # VTT view templates (`view.php`, AJAX snippets, modals)
│   ├── item/                         # Item detail views
│   ├── layouts/                      # App layouts (`main.php`, `game.php`, contents/, snippets/)
│   ├── player/                       # Character sheet snippets (abilities, combat stats, skills, equipment)
│   ├── player-builder/               # Character creation wizard steps
│   ├── player-cart/                  # Shop catalog and cart snippets
│   ├── player-item/                  # Inventory & equipment pack snippets
│   ├── quest/                        # Tavern, lobby, chat snippets, quest summaries
│   ├── site/                         # Guest landing, lobby dashboard, login/signup forms
│   ├── story/                        # Story cards and index
│   ├── user/                         # Profile management
│   └── wizard/                       # Helper wizard AJAX views
└── web/
    ├── css/
    │   └── vtt.css                   # Layout grid, turn bar, side panels, and VTT responsive design
    ├── js/
    │   ├── chart-drawer.js           # Ability score chart renderer
    │   ├── equipment-manager.js      # `EquipmentHandler` class (SVG zones & item equipment)
    │   ├── player-builder.js         # Character builder client script
    │   ├── player-item-manager.js    # Item table manager
    │   ├── quest-events.js           # `NotificationClient` WebSocket handler & audio trigger
    │   ├── quest-game.js             # `VirtualTableTop` class (VTT mechanics & state sync)
    │   ├── quest-tavern.js           # Tavern lobby client script
    │   └── shop-manager.js           # Shop and cart manager
    └── offline/                      # Offline fallback assets (Bootstrap 5, jQuery, FontAwesome)
```

### Shared Global Assets (`common/web/`)
* `common/web/js/core-library.js`: Core client utilities (`CoreLibrary`, `Logger`, `DOMUtils`, `AjaxUtils`, `ToastManager`, `LanguageManager`, `TableManager`, `UserManager`, `ActionButtonManager`, `LayoutInitializer`).
* `common/web/js/simple-rich-text.js`: WYSIWYG editor class (`SimpleRichTextEditor`) handling custom scroll blocks (`§§`), Dwarvish (`--`), and Scroll (`++`) text classes without jQuery dependencies.
* `common/web/css/dragon-lite.css`: Primary theme stylesheet structured into 15 logical design system sections according to `DESIGN.md`.

---

## 3. Architecture, Access Control & Routing

### 3.1 Authentication & Authorizations
* **Role Enforcement:** All authenticated frontend users must have `is_player = true` (enforced at login level).
* **Access Control Behaviors:** Controllers use `yii\filters\AccessControl` with callback rules:
  ```php
  'rules' => [
      ['actions' => ['*'], 'allow' => false, 'roles' => ['?']],
      [
          'actions' => ['view', 'ajax-actions', ...],
          'allow' => true,
          'matchCallback' => function ($rule, $action) {
              return AccessRightsManager::isRouteAllowed($action->controller);
          },
          'roles' => ['@'],
      ],
  ]
  ```

### 3.2 Session & Context Management
* **Session Cookie:** Configured as `advanced-frontend` in `frontend/config/main.php`.
* **CSRF Token:** Parameter named `_csrf-frontend` sent in HTTP headers (`X-CSRF-Token`) for all AJAX requests.
* **Context Manager Initialization:** Connected to `user` component `afterLogin` event:
  ```php
  'on afterLogin' => function ($event) {
      ContextManager::initContext($event->identity);
  }
  ```
* **Language Selection:** Managed by `common\components\LanguageSelector`. User preferences can be toggled via `User::actionAjaxSetLanguage` or guest session cookie.

---

## 4. Virtual Table Top (VTT) & Gameplay Engine

The Virtual Table Top is the central gameplay module located at `r=game/view&id=<questId>`.

```
                    +----------------------------------+
                    |        game/view (PHP View)      |
                    |  (Hidden inputs store context)   |
                    +----------------------------------+
                                     |
                                     v
                    +----------------------------------+
                    |    VirtualTableTop (JS Class)    |
                    |     (quest-game.js lifecycle)    |
                    +----------------------------------+
                        /            |             \
                       /             |              \
                      v              v               v
            +--------------+  +--------------+  +---------------+
            | ajax-mission |  | ajax-actions |  |  ajax-player  |
            +--------------+  +--------------+  +---------------+
                      \              |              /
                       v             v             v
                    +----------------------------------+
                    |      GameController.php (PHP)    |
                    |    Evaluates Action Outcomes     |
                    +----------------------------------+
                                     |
                                     v
                    +----------------------------------+
                    |     NotificationClient (WS)      |
                    | Broadcasts 'game-action' Event   |
                    +----------------------------------+
```

### 4.1 State Synchronization via Hidden Input Tags
Context state is synchronized between client JS and server PHP using hidden `<input type="hidden">` DOM elements inside `frontend/views/game/view.php`:
* `#hiddenStoryId`
* `#hiddenQuestId`
* `#hiddenQuestProgressId`
* `#hiddenQuestMissionId`
* `#hiddenCurrentPlayerId`
* `#hiddenCurrentPlayerName`
* `#hiddenPlayerId`
* `#hiddenQuestActionId`
* `#hiddenDialogLog`

`VirtualTableTop` manages internal `this.context` with `_loadContext()` and updates both memory and DOM elements during transitions using `updateContext()` and `_saveContext()`.

### 4.2 Action Evaluation Flow
1. **Fetch Eligible Actions:** Client calls `game/ajax-actions?questProgressId=...`.
2. **Execute Action:** User clicks an action or dialogue choice (`talk(actionId, replyId)`, `reply(replyId)`).
3. **Dialogue Handling:** `game/ajax-dialog` returns dialogue snippet and appends formatted Markdown lines (`- [CharacterName](#) - text`) to `dialogLog`. Text-to-speech fallback uses Web Speech API (`SpeechSynthesisUtterance`) if no recorded audio exists.
4. **Outcome Evaluation:** `game/ajax-get-outcomes` triggers `OutcomeManager::evaluateActionResult()`, creates `game-action` event via `EventFactory`, logs entry in `QuestLog`, and opens `#gameModal`.
5. **Next Turn Transition:** `game/ajax-next-turn` validates whether actions remain or moves to next default mission via `QuestManager::moveToNextMission()`.

---

## 5. Real-Time WebSockets & Event System

The real-time layer is powered by `NotificationClient` (`frontend/web/js/quest-events.js`).

### 5.1 Connection Lifecycle
* WebSocket URL is dynamically constructed: `${protocol}://${hostname}:${eventHandlerWebSocketPort}`.
* Reconnections are attempted automatically every 3 seconds if disconnected.
* Audio chime (`music/ding.mp3`) signals new chat messages or updates.

### 5.2 Handled Event Subscriptions
| Event Name | Action / Handler |
| :--- | :--- |
| `open` | Registers session with `{type: 'register'}` and updates status indicator `#eventHandlerStatus`. |
| `notification` | Calls `vtt.refresh()` to update member list and player stats. |
| `new-message` | Triggers `updateChatMessages()` to refresh chat feed via AJAX. |
| `game-action` | Invokes `handleGameAction(data)`: Updates party stats and triggers `equipmentHandler.refreshEquipment()` if items/stats changed. |
| `next-turn` | Invokes `vtt.refreshTurn(questId, playerId, detail)`. |
| `next-mission` | Invokes `vtt.refreshMission(questId, playerId, detail)`. |
| `game-over` | Displays toast, clears session via `quest/ajax-end-quest`, and redirects to `r=quest/summarize`. |
| `player-joined` / `player-quit` | Refreshes VTT member list and displays notification toast. |

---

## 6. Interactive Equipment & SVG Body Management

Equipment paperdoll functionality is encapsulated by `EquipmentHandler` (`frontend/web/js/equipment-manager.js`).

```
 +-----------------------------------------------------------------------+
 |                         EquipmentHandler                              |
 |                                                                       |
 |   SVG Body Zones:                                                     |
 |   [equipmentHeadZone]      -> Filter 'Helmet' -> Disarm / Equip       |
 |   [equipmentChestZone]     -> Filter 'Armor'  -> Disarm / Equip       |
 |   [equipmentRightHandZone] -> Filter 'Weapon', 'Tool' -> Disarm/Equip |
 |   [equipmentLeftHandZone]  -> Filter 'Weapon', 'Shield' -> Disarm/Equip|
 +-----------------------------------------------------------------------+
                                     |
                                     v
 +-----------------------------------------------------------------------+
 |                     PlayerItemController.php                          |
 |                                                                       |
 |   - actionAjaxEquipment($playerId): Returns inventory HTML & items    |
 |   - actionAjaxEquipPlayer(): Equips item & recalculates Armor Class  |
 |   - actionAjaxDisarmPlayer(): Disarms body zone                       |
 +-----------------------------------------------------------------------+
                                     |
                                     v
 +-----------------------------------------------------------------------+
 |                   SVG Rendering Update Targets                        |
 |   - #svg-modal (Modal View)                                           |
 |   - #svg-aside (Desktop Side Panel)                                   |
 |   - #svg-aside-offcanvas (Mobile Offcanvas Drawer)                   |
 +-----------------------------------------------------------------------+
```

---

## 7. Client Core Library Utilities (`common/web/js/core-library.js`)

`core-library.js` is written in pure ES6 JavaScript without jQuery dependencies for core network/DOM operations, serving as the foundational client utility layer.

### Key Class Modules
* **`CoreLibrary`:** Initializes CSRF tokens (`CONFIG.CSRF_TOKEN`) and AJAX root URLs.
* **`Logger`:** Configurable logging levels (`CONFIG.LOG_LEVEL`) with stack trace caller extraction.
* **`DOMUtils`:** Standardized DOM inspection helpers (`exists()`, `getParam()`). Supports `.val()` for form inputs and `.html()` for standard containers.
* **`AjaxUtils`:** Centralized wrapper over `fetch` / standard AJAX requests. Sets `X-CSRF-Token` headers and handles JSON responses cleanly.
* **`ToastManager`:** Integrates directly with Bootstrap 5 Toast instances (`new bootstrap.Toast()`) to display dynamic server notifications.
* **`LanguageManager`:** Triggers language switches via `user/ajax-set-language`.
* **`TableManager`:** Handles generic paginated AJAX data tables.
* **`ActionButtonManager`:** Automatically binds click events on `[id^="actionButton-"]` buttons.

---

## 8. Styling & Design System Architecture

### 8.1 Primary Theme: `common/web/css/dragon-lite.css`
Main stylesheet enforcing the dark fantasy RPG aesthetic. Structured into 15 logical sections adhering strictly to `/DESIGN.md`:
1. `ROOT`: CSS variables (colors `--yellow`, `--dark-25`, `--light-10`, typography `--font-body`, `--font-heading`).
2. `TYPOGRAPHY`: Headings (`Berenika` serif font), paragraphs, lists.
3. `BASE`: Body background, general layout defaults.
4. `TABLES`: Custom dark data tables (`.table-dragon`).
5. `FORMS`: Inputs, toggle switches (`.toggle-switch`), labels.
6. `CARDS`: Container cards (`.card`).
7. `NAV_COMPONENTS`: Navbars, pills, tabs.
8. `BREADCRUMB_PAGINATION_BADGES`: Custom badges and paginators.
9. `MODALS`: Modal dialogs (`.modal-content`).
10. `LIST_VIEWS`: Custom list items.
11. `LAYOUT`: Flexible layout containers.
12. `UTILITIES`: Spacing, text helper classes (`.text-scroll`, `.text-dwarvish`, `.flex-grow-min-0`).
13. `ANIMATIONS`: Blinking status indicators (`.blink`).
14. `APP_SPECIFIC`: RPG specific widgets.
15. `PRINT`: Print style overrides.

### 8.2 VTT Layout System: `frontend/web/css/vtt.css`
Mobile-first CSS grid layout designed specifically for gameplay:
* **Mobile (< 768px):** Single column grid (`stage`, `sidebar`), offcanvas character drawer.
* **Tablet Portrait (768px – 991px):** Stacked grid (`party`, `stage`, `sidebar`).
* **Tablet Landscape (992px – 1199px):** Two-column layout (`280px minmax(0, 1fr)`).
* **Desktop (1200px – 1599px):** Three independent columns (`260px minmax(0, 1fr) 320px`) with sticky party and sidebar panels.
* **Full HD (1600px – 1980px):** Scaled desktop grid up to 1880px max-width.
* **Ultra-wide (≥ 1981px):** Expanded four-panel layout (`340px minmax(0, 1180px) 620px`) displaying journal and chat feeds side-by-side.

---

## 9. Script Pipeline & Initialization Pattern

Scripts are loaded dynamically per controller/action in `frontend/views/layouts/snippets/javascript.php` and initialized via template snippets in `frontend/views/layouts/snippets/js/`:

```php
// Example script resolution in javascript.php
$javascriptLibraries = match ($controllerId) {
    'player-builder' => ['player-builder', 'chart-drawer'],
    'player-cart'    => ['shop-manager'],
    'quest'          => ['quest-tavern', 'quest-events'],
    'game'           => ['quest-game', 'quest-events', 'equipment-manager'],
    'player-item'    => ['player-item-manager'],
    default          => []
};
```

On page load (`$(document).ready`), global instances are initialized:
```javascript
vtt = new VirtualTableTop();
vtt.init();

notificationClient = new NotificationClient(url, sessionId, playerId, playerName, avatar, questId, questName, vtt);
notificationClient.init();

equipmentHandler = new EquipmentHandler();
equipmentHandler.init(playerId, document.getElementById('equipmentSvg'));
```

---

## 10. Development & Testing Directives for Frontend Tasks

When modifying or expanding the frontend codebase, adhere strictly to the following guidelines:

1. **DOM Access Safety:** Always verify DOM element existence via `DOMUtils.exists('#elementId')` or explicit null guards before attaching events or setting HTML to avoid JavaScript runtime errors.
2. **Context Preservation:** When updating VTT state or actions, ensure hidden input tags (`#hiddenQuestProgressId`, `#hiddenQuestMissionId`, etc.) are updated via `vtt.updateContext()` so subsequent AJAX requests do not send stale data.
3. **Escaping Output:** Always escape dynamic user inputs, character names, or mission titles in PHP views using `Html::encode()`.
4. **AJAX Error Guards:** Server AJAX actions in controllers must validate request types (`$this->request->isAjax`) and return structured JSON responses (`['error' => true, 'msg' => ...]`) rather than throwing raw unhandled HTTP 400 exceptions.
5. **No Direct Modifying of Minified Vendor Assets:** Vendor files in `web/offline/` or minified libraries shall not be edited directly. Modify source CSS (`dragon-lite.css`, `vtt.css`) or custom JS files.
6. **Testing:** Run frontend Codeception unit and functional test suites (`cd frontend && ../vendor/bin/codecept run`) whenever core controller actions or models are updated.
