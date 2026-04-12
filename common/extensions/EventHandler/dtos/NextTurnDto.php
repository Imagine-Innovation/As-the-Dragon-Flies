<?php

namespace common\extensions\EventHandler\dtos;

class NextTurnDto extends BaseDto
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        parent::__construct('next-turn', $data);
    }
}
