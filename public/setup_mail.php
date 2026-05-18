<?php
require '/home1/vit75277/repositories/lista-compras-laravel/vendor/autoload.php';
$app = require '/home1/vit75277/repositories/lista-compras-laravel/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    Illuminate\Support\Facades\Mail::raw('Teste de e-mail do Smart Listiq!', function($msg) {
        $msg->to('seuemail@gmail.com')->subject('Teste Smart Listiq');
    });
    echo "E-mail enviado com sucesso!";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
