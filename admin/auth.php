<?php
/**
 * Авторизация админ-панели
 */

function adminIsLoggedIn(): bool
{
    return !empty($_SESSION['admin_user_id']);
}

function adminRequireAuth(): void
{
    if (!adminIsLoggedIn()) {
        header('Location: ' . ADMIN_URL . '/login');
        exit;
    }
}

function adminLogin(string $username, string $password): bool
{
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            return true;
        }
    } catch (PDOException $e) {
        // log
    }
    return false;
}

function adminLogout(): void
{
    unset($_SESSION['admin_user_id'], $_SESSION['admin_username']);
    session_destroy();
}
