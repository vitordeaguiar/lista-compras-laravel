<?php
$base = '/home1/vit75277/repositories/lista-compras-laravel';
putenv('HOME=/home1/vit75277');
putenv('COMPOSER_HOME=/home1/vit75277/.composer');

echo "<pre>";
echo "=== migrate ===\n";
echo shell_exec("cd {$base} && php artisan migrate --force 2>&1");

echo "\n=== optimize:clear ===\n";
echo shell_exec("cd {$base} && php artisan optimize:clear 2>&1");

echo "\n=== Concluído! ===\n";
echo "</pre>";
