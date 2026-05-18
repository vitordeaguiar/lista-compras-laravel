<?php
/**
 * Script de setup inicial para ambiente de produção (Hostgator).
 * ATENÇÃO: Delete este arquivo após concluir a configuração.
 */

define('BASE_PATH', '/home1/vit75277/repositories/lista-compras-laravel');
define('ENV_FILE', BASE_PATH . '/.env');
define('ENV_EXAMPLE', BASE_PATH . '/.env.example');
define('PHP_BIN', '/usr/local/bin/php');

$message = '';
$messageType = '';
$step = $_GET['step'] ?? 'configure';

// ── Detecção automática do Composer ───────────────────────────────────────

function detectComposer(): array
{
    $searches = [];

    $out = []; $code = 1;
    exec('which composer 2>&1', $out, $code);
    $searches[] = [
        'cmd'    => 'which composer',
        'output' => trim(implode("\n", $out)),
        'found'  => $code === 0 && !empty($out[0]) && trim($out[0]) !== '',
    ];

    $out = []; $code = 1;
    exec('whereis composer 2>&1', $out, $code);
    $searches[] = [
        'cmd'    => 'whereis composer',
        'output' => trim(implode("\n", $out)),
        'found'  => $code === 0 && strlen(trim(implode('', $out))) > strlen('composer:'),
    ];

    $out = []; $code = 1;
    exec('find /usr /opt /home -name composer 2>/dev/null', $out, $code);
    $searches[] = [
        'cmd'    => 'find /usr /opt /home -name composer',
        'output' => trim(implode("\n", $out)),
        'found'  => !empty($out),
    ];

    return $searches;
}

function resolveComposerBin(): string
{
    $out = [];
    exec('which composer 2>/dev/null', $out);
    if (!empty($out[0]) && is_executable(trim($out[0]))) {
        return trim($out[0]);
    }

    $out = [];
    exec('find /usr /opt /home -name composer -type f 2>/dev/null', $out);
    foreach ($out as $path) {
        $path = trim($path);
        if ($path !== '' && is_executable($path)) {
            return $path;
        }
    }

    return '/usr/local/bin/composer';
}

$composerBin      = resolveComposerBin();
$composerDetected = detectComposer();

function runArtisan(string $command): array
{
    $artisan = BASE_PATH . '/artisan';
    $output = [];
    $code = 0;
    exec(PHP_BIN . " $artisan $command 2>&1", $output, $code);
    return ['output' => implode("\n", $output), 'code' => $code];
}

function runComposer(string $command, string $composerBin): array
{
    $output = [];
    $code = 0;
    $env = 'HOME=/home1/vit75277 COMPOSER_HOME=/home1/vit75277/.composer';
    exec("$env " . PHP_BIN . ' ' . escapeshellarg($composerBin) . " $command --no-interaction --working-dir=" . BASE_PATH . " 2>&1", $output, $code);
    return ['output' => implode("\n", $output), 'code' => $code];
}

function envValue(string $key): string
{
    if (!file_exists(ENV_FILE)) return '';
    foreach (file(ENV_FILE) as $line) {
        $line = trim($line);
        if (str_starts_with($line, $key . '=')) {
            return trim(substr($line, strlen($key) + 1), '"\'');
        }
    }
    return '';
}

