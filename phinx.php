<?php

require dirname(__FILE__) . '/configuration.php';

return [
  'environments' => [
    'default_database' => 'typos',
    'typos' => [
      'adapter' => DB_DRIVER,
      'charset' => 'utf8',
      'collation' => 'utf8_unicode_ci',
      'host' => DB_HOSTNAME,
      'name' => DB_DATABASE,
      'pass' => DB_PASSWORD,
      'user' => DB_USERNAME
    ]
  ],
  'paths' => [
      'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
      'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
  ]
];
