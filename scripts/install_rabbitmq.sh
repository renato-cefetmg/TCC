# !/bin/bash

wget https://packages.erlang-solutions.com/erlang-solutions_1.0_all.deb
sudo dpkg -i erlang-solutions_1.0_all.deb

sudo apt-get update
sudo apt-get install esl-erlang -y

echo "deb https://dl.bintray.com/rabbitmq/debian xenial main" | sudo tee /etc/apt/sources.list.d/bintray.rabbitmq.list


#wget -O- https://dl.bintray.com/rabbitmq/Keys/rabbitmq-release-signing-key.asc | sudo apt-key add -
wget -O- https://www.rabbitmq.com/rabbitmq-release-signing-key.asc | sudo apt-key add -

sudo apt-get update
sudo apt-get install rabbitmq-server -y

sudo rabbitmq-plugins enable rabbitmq_management

sudo rabbitmqctl add_user glpi glpi
sudo rabbitmqctl set_user_tags glpi administrator
sudo rabbitmqctl set_permissions -p / glpi ".*" ".*" ".*"

sudo /etc/init.d/rabbitmq-server restart
