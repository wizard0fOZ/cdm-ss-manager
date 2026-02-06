<?php
namespace App\Controllers;

use App\Core\Db\Db;
use App\Core\Http\Response;
use App\Core\Http\Request;
use App\Core\Support\Flash;

final class AdminController
{
  private string $defaultPassword = 'CDM2026!';

  public function index(): void
  {
    (new Response())->redirect('/admin/users');
  }

  public function roles(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    $pdo = Db::pdo();
    $roles = $pdo->query('SELECT * FROM roles ORDER BY name ASC')->fetchAll();
    $perms = $pdo->query('SELECT * FROM permissions ORDER BY module ASC, name ASC')->fetchAll();
    $rolePerms = $pdo->query('SELECT rp.role_id, p.code FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id')->fetchAll();
    $map = [];
    foreach ($rolePerms as $row) {
      $map[$row['role_id']][] = $row['code'];
    }

    (new Response())->view('admin/roles/index.php', [
      'roles' => $roles,
      'permissions' => $perms,
      'rolePerms' => $map,
    ]);
  }

  public function updateRoles(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    $roleId = (int)($_POST['role_id'] ?? 0);
    $perms = $_POST['permissions'] ?? [];
    $perms = array_filter(array_map('trim', $perms));
    if ($roleId <= 0) {
      Flash::set('error', 'Role is required.');
      (new Response())->redirect('/admin/roles');
      return;
    }

    $pdo = Db::pdo();
    $pdo->beginTransaction();
    try {
      $pdo->prepare('DELETE FROM role_permissions WHERE role_id = ?')->execute([$roleId]);
      if ($perms) {
        $stmt = $pdo->prepare('SELECT id FROM permissions WHERE code IN (' . implode(',', array_fill(0, count($perms), '?')) . ')');
        $stmt->execute($perms);
        $permIds = $stmt->fetchAll();
        foreach ($permIds as $perm) {
          $pdo->prepare('INSERT INTO role_permissions (role_id, permission_id, assigned_by) VALUES (?,?,?)')
            ->execute([$roleId, $perm['id'], $userId]);
        }
      }
      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }

    Flash::set('success', 'Role permissions updated.');
    (new Response())->redirect('/admin/roles');
  }

  public function settings(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    $pdo = Db::pdo();
    $settings = $pdo->query('SELECT * FROM system_settings ORDER BY setting_key ASC')->fetchAll();
    (new Response())->view('admin/settings/index.php', [
      'settings' => $settings,
    ]);
  }

