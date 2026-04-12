<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

abstract class BaseDto implements BroadcastMessageInterface
{
    protected string $type;
    /** @var array<string, mixed> */
    protected array $data;

    /**
     * @param string $type
     * @param array<string, mixed> $data
     */
    public function __construct(string $type, array $data)
    {
        $this->type = $type;
        $this->data = $data;

        if (!isset($this->data['timestamp'])) {
            $this->data['timestamp'] = time();
        }

        if (isset($this->data['detail']) && is_array($this->data['detail'])) {
            if (!isset($this->data['detail']['timestamp'])) {
                $this->data['detail']['timestamp'] = $this->data['timestamp'];
            }
        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->data;
    }

    /**
     * @return string|false
     */
    public function toJson(): string|false
    {
        return json_encode(array_merge(['type' => $this->type], $this->data));
    }
}
