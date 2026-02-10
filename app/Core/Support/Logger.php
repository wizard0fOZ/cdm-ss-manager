<?php
declare(strict_types=1);

namespace App\Core\Support;

final class Logger
{
    private static ?string $logDir = null;

    private static function logDir(): string
    {
        if (self::$logDir === null) {
            self::$logDir = dirname(__DIR__, 3) . '/storage/logs';
        }
        return self::$logDir;
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        $dir = self::logDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $file = $dir . '/app-' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[{$timestamp}] {$level}: {$message}";

        if ($context) {
            $entry .= ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        @file_put_contents($file, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
