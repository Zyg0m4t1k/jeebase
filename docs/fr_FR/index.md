
Description 
===

Ce plugin permet de récupérer toutes les sondes , actionneurs et sensors de votre zibase (Jusqu'à l'arrêt des serveurs).

Il est aussi possible de créer des équipements depuis le plugin .

Il est ensuite possible d'utiliser ces informations dans Jeedom


Utilisation
===

> <span style="color:red">**IMPORTANT**</span>
>
> <span style="color:red">Pour que le plugin fonctionne correctement il faut que les modules aient des ID unique quelque soit le protocole. Sinon cela peut interférer sur la bonne réception des information</span>



Installation
===


* Télécharger le Plugin Jeebase sur le market.

* Activer le Plugin puis remplir les champs de configuration

![jeebase1](../images/jeebase1.png)


* **ip locale** : Ip de jeedom


**Enregistrer et créer les données en cliquant sur le bouton "synchroniser"**


> <span style="color:red">**IMPORTANT**</span>
>
> Il faut attendre au moins 1 minute que le démon se lance


* Aller maintenant dans l'onglet Plugins /monitoring/Jeebase

Vous devriez retrouver tous vos équipements créés lors de la synchronisation.

Configurer l'objet pour qu'il soit visible sur le dashboard et ensuite vous aurez toutes les informations sur le dashboard

![jeebase2](../images/jeebase2.png)

Les Actionneurs
===

Les informations
---

![jeebase3](../images/jeebase3.png)

* **Identifiant Module** : Identifiant qui doit être unique
* **Protocole** : Le protocole du périphérique
* **Variateur** : Cocher si dimmer
* **Somfy my** : Cocher si protocole somfy rts . Cela créé la commande my qui a 2 fonction: arrêt si le store est en mouvement ou positionnement à la position préférée

Possible aussi d'effectuer des actions lorsque la commande est reçu

