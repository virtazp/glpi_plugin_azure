<?php

define('PLUGIN_AZURE_VERSION', '1.0.0');

/**
 * Init the hooks of the plugins - Needed
 *
 * @return void
 */
function plugin_init_azure() {
   global $PLUGIN_HOOKS;

   $autoload = __DIR__ . '/vendor/autoload.php';

   if (file_exists($autoload)) {
      include_once $autoload;
   }

   $PLUGIN_HOOKS['csrf_compliant']['azure'] = true;
   $PLUGIN_HOOKS['display_login']['azure'] = "plugin_azure_display_login";
}

/**
 * Get the name and the version of the plugin - Needed
 *
 * @return array
 */
function plugin_version_azure() {
   return [
      'name'           => 'Azure',
      'version'        => PLUGIN_AZURE_VERSION,
      'author'         => 'Anthony Jaeger',
      'license'        => 'GLPv3',
      'homepage'       => 'https://github.com/virtazp/glpi-connect_azure',
      'requirements'   => [
         'glpi'   => [
            'min' => '9.1'
         ]
      ]
   ];
}

/**
 * Optional : check prerequisites before install : may print errors or add to message after redirect
 *
 * @return boolean
 */
function plugin_azure_check_prerequisites() {
   //do what the checks you want
   return true;
}

/**
 * Check configuration process for plugin : need to return true if succeeded
 * Can display a message only if failure and $verbose is true
 *
 * @param boolean $verbose Enable verbosity. Default to false
 *
 * @return boolean
 */
function plugin_azure_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo "Installed, but not configured";
   }
   return false;
}