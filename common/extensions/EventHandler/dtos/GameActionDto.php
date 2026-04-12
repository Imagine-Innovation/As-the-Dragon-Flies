<?php

namespace common\extensions\EventHandler\dtos;

class GameActionDto extends BaseDto
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        parent::__construct('game-action', $data);
    }
}
