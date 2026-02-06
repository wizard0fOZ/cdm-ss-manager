<?php
namespace App\Core\Http;

final class Request
{
  public array $params = [];

  public function __construct(
    public readonly string $method,
    public readonly string $path,
    public readonly array $query,
    public readonly array $post,
    public readonly array $cookies,
    public readonly array $files,
    public readonly array $server
  ) {}

  public static function fromGlobals(): self
  {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    // Strip query string, normalize trailing slash
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    $path = '/' . ltrim($path, '/');
    $path = rtrim($path, '/');
    if ($path === '') $path = '/';

    return new self(
      $method,
      $path,
      $_GET ?? [],
      $_POST ?? [],
      $_COOKIE ?? [],
      $_FILES ?? [],
      $_SERVER ?? []
    );
  }

  public function input(string $key, mixed $default = null): mixed
  {
    if (array_key_exists($key, $this->post)) return $this->post[$key];
    if (array_key_exists($key, $this->query)) return $this->query[$key];
    return $default;
  }

  public function param(string $key, mixed $default = null): mixed
  {
    if (array_key_exists($key, $this->params)) return $this->params[$key];
    return $default;
  }

  public function isPost(): bool
  {
    return $this->method === 'POST';
  }
}
