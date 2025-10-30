# Hướng dẫn làm việc với Git và vận hành dự án

Tài liệu này tổng hợp các hướng dẫn quan trọng để phát triển, đồng bộ code, chạy và debug dự án "Business Product Management System" trên máy cá nhân (Laragon / PHP built-in server), kết nối database qua Tailscale, và các thủ thuật Git cần thiết.

Mục lục
- [Mục tiêu tài liệu](#m%E1%BB%A5c-ti%C3%AAu-t%E1%BA%A3i-li%E1%BB%87u)
- [Chuẩn bị môi trường](#chu%E1%BA%A9n-b%E1%BB%8B-m%C3%B4i-tr%C6%B0%E6%A1%BFng)
- [Chạy dự án cục bộ (Composer / PHP)](#ch%E1%BA%A1y-d%E1%BB%B1-%C3%A1n-c%E1%BB%A5c-b%E1%BB%91c-composer--php)
- [Cấu hình .env và database (Laragon / Tailscale)](#c%E1%BA%A5u-h%C3%ACnh-env-v%C3%A0-database-laragon--tailscale)
- [Import DB schema bằng phpMyAdmin hoặc CLI](#import-db-schema-b%E1%BA%B1ng-phpmyadmin-ho%E1%BA%B7c-cli)
- [Hướng dẫn Git đầy đủ (branch/workflow/merge/rebase/cherry-pick/stash/conflict)](#h%C6%B0%E1%BB%9Dng-d%E1%BA%ABn-git-%C4%91%E1%BA%A7y-%C4%91%E1%BB%99)
  - [Các lệnh cơ bản](#c%C3%A1c-l%E1%BB%89nh-c%C6%A1-b%E1%BA%A3n)
  - [Tạo & chuyển nhánh](#t%E1%BA%ADo--chuy%E1%BB%83n-nh%C3%A1nh)
  - [Lấy nhánh remote chưa có local](#l%E1%BA%ADy-nh%C3%A1nh-remote-ch%C6%B0a-c%C3%B3-local)
  - [Merge vs Rebase (so sánh + khi dùng)](#merge-vs-rebase-so-s%C3%A1nh--khi-d%C3%B9ng)
  - [Cherry-pick / Lấy 1 commit](#cherry-pick--l%E1%BA%A5y-1-commit)
  - [Lấy 1 file từ nhánh khác](#l%E1%BA%A5y-1-file-t%E1%BB%AB-nh%C3%A1nh-kh%C3%A1c)
  - [Stash (tạm cất) và lấy lại](#stash-t%E1%BA%A1m-c%E1%BA%B7t-v%C3%A0-l%E1%BA%A5y-l%E1%BA%A1i)
  - [Giải quyết conflict (conflict resolution)](#gi%E1%BA%A3i-quy%E1%BA%BFt-conflict-conflict-resolution)
  - [Best practices & commit message](#best-practices--commit-message)
- [Push, pull, PR và quy trình review](#push-pull-pr-v%C3%A0-quy-tr%C3%ACnh-review)
- [Kiểm tra & debug nhanh (test-env, test-db)](#ki%E1%BB%83m-tra--debug-nhanh-test-env-test-db)
- [Quy tắc làm việc nhóm (nên tuân thủ)](#quy-t%E1%BB%ACc-l%C3%A0m-vi%E1%BB%87c-nh%C3%B3m-n%C3%AAn-tu%C3%A2n-th%E1%BB%B1)
- [Tài liệu & file liên quan trong repo](#t%C3%A0i-li%E1%BB%87u--file-li%C3%AAn-quan-trong-repo)


## Mục tiêu tài liệu

Giúp thành viên mới và hiện tại:
- Hiểu cách sử dụng Git an toàn với workflow của dự án.
- Lấy code từ nhánh khác (remote) mà không làm thay đổi nhánh nguồn.
- Thiết lập môi trường (Laragon/Tailscale) và kết nối database từ xa.
- Chạy và debug nhanh ứng dụng.


## Chuẩn bị môi trường

Yêu cầu tối thiểu:
- PHP 8.0+
- Composer
- MySQL (Laragon có sẵn), hoặc remote MySQL truy cập qua Tailscale
- Git
- Trình duyệt, phpMyAdmin nếu cần


## Chạy dự án cục bộ (Composer / PHP)

1. Vào thư mục dự án:
```bash
cd "C:/laragon/www/Business-product-management-system"  # hoặc đường dẫn nơi bạn đặt project
```

2. Cài dependency:
```bash
composer install
composer dump-autoload
```

3. Chuẩn bị file `.env` (nếu chưa có):
```bash
cp .env.example .env
# rồi chỉnh .env theo môi trường (DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL,...)
```

4. Chạy server dev (PHP built-in) — chỉ dành cho test nhanh:
```bash
php -S localhost:8000 -t public
```
Truy cập: `http://localhost:8000` hoặc nếu bạn đã dùng Laragon + domain tự động: `http://business-product-management-system.test`


## Cấu hình .env và database (Laragon / Tailscale)

- Nếu MySQL chạy trên cùng máy Laragon: dùng `DB_HOST=127.0.0.1`, `DB_USER=root`, `DB_PASS=` (Laragon mặc định không password).
- Nếu MySQL chạy trên máy khác và truy cập qua Tailscale (ví dụ IP `100.106.99.41`), set `.env` như:
```
DB_HOST=100.106.99.41
DB_PORT=3306
DB_NAME=business_product_management_system
DB_USER=root
DB_PASS=
```

> Lưu ý: nếu DB user root chỉ bind localhost, cần cấp quyền remote hoặc thêm user cho phép kết nối từ IP của bạn. Kiểm tra `mysql.user` và `bind-address` trong `my.ini`.


## Import DB schema bằng phpMyAdmin hoặc CLI

- Dùng phpMyAdmin: `http://100.106.99.41/phpmyadmin` → Databases → Create `business_product_management_system` → Import file `docs/business_product_management_system.sql` vào database đã tạo.

- Dùng CLI (Laragon hoặc MySQL client):
```bash
# ví dụ trên Windows với Laragon
C:\laragon\bin\mysql\mysql-8.0.x\bin\mysql.exe -u root -p business_product_management_system < docs/business_product_management_system.sql
```


## Hướng dẫn Git đầy đủ

### Các lệnh cơ bản

```bash
git status           # kiểm tra trạng thái
git add <file>       # thêm file vào staging
git add .            # thêm tất cả (cẩn trọng)
git commit -m "msg" # commit
git fetch origin     # lấy refs và commits từ remote
git pull origin BRANCH # fetch + merge
```


### Tạo & chuyển nhánh

- Tạo và chuyển sang nhánh mới từ nhánh hiện tại:
```bash
git switch -c feature/my-feature
# hoặc
git checkout -b feature/my-feature
```

- Chuyển sang nhánh local sẵn có:
```bash
git switch develop
# hoặc
git checkout develop
```

- Nếu nhánh chưa có local nhưng có trên remote (origin):
```bash
git fetch origin
git switch -c Minh2244 origin/Minh2244
# hoặc
git checkout --track origin/Minh2244
```


### Lấy code từ nhánh khác về nhánh của bạn (không thay đổi nhánh nguồn)
Giả sử bạn đang ở nhánh `Phu` và muốn lấy code từ `Minh2244`.

- Tùy chọn A — Merge toàn bộ (an toàn, dễ hiểu):
```bash
# đang ở nhánh Phu
git fetch origin
git switch Phu
git merge origin/Minh2244
# sửa conflict nếu có, rồi commit
```
- Tùy chọn B — Cherry-pick 1 commit (chỉ lấy commit cụ thể):
```bash
git fetch origin
git switch Phu
git log --oneline origin/Minh2244  # tìm commit hash
git cherry-pick <commit-hash>
```
- Tùy chọn C — Lấy 1 file cụ thể từ nhánh khác:
```bash
git switch Phu
git restore --source=origin/Minh2244 -- path/to/file.php
# hoặc (cũ)
git checkout origin/Minh2244 -- path/to/file.php

git add path/to/file.php
git commit -m "chore: lấy file từ Minh2244"
```

> Những thao tác trên sử dụng `origin/Minh2244` nên **không làm thay đổi** nhánh `Minh2244` trên remote.


### Merge vs Rebase (so sánh + khi dùng)

- `git merge origin/main` sẽ tạo merge-commit (giữ lịch sử đầy đủ).
- `git rebase origin/main` sẽ _viết lại_ lịch sử của nhánh hiện tại để áp lên đỉnh `main` (lịch sử sạch hơn) — cần `force-push` sau khi rebase.

Khi nào dùng:
- Dùng **merge** khi làm việc nhóm lớn hoặc khi không muốn rewrite history.
- Dùng **rebase** khi bạn làm việc một mình trên branch và muốn lịch sử sạch trước khi PR.

Lưu ý khi rebase:
```bash
git fetch origin
git switch Phu
git rebase origin/main
# nếu conflict: sửa file -> git add <file> -> git rebase --continue
# khi hoàn tất: git push --force-with-lease origin Phu
```


### Cherry-pick (lấy 1 commit cụ thể)

```bash
# Tìm commit trên nhánh nguồn
git fetch origin
git log --oneline origin/Minh2244
# áp commit vào nhánh hiện tại
git switch Phu
git cherry-pick <commit-hash>
```


### Stash (tạm cất thay đổi)

```bash
# lưu tạm (không commit)
git stash push -m "WIP: mô tả"
# lấy danh sách stash
git stash list
# áp stash gần nhất
git stash pop
# áp stash cụ thể
git stash apply stash@{1}
# xóa stash cụ thể
git stash drop stash@{1}
```


### Lấy 1 file từ remote branch (không làm checkout toàn branch)

```bash
git fetch origin
git restore --source=origin/Minh2244 -- src/modules/some/File.php
# commit thay đổi
git add src/modules/some/File.php
git commit -m "chore: lấy File.php từ Minh2244"
```


### Giải quyết conflict (conflict resolution)

Khi merge hoặc rebase có conflict, Git sẽ đánh dấu vùng conflict trong file:
```
<<<<<<< HEAD
// code trên nhánh hiện tại
=======
// code từ nhánh incoming
>>>>>>> origin/Minh2244
```
- Mở file, chỉnh sửa để giữ phần cần thiết.
- Xóa các dấu `<<<<<<<`, `=======`, `>>>>>>>`.
- Lưu file rồi:
```bash
git add path/to/file
# Nếu merge: git commit -m "merge: resolve conflict"
# Nếu rebase: git rebase --continue
```


### Best practices & commit message

- Sử dụng Conventional Commits:
  - `feat:`, `fix:`, `docs:`, `chore:`, `refactor:`, `test:`
- Commit sớm, commit nhỏ, message rõ ràng.
- Tạo branch theo chức năng: `feature/<tên>`, `bugfix/<tên>`, `hotfix/<tên>`.
- Trước khi tạo PR, cập nhật nhánh từ `main` (merge hoặc rebase tùy quy tắc nhóm).


## Push, pull, PR và quy trình review

- Push nhánh mới lần đầu:
```bash
git push -u origin feature/my-feature
```
- Tạo Pull Request (PR) trên GitHub, mô tả rõ:
  - Mục tiêu PR
  - Thay đổi chính
  - Hướng dẫn test
- Sau review & merge, xóa nhánh local và remote nếu không dùng nữa:
```bash
git branch -d feature/my-feature
git push origin --delete feature/my-feature
```


## Kiểm tra & debug nhanh (test-env, test-db)

- File `public/test-env.php` (nếu có) giúp kiểm tra biến môi trường và kết nối DB nhanh.
- Nếu gặp lỗi DB: kiểm tra `.env`, `config/database.php`, và quyền truy cập MySQL (bind-address, user/host).


## Các lệnh tiện ích hữu dụng

```bash
# xem log ngắn gọn
git log --oneline --graph --decorate -10

# undo thay đổi chưa commit
git restore path/to/file

# revert 1 commit đã được push (tạo commit đảo ngược)
git revert <commit-hash>

# force-push an toàn (khi rebase)
git push --force-with-lease origin feature/my-feature
```


## Quy tắc làm việc nhóm (nên tuân thủ)

- Không commit code broken lên main.
- Tạo PR nhỏ, dễ review.
- Thực hiện code review, test trước khi merge.
- Ghi ticket/issue rõ ràng trước khi bắt tay vào feature.


## Tài liệu & file liên quan trong repo

- `docs/business_product_management_system.sql` — schema DB
- `docs/Hướng dẫn chạy website.md` — original run instructions
- `docs/huongdandungbranch.md` — hướng dẫn branch ngắn
- `README.md` — tổng quan dự án
- `config/` — các file cấu hình (app.php, database.php, routes.php)


---

## Thử ngay (checklist nhanh):
1. Lưu hoặc stash thay đổi:
```bash
git add .
git commit -m "WIP: lưu tạm"   # hoặc git stash push -m "WIP"
```
2. Lấy remote và checkout/create branch từ remote:
```bash
git fetch origin
git switch -c Minh2244 origin/Minh2244
# hoặc lấy file/commit đơn lẻ về nhánh Phu
```
3. Merge vào `Phu`:
```bash
git switch Phu
git merge origin/Minh2244
```
4. Sau khi test xong, push `Phu`:
```bash
git push origin Phu
```


---

Tài liệu này đặt ở: `docs/GIT_WORKFLOW_AND_PROJECT_GUIDE.md` trong repository. Xin mở file bằng VS Code hoặc trình đọc Markdown để tham khảo chi tiết.

Nếu muốn, tôi có thể:
- Thêm mục lục linkable (TOC) tự động.
- Xây phiên bản ngắn gọn (Cheat sheet) để in.
- Commit và push file này lên nhánh `Phu` hoặc `develop` giúp bạn.

Bạn muốn tôi commit + push file này lên remote không? Nếu có, cho biết nhánh bạn muốn (ví dụ `Phu` hoặc `develop`).