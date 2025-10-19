# vào thư mục dự án

cd "C:\Users\PC\Desktop\Business product management system"

# cập nhật nhánh chính

git fetch origin
git checkout main
git pull origin main

# tạo và chuyển sang branch mới

git checkout -b feature/my-feature

# hoặc: git switch -c feature/my-feature

# làm thay đổi, sau đó commit

git add .
git commit -m "Thêm tính năng XYZ"

# đẩy branch mới lên GitHub và đặt upstream

git push -u origin feature/my-feature

# lệnh clone project về máy

git clone https://github.com/NguyenPhu000/Business-product-management-system.git
