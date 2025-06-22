<?php

namespace frontend\components;

use common\models\Ability;
use common\models\AbilityDefault;
use common\models\BackgroundSkill;
use common\models\BackgroundTrait;
use common\models\CharacterTrait;
use common\models\ClassSkill;
use common\models\Player;
use common\models\PlayerAbility;
use common\models\PlayerCoin;
use common\models\PlayerSkill;
use common\models\PlayerTrait;
use common\models\Race;
use common\models\Wizard;
use Yii;

class BuilderTool {

    const RETRY = 5;
    const CREATE_TABS = [
        'races' => [
            'name' => 'Choose a Race',
            'anchor' => 'races',
            'wizard' => 'race',
            'model_name' => null,
            'paragraphs' => [
                "This world teem with diverse races. From common humans, elves, dwarves, and halflings, to rarer dragonborn, tieflings, gnomes, and even drow, the variety is vast. This racial diversity enriches D&D societies and adds depth to its settings.",
                "Race significantly impacts your character. It influences abilities, traits, and backstory. Halflings might be sneaky rogues, dwarves tough warriors, and elves potent magic users. Race also offers roleplaying cues for personality, appearance, culture, and alignment, though individuals can deviate from these norms, creating interesting character possibilities.",
            ],
            'field_name' => 'race',
            'admin' => 0,
        ],
        'classes' => [
            'name' => 'Choose a Class',
            'anchor' => 'classes',
            'wizard' => 'class',
            'model_name' => 'CharacterClass',
            'paragraphs' => [
                "Your character receives a number of benefits from your choice of class. Many of these benefits are class features-capabilities (including spellcasting) that set your character apart from members of other classes.",
                "You also gain a number of proficiencies: armor, weapons, skills, saving throws, and sometimes tools. Your proficiencies define many of the things your character can do particularly well, from using certain weapons to telling a convincing lie.",
            ],
            'field_name' => 'class',
            'admin' => 0,
        ],
        'backgrounds' => [
            'name' => 'Choose your background',
            'anchor' => 'backgrounds',
            'wizard' => null,
            'model_name' => 'Background',
            'paragraphs' => [
                "Your character’s background describes where he or she came from, his or her original occupation, and the character’s place in the D&D world. Your DM might offer additional backgrounds beyond the ones included in that chapter, and might be willing to work with you to craft a background that’s a more precise fit for your character concept.",
                "A background gives your character a background feature (a general benefit) and proficiency in two skills, and it might also give you additional languages or proficiency with certain kinds of tools.",
            ],
            'field_name' => 'background',
            'admin' => 0,
        ],
        'histories' => [
            'name' => 'Choose your history',
            'anchor' => 'histories',
            'wizard' => null,
            'model_name' => 'BackgroundHistory',
            'paragraphs' => [
                "Your character’s background describes where he or she came from, his or her original occupation,     and the character’s place in the D&D world. Your DM might offer additional backgrounds beyond     the ones included in that chapter, and might be willing to work with you to craft a background     that’s a more precise fit for your character concept.",
                "A background gives your character a background feature (a general benefit) and proficiency in two skills, and it might also give you additional languages or proficiency with certain kinds of tools.",
            ],
            'field_name' => 'history',
            'admin' => 0,
        ],
        'form' => [
            'name' => 'form',
            'anchor' => 'form',
            'wizard' => null,
            'model_name' => null,
            'paragraphs' => [],
            'field_name' => null,
            'admin' => 1, // Only for admins
        ]
    ];
    const UPDATE_TABS = [
        'description' => [
            'name' => 'Describe your player',
            'anchor' => 'description',
            'wizard' => 'alignment',
            'model_name' => null,
            'paragraphs' => [],
            'field_name' => null,
            'admin' => 0,
            'onclick' => null,
        ],
        'avatar' => [
            'name' => 'Choose your avatar',
            'anchor' => 'avatar',
            'wizard' => null,
            'model_name' => null,
            'paragraphs' => [],
            'field_name' => null,
            'admin' => 0,
            'onclick' => null,
        ],
        'abilities' => [
            'name' => 'Set your Ability Scores',
            'anchor' => 'abilities',
            'wizard' => null,
            'model_name' => null,
            'paragraphs' => [
                "Much of what your character does in the game depends on his or her six abilities: Strength, Dexterity, Constitution, Intelligence, Wisdom, and Charisma.",
                "The Ability Score Summary table provides a quick reference for what qualities are measured by each ability, what races increases which abilities, and what classes consider each ability particularly important."
            ],
            'field_name' => null,
            'admin' => 0,
            'update' => 1, // Only at update time
            'onclick' => null,
        ],
        'skills' => [
            'name' => "Set your traits and skills",
            'anchor' => 'skills',
            'wizard' => null,
            'model_name' => null,
            'paragraphs' => [
                "A skill represents a specific aspect of an ability score, and an individual's proficiency in a skill demonstrates a focus on that aspect."
            ],
            'field_name' => null,
            'admin' => 0,
            'onclick' => null,
        ],
        'equipment' => [
            'name' => "Get your initial equipment",
            'anchor' => 'equipment',
            'wizard' => null,
            'model_name' => null,
            'paragraphs' => [
                "Each class will provide a list of items included in their starting equipment package. This is often a good, balanced set of gear appropriate for that class's role.",
                "For example, a fighter might start with chain mail, a longsword, and a shield, while a wizard might begin with a spellbook and a component pouch.",
            ],
            'field_name' => null,
            'admin' => 0,
            'onclick' => 'loadEquipmentTab',
        ],
        'form' => [
            'name' => 'form',
            'anchor' => 'form',
            'wizard' => null,
            'model_name' => null,
            'paragraphs' => [],
            'field_name' => null,
            'admin' => 1, // Only for admins
            'onclick' => null,
        ]
    ];

