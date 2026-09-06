<?php

namespace App\Services\AI;

class ModerationGuard extends BaseAgent
{
    /**
     * @param array $context {
     *   content: string,
     *   type: 'comment'|'post'|'bio',
     *   author_tier: 'new'|'trusted'|'verified',
     * }
     * @return array {
     *   action: 'approve'|'review'|'reject',
     *   confidence: float,
     *   violations: string[],
     *   reason: string,
     *   severity: 'low'|'medium'|'high',
     * }
     */
    public function run(array $context): mixed
    {
        $payload = json_encode([
            'content'     => $context['content'],
            'type'        => $context['type'] ?? 'comment',
            'author_tier' => $context['author_tier'] ?? 'new',
        ]);

        $result = $this->callJson(
            promptKey:    'content_moderation',
            userMessages: [['role' => 'user', 'content' => $payload]],
            maxTokens:    200,
        );

        // Safe default if parsing fails
        return $result ?: [
            'action'     => 'review',
            'confidence' => 0.0,
            'violations' => [],
            'reason'     => 'AI parse error — defaulting to manual review',
            'severity'   => 'low',
        ];
    }
}
