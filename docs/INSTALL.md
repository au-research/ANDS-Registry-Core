# Installation Guide

## Infrastructure Setup
RHEL 7

Install webtatic & epel repository
```shell
yum install epel-release
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
```

Install packages
```shell
# install common packages
sudo yum install git libsemanage-python libselinux-python lsof wget mod_ssl openssl-devel ant vim-enhanced telnet iptables iptables-services net-tools

# install nodejs and yarn
sudo yum install nodejs
sudo npm install -g yarn

# install python packages
sudo yum install https://repo.ius.io/ius-release-el7.rpm
sudo yum install yum-utils python-pip
sudo yum install python36u python36u-pip python36u-devel
sudo yum group install "Development Tools"
pip install PyMySQL redis
pip3 install PyMySQL redis

# install java
sudo yum install java-1.8.0-openjdk java-1.8.0-openjdk-devel maven
```

set `selinux` to permissive mode
```shell
vi /etc/selinux/config

SELINUXTYPE=targeted

# restart the server
shutdown -r now
```

allow `httpd` to connect to remote network
```shell
setsebool -P httpd_can_network_connect 1
```

Install webserver packages
```shell
sudo yum install httpd httpd-tools tomcat tomcat-webapps tomcat-admin-webapps
sudo yum install php56w php56w-common php56w-mysql php56w-ldap php56w-xml php56w-mbstring php56w-bcmath
```

Set `date.timezone` for CLI access
```shell
vi /etc/php.ini

# find the line and set
date.timezone = "Australia/Sydney"
```

Install composer
```shell
cd /tmp
wget https://getcomposer.org/installer
cat /tmp/installer | php -- --install-dir=/usr/local/bin
mv /usr/local/bin/composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
ln -sfn /usr/local/bin/composer /usr/bin/composer
```

## RDA Registry
Setup directories
```shell
mkdir -p /opt/apps/registry
mkdir -p /opt/apps/registry/src
mkdir -p /var/data/registry
mkdir -p /var/log/registry
```

Build from source
```shell
cd /opt/apps/registry/src/
git clone https://github.com/au-research/ANDS-ResearchData-Registry.git rda-registry
cd rda-registry
composer install

ln -sfn /opt/apps/registry/src/rda-registry /opt/apps/registry/current 
```

Configure with `.env` and `.htaccess`
```shell
cp /opt/apps/registry/current/.env.sample /opt/apps/registry/current/.env
cp /opt/apps/registry/current/htaccess.sample /opt/apps/registry/current/.htaccess
```
and modify the newly created file(s) accordingly

Set up a virtual host block for Apache, _for e.g._
```shell
<VirtualHost *:80>
    DocumentRoot "/opt/apps/registry/current"
    ErrorLog logs/error_log
    TransferLog logs/access_log
    LogLevel warn
    
    # ssl proxy pass for location_capture_widget
    SSLProxyEngine on
    ProxyPass /api/resolver https://localhost:443/apps/assets/location_capture_widget
    ProxyPreserveHost On
    ProxyStatus On
    RewriteOptions Inherit

    # socket.io pubsub
    RewriteEngine On
    RewriteCond %{REQUEST_URI}  ^/socket.io            [NC]
    RewriteCond %{QUERY_STRING} transport=websocket    [NC]
    RewriteRule /(.*)           ws://localhost:3000/$1 [P,L]
    ProxyPass /socket.io http://localhost:3000/socket.io
    ProxyPassReverse /socket.io http://localhost:3000/socket.io
</VirtualHost>

<Directory "/opt/apps/registry/current">
    Options Indexes FollowSymLinks
    Order allow,deny
    Allow from all
    AllowOverride All
    Require all granted
</Directory>
```
## TaskManager
TBA

## Harvester
Setup directories
```shell
mkdir -p /opt/apps/harvester
mkdir -p /opt/apps/harvester/src
mkdir -p /var/data/harvester
mkdir -p /var/data/harvester/harvested_contents
mkdir -p /var/log/harvester
```
### Installation
Requirements:
* python 3.5 - 3.7
* virtualenv 

```
/usr/bin/python3 -m venv venv && 
venv/bin/pip3 install --upgrade pip && 
venv/bin/pip3 install -r requirements.txt
```
Install from source
```shell
cd /opt/apps/harvester/src
git clone https://github.com/au-research/ANDS-Harvester.git harvester

ln -sfn /opt/apps/harvester/src/harvester /opt/apps/harvester/current 
```
### Running the Harvester as a Linux service

The file `harvester.service` is an init script to be copied into
`/usr/lib/systemd/system/`. Once copied into place, run:
 
 ```
 service harvester start
 ```

The Harvester will start up, and it will be started at each boot time.
The script supports `start`, `stop`, and `status` commands.

## Handle and PIDs Server
TBA