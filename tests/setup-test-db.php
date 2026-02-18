<?php

/**
 * Setup script to create test database
 * Run: php tests/setup-test-db.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$connection = config('database.default');
$database = config('database.connections.mysql.database');

if ($connection === 'mysql') {
    $host = config('database.connections.mysql.host', '127.0.0.1');
    $port = config('database.connections.mysql.port', '3306');
    $username = config('database.connections.mysql.username', 'root');
    $password = config('database.connections.mysql.password', '');

    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port}",
            $username,
            $password
        );

        $testDb = 'crypto_testing';
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$testDb}`");
        echo "✓ Test database '{$testDb}' created successfully!\n";
    } catch (PDOException $e) {
        echo '✗ Error creating test database: '.$e->getMessage()."\n";
        echo "Please create the database manually:\n";
        echo "CREATE DATABASE crypto_testing;\n";
        exit(1);
    }
} else {
    echo "Current database connection is: {$connection}\n";
    echo "Please update phpunit.xml to use the correct database driver.\n";
}
