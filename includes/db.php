<?php
// Database connection singleton (SQLite via PDO)
function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            die(db_install_notice('Nie można utworzyć katalogu bazy danych: ' . $dir));
        }
    }

    if (!extension_loaded('pdo_sqlite')) {
        die(db_install_notice('Błąd: rozszerzenie PHP <strong>pdo_sqlite</strong> nie jest włączone na tym hostingu. Skontaktuj się z dostawcą hostingu.'));
    }

    try {
        $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        $pdo->exec('PRAGMA journal_mode = WAL;');
    } catch (PDOException $e) {
        die(db_install_notice('Błąd połączenia z bazą danych: ' . htmlspecialchars($e->getMessage())));
    }

    // Check if tables are installed (skip during install)
    $tableCheck = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='products'")->fetch();
    if (!$tableCheck && !defined('INSTALL_MODE')) {
        $installUrl = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
            . BASE_PATH . '/install.php?token=UniqueInstall2024';
        die(db_install_notice(
            'Baza danych nie jest zainstalowana. Kliknij poniższy link aby uruchomić instalację:',
            '<br><br><a href="' . htmlspecialchars($installUrl) . '" style="color:#C4714B;font-weight:700;font-size:1.1rem">'
            . '→ Uruchom install.php</a>'
        ));
    }

    return $pdo;
}

function db_install_notice(string $msg, string $extra = ''): string {
    return '<!DOCTYPE html><html lang="pl"><head><meta charset="UTF-8">
    <title>Unique Ceramics — Instalacja wymagana</title>
    <style>body{font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#FAF7F2;}
    .box{background:#fff;border-radius:12px;padding:2.5rem 2rem;max-width:500px;box-shadow:0 4px 20px rgba(0,0,0,.1);text-align:center;}
    h2{color:#8B6F5E;margin-bottom:1rem;} p{color:#6B6B6B;line-height:1.6;}</style></head>
    <body><div class="box"><div style="font-size:3rem">🏺</div><h2>Unique Ceramics</h2>
    <p>' . $msg . $extra . '</p></div></body></html>';
}

function db_query(string $sql, array $params = []): PDOStatement {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function db_fetch(string $sql, array $params = []): ?array {
    $row = db_query($sql, $params)->fetch();
    return $row ?: null;
}

function db_fetch_all(string $sql, array $params = []): array {
    return db_query($sql, $params)->fetchAll();
}

function db_insert(string $table, array $data): int {
    $cols   = implode(', ', array_keys($data));
    $places = implode(', ', array_fill(0, count($data), '?'));
    db_query("INSERT INTO {$table} ({$cols}) VALUES ({$places})", array_values($data));
    return (int)db()->lastInsertId();
}

function db_update(string $table, array $data, string $where, array $whereParams = []): void {
    $set = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
    db_query("UPDATE {$table} SET {$set} WHERE {$where}", [...array_values($data), ...$whereParams]);
}