    public static function loadRandomNames(int $raceId, string $gender, int $n): array {
        $race = Race::findOne(['id' => $raceId]);
        $ethnicity = $race->raceGroup->ethnicities[0];
        $ethnicityId = $ethnicity->id;

        $firstNames = self::getEthnicNames($ethnicityId, 'FirstName', $gender);
        $lastNames = self::getEthnicNames($ethnicityId, 'LastName');

        $names = [];
        $maxAttempts = self::RETRY * $n;

        for ($i = 0; $i < $n; $i++) {
            $attempts = 0;
            do {
                $name = trim(
                        self::randomize($firstNames) . ' ' .
                        self::randomize($lastNames)
                );
                $attempts++;
            } while (in_array($name, $names) && $attempts < $maxAttempts);

            $names[$i] = in_array($name, $names) ?
                    "The {$ethnicity->name} #{$i}" : $name;
        }

        return $names;
    }

    private static function getEthnicNames(int $ethnicityId, string $nameClassName, string|null $gender = null): array {
        $className = "common\\models\\$nameClassName";
        $query = $className::find()
                ->select('name')
                ->where(['ethnicity_id' => $ethnicityId]);
        if ($gender) {
            $query->andWhere(['gender' => $gender]);
        }
        return $query->asArray()->all();
    }

    /**
     * Randomly selects a name from the provided array of names.
     *
     * @param string[] $names
     * @return string
     */
    private static function randomize($names) {
        return $names ? $names[array_rand($names)]['name'] : null;
    }

    /**
     * Populates an array representing different stages of age based
     * on the provided starting age and lifespan parameters.
     * It returns an array with each element containing a stage label ('lib')
     * and the corresponding age ('age').
     *
     * @param int $raceId The race internal id.
     * @return array An array of age categories with their corresponding ages.
     */
    public static function loadAgeTable(int $raceId): array {
        $race = Race::findOne(['id' => $raceId]);
        if (!$race) {
            return [
                ['lib' => 'unknown', 'age' => 0],
            ];
        }

        $adultAge = $race->adult_age;
        $lifespan = $race->lifespan;
        return [
            'adultAge' => $adultAge,
            'lifespan' => $lifespan,
            'labels' => [
                ['lib' => 'young', 'age' => $adultAge],
                ['lib' => 'adult', 'age' => (int) ($adultAge * 1.5)],
                ['lib' => 'in his prime', 'age' => (int) ($lifespan * 0.75)],
                ['lib' => 'elderly', 'age' => (int) ($lifespan * 0.9)],
                ['lib' => 'old', 'age' => $lifespan],
            ]
        ];
    }

