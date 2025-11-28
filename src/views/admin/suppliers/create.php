<?php
/**
 * View: Thêm nhà cung cấp mới
 */
$pageTitle = $pageTitle ?? 'Thêm nhà cung cấp mới';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-plus-circle"></i> <?= $pageTitle ?></h2>
        <a href="/admin/suppliers" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="/admin/suppliers/store">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Tên nhà cung cấp <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="name" name="name" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="contact" class="form-label">Người liên hệ</label>
                            <input type="text" class="form-control" id="contact" name="contact">
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                Số điện thoại <span class="text-danger">*</span>
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   required inputmode="numeric" 
                                   placeholder="Ví dụ: 0901234567 hoặc +84901234567">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ</label>
                            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Hoạt động</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-top pt-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu nhà cung cấp
                    </button>
                    <a href="/admin/suppliers" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/assets/css/supplier-style.css">

<script>
// Pretty validation UI helper: inject a Bootstrap alert into the card
// Show a small inline tooltip next to the input (like native validation bubble)
function showInlineTooltip(message, inputEl) {
    // remove existing tooltip
    const old = document.querySelector('.validation-tooltip');
    if (old) old.remove();

    const tip = document.createElement('div');
    tip.className = 'validation-tooltip';
    tip.innerHTML = '<span class="vt-icon"><i class="fas fa-exclamation-triangle"></i></span>' +
        '<div class="vt-message">' + message + '</div>' +
        '<button type="button" class="vt-close" aria-label="Close">&times;</button>';

    // Append hidden so we can measure its size reliably, then position and show
    tip.style.position = 'absolute';
    tip.style.visibility = 'hidden';
    document.body.appendChild(tip);

    // measure and compute position
    const rect = inputEl.getBoundingClientRect();
    tip.classList.remove('validation-tooltip-right');
    // force reflow/measurement
    const measured = tip.getBoundingClientRect();
    let topPos = window.scrollY + rect.top - measured.height - 8; // above
    let leftPos = window.scrollX + rect.left + (rect.width - measured.width) / 2; // centered

    // if not enough space above, fallback to the right
    if (topPos < window.scrollY + 8) {
        tip.classList.add('validation-tooltip-right');
        const measuredRight = tip.getBoundingClientRect();
        topPos = window.scrollY + rect.top + (rect.height - measuredRight.height) / 2;
        leftPos = window.scrollX + rect.right + 8;
        if (leftPos + measuredRight.width > window.scrollX + window.innerWidth - 12) {
            leftPos = window.scrollX + window.innerWidth - measuredRight.width - 12;
        }
    }

    tip.style.top = topPos + 'px';
    tip.style.left = leftPos + 'px';
    // make visible after positioned
    tip.style.visibility = 'visible';

    // close handlers
    tip.querySelector('.vt-close').addEventListener('click', () => tip.remove());
    inputEl.addEventListener('focus', () => tip.remove(), { once: true });
    // auto remove after 6s
    setTimeout(() => { if (tip.parentNode) tip.remove(); }, 6000);
}

// Validate phone before submit: only digits with optional leading +, length 7-15
// Use flexible form selector and also validate on blur for immediate feedback
let createForm = document.querySelector('form[action="/admin/suppliers/store"]');
if (!createForm) {
    // fallback: first form inside the card
    createForm = document.querySelector('.card form');
}

const phoneValidator = (inputEl) => {
    const phone = (inputEl.value || '').trim();
    // Số điện thoại là bắt buộc
    if (phone === '') {
        showInlineTooltip('Số điện thoại không được để trống', inputEl);
        return false;
    }
    // Kiểm tra format
    const re = /^\+?\d{7,15}$/;
    if (!re.test(phone)) {
        showInlineTooltip('Số điện thoại không hợp lệ. Chỉ chứa chữ số và có thể bắt đầu bằng dấu +, độ dài 7-15 ký tự.', inputEl);
        return false;
    }
    return true;
};

if (createForm) {
    createForm.addEventListener('submit', function(e) {
        const phoneEl = document.getElementById('phone');
        if (!phoneValidator(phoneEl)) {
            e.preventDefault();
            phoneEl.focus();
            return false;
        }
    });

    // immediate validation on blur
    const phoneEl = document.getElementById('phone');
    if (phoneEl) {
        // CHẶN NGAY KHI GÕ: chỉ cho phép số và dấu +
        phoneEl.addEventListener('input', function(e) {
            let value = e.target.value;
            let cursorPos = e.target.selectionStart;
            
            // Chỉ giữ lại số và dấu + (+ chỉ được ở đầu)
            let cleaned = value.replace(/[^\d+]/g, ''); // Xóa tất cả trừ số và +
            
            // Nếu có nhiều dấu +, chỉ giữ cái đầu tiên
            let plusCount = (cleaned.match(/\+/g) || []).length;
            if (plusCount > 1) {
                // Giữ + đầu tiên, xóa các + sau
                let firstPlus = cleaned.indexOf('+');
                cleaned = cleaned.substring(0, firstPlus + 1) + cleaned.substring(firstPlus + 1).replace(/\+/g, '');
            }
            
            // Nếu có +, nó phải ở đầu
            if (cleaned.indexOf('+') > 0) {
                cleaned = cleaned.replace(/\+/g, '');
            }
            
            // Giới hạn độ dài tối đa 16 ký tự (+ + 15 số)
            if (cleaned.length > 16) {
                cleaned = cleaned.substring(0, 16);
            }
            
            // Nếu giá trị thay đổi (có ký tự không hợp lệ bị xóa)
            if (value !== cleaned) {
                e.target.value = cleaned;
                // Giữ vị trí con trỏ
                e.target.setSelectionRange(cursorPos - 1, cursorPos - 1);
                
                // Hiển thị tooltip cảnh báo
                showInlineTooltip('Chỉ được nhập số và dấu + ở đầu', phoneEl);
            }
        });
        
        phoneEl.addEventListener('blur', function() {
            phoneValidator(phoneEl);
        });
    }
}
</script>
