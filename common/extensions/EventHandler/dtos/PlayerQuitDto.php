<?php

namespace common\extensions\EventHandler\dtos;

class PlayerQuitDto extends BaseDto
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        parent::__construct('player-quit', $data);
    }
}
