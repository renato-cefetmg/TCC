<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isInstalled('rmqticket') || !$plugin->isActivated('rmqticket')) {
   Html::displayNotFoundError();
}

//check for ACLs
if (PluginRmqticketConfig::canView()) {
   //View is granted: display the list.

   //Add page header
   Html::header(
      __('RabbitMQ Tickets', 'rmqticket'),
      $_SERVER['PHP_SELF'],
      'assets',
      'pluginrmqticketconfig',
      'myobject'
   );

   Search::show('PluginRmqticketConfig');

   Html::footer();
} else {
   //View is not granted.
   Html::displayRightError();
}