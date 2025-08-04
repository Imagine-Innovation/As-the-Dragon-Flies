<?php

namespace frontend\models;

use common\models\Player;
use frontend\components\BuilderTool;
use Yii;

/**
 * Description of PlayerBuilder
 *
 * @author franc
 */
class PlayerBuilder extends Player {

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
     * @param bool $runValidation
     * @param mixed $attributeNames
     * @return bool Returns true if all player's advanced properties are successfully saved, false otherwise.
     */
    public function save($runValidation = true, $attributeNames = null): bool {
        $ts = time();

        if (!$this->id) {
            $this->created_at = $ts;
        }
        $this->updated_at = $ts;

        $success = parent::save($runValidation, $attributeNames);

        if (!$this->playerTraits) {
            BuilderTool::initTraits($this);
        }

        if (!$this->playerCoins) {
            BuilderTool::initCoinage($this);
        }

        if (!$this->playerAbilities) {
            BuilderTool::initAbilities($this);
        }

        if (!$this->playerSkills) {
            BuilderTool::initSkills($this);
        }

        return $success;
    }

    /**
     *
     * @param common\models\Player $this
     * @return array
     */
    public function getInitialEndowment(): array {
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
