<?php

namespace frontend\components;

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

    public static function loadRandomNames($raceId, $gender, $n) {
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

    private static function getEthnicNames($ethnicityId, $nameClassName, $gender = null) {
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
     * @param int $age The current age.
     * @param int $lifespan The life expectancy.
     *
     * @return array An array of age categories with their corresponding ages.
     */
    public static function loadAgeTable($raceId) {
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
    public static function getFirstQuestion($topic) {
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
}
