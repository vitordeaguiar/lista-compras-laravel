# PRD — Importar lista de compras a partir do cupom fiscal

> Status: pronto para implementação (TDD). Gerado via skill `to-prd` após entrevista `grill-me`.

## Problem Statement

Hoje, para registrar uma compra de mercado, o usuário precisa digitar **item por item** (nome, unidade, quantidade, preço) na tela da lista. Depois de uma compra real, ele já tem em mãos o **cupom fiscal** com exatamente essas informações impressas. Redigitar tudo é lento e propenso a erro.

## Solution

Na página de listas, um botão **"Criar de cupom"** abre a câmera do celular. O usuário tira **uma foto** do cupom. O sistema:

1. **Tenta ler o QR Code** da NFC-e na foto (cliente, via jsQR). Se houver QR e ele apontar para a SEFAZ-SP, o backend consulta a página oficial e extrai os itens com precisão.
2. **Se não houver QR**, faz **OCR no navegador** (Tesseract.js, em português) e envia o texto bruto ao backend, que tenta estruturar os itens.

Em ambos os caminhos, o usuário cai numa **tela de revisão editável** com os itens já preenchidos (nome, qtd, unidade, preço) + nome do mercado e data. Ele corrige o que o OCR errou, remove lixo e confirma. O sistema cria uma **lista aberta** com os itens **marcados como comprados** e preços preenchidos — pronta para ele clicar em "Concluir" no fluxo normal existente.

## User Stories

1. Como usuário, quero um botão "Criar de cupom" na página `/listas`, ao lado de "Nova lista", para iniciar a importação.
2. Como usuário no celular, quero que o botão abra a câmera para eu tirar uma foto do cupom.
3. Como usuário no desktop, quero que o mesmo botão abra um seletor de arquivo para escolher uma foto do cupom.
4. Como usuário, quero que o sistema tente primeiro o QR Code da NFC-e, por ser a fonte mais precisa.
5. Como usuário, quero que, se a foto não tiver QR Code legível, o sistema caia automaticamente no OCR — sem eu precisar escolher.
6. Como usuário em SP, quero que o QR Code da NFC-e seja consultado na SEFAZ-SP e os itens venham exatos (nome, qtd, unidade, preço unitário).
7. Como usuário, quero ver o progresso do OCR ("Lendo cupom…") porque ele pode demorar alguns segundos.
8. Como usuário, quero uma tela de revisão mostrando todos os itens detectados em campos editáveis.
9. Como usuário, quero corrigir nome, quantidade, unidade e preço de qualquer item antes de salvar.
10. Como usuário, quero remover linhas que o OCR detectou errado (ex.: "TOTAL", "TROCO", CNPJ lidos como item).
11. Como usuário, quero adicionar manualmente um item que o OCR não pegou, ainda na tela de revisão.
12. Como usuário, quero que o nome da lista venha pré-preenchido com o nome do mercado lido no cupom (ou "Compra DD/MM" se não for detectado), e editável.
13. Como usuário, quero que a data da lista venha pré-preenchida com a data de emissão do cupom, e editável.
14. Como usuário, ao confirmar, quero que a lista seja criada **aberta**, com os itens **marcados como comprados** e com preços preenchidos.
15. Como usuário, quero ser levado à tela da lista criada para revisar e concluir no fluxo normal (que já calcula total e desconto).
16. Como usuário, quero que apenas eu tenha acesso às minhas listas importadas (mesma regra de posse das demais).
17. Como usuário, se nenhum item for detectado, quero uma mensagem clara ("Não consegui ler o cupom — tente outra foto ou adicione manualmente") em vez de uma lista vazia silenciosa.
18. Como administrador do sistema, quero que a consulta ao QR só acesse o domínio oficial da SEFAZ-SP, para o recurso não virar um proxy de requisições arbitrárias (anti-SSRF).

## Implementation Decisions

### Caminho de dados (alto nível)

