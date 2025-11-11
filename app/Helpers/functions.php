<?php

function isAuth(): bool {
    return isset($_SESSION['user_id']);
}

function auth(): ?array {
    if (!isAuth()) {
        return null;
    }
    return $_SESSION['user'] ?? null;
}

function hasRole(string $role): bool {
    $userRoles = $_SESSION['user_roles'] ?? [];
    return in_array($role, $userRoles);
}

function old(string $key, $default = '') {
    return $_SESSION['old'][$key] ?? $default;
}

function csrf(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function dd(...$vars): void {
    foreach ($vars as $var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
    die();
}