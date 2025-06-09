<?php

namespace common\extensions\EventHandler\contracts;

interface BroadcastMessageInterface {
    public function getType(): string;
    public function getPayload(): array;
    public function toJson(): string;
}
