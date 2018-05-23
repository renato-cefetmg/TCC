<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

class PluginRmqticketConfig extends CommonDBTM {

   static $tags = '[EXAMPLE_ID]';

   public function showForm($ID, $options = []) {
      global $CFG_GLPI;
      

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      if (!isset($options['display'])) {
         //display per default
         $options['display'] = true;
      }

      $params = $options;
      //do not display called elements per default; they'll be displayed or returned here
      $params['display'] = false;

      $out = '<tr>';
      $out .= '<th>' . __('My label', 'rmqticket') . '</th>';

      $objectName = autoName(
         $this->fields["name"],
         "name",
         (isset($options['withtemplate']) && $options['withtemplate']==2),
         $this->getType(),
   
         $this->fields["entities_id"]
      );

      $out .= '<td>';
      $out .= Html::autocompletionTextField(
         $this,
         'name',
         [
            'value'     => $objectName,
            'display'   => false
         ]
      );
      $out .= '</td>';

      $out .= $this->showFormButtons($params);

      if ($options['display'] == true) {
         echo $out;
      } else {
         return $out;
      }
   }


   static function generateLinkContents($link, CommonDBTM $item) {

      if (strstr($link, "[EXAMPLE_ID]")) {
         $link = str_replace("[EXAMPLE_ID]", $item->getID(), $link);
         return array($link);
      }

      return parent::generateLinkContents($link, $item);
   }


	static function cronConsume($task) {

      Plugin::load('rmqticket');

      
      define('HOST', '192.168.56.101');
      define('PORT', 5672);
      define('USER', 'glpi');
      define('PASS', 'glpi');
      define('VHOST', '/');

      $exchange = 'router';
      $queue = 'glpi';
      $consumerTag = 'consumer';
      //$connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
      $connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);

      try{
      	$channel = $connection->channel();
      }
      catch(\PhpAmqpLib\Exception\AMQPTimeoutException $e){
      	return 0;
      }
      /*
          The following code is the same both in the consumer and the producer.
          In this way we are sure we always have a queue to consume from and an
              exchange where to publish messages.
      */
      /*
          name: $queue
          passive: false
          durable: true // the queue will survive server restarts
          exclusive: false // the queue can be accessed in other channels
          auto_delete: false //the queue won't be deleted once the channel is closed.
      */
      $channel->queue_declare($queue, false, true, false, false);
      /*
          name: $exchange
          type: direct
          passive: false
          durable: true // the exchange will survive server restarts
          auto_delete: false //the exchange won't be deleted once the channel is closed.
      */
      $channel->exchange_declare($exchange, 'direct', false, true, false);
      $channel->queue_bind($queue, $exchange);
      /**
       * @param \PhpAmqpLib\Message\AMQPMessage $message
       */

      function process_message($message)
      {

      	$props = $message->get('application_headers')->getNativeData();

      	if(isset($props['device_name']) && isset($props['timestamp']) && isset($props['type'])){ 
	      	$ticket = new Ticket();
	      	$ticket->add(['name' => $props['type'].' - '.$props['device_name'],
	      	              'content' => $props['timestamp'] .' - '. $message->body]);
	        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    	}
    	else
    		$message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag']);
          
          
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
      /**
       * @param \PhpAmqpLib\Channel\AMQPChannel $channel
       * @param \PhpAmqpLib\Connection\AbstractConnection $connection
       */

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

      return 1;
   }

   static function cronInfo($name) {

      switch ($name) {
         case 'Consume' :
            return array('description' => __('Consume messages on target RabbitMQ queue', 'rmqticket')
                       //  'parameter'   => __('Cron parameter for Consume', 'rmqticket')
            				);
      }
      return array();
   }



}

/*function plugin_rmqticket_getDropdown() {
   return ['PluginRmqticketConfig' => PluginRmqticketConfig::getTypeName(2)];
}*/