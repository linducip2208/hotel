# 05 — AI Providers

> 11 preset AI provider termurah untuk BYOK integration. Bukan hardcoded di code — disimpan sebagai JSON template di `storage/app/llm-presets/` untuk autofill convenience saja.

---

## 0. Hard Rule (no hardcoded providers)

**JANGAN PERNAH:**
- ❌ Hardcode nama vendor di class (no `OpenAIAdapter`, `DeepSeekAdapter`, `ClaudeAdapter`)
- ❌ Hardcode model ID di code (no `gpt-4o`, `deepseek-chat` literals di logic)
- ❌ Hardcode API key di config
- ❌ Hardcode "default provider per fitur" mapping
- ❌ Maintain pricing table per vendor (akan stale)

**WAJIB:**
- ✅ Class adapter berdasarkan **format API** (`OpenAICompatibleAdapter`, `AnthropicFormatAdapter`, `GeminiFormatAdapter`)
- ✅ User input semua via admin UI (name, base_url, api_key, default_model, extra_headers)
- ✅ Pricing user input sendiri per model (untuk tracking)
- ✅ Encrypted at rest (api_key)
- ✅ Preset JSON di `storage/app/llm-presets/*.json` HANYA untuk autofill UI — code TIDAK reference saat runtime

Sesuai [global preferences user](file:///C:/Users/lindu%20cipta/.claude/CLAUDE.md) — pattern ini berlaku global di semua project.

---

## 1. Daftar 11 Preset (cheapest BYOK options)

Harga dalam USD per 1 juta token (estimate snapshot 2026-04-28; akan bergeser, tapi preset hanya autofill, user koreksi di UI).

| # | Provider | Format | Best-cheap model | Input $/1M | Output $/1M | Catatan |
|---|---|---|---|---|---|---|
| 1 | **Self-hosted** (Ollama / LM Studio / vLLM) | OpenAI-compat | Llama 3.3 70B, Qwen 2.5 72B, Phi-4 | **$0** | **$0** | Hardware cost only. Full data sovereignty. |
| 2 | **DeepSeek** | OpenAI-compat | `deepseek-chat`, `deepseek-reasoner` | $0.27 | $1.10 | Reasoning model murah |
| 3 | **Mistral La Plateforme** | OpenAI-compat | `ministral-3b-latest` | $0.04 | $0.04 | Termurah dari big provider |
| 4 | **Google Gemini Flash** | Gemini | `gemini-2.5-flash`, `gemini-2.5-flash-lite` | $0.075 | $0.30 | Multimodal (image, audio) |
| 5 | **DeepInfra** | OpenAI-compat | Llama 3.3 70B, Qwen 2.5 72B | $0.08 | $0.40 | Banyak open-source model |
| 6 | **Hyperbolic** | OpenAI-compat | Llama 3.3 70B, Qwen | $0.10 | $0.40 | Cheap inference |
| 7 | **Together AI** | OpenAI-compat | Llama 3.3, DeepSeek V3, Qwen | $0.18 | $0.88 | Free tier untuk dev |
| 8 | **Fireworks AI** | OpenAI-compat | Llama 3.3, DeepSeek, Qwen | $0.20 | $0.90 | Tuning support |
| 9 | **Groq** | OpenAI-compat | `llama-3.3-70b-versatile` | $0.59 | $0.79 | Fastest inference (>500 tok/s) |
| 10 | **OpenRouter** | OpenAI-compat | auto-router (cheapest) | varies | varies | Single key untuk 100+ model |
| 11 | **Anthropic** | Anthropic | `claude-haiku-4-5` | ~$1.00 | ~$5.00 | Premium budget — quality terbaik untuk concierge |

**Kenapa 11, bukan 10:** user request tambah Anthropic. Anthropic Haiku 4.5 lebih mahal dari yang lain di list, tapi quality untuk concierge multi-bahasa (terutama Bahasa Indonesia + bahasa tamu Asing) lebih baik secara umum. Owner bisa mix: pakai DeepSeek/Gemini Flash untuk volume rendah biaya, Anthropic Haiku untuk customer-facing premium.

**Owner bebas swap, add custom provider, atau skip.** UI dropdown isi cuma yang owner tambah; default empty.

---

## 2. JSON Preset Format

`storage/app/llm-presets/*.json`:

```json
{
  "preset_id": "deepseek",
  "display_name": "DeepSeek",
  "api_format": "openai_compatible",
  "base_url": "https://api.deepseek.com/v1",
  "auth_header": "Authorization",
  "auth_prefix": "Bearer ",
  "models_endpoint": "/models",
  "chat_endpoint": "/chat/completions",
  "default_model": "deepseek-chat",
  "supported_models": [
    {
      "id": "deepseek-chat",
      "context": 64000,
      "input_price_per_1m_usd": 0.27,
      "output_price_per_1m_usd": 1.10,
      "supports_vision": false,
      "supports_function_calling": true
    },
    {
      "id": "deepseek-reasoner",
      "context": 64000,
      "input_price_per_1m_usd": 0.55,
      "output_price_per_1m_usd": 2.19,
      "supports_vision": false,
      "supports_function_calling": false
    }
  ],
  "docs_url": "https://api-docs.deepseek.com",
  "free_tier": false,
  "indonesia_friendly": true,
  "notes": "OpenAI-compatible. No regional restriction. Supports streaming."
}
```

Owner buka admin → Integrations → AI → "Add Provider" → pick preset → wizard isi field → manual edit kalau perlu → save (api_key encrypted).

---

## 3. Adapter Contract

```php
<?php

namespace App\Modules\Ai\Adapters;

interface AiAdapterInterface
{
    public function chat(array $messages, array $options = []): AiChatResponse;
    public function listModels(): array;
    public function tokenCount(string $text): int;
    public function supportsVision(): bool;
    public function supportsFunctionCalling(): bool;
}

final readonly class AiChatResponse
{
    public function __construct(
        public string $content,
        public string $model,
        public int $inputTokens,
        public int $outputTokens,
        public ?string $finishReason = null,
        public array $toolCalls = [],
        public array $rawResponse = [],
    ) {}
}
```

Implementations:

| Adapter class | Covers preset # |
|---|---|
| `OpenAICompatibleAdapter` | 1, 2, 3, 5, 6, 7, 8, 9, 10 |
| `GeminiFormatAdapter` | 4 |
| `AnthropicFormatAdapter` | 11 |

**3 adapter classes total** untuk cover 11 provider. Pattern hemat.

---

## 4. Use Cases di Hotel System

Semua opsional. Owner aktifkan per-fitur dan pilih provider.

### A. AI Concierge Chatbot (guest-facing)
- Multi-bahasa (auto-detect ID/EN/JA/ZH/AR)
- Answer FAQ kamar, fasilitas, jam operasional
- Upsell (late checkout, breakfast, transfer)
- Trigger action via function calling: book service, request housekeeping
- **Provider rekomendasi:** Anthropic Haiku 4.5 (quality multi-bahasa) atau Gemini Flash (cheapest dengan multimodal)

### B. Auto-translate room/property descriptions
- Generate ID → EN, ID → JA, ID → ZH, dll.
- Stored di DB, regenerate saat description diedit
- **Provider rekomendasi:** Mistral Ministral 3B (super murah) atau DeepSeek

### C. pSEO content generator
- Generate konten halaman pSEO unik per kombinasi kota+kategori+landmark
- Avoid thin content
- **Provider rekomendasi:** DeepSeek (large output cheap) atau Gemini Flash

### D. Smart email/WhatsApp reply suggestion
- Suggest 3 reply variants di FO inbox
- Tone-adaptive (casual / formal / apology)
- **Provider rekomendasi:** Mistral atau DeepSeek

### E. Auto-reply review (Booking, Agoda, Google)
- Generate response berdasarkan review content + hotel template
- Polite & branded
- **Provider rekomendasi:** Anthropic Haiku 4.5

### F. KTP/Paspor OCR (vision LLM)
- Fallback dari Tesseract local
- Untuk dokumen sulit (cetakan kabur, foto miring)
- **Provider rekomendasi:** Gemini 2.5 Flash (vision-cheap) atau OpenRouter ke vision model

### G. Demand forecasting
- Predict next-7-day occupancy berdasarkan historical + event calendar
- **Provider rekomendasi:** DeepSeek Reasoner (cheap reasoning model)

### H. Sentiment analysis review
- Tag review positive/negative/mixed → priority queue handling
- **Provider rekomendasi:** Mistral Small (super cheap, fit-for-purpose)

### I. Voice-to-reservation (call center, Phase 3)
- Whisper (speech) + LLM (intent) → auto-create reservation draft
- **Provider rekomendasi:** Self-host Whisper + Anthropic Haiku

---

## 5. Configuration UI Flow

```
Admin → Integrations → AI Providers
    ↓
[+ Add Provider]
    ↓
┌──────────────────────────────────────┐
│ Pick a preset (or skip):             │
│  ○ Self-hosted (Ollama / LM Studio)  │
│  ○ DeepSeek                          │
│  ○ Mistral La Plateforme             │
│  ○ Google Gemini Flash               │
│  ○ DeepInfra                         │
│  ○ Hyperbolic                        │
│  ○ Together AI                       │
│  ○ Fireworks AI                      │
│  ○ Groq                              │
│  ○ OpenRouter                        │
│  ○ Anthropic                         │
│  ○ Custom (input semua manual)       │
└──────────────────────────────────────┘
    ↓
Wizard fields (autofill dari preset, editable):
  Name (display): [DeepSeek                ]
  Base URL:       [https://api.deepseek.com/v1]
  API Format:     [openai_compatible v]
  API Key:        [sk-xxxxx                ] (encrypted)
  Default Model:  [deepseek-chat           ]
  Extra Headers:  (JSON, optional)
  Notes:          (free text)
    ↓
[Test Connection] → ping /models endpoint
    ↓
[Save] → Provider record created (api_key encrypted)
    ↓
Per-feature mapping:
  Concierge:        [DeepSeek         v]
  Translation:      [Mistral          v]
  pSEO content:     [DeepSeek         v]
  Email suggestion: [—not configured—]
  OCR fallback:     [Gemini Flash     v]
  ...
```

---

## 6. Auto-fetch Models

Setelah save provider, button "Fetch Models from /models endpoint" tarik daftar model live → owner pilih default. Convenience, tidak wajib.

```http
GET {base_url}/models
Authorization: Bearer {api_key}

→ 200 OK
{
  "data": [
    {"id": "deepseek-chat", "object": "model", ...},
    {"id": "deepseek-reasoner", ...}
  ]
}
```

Untuk provider non-OpenAI-compat, button labeled per spec masing-masing.

---

## 7. Cost Tracking

Tabel `ai_usage_logs`:
```
id | provider_id | model | feature | input_tokens | output_tokens | cost_usd | cost_idr | request_id | created_at
```

Per request, hitung `cost_usd = (input_tokens × input_price + output_tokens × output_price) / 1_000_000`. Konversi ke IDR pakai exchange rate harian (BYOK exchange API atau hardcoded weekly update).

Dashboard: usage per feature, per provider, per model, per period. Owner pantau biaya real.

---

## 8. Rate Limiting & Quota

Per-provider:
- Max requests/minute (configurable, default 60)
- Max tokens/day (configurable, default unlimited)
- Circuit breaker: 3x consecutive 5xx → open 60s

Per-feature:
- Concierge: max 100 messages per guest per day
- pSEO generation: max 1000 pages/day total
- Translation: cache aggressive (1 description = 1 generate)

---

## 9. Streaming

Semua adapter support streaming chat.completions (SSE). Penting untuk concierge UX (typing indicator effect).

```php
$adapter->chatStream($messages, function ($chunk) {
    // emit ke WebSocket / Pusher
    broadcast(new AiChunk($conversationId, $chunk));
});
```

---

## 10. Fallback Strategy

Owner bisa set primary + fallback provider per fitur.

```
Concierge: primary=Anthropic Haiku, fallback=DeepSeek
    ↓
Try Anthropic
  ├─ 5xx atau timeout → switch ke DeepSeek
  └─ Hard error → log + return generic "Sorry, having trouble" message
```

---

## 11. Self-host Option (Provider #1)

Owner punya GPU sendiri (RTX 4090 / 3090 / Mac M-series) bisa run Ollama/LM Studio/vLLM lokal:

```bash
# Ollama install
curl https://ollama.ai/install.sh | sh

# Pull model
ollama pull llama3.3:70b
ollama pull qwen2.5:72b

# Start server (default port 11434)
ollama serve

# Endpoint OpenAI-compatible
# http://localhost:11434/v1
```

Provider config:
- Name: `Local Ollama`
- Base URL: `http://localhost:11434/v1`
- API Format: `openai_compatible`
- API Key: `ollama` (dummy, Ollama tidak require)
- Default Model: `llama3.3:70b`

**Zero biaya per token.** Cocok untuk hotel resort yang butuh AI tapi internet kuota terbatas / data sensitif.

---

## 12. Encryption at Rest

```php
// Migration
Schema::create('providers', function (Blueprint $table) {
    $table->id();
    $table->string('integration_type'); // 'ai', 'payment', 'sms', etc.
    $table->string('name');
    $table->string('api_format');
    $table->string('base_url')->nullable();
    $table->text('api_key_encrypted')->nullable();
    $table->json('extra_headers')->nullable();
    $table->string('default_model')->nullable();
    $table->boolean('is_active')->default(false);
    $table->timestamps();
});

// Eloquent accessor
public function getApiKeyAttribute(): ?string
{
    return $this->api_key_encrypted
        ? Crypt::decryptString($this->api_key_encrypted)
        : null;
}

public function setApiKeyAttribute(?string $value): void
{
    $this->attributes['api_key_encrypted'] = $value
        ? Crypt::encryptString($value)
        : null;
}
```

API response (admin GET) **NEVER** expose `api_key`. Hanya field `has_api_key: true/false` + last-4-char display.

---

## 13. Audit Log

Setiap provider add/edit/delete/test:
```
audit_logs: action='ai_provider.created'
            actor_id=42
            target_type='provider'
            target_id=7
            meta={"name":"DeepSeek","format":"openai_compatible"}
            // api_key tidak pernah di-log
```

---

## 14. Migrasi Dari Hardcoded → Dynamic

Kalau ada code yang masih hardcode (misal di sketch awal `if ($provider === 'deepseek')`), refactor:

❌ Sebelum:
```php
if ($provider === 'deepseek') {
    return new DeepSeekClient($apiKey);
} elseif ($provider === 'openai') {
    return new OpenAIClient($apiKey);
}
```

✅ Sesudah:
```php
$adapter = AdapterFactory::for($provider->api_format);
$adapter->configure($provider->base_url, $provider->api_key, ...);
return $adapter;
```

---

## 15. References

- DeepSeek API docs: https://api-docs.deepseek.com
- Mistral API docs: https://docs.mistral.ai
- Google Gemini API: https://ai.google.dev
- Anthropic API: https://docs.anthropic.com
- Groq API: https://console.groq.com/docs
- Together AI: https://docs.together.ai
- Fireworks AI: https://docs.fireworks.ai
- DeepInfra: https://deepinfra.com/docs
- Hyperbolic: https://docs.hyperbolic.xyz
- OpenRouter: https://openrouter.ai/docs
- Ollama: https://ollama.com
- LM Studio: https://lmstudio.ai

Reference implementation pattern: `D:\project laravel\foodscan\docs\10-AI-PROVIDERS.md`.
