<?php

include(__DIR__ . '/nagios_config.php');

/*
*   NAGIOS_SEND_SERVICE: 
*   ARGS{
*	[1] = message payload
*	[2] = device_name header
*	[3] = $SERVICESTATE$ from nagios
*	[4] = $SERVICESTATETYPE$ from nagios
*	[5] = $SERVICEATTEMPT$ from nagios
*	}
*
*/

if (sizeof($argv) < 6){
	write_on_log($argv[0]." - Invalid # of ARGS - Expected >=6, Got ".sizeof($argv));
	echo $argv[0]." - Invalid # of ARGS - Expected >=6, Got ".sizeof($argv)."\n";
	die();
}

$params = "Params = (payload=".$argv[1].
					" device_name=".$argv[2].
					" service_state=".$argv[3].
					" service_statetype=".$argv[4].
					" service_attempt=".$argv[5].
					")";

if(level_check_service($argv[3],$argv[4],$argv[5])){
	notify_rabbitmq($argv[1],$argv[2],"SERVICE");
	write_on_log($argv[0]." - Message Sent, ".$params);
}

else{
	write_on_log($argv[0]." - Message NOT Sent, ".$params);
}

