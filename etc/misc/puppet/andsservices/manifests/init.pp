class andsservices {

include andsservices::params

# Set default path
Exec { path => '/usr/bin:/bin:/usr/sbin:/sbin' }

# Get ANDS git repo

vcsrepo {"${andsservices::params::repo_dir}" :
  ensure   => latest,
  provider => git,
  source   => 'git://github.com/au-research/ANDS-Online-Services.git',
  revision => 'develop',
  notify   => [ File['deploy-orca'], File['deploy-cosi'] ],
}

# Install Apache

class {'apache': }

# Install PHP module

class {'apache::mod::php': }

# Create Vhost

# Workaround Puppet bug
exec {'create-docroot':
    command => "mkdir -p ${andsservices::params::document_root}",
}

apache::vhost {'*':
    priority      => '10',
    vhost_name    => $ipaddress,
    port          => '80',
    docroot       => $andsservices::params::document_root,
    serveradmin   => 'webmaster@example.com',
    serveraliases => [$fqdn, $hostname,],
    override      => 'All',
    require       => Exec['create-docroot'],
}

# RedHat distros have PHP 5.3 in the package php53. The package 'php', which apache::mod::php installs, is
# PHP 5.1. ANDS services don't run on 5.1 so we have to install 5.3.
if $::osfamily == 'redhat' {
  Package <| title == 'php' |> {
    ensure => absent,
    before => Exec['remove-php51'],
  }

  # There are a lot of php-* packages and this is easier than having Puppet remove them all
  exec {'remove-php51':
    command => 'yum -y remove php-*',
  }

  $php53_packages = [ 'php53', 'php53-pgsql', 'php53-mysql', 'php53-gd', 'php53-ldap', 'php53-mbstring', 'php53-xml', 'php53-pdo' ]

  package {$php53_packages:
    ensure  => latest,
    require => [ Exec['remove-php51'], Package['httpd'] ],
    before  => A2mod['php5'],
  }
}

# Install SOLR

if $::osfamily != 'redhat' {
  warning("This Puppet module only installs Solr automatically for RedHat-based distros. Please install Solr manually.")
} else {
  include jpackage

  package { 'java-1.6.0-openjdk':
    ensure => present,
  }

  package { 'tomcat6':
    ensure => present,
  }

  file { 'tomcat-postgres-driver':
    ensure   => file,
    path     => "/usr/share/tomcat6/lib/postgresql-9.1-902.jdbc4.jar",
    source   => "puppet:///modules/andsservices/postgresql-9.1-902.jdbc4.jar",
    require  => Package['tomcat6'],
  }

  file { 'solr-dir':
    ensure  => directory,
    path    => "/usr/local/solr41",
    owner   => 'tomcat',
    require  => Package['tomcat6'],
  }

  exec { 'get-solr':
    command  => 'ls /tmp/solr-4.1.0.tgz ||
                 wget --output-document=/tmp/solr-4.1.0.tgz --quiet http://apache.mirror.uber.com.au/lucene/solr/4.1.0/solr-4.1.0.tgz ||
                 wget --output-document=/tmp/solr-4.1.0.tgz --quiet http://mirror.mel.bkb.net.au/pub/apache/lucene/solr/4.1.0/solr-4.1.0.tgz ||
                 wget --output-document=/tmp/solr-4.1.0.tgz --quiet http://mirror.overthewire.com.au/pub/apache/lucene/solr/4.1.0/solr-4.1.0.tgz ||
                 wget --output-document=/tmp/solr-4.1.0.tgz --quiet http://archive.apache.org/dist/lucene/solr/4.1.0/solr-4.1.0.tgz &&
                 if [[ "`md5sum /tmp/solr-4.1.0.tgz`" != 740a0a2ce42e502d5cd5da561e6b6af5*solr-4.1.0.tgz ]]; then exit 1; fi &&
                 tar -xzf /tmp/solr-4.1.0.tgz -C /tmp &&
                 rm -rf /usr/local/solr41/* &&
                 mv /tmp/solr-4.1.0/* /usr/local/solr41 &&
                 chown -R tomcat:tomcat /usr/local/solr41',
    cwd      => '/',
    timeout  => 0,
    require  => File['solr-dir'],
  }


  file {'solr.xml':
    ensure  => file,
    path    => '/usr/share/tomcat6/conf/Catalina/localhost/solr.xml',
    source  => 'puppet:///modules/andsservices/solr.xml',
    owner   => 'tomcat',
    require => Package['tomcat6'],
  }

  file {'schema.xml':
    ensure  => file,
    path    => "/usr/local/solr41/example/solr/collection1/conf/schema.xml",
    source  => "${andsservices::params::repo_dir}/arms/misc/solrschema_r10.xml",
    require => Exec['get-solr'],
  }

  service {'tomcat6':
    ensure  => 'running',
    enable  => true,
    require => Package['tomcat6'],
  }

}


class {'orca': }
class {'cosi': }

}
