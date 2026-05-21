<?php
// Proteção: só executa com o token correto na URL
// Acesse: https://smartlistiq.com.br/run-setup.php?token=admin2026
define('SECRET_TOKEN', 'admin2026');

if (($_GET['token'] ?? '') !== SECRET_TOKEN) {
    http_response_code(403);
    die('Acesso negado.');
}

$artisan = __DIR__ . '/../artisan';

if (!file_exists($artisan)) {
    die('Arquivo artisan não encontrado em: ' . $artisan);
}

$php = PHP_BINARY ?: 'php';

echo '<pre style="font-family:monospace;background:#111;color:#0f0;padding:20px;font-size:13px">';
echo "=== Smart Listiq — Setup Admin ===\n\n";

// 1. Migrate
echo "▶ Rodando: php artisan migrate --force\n";
$output = shell_exec("$php $artisan migrate --force 2>&1");
echo htmlspecialchars($output) . "\n";

// 2. AdminSeeder
echo "▶ Rodando: php artisan db:seed --class=AdminSeeder --force\n";
$output = shell_exec("$php $artisan db:seed --class=AdminSeeder --force 2>&1");
echo htmlspecialchars($output) . "\n";

echo "=== Concluído! ===\n";
echo '</pre>';

// Auto-deleta o arquivo após execução
unlink(__FILE__);
echo '<p style="font-family:sans-serif;color:green">✅ Arquivo deletado automaticamente por segurança.</p>';
