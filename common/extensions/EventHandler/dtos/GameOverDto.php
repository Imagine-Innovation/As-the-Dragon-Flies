<?php

namespace common\extensions\EventHandler\dtos;

class GameOverDto extends BaseDto
{
    public const TYPE = 'game-over';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        parent::__construct(self::TYPE, $data);
    }
}
