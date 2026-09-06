<?php

namespace App\Services\AI;

class NewsletterGenerator extends BaseAgent
{
    /**
     * @param array $context {
     *   articles: array,      — top 5-7 articles: title, excerpt, author, view_count, url
     *   category_name: string,
     *   week_range: string,   — e.g. "Aug 10–16, 2026"
     * }
     * @return array {
     *   subject: string,
     *   preview_text: string,
     *   body: string,         — plain text with Markdown links
     * }
     */
    public function run(array $context): mixed
    {
        $payload = json_encode([
            'articles'      => $context['articles'],
            'category_name' => $context['category_name'],
            'week_range'    => $context['week_range'],
        ], JSON_UNESCAPED_UNICODE);

        $raw = $this->call(
            promptKey:    'newsletter_generator',
            userMessages: [['role' => 'user', 'content' => $payload]],
            maxTokens:    1200,
        );

        return $this->parseNewsletterResponse($raw, $context['category_name']);
    }

    private function parseNewsletterResponse(string $raw, string $category): array
    {
        // Extract subject line
        preg_match('/\*\*Subject(?:\s+line)?:\*\*\s*(.+)/i', $raw, $subjectMatch);

        // Extract preview text
        preg_match('/\*\*Preview(?:\s+text)?:\*\*\s*(.+)/i', $raw, $previewMatch);

        $subject = trim($subjectMatch[1] ?? "{$category} — Weekly Digest");
        $preview = trim($previewMatch[1] ?? '');

        // Body is everything after subject/preview lines
        $body = preg_replace('/\*\*(?:Subject|Preview)[^*]*\*\*[^\n]*\n?/i', '', $raw);

        return [
            'subject'      => substr($subject, 0, 50),
            'preview_text' => substr($preview, 0, 90),
            'body'         => trim($body),
        ];
    }
}
