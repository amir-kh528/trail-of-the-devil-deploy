<?php

declare(strict_types=1);

/**
 * فایل نمونه تنظیمات (Settings Example)
 *
 * این فایل فقط ساختار و توضیحات تنظیمات حساس را نشان می‌دهد و حاوی هیچ
 * رمز یا اطلاعات واقعی نیست. برای استفاده، محتوای این فایل را در
 * config/settings.php کپی کنید و مقادیر واقعی را قرار دهید.
 *
 * فایل config/settings.php در .gitignore قرار دارد و هرگز در کنترل نسخه
 * ثبت نمی‌شود.
 */

return [
    // === تنظیمات محیطی ===
    // APP_ENV: محیط اجرا (development برای توسعه، production برای تولید)
    'APP_ENV' => 'development',

    // === تنظیمات پایگاه داده ===
    // DB_HOST: نام میزبان یا IP سرور پایگاه داده
    'DB_HOST' => 'localhost',
    // DB_NAME: نام پایگاه داده
    'DB_NAME' => 'your_database_name',
    // DB_USER: نام کاربری پایگاه داده
    'DB_USER' => 'your_database_user',
    // DB_PASS: رمز عبور پایگاه داده (حساس)
    'DB_PASS' => 'your_database_password',

    // === تنظیمات ورود به پنل مدیریت ===
    // ADMIN_USERNAME: نام کاربری پنل مدیریت (حساس)
    'ADMIN_USERNAME' => 'your_admin_username',
    // ADMIN_PASSWORD_HASH: رمز عبور هش شده (با password_hash در PHP)
    'ADMIN_PASSWORD_HASH' => 'your_bcrypt_password_hash',
];