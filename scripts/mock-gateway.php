<?php
/**
 * Mock OmniRoute gateway — run with:
 *   php -S 127.0.0.1:20128 scripts/mock-gateway.php
 *
 * Handles POST /v1/chat/completions, matches agent by system-prompt keyword,
 * returns realistic stub JSON responses.
 */

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    exit;
}

// Only respond to chat completions
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path !== '/v1/chat/completions') {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not found']);
    exit;
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$body    = file_get_contents('php://input');
$payload = json_decode($body, true) ?? [];

// Extract system prompt for keyword matching
$systemMsg = '';
foreach ($payload['messages'] ?? [] as $msg) {
    if ($msg['role'] === 'system') {
        $systemMsg = strtolower($msg['content']);
        break;
    }
}

$stubs = [
    'search'     => json_encode([
        'expanded_terms'      => ['Nepal election results', 'नेपाल चुनाव परिणाम', 'vote count Nepal', 'निर्वाचन नतिजा'],
        'suggested_categories'=> ['politics', 'news'],
        'intent'              => 'news',
        'corrected_query'     => 'nepal election result',
        'nepali_translation'  => 'नेपाल चुनाव परिणाम',
    ]),
    'moderation' => json_encode([
        'action'     => 'approve',
        'confidence' => 0.97,
        'violations' => [],
        'reason'     => 'Comment is genuine, on-topic, and policy-compliant.',
        'severity'   => 'low',
    ]),
    'survey'     => "## Executive Summary\nOf 847 respondents, 73.2% use mobile as their primary device. Completion rate of 82% indicates strong engagement.\n\n## Key Findings\n- **73.2%** use mobile as primary device\n- **81.2%** are Nepal-based\n- Completion rate of **82%** exceeds industry average\n\n## Recommendations\n1. Optimize for mobile-first experience\n2. Target Nepali diaspora in follow-up survey",
    'newsletter' => "Subject: Nepal Tech's Biggest Week Yet\nPreview: A Kathmandu startup just raised \$2M.\n\nDear reader,\n\nThis week in technology, Nepal's startup ecosystem made headlines...",
    'seo'        => json_encode([
        'title' => 'Nepal Election 2082: Complete Results Guide',
        'meta'  => 'Comprehensive analysis of Nepal 2082 local election results across all seven provinces with vote counts and winning parties.',
    ]),
    'default'    => 'Response from nkhoj mock gateway.',
];

$stub = match (true) {
    str_contains($systemMsg, 'search') || str_contains($systemMsg, 'expand')    => $stubs['search'],
    str_contains($systemMsg, 'moderat') || str_contains($systemMsg, 'violat')    => $stubs['moderation'],
    str_contains($systemMsg, 'survey') || str_contains($systemMsg, 'analytics')  => $stubs['survey'],
    str_contains($systemMsg, 'newsletter') || str_contains($systemMsg, 'digest') => $stubs['newsletter'],
    str_contains($systemMsg, 'seo') || str_contains($systemMsg, 'title')         => $stubs['seo'],
    default                                                                       => $stubs['default'],
};

$model  = $payload['model'] ?? 'mock';
$tokens = $payload['max_tokens'] ?? 0;

echo json_encode([
    'id'      => 'mock-' . uniqid(),
    'object'  => 'chat.completion',
    'model'   => $model,
    'choices' => [[
        'index'         => 0,
        'message'       => ['role' => 'assistant', 'content' => $stub],
        'finish_reason' => 'stop',
    ]],
    'usage' => ['prompt_tokens' => 120, 'completion_tokens' => 80, 'total_tokens' => 200],
]);

error_log("[mock] {$model} max_tokens={$tokens} → stub matched");
