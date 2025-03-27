<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => env('DB_CONNECTION'), //'mysql',
            'host' => env('DB_HOST'),//, '127.0.0.1'),
            'port' => env('DB_PORT'),//, '3306'),
            'database' => env('DB_DATABASE'),//, 'kamadeiep'),
            'username' => env('DB_USERNAME'),//, 'kama'),
            'password' => env('DB_PASSWORD'),//, 'K@m@1144'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'mysql2' => [
            'driver' => env('DB_CONNECTION_SECOND'), //'mysql',
            'host' => env('DB_HOST_SECOND'),//, '127.0.0.1'),
            'port' => env('DB_PORT_SECOND'),//, '3306'),
            'database' => env('DB_DATABASE_SECOND'),//, 'kamadeikb'),
            'username' => env('DB_USERNAME_SECOND'),//, 'kama'),
            'password' => env('DB_PASSWORD_SECOND'),//, 'K@m@1144'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'mysqllog' => [
            'driver' => env('DB_CONNECTION_LOG'),
            'host' => env('DB_HOST_LOG'),
            'port' => env('DB_PORT_LOG'),
            'database' => env('DB_DATABASE_LOG'),
            'username' => env('DB_USERNAME_LOG'),
            'password' => env('DB_PASSWORD_LOG'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

		'mysqllex' => [
            'driver' => env('DB_CONNECTION_LEX'), //'mysql',
            'host' => env('DB_HOST_LEX'),//, '127.0.0.1'),
            'port' => env('DB_PORT_LEX'),//, '3306'),
            'database' => env('DB_DATABASE_LEX'),//, 'kamadeikb'),
            'username' => env('DB_USERNAME_LEX'),//, 'kama'),
            'password' => env('DB_PASSWORD_LEX'),//, 'K@m@1144'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

		'mysqlkaas' => [
            'driver' => env('DB_CONNECTION_KAAS'), //'mysql',
            'host' => env('DB_HOST_KAAS'),//, '127.0.0.1'),
            'port' => env('DB_PORT_KAAS'),//, '3306'),
            'database' => env('DB_DATABASE_KAAS'),//, 'kamadeikb'),
            'username' => env('DB_USERNAME_KAAS'),//, 'kama'),
            'password' => env('DB_PASSWORD_KAAS'),//, 'K@m@1144'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

		'mysqlliveagent' => [
            'driver' => env('DB_CONNECTION_LIVEAGENT'), //'mysql',
            'host' => env('DB_HOST_LIVEAGENT'),//, '127.0.0.1'),
            'port' => env('DB_PORT_LIVEAGENT'),//, '3306'),
            'database' => env('DB_DATABASE_LIVEAGENT'),//, 'kamadeikb'),
            'username' => env('DB_USERNAME_LIVEAGENT'),//, 'kama'),
            'password' => env('DB_PASSWORD_LIVEAGENT'),//, 'K@m@1144'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
		
		'mysqlRPA' => [
            'driver' => env('DB_CONNECTION_RPA'), //'mysql',
            'host' => env('DB_HOST_RPA'),//, '127.0.0.1'),
            'port' => env('DB_PORT_RPA'),//, '3306'),
            'database' => env('DB_DATABASE_RPA'),//, 'kamadeikb'),
            'username' => env('DB_USERNAME_RPA'),//, 'kama'),
            'password' => env('DB_PASSWORD_RPA'),//, 'K@m@1144'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
		
		'mysqlCollection' => [
            'driver' => env('DB_CONNECTION_COL'), //'mysql',
            'host' => env('DB_HOST_COL'),//, '127.0.0.1'),
            'port' => env('DB_PORT_COL'),//, '3306'),
            'database' => env('DB_DATABASE_COL'),//, 'kamadeikb'),
            'username' => env('DB_USERNAME_COL'),//, 'kama'),
            'password' => env('DB_PASSWORD_COL'),//, 'K@m@1144'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
		
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
