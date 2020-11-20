<?php

function plugin_azure_display_login()
{
   // Toolbox::logError("plugin_azure_display_login");
   global $CFG_GLPI;

   // Si l'utilisateur est connecté (true ou false)
   // Si il ne l'est pas, on affiche le formulaire de connexion
   if (isset($_COOKIE['auth'])) {
      $signon_provider = new PluginAzureProvider();
      $REDIRECT = "";

      if (isset($_GET["redirect"])) {
         Toolbox::manageRedirect($_GET["redirect"]);
         setcookie('redirect', $_POST['redirect'], time() + 30, null, null, false, true);
      }

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
         // we have done at least a good login? No, we exit.
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
   } else {
      $url = $CFG_GLPI['root_doc'] . '/plugins/azure/front/callback.php';
      echo ('<form action ="' . $url . '" method="post" id="connexionSubmit">');
      echo ('<a class="azure" href="#" style="color: white">Connexion avec Office 365<br><br><img src="' . $CFG_GLPI['root_doc'] . '/plugins/azure/office365.png" style="width:100px" /></a>');
      if (isset($_GET["redirect"])) {
         Toolbox::manageRedirect($_GET["redirect"]);
         echo '<input type="hidden" name="redirect" value="' . Html::entities_deep($_GET['redirect']) . '"/>';
      }
      Html::closeForm();

      echo '<script type="text/javascript">
      $(".azure").on("click", function (e) {
         $("#connexionSubmit").submit();
      });
       </script>';
   }
}

/**
 * Install hook
 *
 * @return boolean
 */
function plugin_azure_install()
{
   //do some stuff like instanciating databases, default values, ...
   return true;
}

/**
 * Uninstall hook
 *
 * @return boolean
 */
function plugin_azure_uninstall()
{
   //to some stuff, like removing tables, generated files, ...
   return true;
}
