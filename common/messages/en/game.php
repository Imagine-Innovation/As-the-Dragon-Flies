<?php
/**
 * Special character for english:
 *  I'm become I’m
 *  "London" become “London”
 */
return [
    'rolling dice result' => 'Rolling {diceToRoll} gave {diceRoll}',
    'loosing hp' => '{hpLoss, plural,
        =0 {You haven’t lost any Hit Points}
        one {You lost one hit point}
        other {You lost # hit points}
    }',
    'gained gp' => '{gp, plural,
        =0 {You haven’t gain any gold pieces}
        one {You gained one gold piece}
        other {You gained # gold pieces!}
    }',
    'gained xp' => '{xp, plural,
        =0 {You haven’t gain any experience point}
        one {You gained one experience point}
        other {You gained # experience points!}
    }',
    'Something happened' => 'Something happened, that’s for sure, but I don’t really know what',
    'action status' => '{status, select,
        SUCCESS {{playerName} successfully performed “{actionName}”}
        PARTIAL {{playerName} partially succeeded in “{actionName}”}
        FAILURE {{playerName} failed to perform “{actionName}”}
        ITEM_MISSING {{playerName} is missing an item to perform the action “{actionName}”}
        other {It is unknown whether {playerName} succeeded in “{actionName}”}
    }',
    'simple action status' => '{status, select,
        SUCCESS {The action was successful}
        PARTIAL {The action was partially successful}
        FAILURE {The action failed}
        ITEM_MISSING {An object is missing to perform the action}
        other {I don’t know whether the action was successful or not}
    }',
    'gained item' => 'You now have a {itemName} in your back bag',
];
