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

// Permet de sauvegarder le chemin de redirection
if (isset($_POST['redirect']) && (strlen($_POST['redirect']) > 0)) {
   setcookie('redirect', $_POST['redirect'], time() + 30, null, null, false, true);
}

// Instance de la classe PluginAzureProvider()
$signon_provider = new PluginAzureProvider();

// Affichage de la fenêtre de connexion de Microsoft
$signon_provider->checkAuthorization();

if ($signon_provider->login()) {

   if (isset($_COOKIE['redirect']) && (strlen($_COOKIE['redirect']) > 0)) {

      $redirect = $_COOKIE['redirect'];

      global $CFG_GLPI;
      if (!Session::getLoginUserID()) {
         return false;
      }

      if (!$redirect) {
         if (isset($_POST['redirect']) && (strlen($_POST['redirect']) > 0)) {
            $redirect = $_POST['redirect'];
         } else if (isset($_GET['redirect']) && strlen($_GET['redirect']) > 0) {
            $redirect = $_GET['redirect'];
         }
      }

      //Direct redirect
      if ($redirect) {
         Toolbox::manageRedirect($redirect);
      }

      // Rediriger vers Command Central si ce n'est pas uniquement postérieur
      if (Session::getCurrentInterface() == "helpdesk") {
         if ($_SESSION['glpiactiveprofile']['create_ticket_on_login']) {
            Html::redirect($CFG_GLPI['root_doc'] . "/front/helpdesk.public.php?create_ticket=1");
         }
         Html::redirect($CFG_GLPI['root_doc'] . "/front/helpdesk.public.php");
      } else {
         if ($_SESSION['glpiactiveprofile']['create_ticket_on_login']) {
            Html::redirect(Ticket::getFormURL());
         }
         Html::redirect($CFG_GLPI['root_doc'] . "/front/central.php");
      }
   } else {
      Auth::redirectIfAuthenticated();
   }
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
