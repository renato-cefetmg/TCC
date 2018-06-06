<?php
/**
 * Install hook
 *
 * @return boolean
 */

function plugin_rmqticket_install() {

   global $DB;   
   //instanciate migration with version
   $migration = new Migration(100);

   //Create table only if it does not exists yet!
   if (!TableExists('glpi_plugin_rmqticket_config')) {
      //table creation query

   	  //encriptar password
      $query = "CREATE TABLE `glpi_plugin_rmqticket_config` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `host` VARCHAR(255) NOT NULL,
                  `port` INT(5) NOT NULL,
                  `user` VARCHAR(255) NOT NULL,
                  `pass` VARCHAR(255),           
                  `vhost` VARCHAR(255),         
                  PRIMARY KEY  (`id`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, $DB->error());
   }

   //execute the whole migration
   $migration->executeMigration();

   CronTask::Register('PluginRmqticketConfig', 'Consume', DAY_TIMESTAMP);

   return true;
}

/**
 * Uninstall hook
 *
 * @return boolean
 */
function plugin_rmqticket_uninstall() {
	global $DB;  
    $tables = [
      'config'
    ];

    foreach ($tables as $table) {
        $tablename = 'glpi_plugin_rmqticket_' . $table;
        
		if (TableExists($tablename)) {
		    $DB->queryOrDie(
		        "DROP TABLE `$tablename`",
		        $DB->error()
		    );
        };
    }
   
   return true;
}

function cron_plugin_rmqticket() {
	$ticket = new Ticket();
	$ticket->add([
  	'name' => 'my name',
  	'description' => 'some text']);
  	return true;
}