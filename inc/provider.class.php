<?php

use \Firebase\JWT\JWT;

class PluginAzureProvider extends CommonDBTM
{

   protected $_id_token;
   protected $_resource_owner;

   // Interroge l'API Azure pour avoir le token d'identification
   // Affiche la page de connexion Office et invite à s'identifier
   public function checkAuthorization()
   {
      $config = new PluginAzureConfig();

      // Si il y a eu une erreur 
      if (isset($_POST['error'])) {
         Toolbox::logError('error');
         $error_description = isset($_POST['error']) ? $_POST['error'] : __("Action non autorisée.");

         Html::displayErrorAndDie(__($error_description), true);
      }

      // Si le token_id pour la requête d'autorisation n'existe pas
      if (!isset($_POST['id_token'])) {
         $params = [
            'client_id' =>  $config->client_id,
            'scope' =>  $config->scope,
            'response_type' => 'id_token',
            'state' => Session::getNewCSRFToken(),
            'response_mode' => 'form_post',
            'redirect_uri' => $config->redirect_url,
            'nonce' => 678910
         ];

         $url = $config->authorize_url;
         $glue = strstr($url, '?') === false ? '?' : '&';
         $url .= $glue . http_build_query($params);

         header('Location: ' . $url);
         exit();
      }
      // Arrive ici à la première connection pour récupérer le code d'autorisation
      // Vérifier l'état donné par rapport à celui précédemment enregistré pour atténuer l'attaque CSRF
      $state = isset($_POST['state']) ? $_POST['state'] : '';
      Session::checkCSRF([
         '_glpi_csrf_token' => $state,
      ]);

      $this->_id_token = $_POST['id_token'];

      return $_POST['id_token'];
   }

   public function login()
   {
      $user = $this->findUser();

      if (!$user) {
         return false;
      }

      //Création d'un faux utilisateur pour la connexion
      $auth = new Auth();
      $auth->user = $user;
      $auth->auth_succeded = true;
      $auth->extauth = 1;
      $auth->user_present = $auth->user->getFromDBbyName(addslashes($user->fields['name']));
      $auth->user->fields['authtype'] = Auth::DB_GLPI;
      Session::init($auth);

      return $auth->auth_succeded;
   }

   public function findUser()
   {

      $resource_array = $this->getResourceOwner();

      if (!$resource_array) {
         return false;
      }

      $user = new User();

      $default_condition = '';
      if (version_compare(GLPI_VERSION, '9.3', '>=')) {
         $default_condition = [];
      }
      if ($user->getFromDBbyEmail($resource_array['upn'], $default_condition)) {
         return $user;
      }

      // Si l'utilisateur n'existe pas dans la base de données
      try {
         // Génère un token api et un token perso
         $tokenAPI = base_convert(hash('sha256', time() . mt_rand()), 16, 36);
         $tokenPersonnel = base_convert(hash('sha256', time() . mt_rand()), 16, 36);

         $userPost['name'] = $resource_array['name'];
         $userPost['realname'] = preg_split('/ /', $resource_array['name'])[1];
         $userPost['_useremails'][-1] = $resource_array['email'];
         $userPost['firstname'] = preg_split('/ /', $resource_array['name'])[0];
         $userPost['api_token'] = $tokenAPI;
         $userPost['personal_token'] = $tokenPersonnel;
         $userPost['add'] = "Ajouter";

         $user->add($userPost);
         return $user;
      } catch (\Exception $ex) {
         return false;
      }

      return false;
   }

   public function getResourceOwner()
   {
      $config = new PluginAzureConfig();

      if ($this->_resource_owner !== null) {
         return $this->_resource_owner;
      }

      $array_publicKeysWithKIDasArrayKey = $this->loadKeysFromAzure($config->string_microsoftPublicKeyURL);
      $tokenClaims = (array)JWT::decode($_POST['id_token'], $array_publicKeysWithKIDasArrayKey, ['RS256']);
      $verifJwt = $this->validateTokenClaims($config->string_microsoftPublicURL, $config->client_id, $tokenClaims);
      if ($verifJwt) {
         $this->_resource_owner = $tokenClaims;
      } else {
         $this->_resource_owner = false;
      }


      return $this->_resource_owner;
   }

   /**
    * On va chercher les clefs publiques
    * 
    * @param
    */
   function loadKeysFromAzure($string_microsoftPublicKeyURL)
   {
      $array_keys = array();

      $jsonString_microsoftPublicKeys = file_get_contents($string_microsoftPublicKeyURL);
      $array_microsoftPublicKeys = json_decode($jsonString_microsoftPublicKeys, true);

      foreach ($array_microsoftPublicKeys['keys'] as $array_publicKey) {
         $string_certText = "-----BEGIN CERTIFICATE-----\r\n" . chunk_split($array_publicKey['x5c'][0], 64) . "-----END CERTIFICATE-----\r\n";
         $array_keys[$array_publicKey['kid']] = $this->getPublicKeyFromX5C($string_certText);
      }

      return $array_keys;
   }
   function getPublicKeyFromX5C($string_certText)
   {
      $object_cert = openssl_x509_read($string_certText);
      $object_pubkey = openssl_pkey_get_public($object_cert);
      $array_publicKey = openssl_pkey_get_details($object_pubkey);
      return $array_publicKey['key'];
   }

   /**
    * Validate the access token claims from an access token you received in your application.
    *
    * @param $tokenClaims array The token claims from an access token you received in the authorization header.
    *
    * @return bool
    */
   function validateTokenClaims($string_microsoftPublicURL, $client_id, $tokenClaims)
   {
      if ($client_id != $tokenClaims['aud']) {
         var_dump("error1");
         return false;
      } else if ($tokenClaims['nbf'] > time() || $tokenClaims['exp'] < time()) {
         var_dump("error2");
         return false;
      } else if ($tokenClaims['iss'] != $string_microsoftPublicURL) {
         var_dump("error3");
         return false;
      } else {
         return true;
      }
   }
}
