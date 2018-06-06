<?php

//require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;



/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_rmqsolution_install() {
   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_rmqsolution_uninstall() {
   return true;
}

// Hook done on before update item case
function plugin_pre_item_update_rmqsolution($item) {
   /* Manipulate data if needed
   if (!isset($item->input['comment'])) {
      $item->input['comment'] = addslashes($item->fields['comment']);
   }
   $item->input['comment'] .= addslashes("\nUpdate: ".date('r'));
   */
   Session::addMessageAfterRedirect(__("Pre Update Computer Hook", 'rmqsolution'), true);
}


// Hook done on update item case
function plugin_item_update_rmqsolution($item) {

  $tags = array('<p>','</p>');

   $user = new User();
   $user->getFromDB($item->fields['users_id_lastupdater']);

   $type = trim(explode('-',$item->fields['name'])[0]);
   $device_name = trim(explode('-',$item->fields['name'])[1]);
   //$solution = strip_tags($item->fields['solution']);
   $solution = $item->fields['solution'];
   
//   Session::addMessageAfterRedirect(sprintf(__("Updated solution: (%s)", 'rmqsolution'), $solution), true);	

	//Check if Status was updated
   if(in_array('status',$item->updates) && $item->fields['status'] == 5) {
   		connect_and_send($device_name,$user->getRawName(),$item->fields['id'],$type,$solution);
 	}	

   return true;
}


function connect_and_send($device_name,$operator,$ticket_id,$type,$solution) {

	$exchange = 'glpi';

	$queues = array("nagios",
						 "sms"
					   );

	$consumerTag = 'consumer';

	//This Should be parameterized...
	//AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
	$connection = new AMQPStreamConnection("192.168.56.101", 5672, 'glpi', 'glpi', '/');

	$channel = $connection->channel();

	$channel->exchange_declare($exchange, 'direct', false, true, false);

	foreach ($queues as $queue){
		/*
	    name: $queue
	    passive: false
	    durable: true // the queue will survive server restarts
	    exclusive: false // the queue can be accessed in other channels
	    auto_delete: false //the queue won't be deleted once the channel is closed.
	    */
		$channel->queue_declare($queue, false, true, false, false);
		$channel->queue_bind($queue, $exchange);
	}


	

	$message = new AMQPMessage($solution,array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
  	$headers = new AMQPTable();
	$headers->set('device_name',$device_name,AMQPTable::T_STRING_LONG);
	$headers->set('timestamp',date('d/m/o - G:i:s'),AMQPTable::T_STRING_LONG);	
	$headers->set('operator',$operator,AMQPTable::T_STRING_LONG);
	$headers->set('type',$type,AMQPTable::T_STRING_LONG);
	$headers->set('ticket_id',$ticket_id	,AMQPTable::T_STRING_LONG);

	$message->set('application_headers',$headers);

	$channel->basic_publish($message,$exchange);

	$channel->close();
	$connection->close();
}
