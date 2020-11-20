# glpi-connect_azure

Permet l'authentification via Azure AD grâce au protocole d'authentification OpenID Connect. 
Ce plugin ne dispose pas d'un affichage sur le tableau de bord de GLPI. Cela évite la création supplémentaire de table dans la base de données.
Les informations de connexion sont à renseigner dans le fichier inc/config.class.php.
Au préalable, il faut inscrire l'application dans Azure : https://docs.microsoft.com/fr-fr/azure/active-directory/develop/app-objects-and-service-principals

## Fonctionnement

Un bouton de connexion s'ajoute dans la partie connexion de GLPI. En cliquant dessus, une requête est envoyée pour pouvoir se connecter.
Lors de la première connexion, l'utilisateur est automatiquement ajouté dans la base de données de GLPI. Le SSO est activé immédiatement.
Les sessions sont gérés par des cookies. Les cookies ne contiennent pas d'information sensible, juste un état de connexion.

![Fonctionnement](https://github.com/virtazp/glpi_plugin_azure/blob/main/Azure-AD.png)

## Décomposition des fichiers

### inc/ config.class.php

Fichier de configuration du plugin
- TENANT : Id de l'annuaire (Peut être des chiffres ou des lettres)
- DEV : Mode développement si true
- $_client_id : ID d'application (client)
- $_scope : 'openid user.read email profile' (S'assurer que l'application dispose de ces droits dans azure)
- $_authorize_url : Url d'autorisation, permet d'obtenir le token_id
- $_string_microsoftPublicKeyURL : Url pour la vérification du token_id (Ne pas modifier)
- $_string_microsoftPublicURL : Url pour la vérification du token_id décodé (Ne pas modifier)
- $this->redirect_url : Url de redirection (Ex : http(s)://(your-domaine)/plugins/azure/front/callback.php    () = a changer)

La connexion utilise le protocole d'authentification OpenID Connect, pour s'assurer la bonne configuration, se reporter à cette adresse : https://login.microsoftonline.com/common/v2.0/.well-known/openid-configuration
Le résultat sera un tableau en json avec les informations d'azure.

### hook.php -> fonction plugin_azure_display_login()

Détermine si il faut afficher le formulaire de connexion ou rediriger vers la page d'accueil

### inc/ provider.class.php

Classe qui gère la connexion :
- Elle affiche le formulaire Office pour la saisie des identifiants
- Connecte un utilisateur à GLPI et vérifie la validité du token d'autorisation.

La fonction static logoutCookie() permet d'effacer le cookie lors de la déconnexion. L'appelle de la fonction dans le fichier glpi/front/logout.php est recommandé car sinon vous ne pourrait plus vous déconnecter. 
Cette fonction doit être appellé "PluginAzureProvider::logoutCookie();" dans le fichier front/logout.php juste avant la dernière ligne du fichier "Html::redirect($CFG_GLPI["root_doc"]."/index.php".$toADD);"

Cette classe utilise la bibliothèque PHP-JWT pour le décodage du jeton. https://github.com/firebase/php-jwt
Avant d'installer le plugin dans le tableau de bord de GLPI, il faut executer la commande dans la dossier du plugin: composer require firebase/php-jwt , pour l'installation des paquets. Il faut aussi au préalable avoir composer d'installé dans son environnement. https://getcomposer.org/download/

### front/ callback.php

fait exactement la même chose que hook.php mais uniquement lors de la première connexion. 

### Remarques

Le plugin évoluera. Cette version a été codé pour correspondre à mes besoins spécifique, mais est tout à fait utilisable par vous, si vous voulez utiliser le même système d'authentification.

Ce plugin a été inspiré à partir de celui là : https://github.com/edgardmessias/glpi-singlesignon.
Il est plus complet, mais ne gère pas l'ajout en base de donnée.

### TODO

- Gestion du "nonce" pour plus de sécurité, bien que "state" soit géré
- Gestion des erreurs.
