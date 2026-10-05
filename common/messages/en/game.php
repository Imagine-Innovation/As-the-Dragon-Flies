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
    'action result' => '{status, select,
        SUCCESS {{playerName} successfully performed “{actionName}”}
        PARTIAL {{playerName} partially succeeded in “{actionName}”}
        FAILURE {{playerName} failed to perform “{actionName}”}
        ITEM_MISSING {{playerName} is missing an item to perform the action “{actionName}”}
        other {It is unknown whether {playerName} succeeded in “{actionName}”}
    }',
    'simple action result' => '{status, select,
        SUCCESS {The action was successful}
        PARTIAL {The action was partially successful}
        FAILURE {The action failed}
        ITEM_MISSING {An object is missing to perform the action}
        other {I don’t know whether the action was successful or not}
    }',
    'gained item' => 'You now have a {itemName} in your back bag',
    'Player\'s equipment' => 'Player’s equipment',
    'Equipment' => 'Equipment',
    'Back to lobby' => 'Back to lobby',
    'What do you want to do?' => 'What do you want to do?',
    'Next step' => 'Next step',
    'Back to the mission' => 'Back to the mission',
    'Try another action' => 'Try another action',
    'Finish your turn' => 'Finish your turn',
    'Health' => 'Health',
    'Abilities' => 'Abilities',
    'Armor Class {ac}' => 'Armor Class {ac}',
    'unknown class' => 'unknown class',
    'unknown race' => 'unknown race',
    'List of available equipment' => 'List of available equipment',
    'Click on the white areas to see the list of items your player can pick up.' => 'Click on the white areas to see the list of items your player can pick up.',
    'You have nothing at all' => 'You have nothing at all',
    'Partners' => 'Partners',
    'You are alone in the quest' => 'You are alone in the quest',
    'Equip' => 'Equip',
    'To use this weapon, you need both hands.' => 'To use this weapon, you need both hands.',
    'You only need one hand to use this weapon' => 'You only need one hand to use this weapon',
];
