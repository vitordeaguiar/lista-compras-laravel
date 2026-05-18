<?php
$base = '/home1/vit75277/repositories/lista-compras-laravel';
putenv('HOME=/home1/vit75277');
putenv('COMPOSER_HOME=/home1/vit75277/.composer');

echo "<pre>";
echo "=== git pull ===\n";
echo shell_exec("cd {$base} && git pull origin main 2>&1");

echo "\n=== key:generate ===\n";
echo shell_exec("cd {$base} && php artisan key:generate --force 2>&1");

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
