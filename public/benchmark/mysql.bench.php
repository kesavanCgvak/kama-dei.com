<?php

$initialRowCount = 1000;
$mysqli = null;
$dbName = null;

pushArgs([
    'mysql_host' => '127.0.0.1',
    'mysql_user' => "kamauser",//null,
    'mysql_password' => "Ro(aDU3jIYA**xLa",//null,
    'mysql_port' => 3306,
    'mysql_database' => "bench_test",//null,
]);

setup(function ($args) use (&$mysqli, $initialRowCount, &$dbName) {
    if (!extension_loaded('mysqli')) {
        print('The mysqli extension is not loaded' . PHP_EOL);
		//exit();
        return;
    }

    if ($args['mysql_host'] === null || $args['mysql_user'] === null || $args['mysql_password'] === null) {
        print('The --mysql_host=, --mysql_user=, and --mysql_password= arguments are required' . PHP_EOL);
		//exit();
        return;
    }

    $mysqli = new mysqli($args['mysql_host'], $args['mysql_user'], $args['mysql_password'], null,
        isset($args['mysql_port']) ? $args['mysql_port'] : 3306);

    if ($mysqli->connect_error) {
        printf("Mysql Connect Error (%s) %s\n", $mysqli->connect_errno, $mysqli->connect_error);
		//exit();
        return;
    }

    $dbName = 'bench_test';//isset($args['mysql_database']) ? $args['mysql_database'] : 'bench_test';
/*
    // check if database exists
    $result = $mysqli->query("SELECT schema_name FROM information_schema.schemata WHERE schema_name = '$dbName'");

    // if exists, delete all tables
    if ($result->num_rows > 0) {
        $result = $mysqli->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '$dbName'");
        while ($row = $result->fetch_assoc()) {
            $mysqli->query("DROP TABLE IF EXISTS `$dbName`.`{$row['table_name']}`");
        }
    } else {
        $mysqli->query("CREATE DATABASE IF NOT EXISTS `$dbName`");
    }
*/
    $mysqli->select_db($dbName);
	$mysqli->query("DROP TABLE IF EXISTS `$dbName`.`test`");
    $mysqli->query("CREATE TABLE IF NOT EXISTS `$dbName`.`test` (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255), `insert_time` timestamp NULL ON UPDATE CURRENT_TIMESTAMP)");
/*
    'config' => function () use (&$mysqli) {
		print "-------------------------Config------------------------".PHP_EOL;
		print "<pre>";
		print_r($mysqli->host_info);
		print "</pre>".PHP_EOL;
		extraStat('q/s', 0);
        return 1;
    },
*/
/*
		print "<pre>".PHP_EOL;
		printf('%s%s', str_pad(sprintf("MySQL Config"), 55, '-', STR_PAD_BOTH), PHP_EOL);
		foreach($args as $key=>$arg){
			if($key!='mysql_password'){
				printLine($key, $arg);
			}
		}
		printLine('MySql server info', $mysqli->server_info);
//		print "host_info: ".$mysqli->host_info.PHP_EOL;
		printLine('', '', '-');
		print "</pre>".PHP_EOL;
*/
    for ($i = 0; $i < $initialRowCount; $i++) {
        $values[] = "('test$i', now())";
    }
    $r = $mysqli->query("INSERT INTO `$dbName`.`test` (name, insert_time) VALUES " . implode(',', $values));
    if (!$r) {
        printf("Mysql Error (%s) %s\n", $mysqli->errno, $mysqli->error);
    }
});

teardown(function () use (&$mysqli, $dbName) {
    if ($mysqli === null) {
        return;
    }

    $result = $mysqli->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '$dbName'");
    while ($row = $result->fetch_assoc()) {
        $mysqli->query("DROP TABLE IF EXISTS `$dbName`.`{$row['table_name']}`");
    }
    $mysqli->close();
});