    /**
     * Retrieves the ID of the first question for a given topic.
     *
     * This method finds the wizard associated with the specified topic and
     * then identifies the questions marked as the first question within that
     * wizard. It randomly selects one of these questions and returns its ID.
     *
     * @param string $topic The topic of the wizard.
     * @return int|null The ID of the first question, or null if no first question is found.
     */
    public static function getFirstQuestion(string $topic): int|null {
        // Direct query to get first question IDs using a single database call
        $firstQuestions = Wizard::find()
                ->select('wq.id')
                ->alias('w')
                ->innerJoin(['wq' => 'wizard_question'], 'w.id = wq.wizard_id')
                ->where(['w.topic' => $topic, 'wq.is_first_question' => true])
                ->column();

        // Return null if no questions found
        if (empty($firstQuestions)) {
            return null;
        }

        // Return random question ID using array_rand
        return $firstQuestions[array_rand($firstQuestions)];
    }

    public static function setEquipmentResponse(array $equipments, $choice = null): array {
        $items = [];
        $categories = [];
        foreach ($equipments as $equipment) {
            if ($equipment->item_id) {
                $items[] = "$equipment->item_id|$equipment->quantity";
            }
            if ($equipment->category_id) {
                $categories[] = "$equipment->category_id|$equipment->quantity";
            }
        }
        return [
            'choice' => $choice,
            'items' => implode(',', $items),
            'categories' => implode(',', $categories)
        ];
    }

    private static function getFundingFromBackground(Player $player) {
        $backgroundItems = $player->background->backgroundItems;

        $funding = 0;
        foreach ($backgroundItems as $backgroundItem) {
            $funding += ($backgroundItem->funding ?? 0);
        }
        return $funding;
    }

