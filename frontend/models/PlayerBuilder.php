<?php

namespace frontend\models;

use common\models\BackgroundTrait;
use common\models\CharacterTrait;
use common\models\Player;
use common\models\PlayerAbility;
use common\models\PlayerCoin;
use common\models\PlayerTrait;
use common\components\DiceRoller;
use frontend\components\BuilderTool;
use Yii;

/**
 * Description of PlayerBuilder
 *
 * @author franc
 */
class PlayerBuilder extends Player {

    /**
     * Populates the initial join table player_ability with abilities based on
     * race and class.
     *
     * This method initializes the player's abilities by populating the initial
     * join table player_ability based on the abilities associated with the
     * player's race and class.
     *
     * @param common\models\Player $this
     * @return bool Whether the initialization of abilities was successful.
     */
    protected function initAbilities() {
        // Retrieve abilities associated with the player's race
        $raceAbilities = $this->race->raceAbilities;

        // Retrieve abilities associated with the player's class
        $classAbilities = $this->class->classAbilities;

        $success = true;

        foreach ($raceAbilities as $raceAbility) {
            $playerAbility = new PlayerAbility([
                'player_id' => $this->id,
                'ability_id' => $raceAbility->ability_id,
                'score' => 0,
                'bonus' => $raceAbility->bonus,
            ]);

            // Check if the ability is also associated with the player's class
            foreach ($classAbilities as $classAbility) {
                if ($classAbility->ability_id == $playerAbility->ability_id) {
                    // Set primary ability and saving throw flags
                    $playerAbility->is_primary_ability = $classAbility->is_primary_ability;
                    $playerAbility->is_saving_throw = $classAbility->is_saving_throw;
                    break;
                }
            }

            $success = $success && $playerAbility->save();
        }
        return $success;
    }

    /**
     * Initializes coin funding for a player model.
     *
     * This method initializes the coin funding for a player model by creating
     * and saving player coin records for each type of coin (e.g., copper, silver,
     * gold) based on the player's class settings.
     *
     * @return bool Returns true if the coin funding is successfully initialized,
     *                      false otherwise.
     */
    protected function initCoinage() {
        // Retrieve the player's class
        $class = $this->class;

        // Array of coin types
        $coins = ['cp', 'sp', 'ep', 'gp', 'pp'];

        // Initialize success flag
        $success = true;

        // Iterate over each coin type
        foreach ($coins as $coin) {
            // Create a new player coin instance
            $playerCoin = new PlayerCoin([
                'player_id' => $this->id,
                'coin' => $coin
            ]);

            // Calculate initial funding based on class settings
            if ($coin == $class->initial_funding_coin) {
                // Roll dice and calculate initial funding
                $result = DiceRoller::roll($class->initial_funding_dice) * $class->initial_funding_multiplier;
            } else {
                // Non-primary coins start with 0 quantity
                $result = 0;
            }

            // Set quantity
            $playerCoin->quantity = $result;

            // Save the player coin and track success status
            $success = $success && $playerCoin->save();
        }

        // Return whether coin funding initialization was successful
        return $success;
    }

    /**
     * Populates the initial join table player_trait with traits based on
     * background and class.
     *
     * This method initializes the player's traits by populating the initial
     * join table player_trait based on the traits associated with the
     * player's background.
     *
     * @param common\models\Player $this
     * @return bool Whether the initialization of traits was successful.
     */
    public function initTraits() {
        // Initialize return value
        $success = $this->playerTraits ? PlayerTrait::deleteAll(['player_id' => $this->id]) : true;

        $traits = CharacterTrait::find()->all();
        $background_id = $this->background->id;
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
                    'player_id' => $this->id,
                    'trait_id' => $trait->id,
                    'description' => $backgroundTrait->description
                ]);

                // Save the player trait and update return value
                $success = $success && $playerTrait->save();
            }
        }

        // Return whether all player traits were successfully initialized
        return $success;
    }

    /**
     * Saves the advanced properties of the Player model.
     *
     * This method saves the player model itself and ensures that associated
     * player coins and abilities are properly initialized and saved.
     * If the player is already saved, it ensures that player coins and abilities
     * are initialized and saved.
     * If player coins or abilities are not already initialized, this method
     * initializes and saves them accordingly.
     *
     * @param common\models\Player $this
     * @return bool Returns true if all player's advanced properties are successfully saved, false otherwise.
     */
    public function save($runValidation = true, $attributeNames = null) {
        $ts = time();

        if (!$this->id) {
            $this->created_at = $ts;
        }
        $this->updated_at = $ts;

        $success = parent::save($runValidation, $attributeNames);

        if (!$this->playerTraits) {
            $success = $success && $this->initTraits();
        }

        if (!$this->playerCoins) {
            $success = $success && $this->initCoinage();
        }

        if (!$this->playerAbilities) {
            $success = $success && $this->initAbilities();
        } else {
            foreach ($this->playerAbilities as $playerAbility) {
                $success = $success && $playerAbility->save();
            }
        }

        return $success;
    }

    /**
     *
     * @param common\models\Player $this
     * @return array
     */
    public function loadInitialEndowment() {
        $endowments = $this->class->classEndowments;
        $endowmentTable = [];
        foreach ($endowments as $endowment) {
            $endowmentTable[$endowment->choice][$endowment->sort_order] = [
                'id' => $endowment->id,
                'name' => $endowment->name,
            ];
        }
        return $endowmentTable;
    }
}
