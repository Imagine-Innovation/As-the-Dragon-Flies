<?php

namespace common\extensions\EventHandler\dtos;

class PlayerJoinedDto extends BaseDto
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        parent::__construct('player-joined', $data);
    }
}
