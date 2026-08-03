<?php
return [
    'player succeeded action' => '{playerName} a réussi l\'action {actionName}',
    'player failed action' => '{playerName} a échoué à réaliser l\'action {actionName}',
    'player partially succeeded action' => '{playerName} a partiellement réussi l\'action {actionName}',
    'missing item for action by player' => 'Il manquait un objet à {playerName} pour qu\'il réaliser l\'action {actionName}',
    'player action' => '{playerName} a tenté l\'action {actionName}',
    'rolling dice result' => 'Le lancer de {diceToRoll} a donné {diceRoll}',
    'the action partialy succeeded' => 'l\'action a partiellement réussi',
    'the action succeeded' => 'l\'action a réussi',
    'the action failed' => 'l\'action a échoué',
    'the action did something' => 'il s\'est passé quelque chose, mais je ne sais pas quoi',
    'loosing hp' => '{hpLoss, plural,
        =0 {Tu n’as perdu aucun point de vie}
        one {Tu as perdu un point de vie}
        other {Tu as perdu # points de vie}
    }',
    'gained gp' => '{gp, plural,
        =0 {Tu n’as gagné aucune pièce d’or}
        one {Tu as gagné une pièce d’or}
        other {Tu as gagné # pièces d’or !}
    }',
    'gained xp' => '{xp, plural,
        =0 {Tu n’as gagné aucun point d’expérience}
        one {Tu as gagné un point d’expérience}
        other {Tu as gagné # points d’expérience}
    } !',
    'Something happened' => 'Il s\'est passé quelque chose, ça c\'est certain, mais je ne sais pas vraiment quoi',
    'action_status' => '{status, select,
        SUCCESS {{playerName} a réussi l’action «&nbsp;{actionName}&nbsp;»}
        PARTIAL {{playerName} a partiellement réussi l’action «&nbsp;{actionName}&nbsp;»}
        FAILURE {{playerName} a échoué dans l’action «&nbsp;{actionName}&nbsp;»}
        ITEM_MISSING {Il manque un objet à {playerName} pour réaliser l’action «&nbsp;{actionName}&nbsp;»}
        other {Je ne sais pas si {playerName} a réussi l’action « {actionName} »}
    }',
    'gained item' => 'Tu as maintenant un(e) {itemName} dans ton sac à dos',
];
