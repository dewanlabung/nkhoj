<?php

namespace App\Services\AI;

use App\Models\Prompt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

abstract class BaseAgent
{
    protected string $gateway;
    protected string $model;

    public function __construct(string $gateway, string $model = 'claude-opus-5')
    {
        $this->gateway = rtrim($gateway, '/');
        $this->model   = $model;
    }

    protected function call(
        string $promptKey,
        array  $userMessages,
        int    $maxTokens = 1200,
        string $model = null
    ): string {
        $system = $this->loadPrompt($promptKey);

        $messages = [
            ['role' => 'system', 'content' => $system],
            ...$userMessages,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.omniroute.key', 'omniroute-local'),
            'Content-Type'  => 'application/json',
        ])
        ->timeout(5)
        ->post("{$this->gateway}/chat/completions", [
            'model'      => $model ?? $this->model,
            'max_tokens' => $maxTokens,
            'messages'   => $messages,
        ]);

        $this->assertOk($response);

        return $response->json('choices.0.message.content', '');
    }

    protected function callJson(
        string $promptKey,
        array  $userMessages,
        int    $maxTokens = 400,
        string $model = null
    ): array {
        $raw = $this->call($promptKey, $userMessages, $maxTokens, $model);

        // Strip markdown fences if present
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $clean = preg_replace('/```\s*$/m', '', $clean);

        return json_decode(trim($clean), true) ?? [];
    }

    private function loadPrompt(string $name): string
    {
        return Cache::remember("ai_prompt_{$name}", 3600, function () use ($name) {
            $prompt = Prompt::where('name', $name)->where('is_active', true)->first();

            if (!$prompt) {
                throw new \RuntimeException("Prompt '{$name}' not found or inactive.");
            }

            return $prompt->content;
        });
    }

    private function assertOk(Response $response): void
    {
        if ($response->failed()) {
            throw new \RuntimeException(
                "AI gateway error {$response->status()}: " . $response->json('error.message', 'unknown')
            );
        }
    }

    abstract public function run(array $context): mixed;
}
