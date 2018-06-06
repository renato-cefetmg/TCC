<?php
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

// Non menu entry case
//header("Location:../../central.php");

// Entry menu case
define('GLPI_ROOT', '../..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkRight("config", UPDATE);

// To be available when plugin in not activated
Plugin::load('rmqticket');

require_once __DIR__ . '/vendor/autoload.php';
define('HOST', '192.168.56.101');
define('PORT', 5672);
define('USER', 'glpi');
define('PASS', 'glpi');
define('VHOST', '/');
//If this is enabled you can see AMQP output on the CLI
//define('AMQP_DEBUG', true);
use PhpAmqpLib\Connection\AMQPStreamConnection;
//use PhpAmqpLib\Connection\AMQPConnection;
$exchange = 'router';
$queue = 'glpi';
$consumerTag = 'consumer';
//$connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
$connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
$channel = $connection->channel();
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
	$ticket = new Ticket();
	$ticket->add(['name' => 'Auto ticket GLPI RabbitMQ', 'content' => $message->body]);
    echo "\n--------\n";
    echo $message->body;
    echo "\n--------\n";
    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    // Send a message with the string "quit" to cancel the consumer.
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
while (count($channel->callbacks)) {
    $channel->wait();
}




Html::header("Config RMQ Tickets", $_SERVER['PHP_SELF'], "config", "plugins");
_e("This is the plugin config page", 'rmqticket');
Html::footer();
