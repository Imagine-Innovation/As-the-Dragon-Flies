<?php

namespace common\extensions\EventHandler\dtos;

class GameOverDto extends BaseDto
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        parent::__construct('game-over', $data);
    }
}
