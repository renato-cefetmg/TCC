<?php

define('rmqticket_VERSION', '0.1');

/**
 * Init the hooks of the plugins - Needed
 *
 * @return void
 */
function plugin_init_rmqticket() {
   global $PLUGIN_HOOKS;

   //required!
   $PLUGIN_HOOKS['csrf_compliant']['rmqticket'] = true;

   

   //Plugin::registerClass('PluginRmqticketConfig');

   Plugin::registerClass('PluginRmqticketConfig',
                         array('link_types' => true));

   if (class_exists('PluginRmqticketConfig')) {
      Link::registerTag(PluginRmqticketConfig::$tags);
   }

//   $_SESSION["glpi_plugin_rmqticket_profile"]['rmqticket'] = 'w';
//  	if (isset($_SESSION["glpi_plugin_rmqticket_profile"])) { // Right set in change_profile hook
      //$PLUGIN_HOOKS['menu_toadd']['rmqticket'] = array('plugins' => 'PluginRmqTicket');
//    }

//      $PLUGIN_HOOKS["helpdesk_menu_entry"]['rmqticket'] = true;

   //some code here, like call to Plugin::registerClass(), populating PLUGIN_HOOKS, ...

      if (Session::haveRight('config', UPDATE)) {
      $PLUGIN_HOOKS['config_page']['rmqticket'] = 'config.php';
   }
}

/**
 * Get the name and the version of the plugin - Needed
 *
 * @return array
 */
function plugin_version_rmqticket() {
   return [
      'name'           => 'RabbitMQ Tickets',
      'version'        => rmqticket_VERSION,
      'author'         => 'Renato',
      'license'        => '',
      'homepage'       => '',
      'requirements'   => [
         'glpi'   => [
            'min' => '0.90',
         ],
         'php'    => [
            'min' => '7.0'
         ]
      ]
   ];
}

/**
 * Optional : check prerequisites before install : may print errors or add to message after redirect
 *
 * @return boolean
 */
function plugin_rmqticket_check_prerequisites() {
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
function plugin_rmqticket_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo "Installed, but not configured";
   }
   return false;
}
