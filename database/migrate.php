<?php

declare(strict_types=1);

require __DIR__ . '/../config/db.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$config = getDatabaseConfig();
if ($config['environment'] === 'production' && !in_array('--backup-confirmed', $argv, true)) {
    fwrite(STDERR, "Production migration stopped. Create and verify a database backup, then rerun with --backup-confirmed.\n");
    exit(1);
}

try {
    if (in_array('--create-database', $argv, true)) {
        if ($config['environment'] === 'production') {
            throw new RuntimeException('Database creation is disabled in production. Provision the database separately.');
        }

        $server = new PDO(
            'mysql:host=' . $config['host'] . ';charset=utf8mb4',
            $config['user'],
            $config['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $server->exec(
            'CREATE DATABASE IF NOT EXISTS `' . $config['name'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );
    }

    $pdo = getDb();
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS schema_migrations (
            version VARCHAR(190) PRIMARY KEY,
            applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $lock = $pdo->query("SELECT GET_LOCK('trail_of_the_devil_schema_migrations', 30)")->fetchColumn();
    if ((int) $lock !== 1) {
        throw new RuntimeException('Could not acquire database migration lock.');
    }

    try {
        $applied = $pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
        $migrationFiles = glob(__DIR__ . '/migrations/*.php') ?: [];
        sort($migrationFiles, SORT_STRING);

        foreach ($migrationFiles as $migrationFile) {
            $version = basename($migrationFile);
            if (in_array($version, $applied, true)) {
                continue;
            }

            $migration = require $migrationFile;
            if (!is_callable($migration)) {
                throw new RuntimeException('Migration must return a callable: ' . $version);
            }

            $migration($pdo);
            $statement = $pdo->prepare('INSERT INTO schema_migrations (version) VALUES (?)');
            $statement->execute([$version]);
            fwrite(STDOUT, 'Applied migration: ' . $version . PHP_EOL);
        }
    } finally {
        $pdo->query("SELECT RELEASE_LOCK('trail_of_the_devil_schema_migrations')");
    }
} catch (Throwable $exception) {
    error_log('Database migration failure: ' . $exception->getMessage());
    fwrite(STDERR, "Database migration failed. Review the server error log for details. No cleanup or record deletion is performed by migrations.\n");
    exit(1);
}
