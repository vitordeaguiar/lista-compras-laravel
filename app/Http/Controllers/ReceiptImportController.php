<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ShoppingList;
use App\Models\ShoppingItem;
use App\Services\Receipt\NfceClient;
use App\Services\Receipt\ParsedReceipt;
use App\Services\Receipt\ReceiptTextParser;
use App\Services\Receipt\SefazSpReceiptParser;

class ReceiptImportController extends Controller
{
    /** Página de captura da foto + revisão dos itens. */
    public function create()
    {
        return view('lists.receipt');
    }

    /**
     * Recebe o resultado da leitura no cliente (URL do QR ou texto do OCR),
     * extrai os itens no servidor e devolve em JSON para a tela de revisão.
     */
    public function extract(Request $request): JsonResponse
    {
        // Obs.: 'url' do Laravel reprova a URL real da NFC-e (contém '|' não
        // escapado). Validamos como string e barramos o host no isSefazSp().
        $data = $request->validate([
            'source' => 'required|in:qr,ocr',
            'url'    => 'required_if:source,qr|nullable|string|max:2000',
            'text'   => 'required_if:source,ocr|nullable|string|max:20000',
        ]);

        $receipt = $data['source'] === 'qr'
            ? $this->fromQr($data['url'])
            : (new ReceiptTextParser())->parse($data['text']);

        if (empty($receipt->items)) {
            return response()->json([
                'message' => 'Não consegui ler itens no cupom. Tente outra foto ou adicione manualmente.',
            ], 422);
        }

        return response()->json([
            'name'  => $receipt->storeName,
            'date'  => $receipt->date,
            'items' => $receipt->items,
        ]);
    }

    private function fromQr(string $url): ParsedReceipt
    {
        // Anti-SSRF: só consultamos o domínio oficial da SEFAZ-SP.
        abort_unless($this->isSefazSp($url), 422, 'QR Code não é de uma NFC-e de São Paulo.');

        $html = (new NfceClient())->fetch($url);

        return (new SefazSpReceiptParser())->parse($html);
    }

    private function isSefazSp(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return $host === 'nfce.fazenda.sp.gov.br'
            || str_ends_with($host, '.fazenda.sp.gov.br');
    }

    /** Cria a lista (aberta) com os itens confirmados na revisão, já comprados. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'shopping_date' => 'required|date',
            'items'         => 'required|array|min:1',
            'items.*.name'  => 'required|string|max:255',
            'items.*.unit'  => 'nullable|string|max:50',
            'items.*.qty'   => 'nullable|numeric|min:0.001|max:9999',
            'items.*.price' => 'nullable|numeric|min:0|max:99999',
        ]);

        $list = DB::transaction(function () use ($data) {
            $list = new ShoppingList([
                'name'          => strip_tags(trim($data['name'])),
                'shopping_date' => $data['shopping_date'],
                'status'        => 'open',
            ]);
            $list->user_id = Auth::id();
            $list->save();

            foreach ($data['items'] as $item) {
                ShoppingItem::create([
                    'shopping_list_id' => $list->id,
                    'name'             => strip_tags(trim($item['name'])),
                    'unit'             => isset($item['unit']) ? strip_tags(trim($item['unit'])) : null,
                    'qty'              => $item['qty'] ?? 1,
                    'price'            => $item['price'] ?? null,
                    'purchased'        => true,
                ]);
            }

            return $list;
        });

        return redirect()->route('lists.show', $list)
            ->with('success', 'Lista criada a partir do cupom!');
    }
}
