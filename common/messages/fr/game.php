<?php
/**
 * Special character for french:
 *  C'est become C’est
 *  "Paris" become « Paris »
 */
return [
    'rolling dice result' => 'Le lancer de {diceToRoll} a donné {diceRoll}',
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
    'Something happened' => 'Il s’est passé quelque chose, ça c’est certain, mais je ne sais pas vraiment quoi',
    'action status' => '{status, select,
        SUCCESS {{playerName} a réussi l’action « {actionName} »}
        PARTIAL {{playerName} a partiellement réussi l’action « {actionName} »}
        FAILURE {{playerName} a échoué dans l’action « {actionName} »}
        ITEM_MISSING {Il manque un objet à {playerName} pour réaliser l’action « {actionName} »}
        other {Je ne sais pas si {playerName} a réussi l’action « {actionName} »}
    }',
    'simple action status' => '{status, select,
        SUCCESS {L’action a réussi}
        PARTIAL {L’action a partiellement réussi}
        FAILURE {L’action a échoué}
        ITEM_MISSING {Il manque un objet pour réaliser l’action}
        other {Je ne sais pas si l’action a réussi ou non}}
    }',
    'gained item' => 'Tu as maintenant un(e) {itemName} dans ton sac à dos',
];
