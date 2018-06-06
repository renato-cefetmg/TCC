# !/bin/bash

echo "Downloading Dependencies..."

sudo apt-get update
sudo apt-get install -y autoconf gcc libc6 make wget unzip apache2 php libapache2-mod-php7.0 libgd2-xpm-dev

sudo a2enmod cgi
sudo a2enmod rewrite

sudo useradd -m nagios
sudo groupadd nagcmd
sudo usermod -a -G nagcmd nagios
sudo usermod -a -G nagcmd www-data

echo "Installing Nagios..."
wget https://assets.nagios.com/downloads/nagioscore/releases/nagios-4.3.4.tar.gz
tar xvf nagios-4.3.4.tar.gz
cd nagios-4.3.4


sudo ./configure --with-httpd-conf=/etc/apache2/conf-available
sudo make
sudo make all
sudo make install
sudo make install-init
sudo make install-commandmode
sudo make install-config
sudo make install-webconf

sudo update-rc.d nagios defaults

sudo cp etc /usr/local/nagios/

sudo install -c -m 644 sample-config/httpd.conf /etc/apache2/sites-enabled/nagios.conf


cd ..


echo "Installing Nagios Plugins..."
wget https://nagios-plugins.org/download/nagios-plugins-2.2.1.tar.gz
tar xvf nagios-plugins-2.2.1.tar.gz
cd nagios-plugins-2.2.1
sudo ./configure
sudo make
sudo make all
sudo make install

cd ..

echo "Post Config..."

#sudo rm nagios* -rf
sudo htpasswd -c /usr/local/nagios/etc/htpasswd.users nagiosadmin
sudo /etc/init.d/apache2 restart
sudo /etc/init.d/nagios restart
