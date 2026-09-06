<?php

namespace App\Services\AI;

class AuthorAgent extends BaseAgent
{
    /**
     * @param array $context {
     *   action: 'outline'|'seo'|'improve'|'tags'|'translate',
     *   topic?: string,
     *   content?: string,
     *   category?: string,
     *   target_lang?: 'ne'|'en',
     *   author_role?: 'reader'|'author'|'editor',
     * }
     * @return array { result: string, tokens_used?: int }
     */
    public function run(array $context): mixed
    {
        $action = $context['action'] ?? 'improve';

        $userContent = match ($action) {
            'outline'   => "Generate a detailed article outline for topic: {$context['topic']}\nCategory: {$context['category']}",
            'seo'       => "Generate SEO title and meta description for:\n{$context['content']}",
            'improve'   => "Improve readability of this article:\n{$context['content']}",
            'tags'      => "Suggest 3-5 relevant tags for:\n{$context['content']}",
            'translate' => "Translate to {$context['target_lang']}:\n{$context['content']}",
            default     => throw new \InvalidArgumentException("Unknown author action: {$action}"),
        };

        $result = $this->call(
            promptKey:    'author_assistant',
            userMessages: [['role' => 'user', 'content' => $userContent]],
            maxTokens:    $action === 'outline' ? 1500 : 800,
        );

        return ['result' => $result, 'action' => $action];
    }
}
