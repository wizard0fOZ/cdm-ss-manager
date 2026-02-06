<?php
declare(strict_types=1);

use App\Core\Security\Csrf;

/**
 * Simple global helper functions for views.
 * Keep it small and predictable.
 */

function csrf_token(): string
{
  return Csrf::token();
}

function csrf_input(): string
{
  return Csrf::input();
}