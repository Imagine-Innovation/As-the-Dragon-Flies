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
    'action result' => '{status, select,
        SUCCESS {{playerName} a réussi l’action « {actionName} »}
        PARTIAL {{playerName} a partiellement réussi l’action « {actionName} »}
        FAILURE {{playerName} a échoué dans l’action « {actionName} »}
        ITEM_MISSING {Il manque un objet à {playerName} pour réaliser l’action « {actionName} »}
        other {Je ne sais pas si {playerName} a réussi l’action « {actionName} »}
    }',
    'simple action result' => '{status, select,
        SUCCESS {L’action a réussi}
        PARTIAL {L’action a partiellement réussi}
        FAILURE {L’action a échoué}
        ITEM_MISSING {Il te manque un objet pour réaliser l’action}
        other {Je ne sais pas si l’action a réussi ou non}
    }',
    'gained item' => 'Tu as maintenant un(e) {itemName} dans ton sac à dos',
    'Player\'s equipment' => 'Équipement du joueur',
    'Equipment' => 'Équipement',
    'Back to lobby' => 'Retour au lobby',
    'What do you want to do?' => 'Que veux-tu faire ?',
    'Next step' => 'Étape suivante',
    'Back to the mission' => 'Retour à la mission',
    'Try another action' => 'Essayer une autre action',
    'Finish your turn' => 'Terminer ton tour',
    'Health' => 'Santé',
    'Abilities' => 'Caractéristiques',
    'Armor Class {ac}' => 'Classe d’armure {ac}',
    'unknown class' => 'classe inconnue',
    'unknown race' => 'race inconnue',
    'List of available equipment' => 'Liste des équipements disponibles',
    'Click on the white areas to see the list of items your player can pick up.' => 'Clique sur les zones blanches pour voir la liste des objets que ton joueur peut ramasser.',
    'You have nothing at all' => 'Tu n’as rien du tout',
    'Partners' => 'Partenaires',
    'You are alone in the quest' => 'Tu es seul dans la quête',
    'Equip' => 'Équiper',
    'To use this weapon, you need both hands.' => 'Pour utiliser cette arme, tu as besoin de tes deux mains.',
    'You only need one hand to use this weapon' => 'Tu n’as besoin que d’une main pour utiliser cette arme',
    // Tavern & Quest Lobby
    'Quests' => 'Quêtes',
    'Welcome {playerName} in {questName} Quest' => 'Bienvenue {playerName} dans la quête {questName}',
    'This quest allows {companySize} {requiredLevels} to take part in the game.' => 'Cette quête permet à {companySize} {requiredLevels} de participer au jeu.',
    'The adventuring companionship that is building up' => 'La compagnie d’aventuriers qui se constitue',
    '{age}-year-old {gender} {race}' => '{race} {gender} de {age} ans',
    'male' => 'homme',
    'female' => 'femme',
    'Start the quest' => 'Lancer la quête',
    'Leave Tavern' => 'Quitter la taverne',
    'Quest Chat' => 'Tchat de la quête',
    'Type your message...' => 'Écris ton message...',
    'Send' => 'Envoyer',
    'Press Enter to send • Be respectful to fellow adventurers' => 'Appuie sur Entrée pour envoyer • Sois respectueux envers tes compagnons d’aventure',
    'No message yet' => 'Aucun message pour le moment',
    'Join the quest' => 'Rejoindre la quête',
    '{count} partners waiting' => '{count} partenaires en attente',
    'Expected character classes:' => 'Classes de personnages attendues :',
    'and' => 'et',
    '{n, plural, one {Your player {names} is already waiting to start the quest} other {Your players {names} are already waiting to start the quest}}' => '{n, plural, one {Ton joueur {names} attend déjà pour démarrer la quête} other {Tes joueurs {names} attendent déjà pour démarrer la quête}}',
    // TavernManager messages
    'There’s nobody here!' => 'Il n’y a personne ici !',
    'For the moment, it looks like you’re the first one' => 'Pour le moment, il semble que tu sois le premier',
    'Look, there are two of you now' => 'Regarde, vous êtes deux maintenant',
    'Ah! but there are three of you! Wait, I’ll get a chair' => 'Ah ! mais vous êtes trois ! Attends, je vais chercher une chaise',
    'Now that there are four of you, I’m going to put you on a bigger table' => 'Maintenant que vous êtes quatre, je vais vous installer à une plus grande table',
    'With five guys like you, it’s going to be quite a team!' => 'Avec cinq gaillards comme vous, ça va faire une sacrée équipe !',
    'Boy, that’s quite a team!' => 'Dites donc, c’est une sacrée équipe !',
    'We’re still waiting for {missingCount} other members to join us before starting' => 'Nous attendons encore {missingCount} autres membres avant de commencer',
    'One more member to join and we can start' => 'Encore un membre et nous pourrons commencer',
    'The whole company is there, we can start!' => 'Toute la compagnie est là, nous pouvons commencer !',
    'Every expected class is represented in the company!' => 'Toutes les classes attendues sont représentées dans la compagnie !',
    'We still need a {className} to meet all the conditions.' => 'Il nous manque encore un(e) {className} pour remplir toutes les conditions.',
    'We still need a {className1} and a {className2} to meet all the conditions.' => 'Il nous manque encore un(e) {className1} et un(e) {className2} pour remplir toutes les conditions.',
    'We still need a {classesExceptLast} and a {lastClass} to meet all the conditions.' => 'Il nous manque encore un(e) {classesExceptLast} et un(e) {lastClass} pour remplir toutes les conditions.',
    'You are not the quest initiator' => 'Tu n’es pas l’initiateur de la quête',
    'Quest {questName} is not in waiting state.' => 'La quête {questName} n’est pas en état d’attente.',
    'Quest can start once {minPlayers} joined. Current count is {currentCount}' => 'La quête peut commencer dès que {minPlayers} membres ont rejoint. Le nombre actuel est de {currentCount}',
    'Missing required player classes' => 'Classes de joueurs requises manquantes',
    'Quest can start' => 'La quête peut commencer',
    // Mission & Turn Toast Messages
    '{currentPlayerName} has completed mission “{currentMissionName}”.\nNow it’s your turn to start mission “{nextMissionName}”' => '{currentPlayerName} a terminé la mission « {currentMissionName} ».\nC’est maintenant à ton tour de démarrer la mission « {nextMissionName} »',
    '{currentPlayerName} has completed mission “{currentMissionName}”.\nNow it’s {nextPlayerName}’s turn to start mission “{nextMissionName}”' => '{currentPlayerName} a terminé la mission « {currentMissionName} ».\nC’est maintenant au tour de {nextPlayerName} de démarrer la mission « {nextMissionName} »',
    '{currentPlayerName} has finished his turn. Now it’s your turn to play.' => '{currentPlayerName} a terminé son tour. C’est maintenant à ton tour de jouer.',
    '{currentPlayerName} has finished his turn. Now it’s {nextPlayerName}’s turn to play.' => '{currentPlayerName} a terminé son tour. C’est maintenant au tour de {nextPlayerName} de jouer.',
    // Story levels & company size
    'Undefined' => 'Non défini',
    'Beginner only' => 'Débutants uniquement',
    'Level {min} only' => 'Niveau {min} uniquement',
    'Level {min} or {max}' => 'Niveau {min} ou {max}',
    'From level {min} to level {max}' => 'Du niveau {min} au niveau {max}',
    'Single player' => 'Un seul joueur',
    '{min} players' => '{min} joueurs',
    '{min} or {max} players' => '{min} ou {max} joueurs',
    '{min} to {max} players' => '{min} à {max} joueurs',
];