return [
    'server_info' => function () use (&$mysqli) {
        return $mysqli->server_info;
    },
    'ping' => function () use (&$mysqli) {
        if ($mysqli === null) {
            return INF;
        }
        $mysqli->ping();
        return 1;
    },
    'select_version' => function ($multiplier = 1, $count = 1000) use (&$mysqli) {
        if ($mysqli === null) {
            return INF;
        }

        $count = $count * $multiplier;
        $time = StopWatch::time();
        for ($i = 0; $i < $count; $i++) {
            $mysqli->query("SELECT VERSION()");
        }

        extraStat('q/s', round($count / (StopWatch::time() - $time)));

        return $i;
    },
    'select_all' => function ($multiplier = 1, $count = 1000) use (&$mysqli) {
        if ($mysqli === null) {
            return INF;
        }

        $count = $count * $multiplier;
        $time = StopWatch::time();
        for ($i = 0; $i < $count; $i++) {
            $mysqli->query("SELECT * FROM `test`");
        }
        extraStat('q/s', round($count / (StopWatch::time() - $time)));
        return $i;
    },
    'select_cursor' => function ($multiplier = 1, $count = 1000) use (&$mysqli) {
        if ($mysqli === null) {
            return INF;
        }

        $count = $count * $multiplier;
        for ($i = 0; $i < $count; $i++) {
            $result = $mysqli->query("SELECT * FROM `test`");
            while ($row = $result->fetch_assoc()) {
            }
            $result->close();
        }
        return $i;
    },
    'seq_insert' => function ($multiplier = 1, $count = 1000) use (&$mysqli) {
        if ($mysqli === null) {
            return INF;
        }

        $count = $count * $multiplier;
        $time = StopWatch::time();
        for ($i = 0; $i < $count; $i++) {
            $mysqli->query("INSERT INTO `test` (name,insert_time) VALUES ('test', now())");
        }
        extraStat('q/s', round($count / (StopWatch::time() - $time)));
        return $i;
    },
    'bulk_insert' => function ($multiplier = 1, $count = 100000) use (&$mysqli) {
        if ($mysqli === null) {
            return INF;
        }

        $count = $count * $multiplier;
        $values = [];
        for ($i = 0; $i < $count; $i++) {
            $values[] = "('test$i', now())";
        }
        $mysqli->query("INSERT INTO `test` (name, insert_time) VALUES " . implode(',', $values));
        return $i;
    },
    'update' => function ($multiplier = 1, $count = 1000) use (&$mysqli) {
        if ($mysqli === null) {
            return INF;
        }

        $count = $count * $multiplier;
        $time = StopWatch::time();
        for ($i = 0; $i < $count; $i++) {
            $mysqli->query("UPDATE `test` SET name = 'test' WHERE id = '$i'");
        }
        extraStat('q/s', round($count / (StopWatch::time() - $time)));
        return $i;
    },
    'update_with_index' => function ($multiplier = 1, $count = 1000) use (&$mysqli) {
        if ($mysqli === null) {
            return INF;
        }

        $mysqli->query("CREATE INDEX idx ON `test` (id)");

        $count = $count * $multiplier;
        $time = StopWatch::time();
        for ($i = 0; $i < $count; $i++) {
            $mysqli->query("UPDATE `test` SET name = 'test' WHERE id = '$i'");
        }
        extraStat('q/s', round($count / (StopWatch::time() - $time)));

        $mysqli->query("DROP INDEX idx ON `test`");
        return $i;
    },
    'transaction_insert' => function ($multiplier = 1, $count = 1000) use (&$mysqli) {
        if ($mysqli === null) {
            return INF;
        }

        $count = $count * $multiplier;
        $time = StopWatch::time();
        for ($i = 0; $i < $count; $i++) {
            $mysqli->begin_transaction();
            $mysqli->query("INSERT INTO `test` (name, insert_time) VALUES ('test', now())");
            $mysqli->commit();
        }
        extraStat('t/s', round($count / (StopWatch::time() - $time)));
        return $i;
    },
    'aes_encrypt' => function ($multiplier = 1, $count = 1000) use (&$mysqli) {
        if ($mysqli === null) {
            return INF;
        }

        $stmt = $mysqli->prepare("SELECT AES_ENCRYPT(?, 'key')");
        $stmt->bind_param('s', $data);

        $data = str_repeat('a', 16);
        $count = $count * $multiplier;
        $time = StopWatch::time();
        for ($i = 0; $i < $count; $i++) {
            $stmt->execute();
            $stmt->get_result()->fetch_assoc();
        }
        extraStat('q/s', round($count / (StopWatch::time() - $time)));
        $stmt->close();
        return $i;
    },
    'aes_decrypt' => function ($multiplier = 1, $count = 1000) use (&$mysqli) {
        if ($mysqli === null) {
            return INF;
        }

        $stmt = $mysqli->prepare("SELECT AES_DECRYPT(?, 'key')");
        $stmt->bind_param('s', $data);

        $data = str_repeat('a', 16);
        $count = $count * $multiplier;
        $time = StopWatch::time();
        for ($i = 0; $i < $count; $i++) {
            $stmt->execute();
            $stmt->get_result()->fetch_assoc();
        }
        extraStat('q/s', round($count / (StopWatch::time() - $time)));
        $stmt->close();
        return $i;
    },
    'indexes' => function ($multiplier = 1, $count = 1000) use (&$mysqli) {
        if ($mysqli === null) {
            return INF;
        }

        $mysqli->query("CREATE INDEX idx_name ON `test` (name)");
        $mysqli->query("DROP INDEX idx_name ON `test`");
        return 1;
    },
    'delete' => function ($multiplier = 1, $count = 1000) use (&$mysqli) {
        if ($mysqli === null) {
            return INF;
        }

        $count = $count * $multiplier;
        $time = StopWatch::time();
        for ($i = 0; $i < $count; $i++) {
            $mysqli->query("DELETE FROM `test` WHERE id = '$i'");
        }
        extraStat('q/s', round($count / (StopWatch::time() - $time)));
        return $i;
    },
];