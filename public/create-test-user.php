<?php
$base = '/home1/vit75277/repositories/lista-compras-laravel';
putenv('HOME=/home1/vit75277');
putenv('COMPOSER_HOME=/home1/vit75277/.composer');

// ── Proteção: mesmo DEPLOY_TOKEN do migrate.php ──────────────────────────────
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
})();
// ─────────────────────────────────────────────────────────────────────────────

$name     = $_GET['name']     ?? 'Usuário Teste';
$email    = $_GET['email']    ?? 'teste@smartlistiq.com.br';
$password = $_GET['password'] ?? 'Sm@rtL1st1q#T3st3!';

echo "<pre>";
echo "=== Criando usuário de teste ===\n\n";

$cmd = "cd {$base} && php artisan tinker --execute=\""
    . "use App\\\\Models\\\\User;"
    . "if (User::where('email', '{$email}')->exists()) {"
    . "    echo 'Usuário já existe: {$email}';"
    . "} else {"
    . "    \\\$u = User::create(['name'=>'{$name}','email'=>'{$email}','password'=>bcrypt('{$password}')]);"
    . "    echo 'Usuário criado! ID: ' . \\\$u->id . ' | E-mail: {$email} | Senha: {$password}';"
    . "}\" 2>&1";

echo shell_exec($cmd);

echo "\n\n=== Concluído! ===\n";
echo "</pre>";
