<?php

namespace App\Services\AI;

class SearchEnhancer extends BaseAgent
{
    /**
     * @param array $context {
     *   query: string,
     *   category_slugs?: string[]
     * }
     * @return array {
     *   expanded_terms: string[],
     *   suggested_categories: string[],
     *   intent: 'informational'|'news'|'opinion'|'how_to'|'local',
     *   corrected_query: string|null,
     *   nepali_translation: string|null,
     * }
     */
    public function run(array $context): mixed
    {
        $slugList = implode(', ', $context['category_slugs'] ?? []);

        $payload = "Query: {$context['query']}"
            . ($slugList ? "\nAvailable categories: {$slugList}" : '');

        $result = $this->callJson(
            promptKey:    'search_enhancer',
            userMessages: [['role' => 'user', 'content' => $payload]],
            maxTokens:    300,
        );

        return $result ?: [
            'expanded_terms'       => [$context['query']],
            'suggested_categories' => [],
            'intent'               => 'informational',
            'corrected_query'      => null,
            'nepali_translation'   => null,
        ];
    }
}
