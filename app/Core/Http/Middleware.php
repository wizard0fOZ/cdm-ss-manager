<?php
namespace App\Core\Http;

final class Middleware
{
  /** @var array<string, callable> */
  private array $map = [];

  public function register(string $name, callable $handler): void
  {
    $this->map[$name] = $handler;
  }

  public function get(string $name): callable
  {
    if (!isset($this->map[$name])) {
      throw new \RuntimeException("Middleware not registered: {$name}");
    }
    return $this->map[$name];
  }
}
