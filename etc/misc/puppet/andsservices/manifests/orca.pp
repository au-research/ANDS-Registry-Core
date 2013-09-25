class andsservices::orca {

include andsservices::params

# Set default path
Exec { path => '/usr/bin:/bin:/usr/sbin:/sbin' }

$orca_document_root = "${andsservices::params::document_root}/orca"

# Create global_config.php

file {"$orca_document_root/global_config.php":
  ensure  => file,
  require => File["$orca_document_root"],
  content => template('andsservices/orca_global_config.php.erb'),
}

# Delete global_config.sample

file {"$orca_document_root/global_config.sample":
  ensure  => absent,
  require => File['deploy-orca'],
}

# Create .htaccess and delete htaccess.sample

file {"$orca_document_root/.htaccess":
  ensure  => file,
  require => File["$orca_document_root"],
  source  => "${andsservices::params::repo_dir}/arms/src/htaccess.sample",
}

file {"$orca_document_root/htaccess.sample":
  ensure  => absent,
  require => File['deploy-orca'],
}

# Set our own RewriteBase in .htaccess

exec {'set-rewritebase-orca':
  command => "sed -i 's^RewriteBase.*^RewriteBase /orca/^' .htaccess",
  cwd     => $orca_document_root,
  require => File["$orca_document_root/.htaccess"],
}

# Deploy ORCA

file { 'deploy-orca':
  ensure  => directory,
  path    => $orca_document_root,
  source  => "${andsservices::params::repo_dir}/arms/src",
  recurse => true,
  mode   => 644,
}

notify {"Configuring MySQL.":}

# Install MySQL Server

class {'mysql::server':
  config_hash           => {
    'root_password'     => $andsservices::params::mysql_root_password,
    'old_root_password' => $andsservices::params::mysql_old_root_password,
  }
}

# Workaround for Puppet bug http://projects.puppetlabs.com/issues/16262

#exec {'mysql-add-webuser':
  #command => "echo 'GRANT USAGE ON *.* TO \"webuser\"@\"localhost\"; \
                    #GRANT USAGE ON *.* TO \"webuser\"@\"%\"; \
                    #DROP USER \"webuser\"@\"localhost\"; \
                    #DROP USER \"webuser\"@\"%\"; \
                    #FLUSH PRIVILEGES; \
                    #CREATE USER \"webuser\"@\"localhost\" IDENTIFIED BY \"${andsservices::params::mysql_webuser_password}\"; \
                    #CREATE USER \"webuser\"@\"%\" IDENTIFIED BY \"${andsservices::params::mysql_webuser_password}\"; \
                    #GRANT ALL PRIVILEGES ON *.* TO \"webuser\"@\"localhost\" WITH GRANT OPTION; \
                    #GRANT ALL PRIVILEGES ON *.* TO \"webuser\"@\"%\" WITH GRANT OPTION;' | mysql -u root -p${andsservices::params::mysql_root_password}",
  #require => [Class['mysql::server'], Exec[mysqld-restart]],
#}

#database_user { 'webuser@%':
  #password_hash => mysql_password($andsservices::params::mysql_webuser_password),
  #require       => Class['mysql::server'],
#}

#database_grant { 'webuser@%':
  #privileges => ['all'],
  #require    => Class['mysql::server'],
#}

#database_user { 'webuser@localhost':
  #password_hash => mysql_password($andsservices::params::mysql_webuser_password),
  #require       => Class['mysql::server'],
#}

#database_grant { 'webuser@localhost':
  #privileges => ['all'],
  #require    => Class['mysql::server'],
#}

# Run DB install scripts

#exec {'orca-db-scripts':
  #command => "mysql -u root -p${andsservices::params::mysql_root_password} < dbs_vocabs_r9_full.sql &&
              #mysql -u root -p${andsservices::params::mysql_root_password} < dbs_harvester_r9_full.sql &&
              #mysql -u root -p${andsservices::params::mysql_root_password} < dbs_portal_r9_full.sql &&
              #mysql -u root -p${andsservices::params::mysql_root_password} < dbs_registry_r9_full.sql &&
              #mysql -u root -p${andsservices::params::mysql_root_password} < dbs_registry_r9_to_r10_incr.sql",
  #cwd     => "${andsservices::params::repo_dir}/arms/db/mysql",
  #require => [Class['mysql::server'], File['deploy-orca']],
#}

mysql::db { 'dbs_vocabs':
  user     => 'webuser',
  password => "${andsservices::params::mysql_webuser_password}",
  host     => $::hostname,
  sql      => "${andsservices::params::repo_dir}/arms/db/mysql/dbs_vocabs_r9_full.sql",
}

# "-ignored1" is a hack to workaround this Puppet-MySQL bug:
# https://github.com/puppetlabs/puppetlabs-mysql/pull/154
# There's a fix in GitHub, but it hasn't made its way into Puppet Forge yet.
mysql::db { 'dbs_harvester':
  user     => 'webuser-ignored1',
  password => "${andsservices::params::mysql_webuser_password}",
  host     => $::hostname,
  sql      => "${andsservices::params::repo_dir}/arms/db/mysql/dbs_harvester_r9_full.sql",
}

mysql::db { 'dbs_portal':
  user     => 'webuser-ignored2',
  password => "${andsservices::params::mysql_webuser_password}",
  host     => $::hostname,
  sql      => "${andsservices::params::repo_dir}/arms/db/mysql/dbs_portal_r9_full.sql",
}

mysql::db { 'dbs_registry':
  user     => 'webuser-ignored3',
  password => "${andsservices::params::mysql_webuser_password}",
  host     => $::hostname,
  sql      => "${andsservices::params::repo_dir}/arms/db/mysql/dbs_registry_r9_full.sql",
  notify   => [ Exec['registry-sql-incr'], Exec['portal-sql-incr'] ]
}

exec {'registry-sql-incr':
  command => "mysql -u root -p${andsservices::params::mysql_root_password} dbs_registry < dbs_registry_r9_to_r10_incr.sql",
  cwd     => "${andsservices::params::repo_dir}/arms/db/mysql",
}

exec {'portal-sql-incr':
  command => "mysql -u root -p${andsservices::params::mysql_root_password} dbs_portal < dbs_portal_r9_to_r10_incremental.sql",
  cwd     => "${andsservices::params::repo_dir}/arms/db/mysql",
}

}
