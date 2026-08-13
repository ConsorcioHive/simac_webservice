<?php
// app/Helpers/Json.php
namespace App\Helpers;

class Json
{
    public static function encode($data)
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function decode($string, $assoc = true)
    {
        return json_decode($string, $assoc);
    }

    public static function response($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo self::encode($data);
        exit;
    }
}
