<?php
require_once __DIR__ . '/vendor/autoload.php';
include(__DIR__ . '/nagios_config_parameters.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

function get_hoststate_value($state){
        switch($state){
                case 'UP':
                        return 1;
                        break;
                case 'DOWN':
                        return 2;
                        break;
                case 'UNREACHABLE':
                        return 3;
                        break;
        }
        return 0;
}

function get_servicestate_value($state){
        switch($state){
                case 'OK':
                        return 1;
                        break;
                case 'WARNING':
                        return 2;
                        break;
                case 'UNKNOWN':
                        return 3;
                        break;
                case 'CRITICAL':
                        return 4;
                        break;
        }
        return 0;
}

function get_statetype_value($statetype){
	switch($statetype){
		case 'SOFT':
			return 1;
			break;
		case 'HARD':
			return 2;
			break;
	}
	return 0;
}

//Checks if HOST notification should generate a ticket, based on parameters above
function level_check_host($hoststate,$hoststatetype,$hostattempt){
	if(get_hoststate_value($hoststate) < HOST_MINIMUM_STATE){
		return false;
	}
	if(get_statetype_value($hoststatetype) < HOST_MINIMUM_TYPE){
		return false;
	}
/*	
	if($hostattempt < HOST_MINIMUM_ATTEMPT){
		return false;
	}
*/

	return true;
}


//Checks if SERVICE notification should generate a ticket, based on parameters above
function level_check_service($servicestate,$servicestatetype,$serviceattempt){
	if(get_servicestate_value($servicestate) < SERVICE_MINIMUM_STATE){
		return false;
	}
	if(get_statetype_value($servicestatetype) < SERVICE_MINIMUM_TYPE){
		return false;
	}
/*	
	if($serviceattempt < SERVICE_MINIMUM_ATTEMPT){
		return false;
	}
*/
	return true;
}


function write_on_log($log_message){
	$terminal_logging = false;

	if($terminal_logging){
		echo date('d/m/o - G:i:s').' - '.$log_message."\n";
	}

	else{
		$log = fopen(LOG_FILE,'a') or die("Cannot open file".LOG_FILE);
		fwrite($log,date('d/m/o - G:i:s').' - '.$log_message."\n");
		fclose($log);
	}
}

function notify_rabbitmq($payload, $device_name, $type){

	/*
	*
	*	Notification Message Default:
	*	Headers:
	*	    device_name = Device name as is on Nagios
	*	    timestamp = System date/time as it notifies
	*	    type = 'host'/'service' as is on nagios
	*	Payload: Notification Details
	*/

	$queues = QUEUES;

	try{
		$connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
		$channel = $connection->channel();
		/*
		    name: Exchange
		    type: direct
		    passive: false
		    durable: true // the exchange will survive server restarts
		    auto_delete: false //the exchange won't be deleted once the channel is closed.
		*/
		$channel->exchange_declare(EXCHANGE, 'direct', false, true, false);

		foreach ($queues as $queue){
			/*
		    name: $queue
		    passive: false
		    durable: true // the queue will survive server restarts
		    exclusive: false // the queue can be accessed in other channels
		    auto_delete: false //the queue won't be deleted once the channel is closed.
		    */
			$channel->queue_declare($queue, false, true, false, false);
			$channel->queue_bind($queue, EXCHANGE);
		}

		$headers = new AMQPTable();
		$messageBody = $payload;
		$message = new AMQPMessage($messageBody,array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

		$headers->set('device_name',$device_name,AMQPTable::T_STRING_LONG);
		$headers->set('timestamp',date('d/m/o - G:i:s'),AMQPTable::T_STRING_LONG);
		$headers->set('type',$type,AMQPTable::T_STRING_LONG);
		$message->set('application_headers',$headers);
		$channel->basic_publish($message,EXCHANGE);
		
		
		//close channel and connection
		$channel->close();	
		$connection->close();
	
	}
	catch(ErrorException $e){
		write_on_log("Connection Error");
		die();
	}
	catch(\PhpAmqpLib\Exception\AMQPTimeoutException $e){
		write_on_log("Timeout Exception");
		$channel->close();	
		$connection->close();
		die();
	}

	
}





