<?php

namespace App\Services\Receipt;

/**
 * Transforma o texto bruto produzido pelo OCR (Tesseract) de um cupom fiscal
 * em uma estrutura de itens. Heurística focada no layout DANFE NFC-e.
 *
 * Módulo puro: sem I/O, sem dependência de Eloquent. Fácil de testar.
 */
class ReceiptTextParser
{
    /** Número monetário BR: "24,90" ou "1.234,56". */
    private const MONEY = '\d{1,3}(?:\.\d{3})*,\d{2}|\d+,\d{2}';

    public function parse(string $rawText): ParsedReceipt
    {
        $items     = [];
        $storeName = null;

        foreach (preg_split('/\r\n|\r|\n/', $rawText) as $line) {
            if ($item = $this->matchItem($line)) {
                $items[] = $item;
                continue;
            }

            // Nome do mercado: primeira linha relevante que não é item.
            if ($storeName === null && trim($line) !== '') {
                $storeName = trim($line);
            }
        }

        return new ParsedReceipt(
            storeName: $storeName,
            date: $this->matchDate($rawText),
            items: $items,
        );
    }

    /** Procura a primeira data dd/mm/yyyy no texto e devolve em Y-m-d. */
    private function matchDate(string $text): ?string
    {
        if (preg_match('#\b(\d{2})/(\d{2})/(\d{4})\b#', $text, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        return null;
    }

    /**
     * Tenta extrair um item de uma linha no formato:
     *   <nome> <qtd> <unidade> x <preço unitário> <total da linha>
     * Ex.: "ARROZ TIO JOAO 5KG   1 UN X 24,90   24,90"
     */
    private function matchItem(string $line): ?array
    {
        $money = self::MONEY;
        $re = '/^\s*(?<name>.+?)\s+(?<qty>\d+(?:[.,]\d+)?)\s*(?<unit>[A-Za-zçÇ]{1,6})\s*[xX*]\s*'
            . "(?<price>{$money})\s+(?<total>{$money})\s*$/u";

        if (!preg_match($re, $line, $m)) {
            return null;
        }

        return [
            'name'  => trim($m['name']),
            'unit'  => strtoupper($m['unit']),
            'qty'   => $this->brToFloat($m['qty']),
            'price' => $this->brToFloat($m['price']),
        ];
    }

    /** Converte número no formato BR ("1.234,56") para float. */
    private function brToFloat(string $value): float
    {
        $value = trim($value);
        if (str_contains($value, ',')) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }
        return (float) $value;
    }
}
