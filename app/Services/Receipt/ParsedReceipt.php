<?php

namespace App\Services\Receipt;

/**
 * Resultado da extração de um cupom (via OCR ou QR/SEFAZ).
 *
 * Cada item em $items é um array associativo:
 *   ['name' => string, 'unit' => ?string, 'qty' => float, 'price' => ?float]
 */
class ParsedReceipt
{
    public function __construct(
        public ?string $storeName = null,
        public ?string $date = null,   // formato Y-m-d
        public array $items = [],
    ) {}
}
