<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subject VARCHAR(180) NOT NULL DEFAULT \'\',
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            is_responded TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $existingColumns = $pdo->query('SHOW COLUMNS FROM contact_messages')->fetchAll(PDO::FETCH_COLUMN);
    $columnsToAdd = [
        'subject' => "VARCHAR(180) NOT NULL DEFAULT ''",
        'is_read' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'is_responded' => 'TINYINT(1) NOT NULL DEFAULT 0',
    ];

    foreach ($columnsToAdd as $column => $definition) {
        if (in_array($column, $existingColumns, true)) {
            continue;
        }

        try {
            $pdo->exec('ALTER TABLE contact_messages ADD COLUMN ' . $column . ' ' . $definition);
        } catch (PDOException $exception) {
            $mysqlErrorCode = (int) ($exception->errorInfo[1] ?? 0);
            if ($mysqlErrorCode !== 1060) {
                throw $exception;
            }
        }
    }
};
