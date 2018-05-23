<?php

include(__DIR__ . '/nagios_config.php');

/*
*   NAGIOS_SEND_HOST: 
*   ARGS{
*	[1] = message payload
*	[2] = device_name header
*	[3] = $HOSTSTATE$ from nagios
*	[4] = $HOSTSTATETYPE$ from nagios
*	[5] = $HOSTATTEMPT$ from nagios
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
					" host_state=".$argv[3].
					" host_statetype=".$argv[4].
					" host_attempt=".$argv[5].
					")";

if(level_check_host($argv[3],$argv[4],$argv[5])){
	notify_rabbitmq($argv[1],$argv[2],"HOST");
	write_on_log($argv[0]." - Message Sent, ".$params);
}

else{
	write_on_log($argv[0]." - Message NOT Sent, ".$params);
}

