<?php

http_response_code(404);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحه پیدا نشد | NovaWeb</title>
    <link rel="stylesheet" href="assets/fonts/fonts.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .not-found {
            min-height: 80vh;
            display: grid;
            place-content: center;
            justify-items: center;
            gap: 1rem;
            padding: 2rem;
            text-align: center;
        }

        .not-found-code {
            color: var(--primary-dark);
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
        }

        .not-found p {
            color: var(--muted);
        }
    </style>
</head>
<body>
    <main class="not-found">
        <strong class="not-found-code">404</strong>
        <h1>صفحه پیدا نشد</h1>
        <p>نشانی واردشده معتبر نیست یا این صفحه وجود ندارد.</p>
        <a class="primary-btn" href="index.php">بازگشت به صفحه اصلی</a>
    </main>
</body>
</html>
