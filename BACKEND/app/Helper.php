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
    function sanitizeNameForPassword(string $name): string
    {
        // Convert to lowercase
        $name = mb_strtolower($name, 'UTF-8');

        // Remove Vietnamese diacritics using transliteration
        $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);

        // Remove any non-alphanumeric characters
        $name = preg_replace('/[^a-z0-9]/', '', $name);

        return $name;
    }
}
