# Impressão de Cupom Não Fiscal (ESC/POS)

Módulo de impressão de cupom não fiscal para impressora térmica **Epson TM-T20X** (e compatíveis ESC/POS), usando a biblioteca **mike42/escpos-php**.

## Instalação

1. Instale a dependência via Composer:

```bash
composer require mike42/escpos-php
```

2. Configure o arquivo `.env` (copie de `.env.example` se necessário):

```env
PRINTER_NAME=EPSON TM-T20X Receipt
PRINTER_COLS=32
# PRINTER_CODEPAGE=CP860
# STORE_NAME=MINHA LOJA
```

## Como descobrir o nome da impressora no Windows

1. Abra **Configurações** > **Dispositivos** > **Impressoras e scanners**  
   **ou** **Painel de Controle** > **Dispositivos e impressoras**.
2. O nome exato que aparece na lista é o que deve ser usado em `PRINTER_NAME`.

### Exemplos de PRINTER_NAME

| Cenário | Exemplo |
|--------|---------|
| Impressora USB local | `EPSON TM-T20X Receipt` |
| Impressora compartilhada na rede | `\\PDV-01\EPSONTMT20X` |
| Outra rede | `\\NOME-DO-PC\NomeDoCompartilhamento` |

No `.env`, em compartilhamento de rede, use **duas barras invertidas** (a segunda “escape”):  
`PRINTER_NAME="\\\\PDV-01\\EPSONTMT20X"`

### Impressora USB no mesmo PC ("Failed to copy file to printer")

A biblioteca envia o trabalho como **cópia para \\\\SEU-PC\\NomeDaImpressora**. Por isso a impressora precisa estar **compartilhada** no Windows, mesmo no mesmo micro:

1. **Configurações** → **Impressoras e scanners** → clique na **EPSON TM-T20X Receipt** → **Gerenciar**.
2. **Opções de impressora** → aba **Compartilhamento**.
3. Marque **Compartilhar esta impressora** e defina o **nome do compartilhamento** igual ao do `.env` (ex.: `EPSON TM-T20X Receipt`).
4. Confirme com **OK**.

Ou use no `.env` o caminho completo (substitua `SEU-PC` pelo nome do seu computador):

```env
PRINTER_NAME="\\\\SEU-PC\\EPSON TM-T20X Receipt"
```

## Endpoint de impressão

- **URL:** `POST /index.php?route=print/receipt`  
  (Ex.: `http://localhost/PDV/index.php?route=print/receipt`)
- **Content-Type:** `application/json`
- **Body (exemplo):** veja `examples/receipt.json`

### Exemplo com cURL

```bash
curl -X POST "http://localhost/PDV/index.php?route=print/receipt" ^
  -H "Content-Type: application/json" ^
  -d "{\"store_name\":\"MINHA LOJA\",\"title\":\"CUPOM NAO FISCAL\",\"order_number\":\"000042\",\"customer_name\":\"JOAO\",\"datetime\":\"2026-02-20 23:10:00\",\"payment_method\":\"PIX\",\"items\":[{\"desc\":\"Produto A\",\"qty\":1,\"unit\":10.00}],\"notes\":\"Obrigado!\"}"
```

Em ambiente com login (sessão), inclua o `csrf_token` no JSON (o mesmo usado nos formulários do sistema).

### Resposta

- Sucesso: `{ "ok": true }`
- Erro: `{ "ok": false, "error": "mensagem" }`

## Uso na tela do cupom

1. Abra o cupom térmico pela venda, por exemplo:  
   `http://localhost/PDV/index.php?route=pos/receipt_thermal&id=42`
2. Clique em **Imprimir na impressora**.
3. O sistema envia o payload da venda para o endpoint acima e a impressora (configurada no `.env`) imprime o cupom.

## Solução de problemas

| Problema | O que verificar |
|---------|------------------|
| **Caracteres errados (acentos)** | Defina `PRINTER_CODEPAGE=CP860` (ou CP850/CP1252) no `.env`. Reinicie o servidor/PHP. |
| **Não corta o papel** | O código chama `cut()` ao final; confira se o modelo da impressora suporta corte e se o driver está correto. |
| **Sai muito papel em branco** | O cupom já usa avanço de apenas 1 linha antes do corte. Se ainda sair folha grande em branco, no Windows: **Impressoras** > sua impressora > **Preferências de impressão** > aba **Papel/Tamanho** > defina tamanho como **Recibo** ou **80 mm** (e não A4/Carta), e salve. |
| **Erro de permissão / impressora não encontrada** | O usuário que executa o PHP (ex.: usuário do Apache/WAMP) precisa ter permissão para a impressora. No Windows, execute o servidor com o mesmo usuário que tem a impressora instalada, ou compartilhe a impressora e use `\\\\PC\\Compartilhamento`. |
| **PRINTER_NAME não configurado** | No `.env`, defina `PRINTER_NAME` com o nome exato da impressora (como no Windows). |
| **Impressora em outro PC (rede)** | Use o nome de compartilhamento: `\\\\NOME-PC\\NomeImpressora`. O PC onde o PHP roda precisa ter acesso à rede e à impressora compartilhada. |

## Servidor Linux remoto

Se o PHP roda em um servidor **Linux** e a impressora está em um **PC Windows**, o PHP não acessa a impressora diretamente. Opções:

1. **Recomendado:** rodar um **agente local de impressão** no PC do PDV (Windows) que:
   - receba requisições do servidor (ex.: fila ou API interna),
   - chame o endpoint de impressão localmente (ex.: `http://localhost/PDV/...`) ou use a biblioteca no próprio agente.
2. Ou instalar a impressora em um servidor Windows acessível pela aplicação e usar `PRINTER_NAME` com o compartilhamento desse servidor.

## Logs

Erros de impressão são registrados em:  
`storage/logs/print.log`  
(data/hora, mensagem e resumo do payload).

## Segurança

- O endpoint `print/receipt` exige **sessão autenticada** e permissão **pos** (mesma do PDV).
- Em requisições POST com JSON, envie o **csrf_token** no body para validação CSRF.
