<?php

namespace common\extensions\EventHandler\dtos;

class QuestStartedDto extends BaseDto
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        if (!isset($data['redirectUrl']) && isset($data['questId'])) {
            $data['redirectUrl'] = '/frontend/web/index.php?r=game/view&id=' . (is_scalar($data['questId']) ? (string) $data['questId'] : '');
        }
        if (!isset($data['message']) && isset($data['questName'])) {
            $data['message'] = "Quest '" . (is_scalar($data['questName']) ? (string) $data['questName'] : '') . "' has started!";
        }
        if (!isset($data['startedAt'])) {
            $data['startedAt'] = date('Y-m-d H:i:s', time());
        }
        parent::__construct('quest-started', $data);
    }
}
