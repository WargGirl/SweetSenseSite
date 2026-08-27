<?php

class Cache {
    private static string $cacheDir = __DIR__ . '/../storage/cache/';

    public static function get(string $key, int $ttlSeconds = 3600) {
        $file = self::$cacheDir . md5($key) . '.cache';
        
        if (file_exists($file) && (time() - filemtime($file) < $ttlSeconds)) {
            $content = @file_get_contents($file);
            return $content ? unserialize($content) : null;
        }
        
        return null;
    }

    public static function set(string $key, $data): void {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }
        
        $file = self::$cacheDir . md5($key) . '.cache';
        file_put_contents($file, serialize($data));
    }

    public static function delete(string $key): void {
        $file = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    public static function clear(): void {
        if (is_dir(self::$cacheDir)) {
            $files = glob(self::$cacheDir . '*.cache');
            if ($files) {
                foreach ($files as $file) {
                    @unlink($file);
                }
            }
        }
    }
}