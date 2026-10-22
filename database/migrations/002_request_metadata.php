<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $existingColumns = $pdo->query('SHOW COLUMNS FROM contact_messages')->fetchAll(PDO::FETCH_COLUMN);
    $columnsToAdd = [
        'request_type' => "VARCHAR(20) NOT NULL DEFAULT 'contact'",
        'design_style' => "VARCHAR(30) NOT NULL DEFAULT ''",
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
