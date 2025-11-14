/**
 * Modern Login Form JavaScript
 */

// Password toggle functionality
function togglePassword(fieldId) {
  const input = document.getElementById(fieldId);
  const icon = document.getElementById(fieldId + "-icon");

  if (input.type === "password") {
    input.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  } else {
    input.type = "password";
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  }
}

document.addEventListener("DOMContentLoaded", function () {
  // Form validation helper
  function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }

  // Add smooth animations to form elements
  const formInputs = document.querySelectorAll(".input, .form-control");

  formInputs.forEach(function (input) {
    input.addEventListener("focus", function () {
      const wrapper =
        this.closest(".input-wrapper") || this.closest(".inputForm");
      if (wrapper) {
        wrapper.style.transform = "scale(1.02)";
      }
    });

    input.addEventListener("blur", function () {
      const wrapper =
        this.closest(".input-wrapper") || this.closest(".inputForm");
      if (wrapper) {
        wrapper.style.transform = "scale(1)";
      }
    });
  });

  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach(function (alert) {
    setTimeout(function () {
      alert.style.opacity = "0";
      setTimeout(function () {
        alert.style.display = "none";
      }, 300);
    }, 5000);
  });
});
