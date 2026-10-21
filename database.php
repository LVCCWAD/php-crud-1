<?php
// This file only connects PHP to the SQLite database.
try {
    $databasePath = __DIR__ . '/school.db';
    $pdo = new PDO('sqlite:' . $databasePath);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // SQLite must enable foreign keys for each connection.
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit('Database connection failed. Check that PDO SQLite is enabled and the project folder is writable.');
}
