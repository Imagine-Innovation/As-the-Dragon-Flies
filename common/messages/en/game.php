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
        =0 {You haven’t gained any gold pieces}
        one {You gained one gold piece}
        other {You gained # gold pieces!}
    }',
    'gained xp' => '{xp, plural,
        =0 {You haven’t gained any experience point}
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
    // Tavern & Quest Lobby
    'Quests' => 'Quests',
    'Welcome {playerName} in {questName} Quest' => 'Welcome {playerName} in {questName} Quest',
    'This quest allows {companySize} {requiredLevels} to take part in the game.' => 'This quest allows {companySize} {requiredLevels} to take part in the game.',
    'The adventuring companionship that is building up' => 'The adventuring companionship that is building up',
    '{age}-year-old {gender} {race}' => '{age}-year-old {gender} {race}',
    'male' => 'male',
    'female' => 'female',
    'Start the quest' => 'Start the quest',
    'Leave Tavern' => 'Leave Tavern',
    'Quest Chat' => 'Quest Chat',
    'Type your message...' => 'Type your message...',
    'Send' => 'Send',
    'Press Enter to send • Be respectful to fellow adventurers' => 'Press Enter to send • Be respectful to fellow adventurers',
    'No message yet' => 'No message yet',
    'Join the quest' => 'Join the quest',
    '{count} partners waiting' => '{count} partners waiting',
    'Expected character classes:' => 'Expected character classes:',
    'and' => 'and',
    '{n, plural, one {Your player {names} is already waiting to start the quest} other {Your players {names} are already waiting to start the quest}}' => '{n, plural, one {Your player {names} is already waiting to start the quest} other {Your players {names} are already waiting to start the quest}}',
    // TavernManager messages
    'There’s nobody here!' => 'There’s nobody here!',
    'For the moment, it looks like you’re the first one' => 'For the moment, it looks like you’re the first one',
    'Look, there are two of you now' => 'Look, there are two of you now',
    'Ah! but there are three of you! Wait, I’ll get a chair' => 'Ah! but there are three of you! Wait, I’ll get a chair',
    'Now that there are four of you, I’m going to put you on a bigger table' => 'Now that there are four of you, I’m going to put you on a bigger table',
    'With five guys like you, it’s going to be quite a team!' => 'With five guys like you, it’s going to be quite a team!',
    'Boy, that’s quite a team!' => 'Boy, that’s quite a team!',
    'We’re still waiting for {missingCount} other members to join us before starting' => 'We’re still waiting for {missingCount} other members to join us before starting',
    'One more member to join and we can start' => 'One more member to join and we can start',
    'The whole company is there, we can start!' => 'The whole company is there, we can start!',
    'Every expected class is represented in the company!' => 'Every expected class is represented in the company!',
    'We still need a {className} to meet all the conditions.' => 'We still need a {className} to meet all the conditions.',
    'We still need a {className1} and a {className2} to meet all the conditions.' => 'We still need a {className1} and a {className2} to meet all the conditions.',
    'We still need a {classesExceptLast} and a {lastClass} to meet all the conditions.' => 'We still need a {classesExceptLast} and a {lastClass} to meet all the conditions.',
    'You are not the quest initiator' => 'You are not the quest initiator',
    'Quest {questName} is not in waiting state.' => 'Quest {questName} is not in waiting state.',
    'Quest can start once {minPlayers} joined. Current count is {currentCount}' => 'Quest can start once {minPlayers} joined. Current count is {currentCount}',
    'Missing required player classes' => 'Missing required player classes',
    'Quest can start' => 'Quest can start',
    // Story levels & company size
    'Undefined' => 'Undefined',
    'Beginner only' => 'Beginner only',
    'Level {min} only' => 'Level {min} only',
    'Level {min} or {max}' => 'Level {min} or {max}',
    'From level {min} to level {max}' => 'From level {min} to level {max}',
    'Single player' => 'Single player',
    '{min} players' => '{min} players',
    '{min} or {max} players' => '{min} or {max} players',
    '{min} to {max} players' => '{min} to {max} players',
];
