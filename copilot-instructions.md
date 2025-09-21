# Tổng quan dự án
- Dự án: Hệ thống quản lý mượn trả thiết bị trong trường học.
- Backend: Laravel (PHP) + MySQL; Frontend: Vue.js với Vite, JavaScript.
- DevOps: GitHub Actions, triển khai AWS (ví dụ EC2, RDS, S3, CloudFront nếu cần).
- Tất cả công việc phải **tuân thủ file detailed design**.
- Luôn **phản hồi bằng tiếng Việt** khi viết code, commit message, comment.

## Build và Scripts
- **Laravel**:
  - `composer install`, `php artisan migrate`, `php artisan serve` (phải chạy PHP 8.x).
  - Linter: `php artisan lint` hoặc `./vendor/bin/phpstan`.
- **Vue.js**:
  - `npm install`, `npm run dev` (Vite).
  - Linter: `npm run lint`.
- **Test**:
  - Laravel: `php artisan test`.
  - Vue: `npm run test`.
- **Cần luôn làm theo thứ tự**: `composer install` → `npm install` → migrate/run → lint/test.
- Nếu lệnh nào fail, nêu lỗi rõ ràng và cách xử lý.

## Cấu trúc dự án
- `/backend` chứa Laravel app.
- `/frontend` chứa Vue.js app.
- `.github/workflows/` chứa file CI/CD (Ví dụ `ci.yaml`, `deploy.yaml`).
- `detailed-design/` chứa file thiết kế chi tiết (UML, ERD, API spec).
- README, CONTRIBUTING, và các tài liệu thiết kế nằm ở thư mục gốc.

## DevOps (GitHub Actions & AWS)
- CI: Kiểm tra PHP linter, run migrations dạng dry-run, chạy test tự động.
- CD: Deploy tự động lên AWS (sử dụng Secrets để access).
- Luôn kiểm tra chi tiết design trước khi merge PR.

## Quy tắc RESTful API
- Dùng HTTP verbs: GET, POST, PUT/PATCH, DELETE rõ ràng với tài nguyên (ví dụ `/api/thiet-bi`, `/api/muon-tra`).
- Trả về JSON chuẩn, mã HTTP phù hợp: 200, 201, 400, 404, 422, 500…
- Đặt tên route: dùng snake_case hoặc kebab-case (ví dụ `muon-tra`).
- Viết validation (trong Controller hoặc FormRequest) rõ ràng, có thông báo lỗi (message) bằng tiếng Việt.
- API phải được document trong file detailed design (có request/response mẫu).

## Nguyên tắc chung
- Tất cả phải trả lời và viết bằng tiếng Việt.
- Mã phải tuân thủ detailed design; nếu thiếu, hỏi lại trước khi thực hiện.
- Luôn tạo commit messages rõ ràng bằng tiếng Việt, ví dụ: “Thêm endpoint GET /api/thiet-bi”.
- Khi trả lời hoặc gợi ý, Copilot chỉ sử dụng ngôn ngữ tiếng Việt.
