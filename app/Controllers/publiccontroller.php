<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Response;

final class PublicController
{
  public function home(): void
  {
    $contactEmail = $_ENV['PUBLIC_CONTACT_EMAIL'] ?? 'coordinator@divinemercy.my';
    (new Response())->view('public/home.php', [
      'contactEmail' => $contactEmail,
    ]);
  }
}
