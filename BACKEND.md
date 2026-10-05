# Backend Architectural Assessment & Reference Manual

This document provides a comprehensive technical assessment of the backend administration and campaign management application in **As the Dragon Flies**. It is designed to serve as long-term architectural memory and reference for future backend development, content management, maintenance, and AI-assisted tasks.

---

## 1. Executive Summary & Overview

The backend application (`backend/`) is the administrative, campaign authoring, and monitoring hub for **As the Dragon Flies**, built on top of the **Yii2 Advanced Application Template**.

### Key System Capabilities
* **Campaign & Content Authoring:** Full CRUD management for Stories, Chapters, Missions, Actions, Spells, Items, Races, and Character Classes.
* **Role-Based Access Control (RBAC) & Permission Engine:** Fine-grained access control managed via `AccessRightsManager` checking permissions based on `User` properties (`is_admin`, `is_designer`).
* **Database Performance Monitoring System (`DbMonitor`):** Multi-driver database performance analyzer (MySQL, SQLite, PostgreSQL, SQL Server) providing slow query execution statistics, EXPLAIN plan inspections, index recommendations, and activity visualization widgets.
* **Real-time KPI & Analytics Dashboard:** Live updates for active game sessions, user registrations, quest progress, top players, and system activity using `DashboardManager` and AJAX polling.
* **WYSIWYG Rich Text Content Pipeline:** Native integration with `SimpleRichTextEditor` supporting custom markup extensions (e.g. Scroll `++`, Dwarvish `--`, and Scroll Blocks `§§`) matching frontend rendering capabilities.

---

## 2. Directory Structure & File Map

```
backend/
├── assets/
│   └── AppAsset.php                  # Primary AssetBundle registering CSS, JS, and CDN dependencies
├── components/
│   ├── drivers/                      # Database monitoring drivers (MysqlDriver, SqliteDriver, etc.)
│   └── DbMonitorManager.php          # Database monitoring manager abstraction
├── config/
│   ├── bootstrap.php                 # Backend bootstrap configuration
│   ├── main.php                      # Main backend application configuration
│   ├── params.php                    # Backend specific parameters
│   └── test.php                      # Codeception test configuration
├── controllers/
│   ├── AccessRightController.php     # User role & permission management
│   ├── ChapterController.php         # Campaign chapter management
│   ├── CharacterClassController.php  # RPG class stats & requirements
│   ├── DbMonitorController.php       # Database query analytics & performance tools
│   ├── ImageController.php           # Asset image uploading & library management
│   ├── ItemController.php            # Game items, equipment & stat attributes
│   ├── KpiController.php             # System KPI data endpoint for dashboard
│   ├── MissionController.php         # Quest mission steps, actions & outcomes
│   ├── PlayerController.php          # Character inspection & top player rankings
│   ├── RaceController.php            # RPG race stats & traits
│   ├── SearchController.php          # Global administrative entity search
│   ├── SiteController.php            # Admin login, home dashboard, design tokens (fonts/colors/icons)
│   ├── SpellController.php           # Magic spells & abilities
│   ├── StoryController.php           # Main campaign story arcs & publishing
│   └── UserController.php            # Admin user accounts & credential management
├── models/
│   └── DbMonitor.php                 # Model for DB monitoring queries & thresholds
├── tests/                            # Codeception backend test suites (unit, functional)
├── views/
│   ├── access-right/                 # Access rights matrix & user assignment views
│   ├── chapter/                      # Chapter CRUD & mission linking views
│   ├── character-class/              # Character class editor
│   ├── db-monitor/                   # DB performance monitoring, query explain & suggestions
│   ├── image/                        # Image gallery & upload forms
│   ├── item/                         # Item inventory & stat assignment
│   ├── layouts/                      # Main admin layout (`main.php`, `blank.php`, `left-menu.php`)
│   ├── mission/                      # Mission flow, action tree & outcome builder
│   ├── player/                       # Character sheet inspection & top 10 tables
│   ├── race/                         # Race traits & modifier management
│   ├── site/                         # Main dashboard, login, icon/font design guides
│   ├── spell/                        # Spell catalog management
│   ├── story/                        # Story tree, chapter outline & story publishing
│   └── user/                         # User administration forms
├── web/
│   ├── css/
│   │   └── site.css                  # Administrative layout styling & utilities (`.flex-grow-min-0`)
│   └── js/
│       ├── dashboard.js              # `DashboardManager` class for live KPI & active quest updates
│       └── search-select.js          # Searchable dropdown & quick selection helper
└── widgets/
    ├── views/                        # Widget view templates
    ├── ActivityGraph.php             # Database activity visualizer widget
    ├── DbMonitorExplainPlan.php      # SQL EXPLAIN output renderer
    ├── DbMonitorSuggestion.php       # Database optimization suggestions widget
    ├── DbMonitorTopQueries.php       # Top slow/frequent queries table widget
    ├── ItemTable.php                 # Reusable item list table widget
    └── Kpi.php                       # Dashboard KPI metric widget
```

