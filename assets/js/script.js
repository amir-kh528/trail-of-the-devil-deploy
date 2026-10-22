document.addEventListener("DOMContentLoaded", () => {
  const yearElement = document.getElementById("year");
  if (yearElement) {
    yearElement.textContent = new Date().getFullYear();
  }

  const form = document.getElementById("contactForm");
  const statusBox = document.getElementById("formStatus");

  if (form && statusBox) {
    form.addEventListener("submit", async (event) => {
      event.preventDefault();

      const submitButton = form.querySelector("button[type='submit']");
      const formData = new FormData(form);

      if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = "در حال ارسال...";
      }

      statusBox.textContent = "";
      statusBox.classList.remove("success-message", "error-message", "visible");
      statusBox.hidden = true;

      try {
        const response = await fetch(
          window.location.pathname + window.location.search,
          {
            method: "POST",
            body: formData,
            headers: {
              "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "same-origin",
          },
        );

        const contentType = response.headers.get("content-type") || "";

        if (!contentType.includes("application/json")) {
          const rawText = await response.text();
          console.error("Server did not return JSON. Raw response:", rawText);
          throw new Error(
            "پاسخ سرور معتبر نیست (غیر JSON). لطفاً با Administrator تماس بگیرید."
          );
        }

        const result = await response.json();

        if (!response.ok) {
          throw new Error(result.message || `خطای سرور: ${response.status}`);
        }

        statusBox.textContent = result.message || "در حال ارسال...";
        statusBox.classList.remove("success-message", "error-message");
        statusBox.classList.add(
          result.success ? "success-message" : "error-message",
        );
        statusBox.hidden = false;
        statusBox.classList.add("visible");

        if (result.success) {
          form.reset();
        }
      } catch (error) {
        statusBox.textContent = error.message || "خطا در ارسال پیام. لطفاً دوباره تلاش کنید.";
        statusBox.classList.remove("success-message");
        statusBox.classList.add("error-message");
        statusBox.hidden = false;
        statusBox.classList.add("visible");
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.textContent = "ارسال پیام";
        }
      }
    });
  }
});
