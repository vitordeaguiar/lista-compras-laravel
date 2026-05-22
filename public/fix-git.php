<?php
$repo = '/home1/vit75277/repositories/lista-compras-laravel';
$output = shell_exec("cd $repo && git checkout -- .env.example 2>&1");
echo "<pre>" . ($output ?: "Sucesso! Sem erros.") . "</pre>";