---

## 3. Architecture, Access Control & Routing

### 3.1 Authorization & Permissions
* **Role Verification:** Backend access is restricted to authenticated users where `is_admin = 1` or `is_designer = 1`. Non-privileged users are automatically logged out and redirected to the login page with an error flash message.
* **Access Control Behaviors:** Backend controllers implement `yii\filters\AccessControl` using `AccessRightsManager::isRouteAllowed($action->controller)` deferred inside match callbacks:
  ```php
  'rules' => [
      [
          'actions' => ['login', 'error'],
          'allow' => true,
      ],
      [
          'actions' => ['index', 'create', 'update', 'delete', ...],
          'allow' => true,
          'matchCallback' => function ($rule, $action) {
              return AccessRightsManager::isRouteAllowed($action->controller);
          },
          'roles' => ['@'],
      ],
  ]
  ```

### 3.2 Session & Cookie Management
* **App Identifier:** `AccessRightsManager::APP_BACKEND` (`app-backend`).
* **Session Cookie:** Configured as `advanced-backend` in `backend/config/main.php`.
* **Identity Cookie:** Configured as `_identity-backend` with `httpOnly => true`.
* **CSRF Token:** Parameter named `_csrf-backend` required in request headers or body for non-GET requests.

---

## 4. Core Campaign & Content Management Controllers

The backend primary responsibility is campaign creation and entity administration.

```
                    +------------------------------------+
                    |        StoryController.php         |
                    |   (Campaign Story Arc Management)  |
                    +------------------------------------+
                                      |
                                      v
                    +------------------------------------+
                    |        ChapterController.php       |
                    |   (Story Chapters & Sequencing)    |
                    +------------------------------------+
                                      |
                                      v
                    +------------------------------------+
                    |        MissionController.php       |
                    | (Missions, Actions & Outcome Trees)|
                    +------------------------------------+
```

### 4.1 Campaign Hierarchy & Authoring Flow
1. **Stories (`StoryController`):** Manages top-level campaigns. Stories link to language choices ('en', 'fr') and set minimum recommended levels.
2. **Chapters (`ChapterController`):** Divides stories into sequential chapters (`chapter_order`).
3. **Missions & Actions (`MissionController`):** Defines mission encounters, dialogue actions, decision branches, and outcomes. Outcome management allows attaching item rewards, experience, stat checks, and mission branch transitions (`next_mission_id`).

### 4.2 RPG Entity Management
* **Item Management (`ItemController`):** Inventory item catalog with stat modifiers (AC, damage, weight, cost, body zone assignments). Uses `ItemTable` widget for filtering and selection.
* **Spells (`SpellController`):** Spell definitions, level requirements, damage dice, and saving throw attributes.
* **Races & Character Classes (`RaceController`, `CharacterClassController`):** Race traits, ability score modifiers, hit dice, and starting gear defaults.

---

## 5. Database Monitoring Engine (`DbMonitor`) & Analytics

The backend features a dedicated performance monitoring and diagnostics toolkit located in `backend/components/DbMonitorManager.php` and `backend/controllers/DbMonitorController.php`.

### 5.1 Multi-Driver Support
`DbMonitorManager` detects the underlying database connection DSN and delegates execution to driver implementations:
* `MysqlDriver`: Queries `information_schema` and `performance_schema` for slow queries, missing indexes, and buffer pool stats.
* `SqliteDriver`: Analyzes `sqlite_master`, PRAGMA statistics, and page count metrics.
* `PostgresDriver` & `SqlServerDriver`: Driver adapters for PostgreSQL and Microsoft SQL Server monitoring.

