<?php
// Proteção: só executa com o token correto na URL
// Acesse: https://smartlistiq.com.br/run-setup.php?token=admin2026
define('SECRET_TOKEN', 'admin2026');

if (($_GET['token'] ?? '') !== SECRET_TOKEN) {
    http_response_code(403);
    die('Acesso negado.');
}

// Credenciais do banco (do .env)
$host   = 'localhost';
$port   = '3306';
$dbname = 'vit75277_listacompras';
$user   = 'vit75277_admin_dev';
$pass   = 'UuQ4R.i[axH?';

echo '<pre style="font-family:monospace;background:#111;color:#0f0;padding:20px;font-size:13px">';
echo "=== Smart Listiq — Setup Admin ===\n\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✓ Conectado ao banco: $dbname\n\n";

    // 1. Adiciona coluna is_admin se não existir
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_admin'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER email");
        echo "✓ Coluna is_admin adicionada na tabela users\n";
    } else {
        echo "✓ Coluna is_admin já existe (pulando)\n";
    }

    // 2. Marca a migration como executada na tabela migrations
    $exists = $pdo->query(
        "SELECT COUNT(*) FROM migrations WHERE migration = '2024_01_01_000016_add_is_admin_to_users_table'"
    )->fetchColumn();
    if (!$exists) {
        $batch = $pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn() ?: 1;
        $pdo->exec("INSERT INTO migrations (migration, batch) VALUES ('2024_01_01_000016_add_is_admin_to_users_table', " . ((int)$batch + 1) . ")");
        echo "✓ Migration registrada na tabela migrations\n";
    }

    // 3. Define vitordeaguiar0@gmail.com como admin
    $affected = $pdo->exec("UPDATE users SET is_admin = 1 WHERE email = 'vitordeaguiar0@gmail.com'");
    if ($affected > 0) {
        echo "✓ vitordeaguiar0@gmail.com definido como admin\n";
    } else {
        echo "⚠ Usuário vitordeaguiar0@gmail.com não encontrado no banco.\n";
        echo "  (Cadastre-se no site primeiro, depois rode este script novamente)\n";
    }

    echo "\n=== Concluído com sucesso! ===\n";

} catch (Exception $e) {
    echo "✗ ERRO: " . $e->getMessage() . "\n";
}

echo '</pre>';

// Auto-deleta o arquivo após execução
unlink(__FILE__);
echo '<p style="font-family:sans-serif;color:green;font-family:sans-serif">✅ Arquivo deletado automaticamente por segurança.</p>';
