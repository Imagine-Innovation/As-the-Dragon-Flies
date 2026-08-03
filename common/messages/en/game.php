<?php
return [
    'player succeeded action' => '{playerName} successfully completed the “{actionName}” action',
    'player failed action' => '{playerName} partially completed the “{actionName}” action',
    'player partially succeeded action' => '{playerName} failed to complete the “{actionName}” action',
    'missing item for action by player' => '{playerName} tried “{actionName}” but was missing a required item',
    'player action' => '{playerName} completed the “{actionName}” action with an unknown status',
    'rolling dice result' => 'Rolling {diceToRoll} gave {diceRoll}',
    'the action partialy succeeded' => 'the action partialy succeeded',
    'the action succeeded' => 'the action succeeded',
    'the action failed' => 'the action failed',
    'the action did something' => 'something happened, but I don\'t know what',
    'loosing hp' => '{hpLoss, plural,
        =0 {You haven\'t lost any Hit Points}
        one {You lost one hit point}
        other {You lost # hit points}
    }',
    'gained gp' => '{gp, plural,
        =0 {You haven\'t gain any gold pieces}
        one {You gained one gold piece}
        other {You gained # gold pieces!}
    }',
    'gained xp' => '{xp, plural,
        =0 {You haven\'t gain any experience point}
        one {You gained one experience point}
        other {You gained # experience points!}
    }',
    'Something happened' => 'Something happened, that\'s for sure, but I don\'t really know what',
    'action_status' => '{status, select,
        SUCCESS {{playerName} successfully performed “{actionName}”}
        PARTIAL {{playerName} partially succeeded in “{actionName}”}
        FAILURE {{playerName} failed to perform “{actionName}”}
        unknown {It is unknown whether {playerName} succeeded in “{actionName}”}
        ITEM_MISSING {{playerName} is missing an item to perform the action “{actionName}”}
        other {It is unknown whether {playerName} succeeded in “{actionName}”}
    }',
    'gained item' => 'You now have a {itemName} in your back bag',
];