    /**
     * Initializes coin funding for a player model.
     *
     * This method initializes the coin funding for a player model by creating
     * and saving player coin records for each type of coin (e.g., copper, silver,
     * gold) based on the player's background settings.
     *
     * @return bool Returns true if the coin funding is successfully initialized,
     *                      false otherwise.
     */
    public static function initCoinage(Player $player): bool {
        $coins = ['cp', 'sp', 'ep', 'pp'];

        // Iterate over each coin type
        foreach ($coins as $coin) {
            // Create a new player coin instance
            $playerCoin = new PlayerCoin([
                'player_id' => $player->id,
                'coin' => $coin,
                'quantity' => ($coin == 'gp' ? self::getFundingFromBackground($player) : 0)
            ]);

            // Save the player coin and track success status
            if (!$playerCoin->save()) {
                throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($playerCoin->errors, 0, false)));
            }
        }

        // Return whether coin funding initialization was successful
        return true;
    }

    /**
     * Initialize or update every skill bonus for a player
     *
     * @param Player $player
     * @return bool
     * @throws \Exception
     */
    private static function initSkillBonuses(Player $player): bool {
        $proficiencyBonus = $player->level->proficiency_bonus;
        $abilityModifiers = [];
        foreach ($player->playerAbilities as $playerAbility) {
            $abilityModifiers[$playerAbility->ability_id] = $playerAbility->modifier;
        }

        $playerSkills = $player->playerSkills;

        foreach ($playerSkills as $playerSkill) {
            $abilityId = $playerSkill->skill->ability_id;
            $abilityModifier = $abilityModifiers[$abilityId] ?? 0;
            $playerSkill->bonus = $abilityModifier + ($playerSkill->is_proficient ? $proficiencyBonus : 0);
            if (!$playerSkill->save()) {
                throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($playerSkill->errors, 0, false)));
            }
        }
        return true;
    }

    /**
     * Set the initial list of skills regarding the actual player class and backgroung
     *
     * @param Player $player
     * @param array|null $previousPlayerSkills Optional array of previously entered skills proficiency
     * @return array Array of possible skills with default proficiency
     */
    private static function initSkillList(Player $player, array|null $previousPlayerSkills): array {
        $skillList = [];
        // Using the ID as a table key means you don't have to duplicate records
        // Getting the liste of skills provided by the player class
        $classSkills = ClassSkill::findAll(['class_id' => $player->class_id]);
        foreach ($classSkills as $classSkill) {
            $id = $classSkill->skill_id;
            $skill['skill_id'] = $id;
            $skill['is_proficient'] = $previousPlayerSkills[$id] ?? 0; // not proficient by default
            $skillList[$id] = $skill;
        }

        // Getting the liste of skills provided by the player background
        $backgroudSkills = BackgroundSkill::findAll(['background_id' => $player->background_id]);
        foreach ($backgroudSkills as $backgroudSkill) {
            $id = $backgroudSkill->skill_id;
            $skill['skill_id'] = $id;
            $skill['is_proficient'] = 1; // proficient by default
            $skillList[$id] = $skill;
        }

        return $skillList;
    }

    /**
     * Reset the previously entered skills:
     * 1. Saves skills for which the player is proficient
     * 2. Delete the initial data and starting from scratch
     *
     * @param Player $player
     * @return array Array of previously entered skills proficiency
     */
    private static function resetPlayerSkills(Player $player): array {
        // If skills had not been previously entered, do nothing
        if (!$player->playerSkills) {
            return [];
        }

        // Saves skills for which the player is proficient
        $previousPlayerSkills = [];
        foreach ($player->playerSkills as $playerSkill) {
            $id = $playerSkill->skill_id;
            $previousPlayerSkills[$id] = $playerSkill->is_proficient;
        }
        // And the delete the initial data and starting from scratch
        Yii::debug($previousPlayerSkills);
        PlayerSkill::deleteAll(['player_id' => $player->id]);

        return $previousPlayerSkills;
    }

    /**
     * Initialize tge set of skills for player
     * @param Player $player
     * @return bool
     * @throws \Exception
     */
    public static function initSkills(Player $player): bool {
        // Delete the skills of existing players if the initial parameters have changed.
        $previousPlayerSkills = self::resetPlayerSkills($player);

        $skillList = self::initSkillList($player, $previousPlayerSkills);

        foreach ($skillList as $skill) {
            $playerSkill = new PlayerSkill([
                'player_id' => $player->id,
                'skill_id' => $skill['skill_id'],
                'is_proficient' => $skill['is_proficient'],
                'bonus' => 0
            ]);
            if (!$playerSkill->save()) {
                throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($playerSkill->errors, 0, false)));
            }
        }
        return self::initSkillBonuses($player);
    }

    /**
     * Populates the initial join table player_trait with traits based on
     * background and class.
     *
     * This method initializes the player's traits by populating the initial
     * join table player_trait based on the traits associated with the
     * player's background.
     *
     * @param common\models\Player $player
     * @return bool Whether the initialization of traits was successful.
     */
    public static function initTraits(Player $player): bool {
        // Initialize return value
        $player->playerTraits ? PlayerTrait::deleteAll(['player_id' => $player->id]) : true;

        $traits = CharacterTrait::find()->all();
        $background_id = $player->background->id;
        foreach ($traits as $trait) {
            $score = DiceRoller::roll($trait->dice);
            $backgroundTrait = BackgroundTrait::findOne([
                'background_id' => $background_id,
                'trait_id' => $trait->id,
                'score' => $score,
            ]);

            if ($backgroundTrait) {
                // Create a new player trait instance
                $playerTrait = new PlayerTrait([
                    'player_id' => $player->id,
                    'trait_id' => $trait->id,
                    'description' => $backgroundTrait->description
                ]);

                // Save the player trait and update return value
                if (!$playerTrait->save()) {
                    throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($playerTrait->errors, 0, false)));
                }
            }
        }

        return true;
    }

    /**
     * Set ability default value regarding both race and class
     *
     * @param common\models\Player $player
     * @param array $initAbilityArray
     * @return array
     */
    private static function addDefaultAbilityScore(Player $player, array &$initAbilityArray): array {

        $defaultAbilities = AbilityDefault::findAll([
            'race_group_id' => $player->race->race_group_id,
            'class_id' => $player->class->id
        ]);

        foreach ($defaultAbilities as $defaultAbility) {
            $id = $defaultAbility->ability_id;
            $score = $defaultAbility->score ?? 10;
            $initAbilityArray[$id]['score'] = $score;
            $initAbilityArray[$id]['modifier'] = PlayerTool::calcAbilityModifier($score);
        }

        return $initAbilityArray;
    }

    /**
     * Adds any bonuses provided by the player's race
     *
     * @param common\models\Player $player
     * @param array $initAbilityArray
     * @return void
     */
    private static function addRaceAbilities(Player $player, array &$initAbilityArray): void {
        // Retrieve abilities associated with the player's race
        $raceAbilities = $player->race->raceAbilities;

        foreach ($raceAbilities as $raceAbility) {
            $id = $raceAbility->ability_id;
            $initAbilityArray[$id]['bonus'] = $raceAbility->bonus ?? 0;
        }
    }

    /**
     * Determines the saving throws defined by the player's class
     *
     * @param array $initAbilityArray
     * @return void
     */
    private static function addClassSavingThrow(Player $player, array &$initAbilityArray): void {
        // Retrieve abilities associated with the player's class
        $classAbilities = $player->class->classAbilities;

        foreach ($classAbilities as $classAbility) {
            $id = $classAbility->ability_id;
            $initAbilityArray[$id]['is_primary_ability'] = $classAbility->is_primary_ability ?? 0;
            $initAbilityArray[$id]['is_saving_throw'] = $classAbility->is_saving_throw ?? 0;
        }
    }

    /**
     * Initialize the player ability to an array of default values
     *
     * @return array
     */
    private static function initAbilityArray(): array {

        $abilities = Ability::find()->all();

        $initAbilityArray = [];

        foreach ($abilities as $abilitiy) {
            $id = $abilitiy->id;
            $initAbility = [
                'ability_id' => $id,
                'score' => 10,
                'bonus' => 0,
                'modifier' => 0,
                'is_primary_ability' => 0, // false
                'is_saving_throw' => 0 // false
            ];

            $initAbilityArray[$id] = $initAbility;
        }

        return $initAbilityArray;
    }

    /**
     * Populates the initial join table player_ability with abilities based on
     * race and class.
     *
     * This method initializes the player's abilities by populating the initial
     * join table player_ability based on the abilities associated with the
     * player's race and class.
     *
     * @param common\models\Player $player
     * @return bool Whether the initialization of abilities was successful.
     */
    public static function initAbilities($player): bool {

        $initAbilityArray = self::initAbilityArray();
        self::addDefaultAbilityScore($player, $initAbilityArray);
        self::addRaceAbilities($player, $initAbilityArray);
        self::addClassSavingThrow($player, $initAbilityArray);

        foreach ($initAbilityArray as $ability) {
            $playerAbility = new PlayerAbility([
                'player_id' => $player->id,
                'ability_id' => $ability['ability_id'],
                'score' => $ability['score'],
                'bonus' => $ability['bonus'],
                'modifier' => $ability['modifier'],
                'is_primary_ability' => $ability['is_primary_ability'],
                'is_saving_throw' => $ability['is_saving_throw'],
            ]);

            if (!$playerAbility->save()) {
                throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($playerAbility->errors, 0, false)));
            }
        }

        return self::initSkillBonuses($player);
    }
}
