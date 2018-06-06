<?php
require_once __DIR__ . '/vendor/autoload.php';
include(__DIR__ . '/nagios_config.php');
use PhpAmqpLib\Connection\AMQPStreamConnection;

$queue = 'nagios';
$consumerTag = 'consumer';
$connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);

$channel = $connection->channel();
$channel->queue_declare($queue, false, true, false, false);

/**
 * @param \PhpAmqpLib\Message\AMQPMessage $message
 */


function process_message($message)
{

	$command_file = '/usr/local/nagios/var/rw/nagios.cmd';
	//$command_file = '/home/renato/Desktop/nagioscommands.txt';
	$props = $message->get('application_headers');
	
	//message headers validation
	/*
	   MESSAGE MODEL
	   HEADER: device_name: Name of the device on Nagios/GLPI
	   HEADER: timestamp: timetamṕ
	   HEADER: operator: Operator who attended the ticket
	   HEADER: ticket_id: Ticket ID
	   HEADER: type: HOST or SERVICE
	   MESSAGE PAYLOAD: Solution description
	*/
	if (
		   (isset($props->getNativeData()['device_name']))
		&& (isset($props->getNativeData()['timestamp']))
		&& (isset($props->getNativeData()['operator']))
		&& (isset($props->getNativeData()['ticket_id']))
		&& (isset($props->getNativeData()['type']))
		)
	{
		$tags = array('&lt;/p&gt;','&lt;p&gt;');
		$solution = str_replace($tags,'',$message->body);;
	
		$device_name = $props->getNativeData()['device_name'];
		$timestamp = $props->getNativeData()['timestamp'];
		$operator = $props->getNativeData()['operator'];
		$ticket_id = $props->getNativeData()['ticket_id'];
		$type = $props->getNativeData()['type'];

		switch($type){
		case 'HOST':

		/* NAGIOS ADD HOST COMMENT SYNTAX
		* [time] ADD_HOST_COMMENT <host_name>;<persistent>;<author>;<comment>
		*/
			$nagioscommand = '[' . date('U') .'] ' .
						  "ADD_HOST_COMMENT;".
				/*host_name*/	$device_name.';'.
				/*persistent*/	'1;'.
				/*author*/		'GLPI Plugin;'.
				/*comment*/	"Date: ".$timestamp." - Ticket: ".$ticket_id." - Operator: ".$operator. 				  " - Solution: ".str_replace(';','',$message->body)."\n";
			break;
		case 'SERVICE':

		/* NAGIOS ADD SERVICE COMMENT SYNTAX
		* [time] ADD_SVC_COMMENT <host_name>;<service_description>;<persistent>;<author>;<comment>
		*/
			$nagioscommand = '[' . date('U') .'] '.
					"ADD_SVC_COMMENT;".
				/*host_name*/	trim(explode('|',$device_name)[0]).';'.
				/*serv_desc*/	trim(explode('|',$device_name)[1]).';'.		
				/*persistent*/	'1;'.
				/*author*/		'GLPI Plugin;'.
				/*comment*/		"Date: ".$timestamp." - Ticket: ".$ticket_id." - Operator: ".$operator. 				  " - Solution: ".str_replace(';','',$message->body)."\n";
			break;
		default:
			$nagioscommand = '0';	
		}	

		echo $nagioscommand;

		if($nagioscommand != '0'){
			$file = fopen($command_file,'a') or die("Cannot open file nagios.cmd");
			fwrite($file,$nagioscommand);
			fclose($file);
		}
    	$message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
	}
	else{
		echo "\n ---Message out of Standards--- \n";
	}

    if ($message->body === 'quit') {
        $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
    }
}
/*
    queue: Queue from where to get the messages
    consumer_tag: Consumer identifier
    no_local: Don't receive messages published by this consumer.
    no_ack: Tells the server if the consumer will acknowledge the messages.
    exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
    nowait:
    callback: A PHP Callback
*/

$channel->basic_consume($queue, $consumerTag, false, false, false, false, 'process_message');

function shutdown($channel, $connection)
{
    $channel->close();
    $connection->close();
}

register_shutdown_function('shutdown', $channel, $connection);
// Loop as long as the channel has callbacks registered
try{
	while (count($channel->callbacks)) {
   	$channel->wait(null, false, 3);
	}
}
catch(\PhpAmqpLib\Exception\AMQPTimeoutException $e){
	shutdown($channel,$connection);
}









