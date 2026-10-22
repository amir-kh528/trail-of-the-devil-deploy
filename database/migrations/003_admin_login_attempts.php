<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS admin_login_attempts (
            identifier CHAR(64) PRIMARY KEY,
            attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            window_started_at DATETIME NOT NULL,
            locked_until DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
};
