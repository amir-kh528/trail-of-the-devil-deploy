<?php
require __DIR__ . '/config/security.php';
require __DIR__ . '/config/requests.php';

startAppSession();

$requestError = '';
$successMessage = (string) ($_SESSION['project_request_success'] ?? '');
unset($_SESSION['project_request_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $requestError = 'درخواست معتبر نیست. صفحه را تازه‌سازی و دوباره تلاش کنید.';
    } else {
        $result = saveProjectRequest($_POST, true);
        if ($result['success']) {
            $_SESSION['project_request_success'] = $result['message'];
            header('Location: start-project.php', true, 303);
            exit;
        }

        http_response_code($result['status']);
        $requestError = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شروع پروژه جدید | NovaWeb</title>
    <link rel="stylesheet" href="assets/fonts/fonts.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .start-main {
            padding: 4rem 0 5rem;
            min-height: calc(100vh - 78px);
        }

        .start-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .start-header h1 {
            font-size: clamp(2rem, 4vw, 3rem);
            color: var(--dark);
            margin-bottom: 0.75rem;
        }

        .start-header p {
            color: var(--muted);
            font-size: 1.1rem;
            max-width: 560px;
            margin: 0 auto;
        }

        .choice-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.75rem;
            align-items: stretch;
        }

        .choice-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 2.4rem 2rem;
            display: flex;
            flex-direction: column;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .choice-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 50px rgba(76, 70, 150, 0.18);
        }

        .choice-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: rgba(108, 99, 255, 0.12);
            color: var(--primary-dark);
            font-size: 1.8rem;
            margin-bottom: 1.4rem;
        }

        .choice-card h2 {
            color: var(--dark);
            font-size: 1.55rem;
            margin-bottom: 0.9rem;
        }

        .choice-card p {
            color: var(--muted);
            flex-grow: 1;
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .choice-card .primary-btn,
        .choice-card .secondary-btn {
            width: 100%;
            text-align: center;
            cursor: pointer;
        }

        .design-form {
            display: grid;
            gap: 0.85rem;
        }

        .design-form label {
            color: var(--dark);
            font-weight: 700;
        }

        .design-form input,
        .design-form select,
        .design-form textarea {
            width: 100%;
            min-height: 44px;
            padding: 0.75rem 0.9rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            color: var(--text);
            font: inherit;
        }

        .design-form textarea {
            min-height: 120px;
            resize: vertical;
        }

        .design-form .submit-btn {
            width: 100%;
        }

        .request-feedback {
            margin: 0 auto 1.5rem;
            max-width: 760px;
            padding: 0.9rem 1rem;
            border-radius: 12px;
            font-weight: 700;
        }

        .request-feedback.error {
            background: #fff1f2;
            color: #b91c1c;
        }

        .request-feedback.success {
            background: #eafaf1;
            color: #1e8e4f;
        }

        @media (max-width: 768px) {
            .start-main {
                padding: 2.5rem 0 3.5rem;
            }

            .choice-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar container">
            <div class="brand">NovaWeb</div>
        </nav>
    </header>

    <main class="start-main">
        <div class="container">
            <div class="start-header">
                <h1>شروع پروژه جدید</h1>
                <p>مسیر مناسب خودتان را انتخاب کنید تا پروژه را از همان نقطه شروع کنیم.</p>
            </div>

            <?php if ($requestError !== ''): ?>
                <div class="request-feedback error" role="alert"><?php echo escapeHtml($requestError); ?></div>
            <?php elseif ($successMessage !== ''): ?>
                <div class="request-feedback success" role="status"><?php echo escapeHtml($successMessage); ?></div>
            <?php endif; ?>

            <div class="choice-grid">
                <article class="choice-card">
                    <div class="choice-icon">✎</div>
                    <h2>فقط توضیح می‌دم</h2>
                    <p>ترجیح می‌دم نیازم رو بنویسم و شما طراحی و اجرا رو انجام بدید.</p>
                    <a class="primary-btn" href="index.php#contact">ادامه با توضیح متنی</a>
                </article>

                <article class="choice-card">
                    <div class="choice-icon">◈</div>
                    <h2>خودم ظاهر رو مشخص می‌کنم</h2>
                    <p>می‌خوام قالب، رنگ، فونت و سبک دکمه‌ها رو خودم انتخاب کنم.</p>
                    <form method="post" class="design-form">
                        <input type="hidden" name="csrf_token" value="<?php echo escapeHtml(csrfToken()); ?>">
                        <input type="hidden" name="request_type" value="design">
                        <input type="hidden" name="subject" value="درخواست طراحی ظاهری">

                        <label for="design-name">نام درخواست‌دهنده</label>
                        <input id="design-name" name="name" type="text" maxlength="100" autocomplete="name" required>

                        <label for="design-email">ایمیل</label>
                        <input id="design-email" name="email" type="email" maxlength="254" autocomplete="email" required>

                        <label for="design-style">سبک ظاهری</label>
                        <select id="design-style" name="design_style" required>
                            <option value="">انتخاب سبک</option>
                            <option value="minimal">مینیمال</option>
                            <option value="modern">مدرن</option>
                            <option value="formal">رسمی</option>
                            <option value="custom">سفارشی</option>
                        </select>

                        <label for="design-message">توضیحات تکمیلی یا جزئیات سبک سفارشی</label>
                        <textarea id="design-message" name="message" maxlength="5000" required></textarea>

                        <button type="submit" class="submit-btn">ثبت brief طراحی</button>
                    </form>
                </article>
            </div>
        </div>
    </main>
</body>
</html>
