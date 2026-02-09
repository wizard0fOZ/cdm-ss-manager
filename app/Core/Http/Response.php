<?php
declare(strict_types=1);

namespace App\Core\Http;

class Response
{
    public function status(int $code): self
    {
        http_response_code($code);
        return $this;
    }

    public function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    public function html(string $html): void
    {
        echo $html;
        exit;
    }

    public function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    public function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../../views/' . $view;
        exit;
    }
}
