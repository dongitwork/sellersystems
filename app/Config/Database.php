<?php
namespace App\Config;

use Medoo\Medoo;

class Database {
    private static ?Medoo $instance = null;
    
    public static function getInstance(): Medoo {
        if (self::$instance === null) {
            self::$instance = new Medoo([
                'type' => 'mysql',
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'database' => $_ENV['DB_DATABASE'] ?? 'user_management',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'port' => $_ENV['DB_PORT'] ?? 3306,
            ]);
        }
        return self::$instance;
    }
}