  public function updateSettings(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    if (!empty($_POST['maintenance_batch'])) {
      $mode = strtoupper(trim($_POST['maintenance_mode'] ?? ''));
      $message = trim($_POST['maintenance_message'] ?? '');
      if ($mode === 'OFF') {
        $message = '';
      }
      $password = trim($_POST['override_password'] ?? '');
      if (!$this->verifyPassword($userId, $password)) {
        Flash::set('error', 'SysAdmin password required for maintenance settings.');
        (new Response())->redirect('/admin/settings');
        return;
      }

      $pdo = Db::pdo();
      $pdo->beginTransaction();
      try {
        $stmt = $pdo->prepare('INSERT INTO system_settings (setting_key, setting_value, updated_by) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)');
        if ($mode !== '') {
          $stmt->execute(['maintenance_mode', $mode, $userId]);
        }
        $stmt->execute(['maintenance_message', $message, $userId]);
        $pdo->commit();
      } catch (\Throwable $e) {
        $pdo->rollBack();
        throw $e;
      }

      Flash::set('success', 'Maintenance settings updated.');
      (new Response())->redirect('/admin/settings');
      return;
    }

    $key = trim($_POST['setting_key'] ?? '');
    $value = trim($_POST['setting_value'] ?? '');
    $password = trim($_POST['override_password'] ?? '');
    if ($key === '') {
      Flash::set('error', 'Setting key is required.');
      (new Response())->redirect('/admin/settings');
      return;
    }

    $protected = ['maintenance_mode', 'maintenance_message'];
    if (in_array($key, $protected, true)) {
      if (!$this->verifyPassword($userId, $password)) {
        Flash::set('error', 'SysAdmin password required for maintenance settings.');
        (new Response())->redirect('/admin/settings');
        return;
      }
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('INSERT INTO system_settings (setting_key, setting_value, updated_by) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)');
    $stmt->execute([$key, $value, $userId]);

    Flash::set('success', 'Setting saved.');
    (new Response())->redirect('/admin/settings');
  }

  public function monitoring(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    $actor = trim((string)$request->input('actor', ''));
    $entity = trim((string)$request->input('entity', ''));
    $start = trim((string)$request->input('start', ''));
    $end = trim((string)$request->input('end', ''));

    $pdo = Db::pdo();
    $imports = $pdo->query('SELECT * FROM import_jobs ORDER BY created_at DESC LIMIT 25')->fetchAll();
    $auditFilters = [];
    $auditParams = [];
    if ($actor !== '') {
      $auditFilters[] = 'actor_user_id = ?';
      $auditParams[] = (int)$actor;
    }
    if ($entity !== '') {
      if (ctype_digit($entity)) {
        $auditFilters[] = 'entity_id = ?';
        $auditParams[] = (int)$entity;
      } else {
        $auditFilters[] = 'entity_type LIKE ?';
        $auditParams[] = '%' . $entity . '%';
      }
    }
    if ($start !== '') {
      $auditFilters[] = 'created_at >= ?';
      $auditParams[] = $start . ' 00:00:00';
    }
    if ($end !== '') {
      $auditFilters[] = 'created_at <= ?';
      $auditParams[] = $end . ' 23:59:59';
    }
    $auditSql = 'SELECT * FROM audit_logs';
    if ($auditFilters) {
      $auditSql .= ' WHERE ' . implode(' AND ', $auditFilters);
    }
    $auditSql .= ' ORDER BY created_at DESC LIMIT 100';
    $auditStmt = $pdo->prepare($auditSql);
    $auditStmt->execute($auditParams);
    $audits = $auditStmt->fetchAll();

    (new Response())->view('admin/monitoring/index.php', [
      'imports' => $imports,
      'audits' => $audits,
      'auditActor' => $actor,
      'auditEntity' => $entity,
      'auditStart' => $start,
      'auditEnd' => $end,
    ]);
  }

  public function auditShow(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    $id = (int)$request->param('id');
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT * FROM audit_logs WHERE id = ?');
    $stmt->execute([$id]);
    $audit = $stmt->fetch();
    if (!$audit) {
      (new Response())->status(404)->view('errors/error.php', [
        'title' => 'Audit not found',
        'message' => 'The audit record does not exist.',
        'details' => 'Audit id ' . $id,
      ]);
      return;
    }

    (new Response())->view('admin/monitoring/audit_show.php', [
      'audit' => $audit,
    ]);
  }

  public function maintenance(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    $pdo = Db::pdo();
    $orphanEnrollments = (int)$pdo->query('SELECT COUNT(*) FROM student_class_enrollments sce LEFT JOIN students s ON s.id = sce.student_id WHERE s.id IS NULL')->fetchColumn();
    $orphanAttendance = (int)$pdo->query('SELECT COUNT(*) FROM attendance_records ar LEFT JOIN students s ON s.id = ar.student_id WHERE s.id IS NULL')->fetchColumn();
    $orphanClasses = (int)$pdo->query('SELECT COUNT(*) FROM classes c LEFT JOIN sessions s ON s.id = c.session_id WHERE s.id IS NULL')->fetchColumn();

    (new Response())->view('admin/maintenance/index.php', [
      'orphanEnrollments' => $orphanEnrollments,
      'orphanAttendance' => $orphanAttendance,
      'orphanClasses' => $orphanClasses,
    ]);
  }

  public function maintenanceAction(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    $action = trim($_POST['action'] ?? '');
    $password = trim($_POST['override_password'] ?? '');
    if (!$this->verifyPassword($userId, $password)) {
      Flash::set('error', 'SysAdmin password required.');
      (new Response())->redirect('/admin/maintenance');
      return;
    }

    $pdo = Db::pdo();
    if ($action === 'cleanup_orphan_enrollments') {
      $pdo->exec('DELETE sce FROM student_class_enrollments sce LEFT JOIN students s ON s.id = sce.student_id WHERE s.id IS NULL');
      Flash::set('success', 'Orphan enrollments removed.');
    } elseif ($action === 'cleanup_orphan_attendance') {
      $pdo->exec('DELETE ar FROM attendance_records ar LEFT JOIN students s ON s.id = ar.student_id WHERE s.id IS NULL');
      Flash::set('success', 'Orphan attendance records removed.');
    } else {
      Flash::set('error', 'Unknown maintenance action.');
    }

    (new Response())->redirect('/admin/maintenance');
  }

  public function switchRole(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    $roleId = (int)($_POST['role_id'] ?? 0);
    if ($roleId <= 0) {
      unset($_SESSION['_role_override'], $_SESSION['_role_override_name'], $_SESSION['_role_override_code'], $_SESSION['_perm_cache']);
      Flash::set('success', 'Role view reset.');
      (new Response())->redirect($_SERVER['HTTP_REFERER'] ?? '/dashboard');
      return;
    }

    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT id, name, code FROM roles WHERE id = ?');
    $stmt->execute([$roleId]);
    $role = $stmt->fetch();
    if (!$role) {
      Flash::set('error', 'Role not found.');
      (new Response())->redirect($_SERVER['HTTP_REFERER'] ?? '/dashboard');
      return;
    }

    $_SESSION['_role_override'] = (int)$role['id'];
    $_SESSION['_role_override_name'] = $role['name'];
    $_SESSION['_role_override_code'] = $role['code'] ?? null;
    unset($_SESSION['_perm_cache']);

    Flash::set('success', 'Now viewing as ' . $role['name'] . '.');
    (new Response())->redirect($_SERVER['HTTP_REFERER'] ?? '/dashboard');
  }
  public function users(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    $q = trim((string)$request->input('q', ''));
    $status = trim((string)$request->input('status', ''));

    $filters = [];
    $params = [];
    if ($q !== '') {
      $filters[] = '(u.full_name LIKE ? OR u.email LIKE ?)';
      $params[] = "%$q%";
      $params[] = "%$q%";
    }
    if ($status !== '') {
      $filters[] = 'u.status = ?';
      $params[] = $status;
    }

    $pdo = Db::pdo();
    $sql = 'SELECT u.* FROM users u';
    if ($filters) {
      $sql .= ' WHERE ' . implode(' AND ', $filters);
    }
    $sql .= ' ORDER BY u.full_name ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    $roles = $pdo->query('SELECT id, code, name FROM roles ORDER BY name ASC')->fetchAll();
    $userRoles = $pdo->query('SELECT ur.user_id, r.code FROM user_roles ur JOIN roles r ON r.id = ur.role_id')->fetchAll();
    $roleMap = [];
    foreach ($userRoles as $row) {
      $roleMap[$row['user_id']][] = $row['code'];
    }

    (new Response())->view('admin/users/index.php', [
      'users' => $users,
      'roles' => $roles,
      'roleMap' => $roleMap,
      'q' => $q,
      'status' => $status,
    ]);
  }

  public function create(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    $pdo = Db::pdo();
    $roles = $pdo->query('SELECT id, code, name FROM roles ORDER BY name ASC')->fetchAll();

    (new Response())->view('admin/users/create.php', [
      'roles' => $roles,
    ]);
  }

  public function store(): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $status = $_POST['status'] ?? 'ACTIVE';
    $roles = $_POST['roles'] ?? [];

    $errors = [];
    if ($fullName === '') $errors[] = 'Full name is required.';
    if ($email === '') $errors[] = 'Email is required.';
    if (!in_array($status, ['ACTIVE','INACTIVE'], true)) $errors[] = 'Status is invalid.';

    $pdo = Db::pdo();
    if ($errors) {
      $roleRows = $pdo->query('SELECT id, code, name FROM roles ORDER BY name ASC')->fetchAll();
      (new Response())->view('admin/users/create.php', [
        'errors' => $errors,
        'roles' => $roleRows,
        'user' => $_POST,
      ]);
      return;
    }

    $hash = password_hash($this->defaultPassword, PASSWORD_DEFAULT);
    $pdo->beginTransaction();
    try {
      $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, status, must_change_password) VALUES (?,?,?,?,?)');
      $stmt->execute([$fullName, $email, $hash, $status, 1]);
      $newUserId = (int)$pdo->lastInsertId();

      $this->syncRoles($pdo, $newUserId, $roles, $userId);
      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }

