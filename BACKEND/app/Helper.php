<?php

if (!function_exists('sanitizeNameForPassword')) {
    /**
     * Sanitize user name for password generation
     * Removes spaces, accents, special characters
     * Example: "Nguyễn Văn A" → "nguyenvana"
     * 
     * @param string $name
     * @return string
     */
    
  function sanitizeNameForPassword(string $email): string
{
    // Lấy phần chữ trước ký tự @
    $name = strstr($email, '@', true);

    // Nếu không tìm thấy dấu @ (truyền vào chuỗi thường không phải email), giữ nguyên chuỗi
    if ($name === false) {
        $name = $email;
    }

    // Chuyển thành chữ thường
    $name = mb_strtolower($name, 'UTF-8');

    // Chuyển tiếng Việt có dấu thành không dấu
    $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);

    // Xóa các ký tự đặc biệt (chỉ giữ lại chữ cái và số)
    $name = preg_replace('/[^a-z0-9]/', '', $name);

    return $name;
}
}
