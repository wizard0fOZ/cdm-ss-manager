<?php
declare(strict_types=1);

namespace App\Core\Security;

final class Csrf
{
  private const SESSION_KEY = '_csrf';

  public static function token(): string
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    if (empty($_SESSION[self::SESSION_KEY])) {
      $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION[self::SESSION_KEY];
  }

  public static function validate(?string $token): bool
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    $sessionToken = $_SESSION[self::SESSION_KEY] ?? null;
    if (!$token || !$sessionToken) {
      return false;
    }

    return hash_equals((string) $sessionToken, (string) $token);
  }

  public static function input(): string
  {
    $t = self::token();
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '">';
  }
}