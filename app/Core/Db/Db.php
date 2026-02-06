<?php
namespace App\Core\Db;

use PDO;

final class Db
{
  private static ?PDO $pdo = null;

  public static function pdo(): PDO
  {
    if (self::$pdo) return self::$pdo;

    $host = $_ENV['DB_HOST'] ?? 'db';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $name = $_ENV['DB_NAME'] ?? ($_ENV['DB_DATABASE'] ?? 'cdm_ss_manager');
    $user = $_ENV['DB_USER'] ?? ($_ENV['DB_USERNAME'] ?? 'cdm_user');
    $pass = $_ENV['DB_PASS'] ?? ($_ENV['DB_PASSWORD'] ?? 'cdm_pass');

    // Force TCP on local dev when "localhost" would otherwise use a socket.
    if ($host === 'localhost') {
      $host = '127.0.0.1';
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    self::$pdo = new PDO($dsn, $user, $pass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return self::$pdo;
  }
}
