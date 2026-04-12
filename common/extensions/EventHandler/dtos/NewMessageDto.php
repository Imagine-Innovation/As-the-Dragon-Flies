<?php

namespace common\extensions\EventHandler\dtos;

class NewMessageDto extends BaseDto
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        parent::__construct('new-message', $data);
    }
}
