<?php

namespace App\Services\Receipt;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Extrai itens e metadados do HTML da página pública de consulta da NFC-e
 * da SEFAZ-SP (para onde o QR Code do cupom aponta).
 *
 * Módulo puro: recebe o HTML já baixado e devolve um ParsedReceipt. O download
 * fica a cargo do NfceClient, mantendo este parser testável com fixtures.
 */
class SefazSpReceiptParser
{
    public function parse(string $html): ParsedReceipt
    {
        $xp = $this->xpath($html);

        return new ParsedReceipt(
            storeName: $this->firstText($xp, '//*[contains(@class,"txtTopo")]'),
            date: $this->matchDate($html),
            items: $this->items($xp),
        );
    }

    private function xpath(string $html): DOMXPath
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();

        return new DOMXPath($doc);
    }

    /** @return array<int,array> */
    private function items(DOMXPath $xp): array
    {
        $items = [];

        foreach ($xp->query('//*[@id="tabResult"]//tr') as $tr) {
            $name = $this->childText($xp, $tr, './/span[contains(@class,"txtTit")]');
            if ($name === null) {
                continue; // linha sem descrição não é um item
            }

            $items[] = [
                'name'  => $name,
                'unit'  => $this->extractUnit($this->childText($xp, $tr, './/span[contains(@class,"RUN")]')),
                'qty'   => $this->firstNumber($this->childText($xp, $tr, './/span[contains(@class,"Rqtd")]')) ?? 1.0,
                'price' => $this->firstNumber($this->childText($xp, $tr, './/span[contains(@class,"RvlUnit")]')),
            ];
        }

        return $items;
    }

    private function firstText(DOMXPath $xp, string $query): ?string
    {
        $node = $xp->query($query)->item(0);
        return $node ? $this->clean($node->textContent) : null;
    }

    private function childText(DOMXPath $xp, DOMElement $context, string $query): ?string
    {
        $node = $xp->query($query, $context)->item(0);
        return $node ? $this->clean($node->textContent) : null;
    }

    private function clean(string $text): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $text)));
        return $text === '' ? null : $text;
    }

    /** Pega a última palavra (a unidade vem após o rótulo "UN:"). */
    private function extractUnit(?string $text): ?string
    {
        if ($text !== null && preg_match('/([A-Za-zÇç]+)\s*$/u', $text, $m)) {
            return strtoupper($m[1]);
        }
        return null;
    }

    /** Primeiro número (formato BR) encontrado no texto. */
    private function firstNumber(?string $text): ?float
    {
        if ($text !== null && preg_match('/\d{1,3}(?:\.\d{3})*,\d{2}|\d+(?:,\d+)?/', $text, $m)) {
            return $this->brToFloat($m[0]);
        }
        return null;
    }

    private function brToFloat(string $value): float
    {
        if (str_contains($value, ',')) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }
        return (float) $value;
    }

    private function matchDate(string $text): ?string
    {
        if (preg_match('#\b(\d{2})/(\d{2})/(\d{4})\b#', $text, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        return null;
    }
}
