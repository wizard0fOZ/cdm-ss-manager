<?php
namespace App\Core\Support;

use Dotenv\Dotenv;

final class Env
{
  public static function load(string $basePath): void
  {
    if (file_exists($basePath . '/.env')) {
      Dotenv::createImmutable($basePath)->load();
    }
  }

  public static function get(string $key, mixed $default = null): mixed
  {
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
  }
}
