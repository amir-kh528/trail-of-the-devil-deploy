<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/config/security.php';
require __DIR__ . '/config/requests.php';

startAppSession();
$contactSuccessMessage = (string) ($_SESSION['contact_success'] ?? '');
unset($_SESSION['contact_success']);

function isAjaxRequest(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $requestResult = [
            'success' => false,
            'status' => 403,
            'message' => 'درخواست معتبر نیست. صفحه را تازه‌سازی و دوباره تلاش کنید.',
        ];
    } else {
        $requestResult = saveProjectRequest($_POST);
    }

    if (isAjaxRequest()) {
        http_response_code($requestResult['status']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($requestResult, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($requestResult['success']) {
        $_SESSION['contact_success'] = $requestResult['message'];
        header('Location: ' . $_SERVER['PHP_SELF'] . '#contact', true, 303);
        exit;
    }

    http_response_code($requestResult['status']);
    $errorMessage = $requestResult['message'];
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سایت نمونه</title>
    <link rel="stylesheet" href="assets/fonts/fonts.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar container">
            <div class="brand">NovaWeb</div>
            <div class="nav-links">
                <a href="#home">خانه</a>
                <a href="#about">درباره ما</a>
                <a href="#services">خدمات</a>
                <a href="#contact">تماس</a>
            </div>
            <a href="start-project.php" class="nav-button">شروع پروژه</a>
        </nav>
    </header>

    <main>
        <section id="home" class="hero container">
            <div class="hero-text">
                <p class="eyebrow">طراحی حرفه‌ای و سریع</p>
                <h1>وب‌سایتی ساده، زیبا و حرفه‌ای بسازید</h1>
                <p class="subtitle">
                    با استفاده از HTML، CSS، JavaScript و PHP یک تجربه وب‌سایت دلنشین و کاربردی داشته باشید.
                </p>
                <div class="hero-actions">
                    <a href="#contact" class="primary-btn">تماس با ما</a>
                    <a href="#services" class="secondary-btn">مشاهده خدمات</a>
                </div>
                <div class="stats">
                    <div>
                        <strong>120+</strong>
                        <span>پروژه موفق</span>
                    </div>
                    <div>
                        <strong>4.9</strong>
                        <span>امتیاز مشتریان</span>
                    </div>
                    <div>
                        <strong>24/7</strong>
                        <span>پشتیبانی</span>
                    </div>
                </div>
            </div>
            <div class="hero-card">
                <div class="card-top">
                    <span class="dot green"></span>
                    <span class="dot yellow"></span>
                    <span class="dot red"></span>
                </div>
                <div class="preview-box">
                    <div class="mini-card">
                        <span>درآمد ماهانه</span>
                        <strong>+35%</strong>
                    </div>
                    <div class="chart-lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="about container">
            <div class="section-header">
                <p>درباره ما</p>
                <h2>راه‌حل‌های دیجیتال برای رشد کسب‌وکار شما</h2>
            </div>
            <div class="about-grid">
                <div class="about-box">
                    <h3>طراحی پاسخ‌گو</h3>
                    <p>نمایش مناسب در موبایل، تبلت و دسکتاپ با تجربه کاربری عالی.</p>
                </div>
                <div class="about-box">
                    <h3>سرعت و عملکرد</h3>
                    <p>بهینه‌سازی ساختار صفحه برای بارگذاری سریع‌تر و استفاده‌پذیری بهتر.</p>
                </div>
                <div class="about-box">
                    <h3>گروه حرفه‌ای</h3>
                    <p>تیم ما با تمرکز بر نیاز مشتری، راهکارهای دقیق و کاربردی ارائه می‌دهد.</p>
                </div>
            </div>
        </section>

        <section id="services" class="services container">
            <div class="section-header">
                <p>خدمات ما</p>
                <h2>از ایده تا اجرا</h2>
            </div>
            <div class="service-grid">
                <article class="service-item">
                    <span class="icon">🌐</span>
                    <h3>طراحی سایت</h3>
                    <p>ساخت صفحات حرفه‌ای و جذاب برای برند شما.</p>
                </article>
                <article class="service-item">
                    <span class="icon">⚙️</span>
                    <h3>توسعه Front-End</h3>
                    <p>پیاده‌سازی رابط کاربری با JavaScript و CSS مدرن.</p>
                </article>
                <article class="service-item">
                    <span class="icon">🧩</span>
                    <h3>PHP و فرم‌ها</h3>
                    <p>ایجاد فرم‌های تماس و پردازش اطلاعات سمت سرور.</p>
                </article>
            </div>
        </section>

        <section id="contact" class="contact container">
            <div class="contact-info">
                <p>ارتباط با ما</p>
                <h2>درخواست پروژه‌تان را ثبت کنید</h2>
                <p>برای شروع پروژه، موضوع و توضیحات درخواست را ثبت کنید تا در سریع‌ترین زمان با شما تماس بگیریم.</p>
            </div>

            <form id="contactForm" class="contact-form" method="post" novalidate>
                <div id="formStatus" class="status-message" aria-live="polite" hidden></div>
                <?php if ($contactSuccessMessage !== ''): ?>
                    <div class="status-message success-message visible" role="status"><?php echo escapeHtml($contactSuccessMessage); ?></div>
                <?php endif; ?>
                <?php if (!empty($errorMessage)): ?>
                    <div class="status-message error-message visible" role="alert"><?php echo escapeHtml($errorMessage); ?></div>
                <?php endif; ?>
                <input type="hidden" name="csrf_token" value="<?php echo escapeHtml(csrfToken()); ?>">
                <input type="hidden" name="request_type" value="contact">

                <div class="form-group">
                    <label for="subject">موضوع درخواست</label>
                    <input type="text" id="subject" name="subject" maxlength="180" placeholder="برای مثال: طراحی سایت فروشگاهی" required>
                </div>

                <div class="form-group">
                    <label for="name">نام شما</label>
                    <input type="text" id="name" name="name" maxlength="100" placeholder="نام کامل" required>
                </div>

                <div class="form-group">
                    <label for="email">ایمیل</label>
                    <input type="email" id="email" name="email" maxlength="254" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label for="message">پیام شما</label>
                    <textarea id="message" name="message" rows="5" maxlength="5000" placeholder="توضیحات پروژه خود را بنویسید" required></textarea>
                </div>

                <button type="submit" class="submit-btn">ارسال پیام</button>
            </form>
        </section>
    </main>

    <footer class="footer">
        <div class="container footer-content">
            <p>© <span id="year"></span> NovaWeb. همه حقوق محفوظ است.</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>
