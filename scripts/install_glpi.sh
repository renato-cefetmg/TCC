# !/bin/bash

wget https://github.com/glpi-project/glpi/releases/download/9.2.2/glpi-9.2.2.tgz
sudo apt-get install apache2 -y
sudo apt-get install mysql-server -y
sudo apt-get install php7.0 -y
sudo apt-get install libapache2-mod-php7.0 -y
sudo apt-get install php7.0-gd php7.0-xml php7.0-xmlrpc php7.0-mysql php7.0-imap php7.0-snmp php7.0-ldap php7.0-mbstring php7.0-curl php7.0-bcmath -y
sudo tar xvf glpi-9.2.2.tgz -C /var/www/html/
sudo chown www-data /var/www/html/glpi -R
sudo /etc/init.d/apache2 restart


