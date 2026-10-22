// ⚠️ توجه: درخواست‌های POST از طریق fetch/AJAX روی هاست InfinityFree (OpenResty)
// با خطای 403 Forbidden مسدود می‌شدند. فرم عادی HTML بدون مشکل کار می‌کند.
// برای همین منطق AJAX فرم تماس حذف شده و فقط حالت لودینگ دکمه باقی مانده است.
// این محدودیت مربوط به هاست رایگان است، نه باگ کد. اگر بعداً بخواهید AJAX برگردانید,
// مشکل از سمت سرور/هاستینگ است نه از کد.
// ------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
  const yearElement = document.getElementById("year");
  if (yearElement) {
    yearElement.textContent = new Date().getFullYear();
  }

  const form = document.getElementById("contactForm");
  if (form) {
    form.addEventListener("submit", () => {
      const btn = form.querySelector("button[type='submit']");
      if (btn) {
        btn.disabled = true;
        btn.textContent = "در حال ارسال...";
      }
    });
  }
});
