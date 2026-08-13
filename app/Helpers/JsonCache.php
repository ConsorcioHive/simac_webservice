<?php
// app/Helpers/JsonCache.php
namespace App\Helpers;

// Caché de datos en archivos JSON (igual enfoque que en SIMAC cloud).
class JsonCache
{
    private static $cachePath = BASE_PATH . '/app/storage/cache/';

    public static function get(string $key)
    {
        $file = self::$cachePath . $key . '.json';
        if (!file_exists($file)) {
            return null;
        }
        $content = file_get_contents($file);
        if (empty($content)) {
            return null;
        }
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        return $data;
    }

    public static function set(string $key, $data)
    {
        if (empty($data)) {
            return;
        }
        $fullPath = self::$cachePath . $key;
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (!is_writable($dir)) {
            return;
        }
        @file_put_contents($fullPath . '.json', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public static function invalidate(string $pattern)
    {
        if (!is_dir(self::$cachePath)) {
            return;
        }
        $files = glob(self::$cachePath . $pattern . '*.json');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    public static function clearAll()
    {
        self::invalidate('');
    }

    public static function autoClean(): void
    {
        $logFile = self::$cachePath . 'last_cleanup.txt';
        $today = date('Y-m-d');
        if (file_exists($logFile) && trim(file_get_contents($logFile)) === $today) {
            return;
        }
        if (is_dir(self::$cachePath)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(self::$cachePath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($files as $file) {
                if ($file->isFile() && $file->getExtension() === 'json') {
                    @unlink($file->getPathname());
                }
            }
        }
        @file_put_contents($logFile, $today);
    }
}
