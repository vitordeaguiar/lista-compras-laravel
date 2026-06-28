<?php

namespace App\Services\Receipt;

use Illuminate\Support\Facades\Http;

/**
 * Wrapper fino para baixar o HTML da página pública da NFC-e.
 * Isolado do parser para que o parsing permaneça puro e testável.
 */
class NfceClient
{
    public function fetch(string $url): string
    {
        // allow_redirects=false: anti-SSRF. O host já foi validado pelo chamador;
        // sem seguir redirects, um eventual open-redirect na SEFAZ não nos leva a
        // hosts internos. O QR da NFC-e SP aponta direto para a página final.
        $body = Http::timeout(15)
            ->withOptions(['allow_redirects' => false])
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; ListaCompras/1.0)'])
            ->get($url)
            ->body();

        // Cap defensivo: uma página de NFC-e cabe folgado em ~1,5 MB.
        return mb_substr($body, 0, 1_500_000);
    }
}
