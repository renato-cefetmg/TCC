<?php

define('PLUGIN_RMQSOLUTION_VERSION', '0.0.1');

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_rmqsolution() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['rmqsolution'] = true;
   $PLUGIN_HOOKS['pre_item_update']['rmqsolution'] = array('Ticket' => 'plugin_pre_item_update_rmqsolution');
   $PLUGIN_HOOKS['item_update']['rmqsolution'] = array('Ticket' => 'plugin_item_update_rmqsolution');
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_rmqsolution() {
   return [
      'name'           => 'RmqSolution',
      'version'        => PLUGIN_RMQSOLUTION_VERSION,
      'author'         => 'Renato',
      'license'        => '',
      'homepage'       => '',
      'minGlpiVersion' => '9.1'
   ];
}

/**
 * Check pre-requisites before install
 * OPTIONAL, but recommended
 *
 * @return boolean
 */
function plugin_rmqsolution_check_prerequisites() {
   // Strict version check (could be less strict, or could allow various version)
   if (version_compare(GLPI_VERSION, '9.1', 'lt')) {
      if (method_exists('Plugin', 'messageIncompatible')) {
         echo Plugin::messageIncompatible('core', '9.1');
      } else {
         echo "This plugin requires GLPI >= 9.1";
      }
      return false;
   }
   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_rmqsolution_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      _e('Installed / not configured', 'rmqsolution');
   }
   return false;
}
