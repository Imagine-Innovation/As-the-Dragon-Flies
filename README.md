# As the Dragon Flies: A Digital RPG Adventure Platform

As the Dragon Flies is a comprehensive web-based platform designed to facilitate rich role-playing game (RPG) experiences, likely inspired by classic tabletop games like Dungeons & Dragons. Built on the robust Yii 2 Advanced Project Template, it provides a structured environment for players, game masters, and designers.

The application features distinct frontend and backend interfaces. The frontend caters to players, enabling character creation and management (including aspects like character class, race, and alignment), participation in interactive quests, inventory and equipment handling, and communication through features like quest chat. Core gameplay elements such as items and spells are integral to the player experience.

The backend likely serves administrative and content creation purposes, allowing for the design of intricate stories, multi-step quests (managed via `Quest` and `Story` models), and the definition of game elements like items, spells, and potentially non-player characters. With integrated WebSocket technology, evidenced by components like `EventServerController` and `WebSocketController`, the platform aims to support real-time interactions, crucial for enhancing the immersive experience of collaborative storytelling and dynamic gameplay.

## Usage Guide

"As the Dragon Flies" offers different experiences based on user roles. Here's a general guide:

### For Players

1.  **Getting Started:**
    *   **Account Creation:** New players can sign up for an account through the registration page (`site/signup`). Email verification (`site/verify-email`, `site/resend-verification-email`) may be required.
    *   **Login:** Registered players can log in using their credentials (`site/login`).

2.  **Character Creation:**
    *   The platform likely provides a character builder tool (suggested by `PlayerBuilderController`).
    *   Players can define their character's name, choose a race (`Race` model), class (`CharacterClass` model), and alignment (`Alignment` model).
    *   Allocate ability scores, select skills, traits, and potentially initial equipment (`Equipment` model).
    *   (Potentially) Choose an avatar for their character (inferred from `ImageUploadForm`).

3.  **Joining Adventures:**
    *   **The Tavern/Quest Hub:** Quests (`Quest` model) might be found and joined via a central interface, possibly referred to as a "Tavern" or quest listing area.
    *   Players can view available stories (`Story` model) and quests suitable for their character.

4.  **Playing the Game:**
    *   **Interactive Gameplay:** Participate in quests with real-time updates and interactions, facilitated by WebSocket components (`QuestChat`, `WebSocketController`).
    *   **Chat:** Communicate with the Game Master and other players through an integrated chat system (`QuestChat`).
    *   **Inventory Management:** Manage your character's items (`Item` model) and equipment acquired during adventures.
    *   **Character Progression:** Characters likely gain experience and levels, though specific mechanics are not detailed.

### For Designers / Game Masters (GMs)

Designers and GMs (users with `is_designer` flag) are the storytellers and world-builders. The backend interface provides tools for:

*   **Story and Quest Creation:** Design intricate narratives (using `Story` and `Quest` models), define quest objectives, encounters (`QuestEncounter`), and rewards.
*   **Content Management:**
    *   Create and manage character classes (`CharacterClassController`), races (`RaceController`), and alignments (`AlignmentController`).
    *   Define items (`ItemController`), equipment, and magical spells (`SpellController`) available in the game world.
    *   Develop rules and game mechanics (`RuleController`).
*   **(Potentially) Live Game Management:** GMs might have tools to oversee active quests (`QuestPlayer` model indicates player involvement in quests), guide players, or manage non-player characters (NPCs).

### For Administrators

Administrators (users with `is_admin` flag) have oversight of the platform:

*   **User Management:** View user accounts (`UserController`), manage user roles (player, designer, admin via `is_player`, `is_designer`, `is_admin` flags in `User` model), and monitor user activity logs (`UserLog`).
*   **Access Rights Configuration:** Define permissions for different parts of the application based on user roles and context using the `AccessRight` system (`AccessRightController`, `ManageAccessRights` component).
*   **System Maintenance & Configuration:** (Details would depend on specific backend modules not fully explored, but general site settings might be available).

This guide provides a general overview. Specific functionalities may vary as the platform evolves.

## Contribution Guidelines

We welcome contributions to "As the Dragon Flies"! Whether you're fixing a bug, adding a new feature, or improving documentation, your help is appreciated.

### Getting Involved

*   **Issues:** Check the GitHub issues tab for existing bugs, feature requests, or discussions.
*   **Feature Suggestions:** If you have a new idea, please open an issue to discuss it before starting significant work.

### Development Process

1.  **Fork & Branch:** Fork the repository and create a new feature branch from the main development branch (e.g., `main` or `develop`).
    ```bash
    git checkout -b feature/your-feature-name
    ```
2.  **Code:** Make your changes.
    *   **Coding Standards:**
        *   Try to follow the general Yii 2 coding style and conventions used in the project.
        *   Write clear, understandable, and well-commented code.
        *   Ensure your code is PSR-12 compliant where applicable.
    *   **Dependencies:** If adding new dependencies, update `composer.json` and run `composer update`. Remember to commit the `composer.lock` file.

3.  **Testing:**
    *   This project uses Codeception for testing. Please add new tests for any new features or bug fixes. Test files are typically found in `frontend/tests`, `backend/tests`, and `common/tests`.
    *   Ensure all tests pass before submitting a pull request. You can typically run tests using commands like:
        ```bash
        # Navigate to the specific directory (frontend, backend, or common)
        # For frontend tests (example)
        cd frontend
        php vendor/bin/codecept run
        cd ..

        # For backend tests (example)
        cd backend
        php vendor/bin/codecept run
        cd ..

        # For common unit tests (example)
        cd common
        php vendor/bin/codecept run
        cd ..
        ```
        (Note: Specific Codeception setup and execution might vary based on project configuration. Refer to `codeception.yml` files in each section.)

4.  **Commit:** Write clear and concise commit messages, referencing issue numbers if applicable (e.g., "feat: Implement player avatar upload (Fixes #123)").

### Submitting Pull Requests

1.  Push your feature branch to your fork.
2.  Open a Pull Request (PR) against the main development branch of the original repository.
3.  **PR Description:**
    *   Provide a clear title and a detailed description of the changes.
    *   Link to any relevant GitHub issues (e.g., "Fixes #123" or "Implements #456").
4.  **Review:** Your PR will be reviewed, and feedback may be provided. Please address any comments or requested changes promptly.

### Code of Conduct

Please note that this project aims to foster an open and welcoming environment. All contributors are expected to adhere to a Code of Conduct. (If a `CODE_OF_CONDUCT.md` file exists, link to it. If not, this serves as a placeholder and a reminder to consider creating one, perhaps adapting the Contributor Covenant.)

Thank you for contributing!
