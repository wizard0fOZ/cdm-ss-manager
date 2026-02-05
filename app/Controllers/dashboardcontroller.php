<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;

final class DashboardController
{
  public function index(): void
  {
    (new Response())->view('dashboard/index.php');
  }

  public function health(): void
  {
    $pdo = Db::pdo();
    $v = $pdo->query('SELECT VERSION()')->fetchColumn();

    (new Response())->json([
      'ok' => true,
      'mysql_version' => $v,
      'time' => date('c'),
    ]);
  }
}
