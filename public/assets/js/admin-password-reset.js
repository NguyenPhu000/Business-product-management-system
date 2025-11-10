/**
 * Admin Password Reset Management
 * Xử lý các thao tác quản lý yêu cầu đặt lại mật khẩu
 */

// Global state
let lastPendingCount = 0;
let currentRequestIds = [];

/**
 * Khởi tạo module
 */
function initPasswordResetModule() {
  const container = document.getElementById("password-reset-container");
  if (!container) return;

  // Lấy data từ attributes
  lastPendingCount = parseInt(container.dataset.pendingCount || "0");
  const requestIdsStr = container.dataset.requestIds || "";
  currentRequestIds = requestIdsStr
    ? requestIdsStr.split(",").map((id) => parseInt(id))
    : [];

  // Bắt đầu polling
  setTimeout(updatePendingCount, 1000);
  setTimeout(checkCancelledRequests, 1000);
  setInterval(updatePendingCount, 3000);
  setInterval(checkCancelledRequests, 2000);

  console.log("Password Reset Module initialized");
}

/**
 * Phê duyệt yêu cầu
 */
function approveRequest(id) {
  if (
    !confirm(
      'Bạn có chắc muốn phê duyệt yêu cầu này?\n\nSau khi phê duyệt, người dùng có thể vào trang "Quên mật khẩu" để tự đổi mật khẩu mới.'
    )
  ) {
    return;
  }

  fetch(`/admin/password-reset/approve/${id}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(data.message);

        // Cập nhật trạng thái trong bảng thành "Đã phê duyệt"
        const row = document.getElementById(`request-${id}`);
        if (row) {
          row.querySelector("td:nth-child(5)").innerHTML =
            '<span class="badge badge-success"><i class="fas fa-check"></i> Đã phê duyệt</span>';
          row.querySelector("td:nth-child(7)").textContent = "Vừa xong";
          row.querySelector("td:nth-child(8)").innerHTML =
            '<span class="badge badge-secondary">Đã xử lý</span>';

          // Sau 1 giây, tự động đánh dấu hoàn tất trong database
          setTimeout(() => {
            fetch(`/admin/password-reset/mark-completed/${id}`, {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
            })
              .then((response) => response.json())
              .then((markData) => {
                if (markData.success) {
                  console.log("Đã tự động đánh dấu hoàn tất sau 1 giây");
                  // Reload để cập nhật UI
                  location.reload();
                } else {
                  console.error("Lỗi khi đánh dấu hoàn tất:", markData.message);
                }
              })
              .catch((error) => {
                console.error("Lỗi khi gọi API mark-completed:", error);
              });
          }, 1000);
        }

        // Cập nhật số lượng pending
        updatePendingCount();
      } else {
        alert(data.message || "Có lỗi xảy ra!");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Có lỗi xảy ra khi phê duyệt!");
    });
}
/**
 * Từ chối yêu cầu
 */
function rejectRequest(id) {
  if (!confirm("Bạn có chắc muốn từ chối yêu cầu này?")) {
    return;
  }

  fetch(`/admin/password-reset/reject/${id}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(data.message + "\n\nYêu cầu sẽ tự động bị xóa sau 10 giây.");

        // Cập nhật trạng thái trong bảng
        const row = document.getElementById(`request-${id}`);
        if (row) {
          row.querySelector("td:nth-child(5)").innerHTML =
            '<span class="badge badge-danger"><i class="fas fa-times"></i> Đã từ chối</span>';
          row.querySelector("td:nth-child(7)").textContent = "Vừa xong";
          row.querySelector("td:nth-child(8)").innerHTML =
            '<span class="text-muted">Đang xử lý...</span>';
        }

        // Tự động xóa request và reload sau 10 giây
        setTimeout(() => {
          fetch("/admin/password-reset/delete/" + id, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
          })
            .then((response) => response.json())
            .then((deleteData) => {
              console.log("Đã xóa request:", deleteData);
              location.reload();
            })
            .catch((error) => {
              console.error("Lỗi khi xóa:", error);
              location.reload();
            });
        }, 10000);

        // Cập nhật số lượng pending
        updatePendingCount();
      } else {
        alert(data.message || "Có lỗi xảy ra!");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Có lỗi xảy ra khi từ chối!");
    });
}

/**
 * Xóa request
 */
function deleteRequest(id) {
  if (
    !confirm(
      "Bạn có chắc muốn XÓA VĨNH VIỄN request này?\n\nHành động này không thể hoàn tác!"
    )
  ) {
    return;
  }

  fetch("/admin/password-reset/delete/" + id, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(data.message);

        // Xóa hàng khỏi bảng
        const row = document.getElementById(`request-${id}`);
        if (row) {
          row.remove();
        }

        // Reload trang nếu không còn request nào
        const tbody = document.querySelector("table tbody");
        if (tbody && tbody.children.length === 0) {
          location.reload();
        }

        // Cập nhật số lượng pending
        updatePendingCount();
      } else {
        alert(data.message || "Có lỗi xảy ra!");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Có lỗi xảy ra khi xóa!");
    });
}

/**
 * Kiểm tra các request bị cancelled
 */
