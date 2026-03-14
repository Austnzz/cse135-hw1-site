<?php

class Database
{
    public static function connect(): PDO
    {
        $config = require __DIR__ . '/../../config/app.php';
        $db = $config['db'];

        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $db['host'],
            $db['port'],
            $db['dbname']
        );

        return new PDO(
            $dsn,
            $db['user'],
            $db['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
}