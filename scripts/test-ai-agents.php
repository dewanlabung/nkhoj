<?php

/**
 * Quick local test for all five AI agents via OmniRoute.
 * Run from project root: php scripts/test-ai-agents.php
 *
 * Requires OmniRoute running on localhost:20128
 */

$gateway = getenv('OMNIROUTE_ENDPOINT') ?: 'http://localhost:20128/v1';
$key     = getenv('OMNIROUTE_KEY') ?: 'omniroute-local';

function callGateway(string $gateway, string $key, string $model, array $messages, int $maxTokens = 300): string
{
    $payload = json_encode([
        'model'      => $model,
        'max_tokens' => $maxTokens,
        'messages'   => $messages,
    ]);

    $ch = curl_init("{$gateway}/chat/completions");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            "Authorization: Bearer {$key}",
        ],
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status !== 200) {
        return "ERROR {$status}: {$response}";
    }

    $data = json_decode($response, true);
    return $data['choices'][0]['message']['content'] ?? 'No content';
}

function hr(string $title): void
{
    echo "\n" . str_repeat('─', 60) . "\n";
    echo "  {$title}\n";
    echo str_repeat('─', 60) . "\n";
}

echo "\nnkhoj.com AI Agent Test Suite\n";
echo "Gateway: {$gateway}\n";

// ── 1. Author Assistant ──────────────────────────────────
hr('1. AUTHOR ASSISTANT — SEO title + meta');
$result = callGateway($gateway, $key, 'claude-opus-5', [
    ['role' => 'system', 'content' => 'Generate SEO title (max 60 chars) and meta description (max 155 chars) for the given article. Output clean text only.'],
    ['role' => 'user',   'content' => 'Article topic: Nepal's 2026 local election results and what they mean for development spending in rural provinces'],
]);
echo $result . "\n";

// ── 2. Search Enhancer ───────────────────────────────────
hr('2. SEARCH ENHANCER — Semantic expansion');
$result = callGateway($gateway, $key, 'gemini-2.5-flash', [
    ['role' => 'system', 'content' => 'Enhance the search query. Output JSON with: expanded_terms (array), intent (string), corrected_query (string|null), nepali_translation (string|null).'],
    ['role' => 'user',   'content' => 'Query: nepal eletcion result'],
], 200);
echo $result . "\n";

// ── 3. Content Moderation ────────────────────────────────
hr('3. MODERATION GUARD — Comment check');
$result = callGateway($gateway, $key, 'gemini-2.5-flash', [
    ['role' => 'system', 'content' => 'Review content for policy violations. Output JSON: {action, confidence, violations, reason, severity}'],
    ['role' => 'user',   'content' => json_encode(['content' => 'Great article! Very informative read about the election.', 'type' => 'comment', 'author_tier' => 'new'])],
], 150);
echo $result . "\n";

// ── 4. Survey Narrator ───────────────────────────────────
hr('4. SURVEY NARRATOR — Mini report');
$surveyData = [
    'title'            => 'Tech Usage in Nepal 2026',
    'total_responses'  => 847,
    'completion_rate'  => 0.82,
    'questions'        => [
        ['question' => 'Primary device?', 'options' => [
            ['label' => 'Mobile', 'count' => 620, 'pct' => 73.2],
            ['label' => 'Desktop', 'count' => 178, 'pct' => 21.0],
            ['label' => 'Tablet', 'count' => 49, 'pct' => 5.8],
        ]],
    ],
    'geography' => [
        ['country' => 'NP', 'pct' => 81.2],
        ['country' => 'IN', 'pct' => 9.4],
    ],
];
$result = callGateway($gateway, $key, 'claude-opus-5', [
    ['role' => 'system', 'content' => 'Generate a brief analytics report (Executive Summary + 2 Key Findings) for this survey data. Cite percentages.'],
    ['role' => 'user',   'content' => json_encode($surveyData)],
], 500);
echo $result . "\n";

// ── 5. Newsletter Generator ──────────────────────────────
hr('5. NEWSLETTER GENERATOR — Subject + preview');
$result = callGateway($gateway, $key, 'claude-opus-5', [
    ['role' => 'system', 'content' => 'Generate only the subject line (max 50 chars) and preview text (max 90 chars) for a newsletter digest. Format: Subject: ...\nPreview: ...'],
    ['role' => 'user',   'content' => json_encode([
        'category_name' => 'Technology',
        'week_range'    => 'Aug 10–16, 2026',
        'top_article'   => 'Nepal's first AI startup raises $2M seed round',
    ])],
], 100);
echo $result . "\n";

echo "\n" . str_repeat('═', 60) . "\n";
echo "  All agents tested. Check OmniRoute dashboard for token usage.\n";
echo str_repeat('═', 60) . "\n\n";