// ── Processar ações POST ───────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_env') {
        $fields = [
            'APP_NAME'         => '"' . ($_POST['app_name'] ?? 'Lista de Compras') . '"',
            'APP_ENV'          => 'production',
            'APP_KEY'          => $_POST['app_key'] ?? '',
            'APP_DEBUG'        => 'false',
            'APP_URL'          => $_POST['app_url'] ?? '',
            'LOG_CHANNEL'      => 'stack',
            'LOG_LEVEL'        => 'error',
            'DB_CONNECTION'    => 'mysql',
            'DB_HOST'          => $_POST['db_host'] ?? '127.0.0.1',
            'DB_PORT'          => $_POST['db_port'] ?? '3306',
            'DB_DATABASE'      => $_POST['db_database'] ?? '',
            'DB_USERNAME'      => $_POST['db_username'] ?? '',
            'DB_PASSWORD'      => $_POST['db_password'] ?? '',
            'CACHE_STORE'      => 'database',
            'SESSION_DRIVER'   => 'database',
            'SESSION_LIFETIME' => '120',
            'QUEUE_CONNECTION' => 'sync',
        ];

        $content = '';
        foreach ($fields as $key => $value) {
            $content .= "$key=$value\n";
        }

        if (file_put_contents(ENV_FILE, $content) !== false) {
            $message = '.env salvo com sucesso.';
            $messageType = 'success';
            $step = 'commands';
        } else {
            $message = 'Erro ao salvar .env. Verifique as permissões do diretório.';
            $messageType = 'error';
        }
    }

    if ($action === 'run_key_generate') {
        $result = runArtisan('key:generate --force');
        $message = $result['code'] === 0
            ? 'APP_KEY gerada com sucesso.'
            : 'Erro ao gerar key: ' . $result['output'];
        $messageType = $result['code'] === 0 ? 'success' : 'error';
        $step = 'commands';
    }

    if ($action === 'run_migrate') {
        $result = runArtisan('migrate --force');
        $message = $result['code'] === 0
            ? "Migrations executadas:\n" . $result['output']
            : 'Erro nas migrations: ' . $result['output'];
        $messageType = $result['code'] === 0 ? 'success' : 'error';
        $step = 'commands';
    }

    if ($action === 'run_optimize') {
        $result = runArtisan('optimize');
        $message = $result['code'] === 0
            ? 'Cache otimizado com sucesso.'
            : 'Erro ao otimizar: ' . $result['output'];
        $messageType = $result['code'] === 0 ? 'success' : 'error';
        $step = 'commands';
    }

    if ($action === 'run_composer_install') {
        $result = runComposer('install --no-dev --optimize-autoloader', $composerBin);
        $message = $result['code'] === 0
            ? "Dependências instaladas:\n" . $result['output']
            : 'Erro no composer install: ' . $result['output'];
        $messageType = $result['code'] === 0 ? 'success' : 'error';
        $step = 'commands';
    }

    if ($action === 'fix_permissions') {
        $dirs = [
            BASE_PATH . '/storage',
            BASE_PATH . '/bootstrap/cache',
        ];
        $errors = [];
        foreach ($dirs as $dir) {
            if (is_dir($dir) && !chmod($dir, 0775)) {
                $errors[] = $dir;
            }
        }
        $message = empty($errors)
            ? 'Permissões ajustadas.'
            : 'Não foi possível ajustar: ' . implode(', ', $errors);
        $messageType = empty($errors) ? 'success' : 'error';
        $step = 'commands';
    }
}

// ── Verificações de ambiente ───────────────────────────────────────────────

$checks = [
    'PHP >= 8.2 (' . PHP_VERSION . ')'          => version_compare(PHP_VERSION, '8.2.0', '>='),
    'php bin: ' . PHP_BIN                        => is_executable(PHP_BIN),
    'composer bin: ' . $composerBin              => is_executable($composerBin),
    '.env existe'                                 => file_exists(ENV_FILE),
    'vendor/ existe'                              => is_dir(BASE_PATH . '/vendor'),
    'storage gravável'                            => is_writable(BASE_PATH . '/storage'),
    'bootstrap/cache gravável'                    => is_writable(BASE_PATH . '/bootstrap/cache'),
    'APP_KEY definida'                            => !empty(envValue('APP_KEY')),
    'PDO MySQL disponível'                        => in_array('mysql', PDO::getAvailableDrivers()),
];

