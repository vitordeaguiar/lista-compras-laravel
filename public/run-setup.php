<?php
// Proteção: só executa com o token correto na URL
// Acesse: https://smartlistiq.com.br/run-setup.php?token=admin2026
define('SECRET_TOKEN', 'admin2026');

if (($_GET['token'] ?? '') !== SECRET_TOKEN) {
    http_response_code(403);
    die('Acesso negado.');
}

// Tenta encontrar o artisan em múltiplos caminhos comuns de hospedagem
$candidates = [
    __DIR__ . '/../artisan',                          // padrão Laravel (public/ dentro do projeto)
    __DIR__ . '/../../artisan',                       // public_html é symlink dentro de subpasta
    '/home1/vit75277/artisan',                        // raiz do usuário cPanel
    '/home/vit75277/artisan',
    dirname(__DIR__) . '/artisan',
];

$artisan = null;
foreach ($candidates as $path) {
    if (file_exists($path)) {
        $artisan = realpath($path);
        break;
    }
}

if (!$artisan) {
    echo '<pre style="font-family:monospace;background:#111;color:#f00;padding:20px">';
    echo "Artisan não encontrado. Caminhos testados:\n";
    foreach ($candidates as $p) echo "  - $p\n";
    echo "\n__DIR__ = " . __DIR__ . "\n";
    echo "Conteúdo de " . dirname(__DIR__) . ":\n";
    $files = @scandir(dirname(__DIR__)) ?: [];
    foreach ($files as $f) echo "  $f\n";
    echo '</pre>';
    exit;
}

$php = PHP_BINARY ?: 'php';

echo '<pre style="font-family:monospace;background:#111;color:#0f0;padding:20px;font-size:13px">';
echo "=== Smart Listiq — Setup Admin ===\n";
echo "Artisan encontrado em: $artisan\n\n";

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
