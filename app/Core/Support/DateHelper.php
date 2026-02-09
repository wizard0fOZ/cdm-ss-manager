<?php
namespace App\Core\Support;

final class DateHelper
{
  public static function parseDate(string $value): ?string
  {
    $value = trim($value);
    if ($value === '') return null;

    if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $value)) return $value;
    if (preg_match('/^(\\d{2})\\/(\\d{2})\\/(\\d{4})$/', $value, $m)) {
      return $m[3] . '-' . $m[2] . '-' . $m[1];
    }

    return null;
  }

  public static function normalizeDate(string $value): ?string
  {
    return self::parseDate($value);
  }

  public static function normalizeDateOrToday(string $value): string
  {
    return self::normalizeDate($value) ?? date('Y-m-d');
  }

  public static function formatDate(?string $value): ?string
  {
    if (!$value) return null;
    $parts = explode('-', $value);
    if (count($parts) === 3) {
      return $parts[2] . '/' . $parts[1] . '/' . $parts[0];
    }
    return $value;
  }

  public static function parseDateTime(string $value): ?string
  {
    $value = trim($value);
    if ($value === '') return null;

    if (preg_match('/^\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}$/', $value)) {
      return str_replace('T', ' ', $value) . ':00';
    }

    return null;
  }

  public static function formatDateTime(?string $value): ?string
  {
    if (!$value) return null;
    return str_replace(' ', 'T', substr($value, 0, 16));
  }
}
