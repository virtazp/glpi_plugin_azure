<?php

function plugin_azure_display_login()
{
   global $CFG_GLPI;

   if (isset($_GET['redirect']) && strlen($_GET['redirect']) > 0 && !isset($_COOKIE['redirect']) ) {
      setcookie('redirect',$_GET['redirect'], 0 , '/');
   }

   $url = $CFG_GLPI['root_doc'] . '/plugins/azure/front/callback.php';
   echo ('<form action ="' . $url . '" method="post" id="connexionSubmit">');
   echo ('<a class="azure" href="#" style="color: white">Connexion avec Office 365<br><br><img src="' . $CFG_GLPI['root_doc'] . '/plugins/azure/office365.png" style="width:100px" /></a>');
   Html::closeForm();

   echo '<script type="text/javascript">
      $(".azure").on("click", function (e) {
         $("#connexionSubmit").submit();
      });
       </script>';
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
