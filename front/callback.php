<?php

/**
 * Cette page est appellée par le callback d'azure
 */

// Désactivation CSRF token
define('GLPI_USE_CSRF_CHECK', 0);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../../../inc/includes.php');

// Instance de la classe PluginAzureProvider()
$signon_provider = new PluginAzureProvider();

// Affichage de la fenêtre de connexion de Microsoft
$signon_provider->checkAuthorization();

$REDIRECT = "";

if(isset($_COOKIE['redirect']) && (strlen($_COOKIE['redirect']) > 0)) {
   $REDIRECT = $_COOKIE['redirect'];
   $_GET['redirect'] = $_COOKIE['redirect'];
}

if ($signon_provider->login()) {
   setcookie('redirect', '', 0 , '/');
   Auth::redirectIfAuthenticated();
   
} else {
   Html::nullHeader("Login", $CFG_GLPI["root_doc"] . '/index.php');
   echo '<div class="center b">' . __('User not authorized to connect in GLPI') . '<br><br>';
   // Logout whit noAUto to manage auto_login with errors
   echo '<a href="' . $CFG_GLPI["root_doc"] . '/front/logout.php?noAUTO=1' .
      str_replace("?", "&", $REDIRECT) . '" class="singlesignon">' . __('Log in again') . '</a></div>';
   echo '<script type="text/javascript">
   if (window.opener) {
      $(".singlesignon").on("click", function (e) {
         e.preventDefault();
         window.opener.location = $(this).attr("href");
         window.focus();
         window.close();
      });
   }
   </script>';
   Html::nullFooter();
   exit();
}
