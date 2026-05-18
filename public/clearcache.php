<?php
$base = '/home1/vit75277/repositories/lista-compras-laravel';
putenv('HOME=/home1/vit75277');
echo shell_exec("cd {$base} && php artisan config:clear 2>&1");
echo shell_exec("cd {$base} && php artisan cache:clear 2>&1");
echo shell_exec("cd {$base} && php artisan view:clear 2>&1");
echo "Concluído!";
