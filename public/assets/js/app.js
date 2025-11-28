// app.js - JavaScript tùy chỉnh chính

// Không dùng DOMContentLoaded nữa vì đã có inline script trong header

// Close dropdowns when clicking outside (sử dụng checkbox system)
document.addEventListener("click", function (event) {
  // Nếu click outside menu items, uncheck các checkbox toggle
  if (
    !event.target.closest(".menu-item-has-children") &&
    !event.target.closest(".menu-label")
  ) {
    document
      .querySelectorAll(".menu-toggle:checked")
      .forEach(function (checkbox) {
        // Chỉ uncheck nếu menu không active
        const menuItem = checkbox.closest(".menu-item-has-children");
        if (menuItem && !menuItem.classList.contains("active")) {
          checkbox.checked = false;
        }
      });
  }
});

// TODO: Form validation
// TODO: AJAX requests
// TODO: UI interactions