$appKey   = envValue('APP_KEY');
$appUrl   = envValue('APP_URL');
$dbHost   = envValue('DB_HOST');
$dbPort   = envValue('DB_PORT');
$dbName   = envValue('DB_DATABASE');
$dbUser   = envValue('DB_USERNAME');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup — Lista de Compras</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: system-ui, sans-serif; background: #f1f5f9; color: #1e293b; min-height: 100vh; padding: 2rem 1rem; }
  .container { max-width: 760px; margin: 0 auto; }
  h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.25rem; }
  .subtitle { color: #64748b; font-size: 0.9rem; margin-bottom: 2rem; }
  .warning { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.875rem; color: #92400e; }
  .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.08); padding: 1.5rem; margin-bottom: 1.5rem; }
  .card h2 { font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #0f172a; }
  .checks { display: grid; gap: 0.5rem; }
  .check { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; }
  .check .icon { font-size: 1rem; }
  label { display: block; font-size: 0.8rem; font-weight: 500; color: #475569; margin-bottom: 0.3rem; margin-top: 0.75rem; }
  input[type=text], input[type=password], input[type=url] {
    width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px;
    font-size: 0.875rem; color: #1e293b; background: #f8fafc; outline: none;
  }
  input:focus { border-color: #6366f1; background: #fff; }
  .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1rem; }
  .btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1.2rem;
    border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: opacity .15s; }
  .btn:hover { opacity: .85; }
  .btn-primary { background: #6366f1; color: #fff; }
  .btn-secondary { background: #e2e8f0; color: #475569; }
  .btn-danger { background: #ef4444; color: #fff; }
  .btn-group { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 1rem; }
  .alert { border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.875rem; white-space: pre-wrap; }
  .alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
  .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
  .tabs { display: flex; gap: 0.25rem; margin-bottom: 1.5rem; }
  .tab { padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; cursor: pointer; text-decoration: none; color: #64748b; background: #e2e8f0; }
  .tab.active { background: #6366f1; color: #fff; }
</style>
</head>
<body>
<div class="container">
  <h1>Setup — Lista de Compras</h1>
  <p class="subtitle">Configure o ambiente de produção na Hostgator</p>

  <div class="warning">
    ⚠️ <strong>Atenção:</strong> Delete este arquivo (<code>setup.php</code>) imediatamente após concluir a configuração.
  </div>

  <?php if ($message): ?>
  <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="tabs">
    <a href="?step=configure" class="tab <?= $step === 'configure' ? 'active' : '' ?>">1. Configurar .env</a>
    <a href="?step=commands"  class="tab <?= $step === 'commands'  ? 'active' : '' ?>">2. Comandos</a>
    <a href="?step=checks"    class="tab <?= $step === 'checks'    ? 'active' : '' ?>">3. Verificações</a>
  </div>

  <?php if ($step === 'configure'): ?>
  <div class="card">
    <h2>Configurar .env</h2>
    <form method="POST">
      <input type="hidden" name="action" value="save_env">

      <label>Nome da Aplicação</label>
      <input type="text" name="app_name" value="Lista de Compras">

      <label>URL da Aplicação</label>
      <input type="url" name="app_url" value="<?= htmlspecialchars($appUrl ?: 'https://') ?>" placeholder="https://seudominio.com.br">

      <label>APP_KEY (deixe em branco para gerar depois)</label>
      <input type="text" name="app_key" value="<?= htmlspecialchars($appKey) ?>" placeholder="base64:...">

      <hr style="margin:1.25rem 0; border-color:#e2e8f0;">
      <h2 style="margin-bottom:0">Banco de Dados (MySQL)</h2>

      <div class="grid-2">
        <div>
          <label>Host</label>
          <input type="text" name="db_host" value="<?= htmlspecialchars($dbHost ?: '127.0.0.1') ?>">
        </div>
        <div>
          <label>Porta</label>
          <input type="text" name="db_port" value="<?= htmlspecialchars($dbPort ?: '3306') ?>">
        </div>
      </div>

      <label>Nome do Banco</label>
      <input type="text" name="db_database" value="<?= htmlspecialchars($dbName) ?>" placeholder="nome_do_banco">

      <label>Usuário</label>
      <input type="text" name="db_username" value="<?= htmlspecialchars($dbUser) ?>" placeholder="usuario_db">

      <label>Senha</label>
      <input type="password" name="db_password" placeholder="senha">

      <div class="btn-group">
        <button type="submit" class="btn btn-primary">Salvar .env</button>
      </div>
    </form>
  </div>

  <?php elseif ($step === 'commands'): ?>
  <div class="card">
    <h2>Comandos Artisan</h2>
    <p style="font-size:.85rem;color:#64748b;margin-bottom:1rem;">Execute na ordem recomendada.</p>

    <form method="POST" style="margin-bottom:.75rem;">
      <input type="hidden" name="action" value="fix_permissions">
      <button class="btn btn-secondary">🔒 Ajustar permissões (storage / bootstrap/cache)</button>
    </form>

    <form method="POST" style="margin-bottom:.75rem;">
      <input type="hidden" name="action" value="run_composer_install">
      <button class="btn btn-secondary">📦 composer install --no-dev --optimize-autoloader</button>
    </form>

    <form method="POST" style="margin-bottom:.75rem;">
      <input type="hidden" name="action" value="run_key_generate">
      <button class="btn btn-secondary">🔑 php artisan key:generate</button>
    </form>

    <form method="POST" style="margin-bottom:.75rem;">
      <input type="hidden" name="action" value="run_migrate">
      <button class="btn btn-secondary">🗄️ php artisan migrate --force</button>
    </form>

    <form method="POST">
      <input type="hidden" name="action" value="run_optimize">
      <button class="btn btn-secondary">⚡ php artisan optimize</button>
    </form>
  </div>

  <?php elseif ($step === 'checks'): ?>
  <div class="card">
    <h2>Verificações de Ambiente</h2>
    <div class="checks">
      <?php foreach ($checks as $label => $ok): ?>
      <div class="check">
        <span class="icon"><?= $ok ? '✅' : '❌' ?></span>
        <span><?= htmlspecialchars($label) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card">
    <h2>Localização do Composer</h2>
    <p style="font-size:.8rem;color:#64748b;margin-bottom:1rem;">
      Caminho resolvido: <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;"><?= htmlspecialchars($composerBin) ?></code>
      <?= is_executable($composerBin) ? '<span style="color:#16a34a;font-weight:600;"> ✓ executável</span>' : '<span style="color:#dc2626;font-weight:600;"> ✗ não encontrado</span>' ?>
    </p>
    <div style="display:grid;gap:.75rem;">
      <?php foreach ($composerDetected as $search): ?>
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.75rem;">
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem;">
          <span><?= $search['found'] ? '✅' : '❌' ?></span>
          <code style="font-size:.8rem;font-weight:600;"><?= htmlspecialchars($search['cmd']) ?></code>
        </div>
        <pre style="font-size:.75rem;color:#475569;white-space:pre-wrap;margin:0;padding-left:1.6rem;"><?= htmlspecialchars($search['output'] ?: '(sem resultado)') ?></pre>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card">
    <h2>Informações do Servidor</h2>
    <div class="checks">
      <div class="check"><span class="icon">ℹ️</span><span>PHP <?= PHP_VERSION ?></span></div>
      <div class="check"><span class="icon">ℹ️</span><span>OS: <?= PHP_OS ?></span></div>
      <div class="check"><span class="icon">ℹ️</span><span>SAPI: <?= PHP_SAPI ?></span></div>
      <div class="check"><span class="icon">ℹ️</span><span>
        PDO drivers: <?= implode(', ', PDO::getAvailableDrivers()) ?>
      </span></div>
    </div>
  </div>
  <?php endif; ?>

  <div class="card" style="border: 1px solid #fecaca; background:#fff5f5;">
    <h2 style="color:#dc2626;">Remover após uso</h2>
    <p style="font-size:.875rem;color:#64748b;margin-bottom:1rem;">
      Após finalizar o setup, delete este arquivo via FTP ou painel de arquivos da Hostgator.
      Manter o arquivo acessível em produção é um risco de segurança.
    </p>
    <code style="font-size:.8rem;color:#dc2626;">rm setup.php</code>
  </div>
</div>
</body>
</html>
