<?php

//AMQPStreamConnection Definitions
define('HOST', '192.168.56.101');
define('PORT', 5672);
define('USER', 'glpi');
define('PASS', 'glpi');
define('VHOST', '/');
//If this is enabled you can see AMQP output on the CLI
//define('AMQP_DEBUG', true);

// HOST|SERVICE STATE Definitions
define('OK', 1);
define('WARNING', 2);
define('UNKNOWN', 3);
define('CRITICAL', 4);

define('UP', 1);
define('DOWN', 2);
define('UNREACHABLE', 3);



// HOST|SERVICE STATETYPE Definitions
define('SOFT', 1);
define('HARD', 2);

//Config variables:
define('LOG_FILE' , '/usr/local/nagios/etc/utils/log_nagios_rmq.log');

define('HOST_MINIMUM_STATE', DOWN);
define('HOST_MINIMUM_TYPE', HARD);
define('HOST_MINIMUM_ATTEMPT', 10);

define('SERVICE_MINIMUM_STATE', CRITICAL);
define('SERVICE_MINIMUM_TYPE', HARD);
define('SERVICE_MINIMUM_ATTEMPT', 10);

define('EXCHANGE','nagios');
define('QUEUES',array('glpi','sms'));
