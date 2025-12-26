<?php

namespace App\Helper;

function sanitizeNameForPassword(string $name): string
{
    $name = mb_strtolower($name, 'UTF-8');

    $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);

    $name = preg_replace('/[^a-z0-9]/', '', $name);

    return $name;
}
