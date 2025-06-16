<?php

namespace frontend\modules\playerBuilder\application;

interface PlayerRepositoryInterface
{
    public function findById(int $id): ?\common\models\Player;
    public function savePlayerAbilities(\common\models\Player $player, array $abilitiesData): bool;
}
