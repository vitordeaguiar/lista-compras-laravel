<?php
$base = '/home1/vit75277/repositories/lista-compras-laravel';
putenv('HOME=/home1/vit75277');
putenv('COMPOSER_HOME=/home1/vit75277/.composer');

// ── Proteção: token forte (DEPLOY_TOKEN) + IP allowlist opcional (DEPLOY_ALLOWED_IPS) ──
(function () use ($base) {
    $env = [];
    $envFile = $base . '/.env';
    if (is_readable($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim(trim($v), "\"'");
        }
    }
    $token = $env['DEPLOY_TOKEN'] ?? '';
    if ($token === '' || !hash_equals($token, (string) ($_GET['token'] ?? ''))) {
        http_response_code(403);
        exit('Acesso negado.');
    }
    $allow = array_filter(array_map('trim', explode(',', $env['DEPLOY_ALLOWED_IPS'] ?? '')));
    if ($allow && !in_array($_SERVER['REMOTE_ADDR'] ?? '', $allow, true)) {
        http_response_code(403);
        exit('Acesso negado.');
    }
})();
// ──────────────────────────────────────────────────────────────────────────────────────

echo "<pre>";
echo "=== git pull ===\n";
echo shell_exec("cd {$base} && git pull origin main 2>&1");

// NOTA: key:generate --force foi removido de propósito — regenerar a APP_KEY em
// cada deploy invalida todas as sessões e quebra dados encriptados existentes.

echo "\n=== optimize:clear ===\n";
echo shell_exec("cd {$base} && php artisan optimize:clear 2>&1");

echo "\n=== route:clear ===\n";
echo shell_exec("cd {$base} && php artisan route:clear 2>&1");

echo "\n=== config:clear ===\n";
echo shell_exec("cd {$base} && php artisan config:clear 2>&1");

echo "\n=== cache:clear ===\n";
echo shell_exec("cd {$base} && php artisan cache:clear 2>&1");

echo "\n=== view:clear ===\n";
echo shell_exec("cd {$base} && php artisan view:clear 2>&1");

echo "\n=== Concluído! ===\n";
echo "</pre>";