* **URL** : Remplir le champs avec  une url valide (Utile en cas d'autre solution domotique par exemple)

Les Commandes
---

![jeebase4](../images/jeebase4.png)

> <span style="color:blue">**NOTE**</span>
>
>  La table des commandes se remplie au fur et à mesure que le plugin reçoit les information
>
> En fonction des types et sous-types, certaines options peuvent être
> absentes.

-   le nom affiché sur le dashboard

-   icône : dans le cas d’une action permet de choisir une icône à
    afficher sur le dashboard au lieu du texte

-   valeur de la commande : dans le cas d’une commande type action, sa
    valeur peut être liée à une commande de type info, c’est ici que
    cela se configure. Exemple pour une lampe l’intensité est liée à son
    état, cela permet au widget d’avoir l’état réel de la lampe.

-   le type et le sous-type.

-   "Valeur de retour d’état" et "Durée avant retour d’état" : permet
    d’indiquer à Jeedom qu’après un changement sur l’information sa
    valeur doit revenir à Y, X min après le changement. Exemple : dans
    le cas d’un détecteur de présence qui n’émet que lors d’une
    détection de présence, il est utile de mettre par exemple 0 en
    valeur et 4 en durée, pour que 4 min après une détection de
    mouvement (et si ensuite, il n’y en a pas eu de nouvelles) Jeedom
    remette la valeur de l’information à 0 (plus de mouvement détecté).

-   Historiser : permet d’historiser la donnée.

-   Afficher : permet d’afficher la donnée sur le dashboard.

-   Inverser : permet d’inverser l’état pour les types binaires.

-   Unité : unité de la donnée (peut être vide).

-   Min/Max : bornes de la donnée (peuvent être vides).

-   Configuration avancée (petites roues crantées) : permet d’afficher
    la configuration avancée de la commande (méthode
    d’historisation, widget…​).

-   Tester : permet de tester la commande.

-   Supprimer (signe -) : permet de supprimer la commande.Ci-dessous vous retrouvez la liste des commandes :


Les sondes
===

![jeebase5](../images/jeebase5.png)

Beaucoup plus de commandes que les actionneurs.

* **bat** : état de la batterie (Low ou OK)
* **Level** : Niveau de réception RF (1 à 5 )
* **Noise** : Bruit

> <span style="color:blue">**NOTE**</span>
>
>  La table des commandes se remplie au fur et à mesure que le plugin reçoit les information
>
> En cas de changement de pile , l'id peut changer. Il faut activer le mode debug et regarder le relevé d'activité. Changer l'id si besoin dans l'équipement . Ne pas oublier de remettre les logs par défaut si plus de nécessité.

Les Détecteurs (sensors)
===

> <span style="color:red">**IMPORTANT**</span>
>
> Certains détecteurs(selon protocole mais zwave OK) n’émettent que lors d’une détection de présence. Utiliser alors Valeur de retour d’état/durée
>
> D'autres émettent 2 id différents .==> reportez-vous aux équipement autre

Les équipements "Autres"
===

En plus des actionneurs,sondes et détecteurs il est possible de créer des équipements personnalisés. Cela peut-être utile pour:

- Créer des actionneurs personnalisés
- Créer des détecteurs "particulier" ==> avec un id ou 2 ids 


Pour cela cliquer sur le plus et choisir "Autres" dans la liste déroulante . Ne pas oublier de donner un nom à l'équipement.

![jeebase6](../images/jeebase6.png)

L'équipement apparaît ensuite dans la liste. Aller dans l'onglet information

![jeebase7](../images/jeebase7.png)

* **Identifiant Actif** : Correspond à la commande ON d'un actionneur
* **Identifiant inactif** : Correspond à la commande OFF d'un actionneur (Ce Champs peut ne pas être rempli) . Utiliser le champs en dessous pour positionner l'équipement en position OFF 
* **Temps RAZ (minutes)** : Durée aprés lequel l'actionneur se positionnera en position OFF ( Utile pour les détecteurs avec un seul ID)
* **Refresh** : Dernière RAZ ou prochaine si activé

> <span style="color:red">**IMPORTANT**</span>
>
> Les ID sont uniques. Donc il ne faut pas qu'un autre équipement utilise le même ID sinon cela va retourner des valeurs erronées


Les Logs
===

> <span style="color:red">**IMPORTANT**</span>
>
> En mode *Debug* le plugin est très verbeux, il est recommandé d'utiliser ce mode seulement si vous devez diagnostiquer un problème particulier , rechercher un ID , en mode inclusion/exclusion
>
> Il faut redémarrer le démon aprés tout changement pour que cela soit effectif

Le log jeebase_php
---

Il est correspond au suivi d'activité de la zibase et n'est rempli que losque le mode debug est activé. C'est le plus important

Pour ouvrir la fenêtre aller dans la configuration générale du plugin et cliquer sur le bouton jeebase_php

![jeebase8](../images/jeebase8.png)

![jeebase9](../images/jeebase9.png)

Gestion des batteries
===

La zibase ne renvoit que 2 états Low et OK.

La gestion des batteries passent par jeedom.

Pour cela il faut aller dans la configuration générale via la roue crantée en haut à droite puis configuration.

![jeebase10](../images/jeebase10.png)

Dans l'exemple ci-dessus j'ai placé le niveau d'alerte sur 15. Si vous avez d'autres valeurs ne rien changer. 

Ensuite aller dans la configuration du plugin

![jeebase11](../images/jeebase11.png)

Et remplir le champs concernant la batterie. ici j'ai mis 10 , une valeur inférieur au niveau warning (15 précédemment)

Ajouter un équipement
===

> <span style="color:red">**IMPORTANT**</span>
>
> <span style="color:red">Pour rappel , l'ID doit être unique quelque soit le protocole. Sinon cela peut interférer sur la bonne réception des information</span>


Depuis la zibase
---

- Suivre la procédure habituelle et une fois terminée ,synchroniser les équipements dans la configuration générale du plugin

Depuis le plugin
---
> <span style="color:red">**IMPORTANT**</span>
>
> <span style="color:red">Bien suivre les étapes pour la réussite de l'opération. Pour rappel l'id doit être unique dans le plugin</span>

###Inclusion###

* Mettre les logs du plugin en mode debug,Redémarrer le démon pour que cela soit effectif

* Créer un équipement

* Choisir l'id et le protocole

> <span style="color:red">**IMPORTANT**</span>
>
>Pour le Zwave il ne faut pas d'ID car sera déterminé par le controleur
>
>Pour les périphériques somfy , il faut choisir les identifiants C1 à C16 et/ou D1 à D16
>
>Pour les périphériques X2D, il est conseillé d’utiliser les groupes A1 à A16 et/ou B1 à B16

* Enregistrer la configuration

* Déroulement de l'inclusion 

**Si besoin , Appuyer sur le bouton d’apprentissage du récepteur. (voir procédure selon protocole)**

**Cliquer ensuite rapidement sur le bouton inclusion de l'équipement**

* Récupérer les informations

Hors Zwave: Votre module doit être inclus . Vous pouvez l'utiliser dans jeedom.

Zwave : Il faut récupérer l'id que vous allez voir dans le relevé d'activité et l'ajouter à l'équipement. Certains modules permettent de récupérer la température , l'humidité , la luminosité. Idem lors de la confirmation les ids sont notifiés. Il faut en plus créer des équipements(sondes en générale) avec l'ID récupéré

* Terminer le processus

> <span style="color:red">**IMPORTANT**</span>
>
> <span style="color:red">Aprés la fin de/des inclusions , ne pas oublier de mettre les logs du plugin sur défaut selon votre besoin et au cas ou Redémarrer le démon </span>

###Exclusion (Zwave seulement)###

* Mettre les logs du plugin en mode debug . Redémarrer le démon pour que cela soit effectif

* Aller sur l'équipement à exclure et cliquer sur bouton "Exclusion". La zibase se met en mode exclusion

* Effectuer les actions spécifiques au module.

* Le relevé d'activité confirme la bonne réussite de l'opération.Ne pas oublier de mettre les logs du plugin sur défaut selon votre besoin et au cas ou Redémarrer le démon

> <span style="color:blue">**NOTE**</span>
>
> Si vous n'avaz pas de module Zwave et voulez l'exclure , il faut créer un équipement , ne pas mettre d'id mais le protocole "Zwave" . Enregistrer puis cliquer sur exclusion.


Troubleshooting
===

Le démon ne démarre pas
---
- Vérifier que les informations de connexion sont exactes
- Vérifier les logs jeebase ,jeebase_php , http.error pour contrôler s'il y a une erreur

Détecteur ne remonte pas l'information
---
- Se référer aux chapitres sur les équipements "Autres"

Je ne connais pas l'ID de mon module
---

- Utiliser le mode debug (se référer aux chapitres sur les logs) puis activer/stimuler le pour vérifier les informations dans les logs jeebase_php



Le forum
---

- Pour toutes questions , ne pas hésitez à poster sur le fil officiel du plugin 
[Cliquer ICI](https://www.jeedom.com/forum/viewtopic.php?f=184&t=2471)
 










