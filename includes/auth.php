<?php
function admin_login(string $username, string $password): bool {
    $admin = db_fetch('SELECT * FROM admins WHERE username = ?', [$username]);
    if (!$admin) return false;
    if (!password_verify($password, $admin['password_hash'])) return false;

    $_SESSION['admin_id']   = $admin['id'];
    $_SESSION['admin_name'] = $admin['name'] ?: $admin['username'];
    session_regenerate_id(true);
    return true;
}

function admin_logout(): void {
    unset($_SESSION['admin_id'], $_SESSION['admin_name']);
    session_regenerate_id(true);
}

function admin_change_password(int $adminId, string $newPassword): void {
    $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    db_query('UPDATE admins SET password_hash = ? WHERE id = ?', [$hash, $adminId]);
}
