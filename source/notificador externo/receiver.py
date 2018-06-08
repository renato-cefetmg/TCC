#!/usr/bin/env python
# -*- coding: utf-8 -*-

import datetime
import gammu
import pika
import sys 
import time

connection = pika.BlockingConnection(pika.URLParameters('amqp://glpi:glpi@192.168.56.101:5672/%2F'))
channel = connection.channel()

reload(sys)  
sys.setdefaultencoding('utf8')

recv_list = ["+5531123456789","+5531987654321"]

channel.queue_declare(queue="sms", durable=True, exclusive=False, auto_delete=False)

def sendsms(payload):

	for tel in recv_list:
	    try:
	        state_machine = gammu.StateMachine()
	        state_machine.ReadConfig()
	        state_machine.Init()
	        message = {
	            'Text': payload,
	            'SMSC': {'Location': 1},
	            'Number': destinatario,
	        }
	        status = state_machine.SendSMS(message)
	        state_machine.Terminate()
	        time.sleep(15)
	        return status
	    except gammu.GSMError as gsm_error:
	    	sendsms(payload)
	        '''
	        Trata-se qualquer erro do GAMMU da seguinte maneira:
	        desabilita-se o Modem USB
	        espera-se 30 segundos
	        habilita-se novamente o Modem USB
	        Reinfileira a tarefa para executar dai a 120 segundos * numero de tentativas
	        Observacao: A tarefa pode ser tentada no maximo 3 vezes
	        '''
	        #print((self.request.retries + 1) * 120)
	        #disable_usb()
	        #time.sleep(30)
	        #enable_usb()
	        #raise self.retry(countdown = 120 * (self.request.retries + 1), exc = gsm_error)


def write(message):

	file = open('log','a')

	for tel in recv_list:
		st = datetime.datetime.fromtimestamp(time.time()).strftime('%d-%m-%Y %H:%M:%S')
		file.write('\nSMS\n')
		file.write(st)
		file.write("\nDest: "+tel)
		file.write("\nPayload: "+message)
		file.write('\n--------------------\n')

	file.close()


#Callback for message received
def callback(ch, method, properties, body):
    print(" [x] Received %r" % body)

    #Message02
    if 'device_name' in properties.headers and 'timestamp' in properties.headers and 'type' in properties.headers and 'operator' in properties.headers and 'ticket_id' in properties.headers:
    	message = 'SOLUTION: ' + properties.headers['timestamp'] +' ON: '+ properties.headers['device_name'][0:20] + ' OPERATOR: '+properties.headers['operator']+' TICKET: ' + properties.headers['ticket_id']
    	print message
    	write(message)
    	#sendsms(message)
    else:
    #Message 01
	    if 'device_name' in properties.headers and 'timestamp' in properties.headers and 'type' in properties.headers:
	    	
	    	message = 'ERROR: ' + properties.headers['timestamp'] +' ON: '+ properties.headers['device_name'][0:20] + ' DESC: '+ body[0:100]
	    	print message
	    	write(message)
	    	#sendsms(message)
	    else:
	    	print ('Model mismatch')

channel.basic_consume(callback,
                      queue='sms',
                      no_ack=True)

print(' [*] Waiting for messages. To exit press CTRL+C')
channel.start_consuming()