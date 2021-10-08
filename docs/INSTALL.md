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
sudo yum install git libsemanage-python libselinux-python lsof wget mod_ssl openssl-devel ant vim-enhanced telnet iptables iptables-services net-tools mod_ssl openssh

# install nodejs and yarn, update node js to latest stable
sudo yum install nodejs
sudo npm install -g n
sudo n stable
sudo npm install -g yarn

# install python packages
sudo yum install https://repo.ius.io/ius-release-el7.rpm
sudo yum install yum-utils python-pip
sudo yum install python36u python36u-pip python36u-devel
sudo yum group install "Development Tools"

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
setfacl -R -d -m apache:rwx /var/data/registry && setfacl -R -m u:apache:rwx /var/data/registry
setfacl -R -d -m apache:rwx /var/log/registry && setfacl -R -m u:apache:rwx /var/log/registry
```

Build from source
```shell
cd /opt/apps/registry/src/
git clone https://github.com/au-research/ANDS-ResearchData-Registry.git rda-registry
cd rda-registry
composer install -o

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
Setup directories
```shell
mkdir -p /opt/apps/taskmanager
mkdir -p /opt/apps/taskmanager/src
mkdir -p /var/data/taskmanager
mkdir -p /var/log/taskmanager
```

### Installation
Requirements:
* python 3
* virtualenv

Install from source
```shell
cd /opt/apps/taskmanager/src
git clone https://github.com/au-research/ANDS-TaskManager.git taskmanager
ln -sfn /opt/apps/taskmanager/src/taskmanager /opt/apps/taskmanager/current

cd /opt/apps/taskmanager/current
rm -rf venv && rm -rf __pycache__ && /usr/bin/python3 -m venv venv && venv/bin/pip3 install --upgrade pip && venv/bin/pip3 install -r requirements.txt 
```
### Running the TaskManager as a Linux service

The file `taskmanager.service` is an init script to be copied into
`/usr/lib/systemd/system/`. Once copied into place, run:
 
 ```
 service taskmanager start
 ```

The Harvester will start up, and it will be started at each boot time.
The script supports `start`, `stop`, and `status` commands.

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
* python 3
* virtualenv 

Install from source
```shell
cd /opt/apps/harvester/src
git clone https://github.com/au-research/ANDS-Harvester.git harvester
ln -sfn /opt/apps/harvester/src/harvester /opt/apps/harvester/current 

cd /opt/apps/harvester/current
rm -rf venv && rm -rf __pycache__ && /usr/bin/python3 -m venv venv && venv/bin/pip3 install --upgrade pip && venv/bin/pip3 install -r requirements.txt
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
#### Handle server
downloading and configuring on a new system when installing new server or migrating existing server to new a system
```shell script
cd /opt
sudo wget https://www.handle.net/hnr-source/handle-9.2.0-distribution.tar.gz
sudo tar -xvf handle-9.2.0-distribution.tar.gz
sudo ln -s /opt/handle-9.2.0-distribution /opt/handle
sudo chown -R ands-services:ands-services /opt/handle
sudo su ands-services
```
Create a work folder for the handle server
and Configure the new server instance
##### No need to run if a copy of an existing server exists
after setup is complete the workfolder will contain a sitebndl.zip that will need to be updated with the correct prefix to look like the following example
Note: if the server is moving to a new System / VM / IP Copy the entire work folder (or handle folder)
and when running hdl-setup-server point it to the same folder so the set of keys can be reused (no need to rebuild the PIDS app)
```shell script
mkdir /opt/handle/svr_prod
/opt/handle/bin/hdl-setup-server /opt/handle/svr_prod
```

###### MySQL connector
Find the current MySQL connector JDBC driver eg: /usr/share/java/mysql-connector-java-5.1.41-bin.jar
If the mysql-connector-java does not exist install it eg: using yum 
Add a symbolic link to the Handle Server lib directory:
```shell script
sudo yum install mysql-connector-java
ln -s  /usr/share/java/mysql-connector-java{-5.1.41-bin}.jar /opt/handle/lib/mysql-connector.jar
```
###### Start the Handle server
when starting a Handle server user the binary hdl-server command with the given server configuration as param 1 
```shell script
/opt/handle/bin/hdl-server /opt/handle/svr_prod
```

###### Stop the Handle Server
the given file needs to be deleted to stop the handle server
```shell script
rm -f /opt/handle/svr_prod/delete_this_to_stop_server
```

##### PIDS Service running in Tomcat
Building the PIDS application 
It has to be built on a system that has access to the handle server's library (Note: we could just add them to the PIDS lib)
it will need the following jar files from the handle server's lib directory

	handle-9.2.0.jar
	commons-codec-1.11.jar
	gson-2.8.5.jar
	cnriutil-2.0.jar

To be able to administer the handles on the server the PIDS server needs to have access to the admin keys that are located in the handle server work directory 
It need to have access to or a copy of the handle server's working directory at run time 

#### Build and deploy the PIDS service 
The build.xml (ANT script) requires ant-contrib-1.0b3.jar to be in the PATH 
it must be downloaded manually 
```shell script
cd /opt/conf
git clone ssh://git@{git-repository}/conf/pids.git
git clone ssh://git@{git-repository}/conf/handle.git
cd /opt/ands
git clone ssh://git@{git-repository}/rd/ands-pids-service.git
cd ands-pids-service
ant clean build_wars -Dconf-path=/opt/conf/pids/ENVIRONMENT_NAME/build.properties
### Or deploy to Tomcat using ant
ant clean tomcat-deploy -Dconf-path=/opt/conf/pids/ENVIRONMENT_NAME/build.properties
```

###### Sample PIDS built.properties
```
# handle library
handle-lib=/opt/handle/lib
# the config of the handle server this PIDS server is client of
handle-conf=/opt/conf/handle/test.rda
# The prefix administered by the PIDS server
prefix=10378.3
# the IP address of the registry that is managing trusted clients for PIDS
admin-ip=IP.IP.IP.IP
# logging
log-level=INFO
log-file=/var/log/pids/pids.log
# database
dbs-pids-user-name=pid_user
dbs-pids-password=password
 
dbs-pids-driver=com.mysql.jdbc.Driver
dbs-pids-factory=org.apache.commons.dbcp.BasicDataSourceFactory
dbs-pids-url=jdbc:mysql://IP.IP.IP.IP:PORT/dbs_pids
 
#tomcat deploy
tomcat-manager-url=http://IP.IP.IP.IP:PORT/manager/text
tomcat-manager-username=bamboo-deploy
tomcat-manager-password=password
```