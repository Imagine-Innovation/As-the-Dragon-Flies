<?php

namespace common\extensions\EventHandler\dtos;

class NextMissionDto extends BaseDto
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        parent::__construct('next-mission', $data);
    }
}