    Flash::set('success', 'User created. Temp password set to ' . $this->defaultPassword . '.');
    (new Response())->redirect('/admin/users');
  }

  public function edit(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    $id = (int)$request->param('id');
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) {
      (new Response())->status(404)->html('User not found');
      return;
    }

    $roles = $pdo->query('SELECT id, code, name FROM roles ORDER BY name ASC')->fetchAll();
    $userRoles = $pdo->prepare('SELECT r.code FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ?');
    $userRoles->execute([$id]);
    $assigned = array_map(fn($row) => $row['code'], $userRoles->fetchAll());

    (new Response())->view('admin/users/edit.php', [
      'user' => $user,
      'roles' => $roles,
      'assigned' => $assigned,
    ]);
  }

  public function update(Request $request): void
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$this->isSysAdmin($userId)) {
      (new Response())->status(403)->view('errors/403.php', ['code' => 'admin']);
      return;
    }

    $id = (int)$request->param('id');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $status = $_POST['status'] ?? 'ACTIVE';
    $mustChange = !empty($_POST['must_change_password']) ? 1 : 0;
    $resetPassword = !empty($_POST['reset_password']);
    $roles = $_POST['roles'] ?? [];

    $errors = [];
    if ($fullName === '') $errors[] = 'Full name is required.';
    if ($email === '') $errors[] = 'Email is required.';
    if (!in_array($status, ['ACTIVE','INACTIVE'], true)) $errors[] = 'Status is invalid.';

    $pdo = Db::pdo();
    if ($errors) {
      $roleRows = $pdo->query('SELECT id, code, name FROM roles ORDER BY name ASC')->fetchAll();
      (new Response())->view('admin/users/edit.php', [
        'errors' => $errors,
        'roles' => $roleRows,
        'assigned' => $roles,
        'user' => array_merge(['id' => $id], $_POST),
      ]);
      return;
    }

    $pdo->beginTransaction();
    try {
      if ($resetPassword) {
        $hash = password_hash($this->defaultPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET full_name=?, email=?, status=?, must_change_password=?, password_hash=? WHERE id=?');
        $stmt->execute([$fullName, $email, $status, 1, $hash, $id]);
      } else {
        $stmt = $pdo->prepare('UPDATE users SET full_name=?, email=?, status=?, must_change_password=? WHERE id=?');
        $stmt->execute([$fullName, $email, $status, $mustChange, $id]);
      }

      $this->syncRoles($pdo, $id, $roles, $userId);
      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }

    Flash::set('success', 'User updated.');
    (new Response())->redirect('/admin/users');
  }

  private function syncRoles(\PDO $pdo, int $userId, array $roles, int $actorId): void
  {
    $roles = array_filter(array_map('trim', $roles));
    $pdo->prepare('DELETE FROM user_roles WHERE user_id = ?')->execute([$userId]);
    if (!$roles) return;

    $stmt = $pdo->prepare('SELECT id, code FROM roles WHERE code IN (' . implode(',', array_fill(0, count($roles), '?')) . ')');
    $stmt->execute($roles);
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
      $pdo->prepare('INSERT INTO user_roles (user_id, role_id, assigned_by) VALUES (?,?,?)')->execute([$userId, $row['id'], $actorId]);
    }
  }

  private function isSysAdmin(int $userId): bool
  {
    if ($userId <= 0) return false;
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT 1 FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND r.code = ? LIMIT 1');
    $stmt->execute([$userId, 'SYSADMIN']);
    return (bool)$stmt->fetchColumn();
  }

  private function verifyPassword(int $userId, string $password): bool
  {
    if ($userId <= 0 || $password === '') return false;
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $hash = $stmt->fetchColumn();
    if (!$hash) return false;
    return password_verify($password, $hash);
  }
}