```
[foto] --(cliente)--> tenta jsQR
   ├── achou URL NFC-e ──> POST /listas/cupom/extrair {source:"qr", url}
   │                         backend: valida host SEFAZ-SP -> HTTP GET -> SefazSpReceiptParser -> JSON
   └── sem QR ───────────> Tesseract.js (por) -> texto bruto
                             POST /listas/cupom/extrair {source:"ocr", text}
                             backend: ReceiptTextParser -> JSON
JSON {name, date, items[]} --(cliente)--> tela de revisão editável
   confirma --> POST /listas/cupom {name, date, items[]} --> cria ShoppingList(open) + ShoppingItem(purchased=true)
```

### Módulos a construir (profundos e testáveis isoladamente)

**1. `App\Services\Receipt\ParsedReceipt` (DTO)**
- Propriedades: `storeName: ?string`, `date: ?string` (Y-m-d), `total: ?float`, `items: array` (cada item `['name'=>string,'unit'=>?string,'qty'=>float,'price'=>?float]`).
- Sem I/O. Apenas transporta o resultado da extração.

**2. `App\Services\Receipt\ReceiptTextParser` (módulo profundo — alvo principal de testes)**
- Interface: `parse(string $rawText): ParsedReceipt`.
- Responsabilidades: quebrar em linhas; identificar linhas de item e extrair nome/qtd/unidade/preço; converter número BR `1.234,56` → `1234.56`; ignorar ruído (CNPJ, CPF, TOTAL, SUBTOTAL, TROCO, VALOR PAGO, FORMA PAGTO, "Qtde total de itens"); tentar detectar nome do mercado (primeira linha relevante) e data de emissão.
- Pura, sem dependência de Eloquent/HTTP. Heurística focada no layout DANFE NFC-e; casos de mercado exótico ficam como melhoria futura.

**3. `App\Services\Receipt\SefazSpReceiptParser` (módulo profundo)**
- Interface: `parse(string $html): ParsedReceipt`.
- Responsabilidade: extrair do HTML da página pública da NFC-e SP (`#tabResult`) nome do item, código, qtd, unidade, valor unitário e valor da linha; mais nome do emitente, data de emissão e valor total.
- Parsing via `DOMDocument`/`DOMXPath`. Pura: recebe HTML, devolve `ParsedReceipt`. Testada com fixture HTML.

**4. `App\Services\Receipt\NfceClient` (wrapper fino, não-testado a fundo)**
- Interface: `fetch(string $url): string`.
- Faz `Http::timeout(...)->get($url)->body()`. Mantém o parser puro. Em testes, `Http::fake()`.

**5. `App\Http\Controllers\ReceiptImportController`**
- `create()`: renderiza a página de captura + revisão (`lists.receipt.create`).
- `extract(Request)`: valida `source ∈ {qr, ocr}`.
  - `qr`: valida `url` e **exige host na allowlist da SEFAZ-SP** (`*.fazenda.sp.gov.br` / `nfce.fazenda.sp.gov.br`); senão `422`. Busca via `NfceClient`, parseia via `SefazSpReceiptParser`.
  - `ocr`: valida `text` (string, limite de tamanho), parseia via `ReceiptTextParser`.
  - Resposta JSON: `{name, date, items:[{name,unit,qty,price}, …]}`. Se `items` vazio → `422` com mensagem.
- `store(Request)`: valida `name` (obrigatório), `shopping_date` (obrigatório, date), `items` (array, min 1) com `name/unit/qty/price`. Cria `ShoppingList` **status `open`** do usuário logado; cada item com `purchased = true`. Sanitiza com `strip_tags`/`trim` como nos controllers existentes. Redireciona para `lists.show`.

### Rotas (registrar `cupom` ANTES de `/listas/{list}` para não colidir com o model binding)

```
GET   /listas/cupom           -> ReceiptImportController@create   (lists.receipt.create)
POST  /listas/cupom/extrair   -> ReceiptImportController@extract  (lists.receipt.extract)
POST  /listas/cupom           -> ReceiptImportController@store    (lists.receipt.store)
```
Tudo dentro do grupo `auth`. `extract` herda o throttle geral (60/min).

### Frontend / CSP (restrições reais do projeto)

