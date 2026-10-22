<?php

declare(strict_types=1);

/**
 * فایل پیکربندی مرکزی پروژه (Central Configuration)
 *
 * این فایل مسئول بارگذاری تمام تنظیمات حساس و Nãoحساس است.
 * هرಸنگی تنظیمات حساس (رمزهای عبور، اطلاعات پایگاه داده و ...) را از فایل
 * خصوصی config/settings.php می‌خواند و در صورت عدم دسترسی به آن، از
 * متغیرهای محیطی (environment variables) Fallback می‌گیرد.
 *
 * فایل config/settings.php نباید در کنترل نسخه Commit شود
 * (در فایل .gitignore حذف/nekonfalde شده است). برای تعاریف اولیه می‌توان
 * از فایل config/settings.example.php به عنوان الگو استفاده کرد.
 */

/**
 * بارگذاری تنظیمات از فایل خصوصی settings.php اگر وجود داشته باشد.
 *
 * @return array<string, mixed> آرایه‌ای از تنظیمات (შ Flags)
 */
function loadSettingsFromFile(): array
{
    $settingsFile = __DIR__ . '/settings.php';

    if (!file_exists($settingsFile) || !is_readable($settingsFile)) {
        return [];
    }

    $settings = require $settingsFile;

    if (!is_array($settings)) {
        return [];
    }

    return $settings;
}

/**
 * دریافت یک مقدار تنظیم با اولویت:
 * 1) فایل config/settings.php
 * 2) متغیر محیطی (getenv)
 * 3) مقدار پیش‌فرض (اختیاری)
 *
 * @param string $key        کلید تنظیم (مثلاً DB_HOST، ADMIN_USERNAME)
 * @param mixed  $default    مقدار پیش‌فرض در صورت عدم یافتن
 * @return mixed
 */
function getConfig(string $key, mixed $default = null): mixed
{
    static $settings = null;

    if ($settings === null) {
        $settings = loadSettingsFromFile();
    }

    // اولویت اول: فایل تنظیمات خصوصی
    if (array_key_exists($key, $settings) && $settings[$key] !== null && $settings[$key] !== '') {
        return $settings[$key];
    }

    // اولویت دوم: متغیر محیطی
    $envValue = getenv($key);
    if ($envValue !== false && $envValue !== '') {
        return $envValue;
    }

    return $default;
}

/**
 * بازیابی تمام پیکربندی پایگاه داده به صورت آرایه واحد.
 *
 * @return array{
 *     environment: string,
 *     host: string,
 *     name: string,
 *     user: string,
 *     password: string,
 * }
 */
function getDatabaseConfigFromSettings(): array
{
    $environment = (string) getConfig('APP_ENV', 'development');
    $host = (string) getConfig('DB_HOST', 'localhost');
    $dbName = (string) getConfig('DB_NAME', 'test_project_db');
    $dbUser = (string) getConfig('DB_USER', '');
    $dbPass = (string) getConfig('DB_PASS', '');

    // در محیط تولید (production) الزام است مقادیر حساس تنظیم شده باشند
    if ($environment === 'production') {
        if ($host === '' || $dbName === '' || $dbUser === '' || $dbUser === 'root' || $dbPass === '') {
            throw new RuntimeException('Production database credentials are not configured safely.');
        }
    } else {
        // مقادیر پیش‌فرض برای محیط توسعه
        if ($dbUser === '') {
            $dbUser = 'root';
        }
        if ($dbPass === '') {
            $dbPass = '';
        }
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbName)) {
        throw new RuntimeException('Database name contains unsupported characters.');
    }

    return [
        'environment' => $environment,
        'host' => $host,
        'name' => $dbName,
        'user' => $dbUser,
        'password' => $dbPass,
    ];
}

/**
 * بازیابی اعتبار ورود پنل مدیریت از تنظیمات.
 *
 * @return array{username: string, password_hash: string}|null
 */
function getAdminCredentialsFromSettings(): ?array
{
    $username = (string) getConfig('ADMIN_USERNAME', '');
    $passwordHash = (string) getConfig('ADMIN_PASSWORD_HASH', '');

    if ($username === '' || $passwordHash === '') {
        return null;
    }

    if (password_get_info($passwordHash)['algo'] === null) {
        return null;
    }

    return ['username' => $username, 'password_hash' => $passwordHash];
}