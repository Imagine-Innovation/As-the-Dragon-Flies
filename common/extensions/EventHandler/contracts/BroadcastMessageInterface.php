<?php

namespace common\extensions\EventHandler\contracts;

interface BroadcastMessageInterface
{

    /**
     *
     * @return string
     */
    public function getType(): string;

    /**
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array;

    /**
     *
     * @return string
     */
    public function toJson(): string;
}