### 5.2 Performance & Diagnostics Widgets
* `DbMonitorTopQueries`: Renders query execution counts, average runtime, and peak memory usage.
* `DbMonitorExplainPlan`: Formats and renders raw SQL `EXPLAIN` query execution plans.
* `DbMonitorSuggestion`: Generates automated recommendations (e.g., missing index warnings, table scan detection).
* `ActivityGraph`: Visualizes database read/write throughput and query execution spikes over time.

---

## 6. Key Widgets & UI Integration

### 6.1 Layout & Responsive Shell (`backend/views/layouts/main.php`)
* **Flexible Main Content Area:** Employs the utility class `.flex-grow-min-0` (`flex-grow: 1; min-width: 0;` defined in `backend/web/css/site.css`) on the main container to prevent wide tables or code blocks from overflowing horizontally.
* **Mobile Navigation Offcanvas:** Clones main navigation links (`#mainNavContent`) into a mobile-friendly Bootstrap offcanvas sidebar (`#mobileSidebar`).
* **Sidebar Manual Shrink:** Toggleable side navigation (`#sidebarToggle`) supporting compact mode (`.manual-shrink`).

### 6.2 Administrative Widgets
* `Kpi`: Widget displaying real-time metric counters (e.g. Active Users, Total Quests, Active Games, Completed Stories). Updated via AJAX by `DashboardManager`.
* `ItemTable`: Reusable data grid widget for browsing and selecting items in forms.
* `Alert`: System flash message container for success, warning, and error alerts.

---

## 7. Client-Side JavaScript Architecture (`backend/web/js/`)

### 7.1 Dashboard Manager (`backend/web/js/dashboard.js`)
`DashboardManager` coordinates live administrative updates on the homepage dashboard:
* **KPI Polling (`updateKpis`):** Periodically requests `kpi/update` via `AjaxUtils.request` (default 60s) and updates KPI DOM elements.
* **Active Quests Refresh (`updateActiveQuests`):** Polls `site/ajax-active-quests` (default 300s) to update active game sessions table `#activeQuestsTable`.
* **Top 10 Players Refresh (`updateTop10Players`):** Polls `player/ajax-top10` to update high score leaderboards `#top10PlayersTable`.

### 7.2 Core Asset Dependencies (`backend/assets/AppAsset.php`)
* CSS Assets: `/common/web/css/icons.css`, `/common/web/css/fonts.css`, `/common/web/css/dragon-lite.css`, `css/site.css`, `/common/web/css/dev-icons.css`.
* JS Assets: `/common/web/js/core-library.js`, `/common/web/js/simple-rich-text.js`, `js/dashboard.js`, `js/search-select.js`.
* Dependencies: `yii\web\YiiAsset`, `yii\bootstrap5\BootstrapAsset`.

---

## 8. Development & Testing Directives for Backend Tasks

When modifying or expanding the backend codebase, adhere strictly to the following guidelines:

1. **Access Control Checks:** Ensure all new controller actions define appropriate access rules matching `AccessRightsManager::isRouteAllowed($action->controller)` within deferred closures.
2. **Layout Container Safeguards:** Ensure top-level containers inside backend views use Bootstrap grid rows with gutter spacing (e.g. `.row.g-3`) and responsive columns (e.g. `col-12 col-md-10`) to maintain layout integrity.
3. **Pjax Download Buttons:** When placing file download links or action buttons inside Pjax-enabled containers, append `'data-pjax' => '0'` to the button attributes to bypass Pjax request interception.
4. **Rich Text Content Styling:** Ensure backend forms using rich text fields rely on `SimpleRichTextEditor` (`common/web/js/simple-rich-text.js`) and include `/common/web/css/fonts.css` and `/common/web/css/dragon-lite.css` so formatting classes (`.text-scroll`, `.text-dwarvish`) render accurately.
5. **Codeception Test Namespace:** Ensure Codeception suite configurations for backend testing (e.g., `backend/tests/unit.suite.yml`) strictly use the `backend\tests` namespace.
6. **Static Analysis & Testing:** Verify changes by running PHPStan (`vendor/bin/phpstan analyse`) and Codeception unit tests (`cd backend && ../vendor/bin/codecept run`) before committing code.
