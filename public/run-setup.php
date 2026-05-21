<?php
// Acesse: https://smartlistiq.com.br/run-setup.php?token=admin2026
define('SECRET_TOKEN', 'admin2026');

if (($_GET['token'] ?? '') !== SECRET_TOKEN) {
    http_response_code(403);
    die('Acesso negado.');
}

$host   = 'localhost';
$port   = '3306';
$dbname = 'vit75277_listacompras';
$user   = 'vit75277_admin_dev';
$pass   = 'UuQ4R.i[axH?';

echo '<pre style="font-family:monospace;background:#111;color:#0f0;padding:20px;font-size:13px">';
echo "=== Smart Listiq — Migration: discount ===\n\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✓ Conectado ao banco: $dbname\n\n";

    // Adiciona coluna discount em shopping_lists
    $cols = $pdo->query("SHOW COLUMNS FROM shopping_lists LIKE 'discount'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE shopping_lists ADD COLUMN discount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER total");
        echo "✓ Coluna discount adicionada em shopping_lists\n";
    } else {
        echo "✓ Coluna discount já existe (pulando)\n";
    }

    // Registra migration
    $exists = $pdo->query(
        "SELECT COUNT(*) FROM migrations WHERE migration = '2024_01_01_000017_add_discount_to_shopping_lists_table'"
    )->fetchColumn();
    if (!$exists) {
        $batch = (int) $pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn();
        $pdo->exec("INSERT INTO migrations (migration, batch) VALUES ('2024_01_01_000017_add_discount_to_shopping_lists_table', " . ($batch + 1) . ")");
        echo "✓ Migration registrada\n";
    }

    echo "\n=== Concluído com sucesso! ===\n";

} catch (Exception $e) {
    echo "✗ ERRO: " . $e->getMessage() . "\n";
}

echo '</pre>';

unlink(__FILE__);
echo '<p style="font-family:sans-serif;color:green">✅ Arquivo deletado automaticamente.</p>';
