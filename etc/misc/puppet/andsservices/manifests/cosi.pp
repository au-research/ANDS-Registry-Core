class andsservices::cosi {

include andsservices::params

# Set default path
Exec { path => '/usr/bin:/bin:/usr/sbin:/sbin' }

$cosi_document_root = "${andsservices::params::document_root}/cosi"

# Deploy COSI

file {'deploy-cosi':
  ensure  => directory,
  path    => $cosi_document_root,
  source  => "${andsservices::params::repo_dir}/registry/src",
  recurse => true,
  mode    => 644,
}

# Create global_config.php

file {"$cosi_document_root/global_config.php":
  ensure  => file,
  require => File["$cosi_document_root"],
  content => template('andsservices/cosi_global_config.php.erb'),
}

# Delete global_config.sample

file {"$cosi_document_root/global_config.sample":
  ensure  => absent,
  require => File['deploy-cosi'],
}

# Set our own RewriteBase in .htaccess

exec {'set-rewritebase-cosi':
  command => "echo $cosi_document_root && sed -i 's^RewriteBase.*^RewriteBase /cosi/^' .htaccess",
  cwd     => $cosi_document_root,
  require => File['deploy-cosi'],
}

# Install Postgres

class {'postgresql::params':
  version             => '9.1',
  manage_package_repo => true,
  before              => Class['postgresql::server'],
}

class {'postgresql::server':
    config_hash => {
        'ip_mask_deny_postgres_user' => '0.0.0.0/32',
        'ip_mask_allow_all_users' => '0.0.0.0/0',
        'listen_addresses' => '*',
        'manage_redhat_firewall' => false, # TODO: would be nice, seems broken
        'postgres_password' => $andsservices::params::postgres_postgres_password,
    },
}

postgresql::db {'dbs_cosi':
  user     => 'dba',
  password => $andsservices::params::postgres_dba_password,
  grant    => 'all',
  charset  => 'UTF8',
  # locale doesn't seem to have made it from GitHub to puppetlabs forge yet
  #locale   => 'en_AU',
  require => Class['postgresql::server'],
}

postgresql::database_user {'webuser':
  password_hash => postgresql_password('webuser', $andsservices::params::postgres_webuser_password),
  require => Class['postgresql::server'],
}

postgresql::database_grant {'webuser-grant-connect':
  privilege   => 'CONNECT',
  db          => 'dbs_cosi',
  role        => 'webuser',
  require => Class['Postgresql::Db[dbs_cosi]'],
}

postgresql::database_grant {'webuser-grant-temporary':
  privilege   => 'TEMPORARY',
  db          => 'dbs_cosi',
  role        => 'webuser',
  require => Class['Postgresql::Db[dbs_cosi]'],
}

postgresql::pg_hba_rule {'local-rule':
  type        => 'local',
  database    => 'dbs_cosi',
  user        => 'all',
  auth_method => 'trust',
  order       => '0',
}

postgresql::pg_hba_rule { 'network-rule':
  type => 'host',
  database => 'dbs_cosi',
  user => 'all',
  address => '127.0.0.1/32',
  auth_method => 'trust',
  order       => '0',
}

# The DB scripts need to be readable by the postgres user

exec {'cosi-db-scripts-chmod':
  command => 'cp -f release8/dbs_cosi_full.sql \
                    release8.1_to_8.2_incremental/dbs_cosi_r8.1_to_r8.2.sql \
                    release8.2_to_9_incremental/dbs_cosi_r8.2_to_r9.sql \
                    release9_to_10_incremental/dbs_cosi_r9_to_r10.sql \
                    /tmp &&
              chmod 777 /tmp/dbs_cosi_full.sql \
                        /tmp/dbs_cosi_r8.1_to_r8.2.sql \
                        /tmp/dbs_cosi_r8.2_to_r9.sql \
                        /tmp/dbs_cosi_r9_to_r10.sql',
  cwd     => "${andsservices::params::repo_dir}/registry/db",
  require => File['deploy-cosi'],
}

# Run DB install scripts

exec {'cosi-db-scripts':
  command  => "psql -d dbs_cosi -f /tmp/dbs_cosi_full.sql &&
               psql -d dbs_cosi -f /tmp/dbs_cosi_r8.1_to_r8.2.sql &&
               psql -d dbs_cosi -f /tmp/dbs_cosi_r8.2_to_r9.sql &&
               psql -d dbs_cosi -f /tmp/dbs_cosi_r9_to_r10.sql",
  cwd      => "${andsservices::params::repo_dir}/registry/db",
  user     => 'postgres',
  require  => [ Class['postgresql::server'], Exec['cosi-db-scripts-chmod'] ],
}

}