- A CSP é estrita: `script-src 'self'` (sem CDN) e `connect-src 'self'`. Portanto **auto-hospedar**:
  - `public/vendor/jsqr/jsQR.js`
  - `public/vendor/tesseract/` — `tesseract.min.js`, `worker.min.js`, core wasm e `por.traineddata.gz`. Configurar `Tesseract` com `workerPath/corePath/langPath` locais para nunca chamar CDN.
- `Permissions-Policy: camera=()` desliga `getUserMedia`; por isso a captura usa `<input type="file" accept="image/*" capture="environment">` (app de câmera nativo), não scanner ao vivo. **Não** introduzir `getUserMedia`.
- JS próprio em `public/js/receipt-import.js` (vanilla), incluído com `nonce="{{ $cspNonce }}"` quando inline. Orquestra: ler arquivo → desenhar em canvas → jsQR → se URL, POST `extrair {source:qr}`; senão Tesseract → POST `extrair {source:ocr}` → renderizar tabela de revisão → submit para `store`.
- `img-src 'self' data:` já permite preview da foto via dataURL.

### Schema / contratos

- **Sem mudança de schema.** Reaproveita `shopping_lists` (`name,shopping_date,status,total,discount,notes,completed_at`) e `shopping_items` (`name,unit,qty,price,purchased`). Nada de migração nova.

## Testing Decisions

Bons testes aqui exercitam **comportamento** pelas interfaces públicas, resistindo a refator interno dos parsers.

- **`Tests\Unit\ReceiptTextParserTest`** (prior art: `tests/Unit/CreditCardInstallmentTest.php` — unit puro, instanciar e asserir):
  - extrai nome/qtd/unidade/preço de linha de item DANFE típica;
  - converte `1.234,56` → `1234.56`;
  - ignora linhas TOTAL/TROCO/CNPJ/forma de pagamento;
  - detecta nome do mercado e data quando presentes; deixa `null` quando ausentes;
  - texto sem itens → `items` vazio.
- **`Tests\Unit\SefazSpReceiptParserTest`** (fixture HTML em `tests/Fixtures/nfce-sp.html`):
  - extrai todos os itens com qtd/unidade/preço corretos;
  - extrai emitente, data e total.
- **`Tests\Feature\ReceiptImportTest`** (prior art: `tests/Feature/ItemSuggestionsTest.php` — `RefreshDatabase`, `actingAs`, `forceCreate`):
  - rotas exigem autenticação (redirect `/login`);
  - `extrair` com `source=ocr` e texto retorna JSON de itens;
  - `extrair` com `source=qr` usa `Http::fake()` devolvendo o fixture SP → JSON de itens;
  - `extrair` com `url` fora do host SEFAZ-SP → `422` (anti-SSRF);
  - `extrair` sem itens detectados → `422` com mensagem;
  - `store` cria lista `open` do usuário, itens `purchased=true` com preço, e redireciona para `lists.show`;
  - `store` sem itens → erro de validação.

## Out of Scope

- Outros estados além de SP no caminho QR (arquitetura permite plugar depois).
- Scanner de QR ao vivo / `getUserMedia` (bloqueado pela `Permissions-Policy` atual).
- OCR via API de visão (Claude) — decidido usar Tesseract local, sem API key.
- Criar a lista já **concluída** direto do cupom (decidido: nasce aberta).
- Importar cupom para uma lista **já existente** (sempre cria lista nova).
- Categorização automática de itens / vínculo com o módulo financeiro.
- Múltiplas fotos por importação.

## Further Notes

- **Segurança (SSRF):** o caminho QR faz HTTP server-side a partir de URL vinda do cliente; a allowlist de host da SEFAZ-SP é obrigatória, com teste cobrindo a rejeição.
- **Assets grandes:** `por.traineddata.gz` (~1–4 MB) será versionado em `public/vendor/tesseract/`. Aceitável para cumprir a CSP `connect-src 'self'`.
- **Precisão do OCR:** o `ReceiptTextParser` cobre o layout DANFE NFC-e comum; cupons muito fora do padrão podem exigir ajuste fino das heurísticas — por isso a tela de revisão editável é o backstop de qualidade.
- Sem runtime PHP local nesta máquina: os testes serão escritos seguindo o ciclo RED→GREEN, mas executados no ambiente com PHP/CI, não localmente.