function checkCancelledRequests() {
  fetch("/admin/password-reset/check-cancelled")
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.cancelledIds && data.cancelledIds.length > 0) {
        console.log("Phát hiện request bị hủy:", data.cancelledIds);

        // Cập nhật UI cho các row đã tồn tại
        data.cancelledIds.forEach((cancelledId) => {
          const row = document.getElementById(`request-${cancelledId}`);
          if (row) {
            // Cập nhật trạng thái
            row.querySelector("td:nth-child(5)").innerHTML =
              '<span class="badge badge-warning"><i class="fas fa-ban"></i> Đã hủy</span>';
            row.querySelector("td:nth-child(8)").innerHTML =
              '<span class="text-muted">Người dùng đã hủy yêu cầu</span>';

            // Highlight row
            row.style.backgroundColor = "#fff3cd";
          }
        });

        // Hiển thị thông báo nhẹ nhàng (toast)
        const alertDiv = document.createElement("div");
        alertDiv.className = "alert alert-warning alert-dismissible fade show";
        alertDiv.style.cssText =
          "position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 350px;";
        alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i> ${data.cancelledIds.length} yêu cầu đã bị hủy bởi người dùng!
                    <br><small></small>
                    <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
                `;
        document.body.appendChild(alertDiv);

        // Sau 10 giây, xóa các request ở phía server
        setTimeout(() => {
          console.log("Đang xóa các request bị hủy...");

          const deletePromises = data.cancelledIds.map((id) => {
            return fetch("/admin/password-reset/delete/" + id, {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
            })
              .then((res) => res.json())
              .catch((err) => ({ success: false }));
          });

          Promise.all(deletePromises)
            .then((results) => {
              // Xoá row khỏi bảng
              data.cancelledIds.forEach((cancelledId) => {
                const row = document.getElementById(`request-${cancelledId}`);
                if (row) {
                  row.remove();
                }
              });

              // Cập nhật số lượng pending
              updatePendingCount();

              // Hiển thị empty state nếu không còn request
              const tbody = document.querySelector("table tbody");
              const cardBody = document.querySelector(".card .card-body");
              if (tbody && tbody.children.length === 0 && cardBody) {
                cardBody.innerHTML = `
                                    <div class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Chưa có yêu cầu nào</p>
                                    </div>`;
              }

              // Ẩn toast
              setTimeout(() => {
                alertDiv.remove();
              }, 4000);
            })
            .catch((error) => {
              console.error("Lỗi khi xóa các request bị hủy:", error);
              updatePendingCount();
              setTimeout(() => {
                alertDiv.remove();
              }, 4000);
            });
        }, 10000);
      }
    })
    .catch((error) => {
      console.error("Error checking cancelled requests:", error);
    });
}

/**
 * Cập nhật số lượng pending và phát hiện silent-cancel
 */
function updatePendingCount() {
  fetch("/admin/password-reset/check-new")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const newCount = data.pendingCount;
        const serverRequestIds = data.requests
          ? data.requests.map((r) => r.id)
          : [];

        // Cập nhật badge số lượng
        const badge = document.querySelector(".pending-badge");
        if (badge) {
          badge.innerHTML = `<i class="fas fa-clock"></i> ${newCount} yêu cầu đang chờ`;
        }

        // Kiểm tra các request đã bị xóa (silent cancel)
        const currentPendingRows =
          document.querySelectorAll('tr[id^="request-"]');
        currentPendingRows.forEach((row) => {
          const requestId = parseInt(row.id.replace("request-", ""));
          const statusBadge = row.querySelector("td:nth-child(5) .badge");

          // Chỉ kiểm tra các request đang pending
          if (
            statusBadge &&
            statusBadge.classList.contains("badge-warning") &&
            statusBadge.textContent.includes("Đang chờ")
          ) {
            // Nếu request pending không còn trong danh sách server
            if (!serverRequestIds.includes(requestId)) {
              console.log("Phát hiện request bị silent-cancel:", requestId);
              // Fade out và xóa row mềm mại
              row.style.transition = "opacity 0.5s ease";
              row.style.opacity = "0";
              setTimeout(() => {
                row.remove();

                // Hiển thị empty state nếu không còn row nào
                const tbody = document.querySelector("table tbody");
                const cardBody = document.querySelector(".card .card-body");
                if (tbody && tbody.children.length === 0 && cardBody) {
                  cardBody.innerHTML = `
                                        <div class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Chưa có yêu cầu nào</p>
                                        </div>`;
                }
              }, 500);
            }
          }
        });

        // Nếu có yêu cầu mới, hiển thị notification và reload
        if (newCount > lastPendingCount) {
          const alertDiv = document.createElement("div");
          alertDiv.className = "alert alert-info alert-dismissible fade show";
          alertDiv.style.cssText =
            "position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;";
          alertDiv.innerHTML = `
                        <i class="fas fa-bell"></i> Có ${
                          newCount - lastPendingCount
                        } yêu cầu mới!
                        <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
                    `;
          document.body.appendChild(alertDiv);

          // Tự động ẩn sau 3 giây và reload
          setTimeout(() => {
            alertDiv.remove();
            location.reload();
          }, 3000);
        }

        lastPendingCount = newCount;
      }
    })
    .catch((error) => {
      console.error("Error checking new requests:", error);
    });
}

/**
 * Jump to page (pagination)
 */
function jumpToPage() {
  const input = document.getElementById("jumpToPage");
  if (!input) return;

  const page = input.value;
  const maxPage = parseInt(input.max);

  if (page && page >= 1 && page <= maxPage) {
    window.location.href = "/admin/password-reset?page=" + page;
  } else {
    alert("Vui lòng nhập số trang hợp lệ (1 - " + maxPage + ")");
  }
}

// Khởi tạo khi DOM ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initPasswordResetModule);
} else {
  initPasswordResetModule();
}

// Setup Enter key cho jump input
document.addEventListener("DOMContentLoaded", function () {
  const jumpInput = document.getElementById("jumpToPage");
  if (jumpInput) {
    jumpInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        jumpToPage();
      }
    });
  }
});
