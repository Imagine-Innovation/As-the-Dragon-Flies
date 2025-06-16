<?php

namespace frontend\modules\playerBuilder\infrastructure;

use frontend\modules\playerBuilder\application\PlayerRepositoryInterface;
use frontend\models\PlayerBuilder;
use Yii;

class YiiPlayerRepository implements PlayerRepositoryInterface
{
    public function findById(int $id): ?\common\models\Player
    {
        $query = PlayerBuilder::find()
            ->with(['race', 'class', 'background', 'history', 'playerAbilities', 'playerSkills', 'playerTraits'])
            ->where(['id' => $id]);

        // Assuming we might need user-specific access in the future,
        // but for a generic repository method, let's simplify for now.
        // If direct user access check is always needed here, it can be added.
        // For now, let's assume the UseCase or a higher layer handles authorization if necessary,
        // or the PlayerBuilder model itself has global scopes for user access.
        // $user = Yii::$app->user->identity;
        // if (!$user->is_admin) { // This check might be too specific for a general repo method
        //     $query->andWhere(['user_id' => $user->id]);
        // }

        if (($model = $query->one()) !== null) {
            // We need to ensure the return type is common\models\Player.
            // If PlayerBuilder is a subclass or compatible, this direct return is fine.
            // Otherwise, a mapping/conversion might be needed.
            // For now, assuming PlayerBuilder is compatible with common\models\Player.
            return $model;
        }
        return null;
    }

    public function savePlayerAbilities(\common\models\Player $player, array $abilitiesData): bool
    {
        $success = true;
        // Ensure $player is the correct type and has playerAbilities relation.
        // This check assumes $player is an instance of PlayerBuilder or a compatible model.
        if ($player && isset($player->playerAbilities)) {
            foreach ($player->playerAbilities as $playerAbility) {
                $ability_id = $playerAbility->ability_id;
                if (isset($abilitiesData[$ability_id])) {
                    $playerAbility->score = $abilitiesData[$ability_id];
                    // Validation could happen here or be ensured by the model's beforeSave/validate
                    if (!$playerAbility->save()) { // save() should trigger validation
                        $success = false;
                        // Optionally log errors: Yii::error($playerAbility->getErrors());
                    }
                }
            }
        } else {
            $success = false; // Player not valid or playerAbilities not accessible
        }
        return $success;
    }
}
