# Hướng dẫn sử dụng Cloudinary để upload ảnh

## Tổng quan

Hệ thống đã được tích hợp Cloudinary để xử lý upload và quản lý ảnh một cách hiệu quả, bao gồm:

-   Upload avatar người dùng
-   Upload nhiều ảnh (gallery, documents, etc.)
-   Tự động tối ưu hóa ảnh
-   Transform và resize ảnh trên Cloudinary
-   Quản lý media với polymorphic relationships

## Cấu hình

### 1. Thêm biến môi trường vào `.env`

```env
CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_KEY=your_api_key
CLOUDINARY_SECRET=your_api_secret
CLOUDINARY_SECURE=true
CLOUDINARY_PREFIX=
```

Bạn có thể lấy thông tin này từ [Cloudinary Dashboard](https://cloudinary.com/console).

### 2. Chạy migration

```bash
php artisan migrate
```

Migration sẽ tạo bảng `media` với các trường cần thiết cho polymorphic relationships.

## Kiến trúc

### Services

1. **CloudinaryService** (`app/Services/CloudinaryService.php`)

    - Xử lý upload/delete files với Cloudinary
    - Tạo URL với transformations
    - Tối ưu hóa avatar tự động

2. **MediaService** (`app/Services/MediaService.php`)
    - Quản lý media records trong database
    - Liên kết media với models (polymorphic)
    - Xử lý upload/delete multiple files

### Trait

-   **HasMedia** (`app/Traits/HasMedia.php`)
    -   Thêm vào model để dễ dàng quản lý media
    -   Cung cấp methods: `uploadMedia()`, `getMediaByType()`, etc.

### Model

-   **Media** (`app/Models/Media.php`)
    -   Lưu thông tin media từ Cloudinary
    -   Polymorphic relationship với các model khác

## Sử dụng

### 1. Upload Avatar cho User

#### Trong Controller

```php
use App\Http\Requests\UploadAvatarRequest;

public function uploadAvatar(UploadAvatarRequest $request, string $id)
{
    $user = User::find($id);
    $media = $user->uploadMedia($request->file('avatar'), 'avatar');

    return response()->json([
        'avatar_url' => $media->secure_url,
    ]);
}
```

#### API Endpoint

**Upload avatar cho user hiện tại:**

```
POST /api/auth/upload-avatar
Content-Type: multipart/form-data

{
    "avatar": File
}
```

**Upload avatar cho user khác (Admin):**

```
POST /api/admin/users/{id}/upload-avatar
Content-Type: multipart/form-data

{
    "avatar": File
}
```

### 2. Sử dụng HasMedia Trait trong Model

```php
use App\Traits\HasMedia;

class User extends Model
{
    use HasMedia;

    // Model tự động có các methods:
    // - uploadMedia($file, $type, $folder, $options)
    // - uploadMultipleMedia($files, $type, $folder)
    // - getMediaByType($type)
    // - deleteMediaByType($type)
    // - media() relationship
}
```

### 3. Upload nhiều ảnh

```php
// Trong Controller
$user = auth()->user();
$files = $request->file('images');

// Upload nhiều ảnh
$mediaCollection = $user->uploadMultipleMedia($files, 'gallery', 'users/gallery');

// Hoặc sử dụng MediaService trực tiếp
$mediaService = app(MediaService::class);
$mediaCollection = $mediaService->attachMultipleMedia($user, $files, 'gallery');
```

#### API Endpoint

```
POST /api/media/upload
Content-Type: multipart/form-data

{
    "files": [File1, File2, File3],
    "type": "gallery",
    "folder": "custom/folder/path" // optional
}
```

### 4. Lấy ảnh từ Model

```php
$user = User::find(1);

// Lấy tất cả media
$allMedia = $user->media;

// Lấy media theo type
$avatar = $user->getMediaByType('avatar');
$gallery = $user->mediaOfType('gallery')->get();

// Lấy avatar URL (tự động)
$avatarUrl = $user->avatar_url;
```

#### API Endpoint

```
GET /api/media?model_type=App\Models\User&model_id=1&type=avatar
```

### 5. Xóa ảnh

```php
// Xóa media cụ thể
$media = Media::find($id);
$mediaService = app(MediaService::class);
$mediaService->deleteMedia($media);

// Xóa tất cả media theo type
$user->deleteMediaByType('avatar');
```

#### API Endpoint

```
DELETE /api/media/{id}
```

### 6. Transform ảnh

```php
use App\Services\CloudinaryService;

$cloudinaryService = app(CloudinaryService::class);

// Lấy URL với transformations
$thumbnailUrl = $cloudinaryService->getThumbnailUrl($publicId, 200, 200);

// Custom transformations
$optimizedUrl = $cloudinaryService->getOptimizedUrl($publicId, [
    'width' => 800,
    'height' => 600,
    'crop' => 'fill',
    'quality' => 'auto:good',
    'fetch_format' => 'auto',
]);
```

## Tối ưu hóa hiệu suất

### 1. Avatar Upload

Avatar tự động được tối ưu với:

-   Auto-resize về 400x400px (với face detection)
-   Tạo nhiều kích thước eager (200x200, 100x100)
-   Auto format và quality optimization

### 2. Lazy Loading

Cloudinary tự động tối ưu:

-   Chỉ tải ảnh khi cần (CDN)
-   Auto format (WebP khi browser hỗ trợ)
-   Auto quality optimization

### 3. Database Indexes

Bảng `media` đã có indexes cho:

-   `mediable_type` + `mediable_id` (polymorphic lookup)
-   `type` (filter by media type)
-   `folder` (filter by folder)

### 4. Eager Transformations

Sử dụng eager transformations cho ảnh thường dùng:

```php
$options = [
    'eager' => [
        ['width' => 800, 'height' => 600, 'crop' => 'fill'],
        ['width' => 400, 'height' => 400, 'crop' => 'thumb'],
    ],
];
```

## Best Practices

1. **Tổ chức folder có cấu trúc:**

    ```
    avatars/user_{id}
    devices/{device_id}/images
    documents/{type}/{year}/{month}
    ```

2. **Sử dụng type cho media:**

    - `avatar`: Ảnh đại diện (chỉ 1)
    - `gallery`: Nhiều ảnh
    - `document`: Tài liệu
    - `thumbnail`: Ảnh thumbnail

3. **Validate file trước khi upload:**

    - Size limit
    - MIME type
    - Dimensions (nếu cần)

4. **Cleanup khi xóa model:**

    - HasMedia trait tự động xóa media khi model bị xóa

5. **Sử dụng secure_url:**
    - Luôn dùng `secure_url` thay vì `url` cho HTTPS

## Ví dụ đầy đủ

### Upload avatar và hiển thị

```php
// Controller
public function updateAvatar(UploadAvatarRequest $request)
{
    $user = auth()->user();

    // Upload
    $media = $user->uploadMedia($request->file('avatar'), 'avatar');

    // Response
    return response()->json([
        'success' => true,
        'avatar_url' => $media->secure_url,
        'user' => $user->fresh(),
    ]);
}

// Frontend sử dụng
// $user->avatar_url sẽ tự động trả về URL từ media hoặc default
```

### Upload gallery cho Device

```php
class Device extends Model
{
    use HasMedia;
}

// Upload
$device = Device::find(1);
$files = $request->file('images');
$mediaCollection = $device->uploadMultipleMedia($files, 'gallery');

// Hiển thị
$gallery = $device->mediaOfType('gallery')->get();
foreach ($gallery as $image) {
    echo $image->secure_url;
}
```

## Troubleshooting

### Lỗi upload thất bại

-   Kiểm tra Cloudinary credentials trong `.env`
-   Kiểm tra file size limit
-   Kiểm tra network connection

### Ảnh không hiển thị

-   Kiểm tra `secure_url` có đúng không
-   Kiểm tra Cloudinary CORS settings
-   Kiểm tra public_id có đúng không

### Performance issues

-   Sử dụng eager transformations
-   Cache URLs nếu có thể
-   Sử dụng CDN (Cloudinary tự động có CDN)

## Migration từ hệ thống cũ

Nếu bạn đã có avatar trong field `avatar` của User:

1. Media mới sẽ được lưu vào bảng `media`
2. `avatar_url` attribute tự động ưu tiên media, fallback về field `avatar` cũ
3. Có thể migrate dữ liệu cũ nếu cần:

```php
User::whereNotNull('avatar')->each(function ($user) {
    // Migrate old avatar URL to Cloudinary if needed
});
```
