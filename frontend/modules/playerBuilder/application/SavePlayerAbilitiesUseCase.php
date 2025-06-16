<?php

namespace frontend\modules\playerBuilder\application;

use common\models\Player;

class SavePlayerAbilitiesUseCase
{
    private PlayerRepositoryInterface $playerRepository;

    public function __construct(PlayerRepositoryInterface $playerRepository)
    {
        $this->playerRepository = $playerRepository;
    }

    public function execute(int $playerId, array $abilitiesData): bool
    {
        $player = $this->playerRepository->findById($playerId);

        if ($player) {
            return $this->playerRepository->savePlayerAbilities($player, $abilitiesData);
        }

        return false;
    }
}
