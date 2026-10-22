<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function getAdminCredentials(): ?array
{
    return getAdminCredentialsFromSettings();
}

function adminLoginIdentifier(string $ipAddress): string
{
    return hash('sha256', $ipAddress);
}

function getAdminLoginLock(PDO $pdo, string $identifier): ?int
{
    $statement = $pdo->prepare('SELECT locked_until FROM admin_login_attempts WHERE identifier = ?');
    $statement->execute([$identifier]);
    $lockedUntil = $statement->fetchColumn();
    if (!is_string($lockedUntil)) {
        return null;
    }

    $remainingSeconds = strtotime($lockedUntil) - time();
    return $remainingSeconds > 0 ? $remainingSeconds : null;
}

function recordFailedAdminLogin(PDO $pdo, string $identifier): int
{
    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare('SELECT attempts, window_started_at FROM admin_login_attempts WHERE identifier = ? FOR UPDATE');
        $statement->execute([$identifier]);
        $attempt = $statement->fetch();

        $now = time();
        if (!$attempt || ($now - strtotime($attempt['window_started_at'])) >= 900) {
            $attemptCount = 1;
            $windowStartedAt = date('Y-m-d H:i:s', $now);
        } else {
            $attemptCount = (int) $attempt['attempts'] + 1;
            $windowStartedAt = $attempt['window_started_at'];
        }

        $lockedUntil = $attemptCount >= 5 ? date('Y-m-d H:i:s', $now + 900) : null;
        $upsert = $pdo->prepare(
            'INSERT INTO admin_login_attempts (identifier, attempts, window_started_at, locked_until)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE attempts = VALUES(attempts), window_started_at = VALUES(window_started_at), locked_until = VALUES(locked_until)'
        );
        $upsert->execute([$identifier, $attemptCount, $windowStartedAt, $lockedUntil]);
        $pdo->commit();

        return $attemptCount;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

function clearAdminLoginFailures(PDO $pdo, string $identifier): void
{
    $statement = $pdo->prepare('DELETE FROM admin_login_attempts WHERE identifier = ?');
    $statement->execute([$identifier]);
}
