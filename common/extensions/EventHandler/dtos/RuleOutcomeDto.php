<?php

namespace common\extensions\EventHandler\dtos;

class RuleOutcomeDto {
    public string $type;
    public string $status;
    public array $data;
    public ?string $messageKey;
    public int $timestamp;
    public string $broadcastScope;
    public ?string $broadcastTargetId;

    public function __construct(
        string $type,
        array $data = [],
        string $status = 'success',
        ?string $messageKey = null,
        string $broadcastScope = 'quest',
        ?string $broadcastTargetId = null,
        ?int $timestamp = null
    ) {
        $this->type = $type;
        $this->data = $data;
        $this->status = $status;
        $this->messageKey = $messageKey;
        $this->broadcastScope = $broadcastScope;
        $this->broadcastTargetId = $broadcastTargetId;
        $this->timestamp = $timestamp ?? time();
    }
}
