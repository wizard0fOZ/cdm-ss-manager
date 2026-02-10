<?php
namespace App\Core\Db;

use PDO;

final class Db
{
  private static ?PDO $pdo = null;

  public static function pdo(): PDO
  {
    if (self::$pdo) return self::$pdo;

    $host = $_ENV['DB_HOST'] ?? $_ENV['DB_DATABASE_HOST'] ?? null;
    $port = $_ENV['DB_PORT'] ?? '3306';
    $name = $_ENV['DB_NAME'] ?? ($_ENV['DB_DATABASE'] ?? null);
    $user = $_ENV['DB_USER'] ?? ($_ENV['DB_USERNAME'] ?? null);
    $pass = $_ENV['DB_PASS'] ?? ($_ENV['DB_PASSWORD'] ?? '');

    if (!$host || !$name || !$user) {
      throw new \RuntimeException('Database configuration missing. Set DB_HOST, DB_DATABASE, and DB_USERNAME in .env');
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    self::$pdo = new PDO($dsn, $user, $pass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return self::$pdo;
  }
}
