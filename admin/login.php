<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../config/security.php';
require __DIR__ . '/../config/admin_auth.php';

startAppSession();

if (isset($_POST['logout']) && (string) $_POST['logout'] === '1') {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('درخواست معتبر نیست.');
    }

    destroyAppSession();
    header('Location: login.php');
    exit;
}

if (($_SESSION['admin_logged_in'] ?? false) === true) {
    header('Location: view_messages.php');
    exit;
}

$locked = false;
$credentials = getAdminCredentials();
$error = $credentials === null ? 'ورود مدیر هنوز در تنظیمات سرور پیکربندی نشده است.' : '';
if ($credentials === null) {
    http_response_code(503);
}
$loginIp = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$loginIdentifier = adminLoginIdentifier($loginIp);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['logout'])) {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'درخواست معتبر نیست. صفحه را تازه‌سازی و دوباره تلاش کنید.';
    } elseif ($credentials === null) {
        http_response_code(503);
        $error = 'ورود مدیر هنوز در تنظیمات سرور پیکربندی نشده است.';
    } else {
        try {
            $pdo = getDb();
            $remainingLock = getAdminLoginLock($pdo, $loginIdentifier);
            if ($remainingLock !== null) {
                http_response_code(429);
                $locked = true;
                $error = 'تلاش‌های ورود بیش از حد مجاز بوده است. کمی بعد دوباره تلاش کنید.';
            } else {
                $username = trim((string) ($_POST['username'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                if (hash_equals($credentials['username'], $username)
                    && password_verify($password, $credentials['password_hash'])) {
                    clearAdminLoginFailures($pdo, $loginIdentifier);
                    session_regenerate_id(true);
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username'] = $username;
                    unset($_SESSION['csrf_token']);
                    csrfToken();
                    header('Location: view_messages.php');
                    exit;
                }

                recordFailedAdminLogin($pdo, $loginIdentifier);
                $error = 'نام کاربری یا رمز عبور اشتباه است.';
            }
        } catch (Throwable $exception) {
            error_log('Admin login error: ' . $exception->getMessage());
            http_response_code(503);
            $error = 'ورود موقتاً در دسترس نیست. بعداً دوباره تلاش کنید.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت</title>
    <link rel="stylesheet" href="../assets/fonts/fonts.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="login-page-body">
    <div class="login-shell">
        <div class="login-card">
            <div class="login-header">
                <span class="login-badge">پنل مدیریت</span>
                <h1>ورود به حساب کاربری</h1>
            </div>

            <?php if ($error !== ''): ?>
                <div class="status show error" aria-live="polite"><?php echo escapeHtml($error); ?></div>
            <?php endif; ?>

            <form method="post" class="login-form">
                <input type="hidden" name="csrf_token" value="<?php echo escapeHtml(csrfToken()); ?>">
                <label class="field-label" for="username">نام کاربری</label>
                <input id="username" name="username" type="text" placeholder="نام کاربری" autocomplete="username" required <?php echo $credentials === null || $locked ? 'disabled' : ''; ?>>

                <label class="field-label" for="password">رمز عبور</label>
                <input id="password" name="password" type="password" placeholder="رمز عبور" autocomplete="current-password" required <?php echo $credentials === null || $locked ? 'disabled' : ''; ?>>

                <button type="submit" class="btn primary login-btn">ورود</button>
            </form>
        </div>
    </div>
</body>
</html>
