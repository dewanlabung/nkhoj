<?php

namespace App\Console\Commands;

use App\Models\AiPostTopic;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateAiPost extends Command
{
    protected $signature = 'ai:generate-posts {--topic=} {--force}';
    protected $description = 'Generate AI draft posts from configured topics using Gemini API';

    private string $geminiKey = '';
    private string $geminiModel = 'gemini-1.5-flash';

    public function handle(): int
    {
        $settings = $this->getSettings();
        $this->geminiKey = $settings['gemini_api_key'] ?? env('GEMINI_API_KEY', '');

        if (empty($this->geminiKey)) {
            $this->error('No Gemini API key configured. Set it in Admin → AI Content → Settings.');
            return 1;
        }

        $query = AiPostTopic::where('is_active', true);
        if ($this->option('topic')) {
            $query->where('id', $this->option('topic'));
        }
        $topics = $query->get();

        if ($topics->isEmpty()) {
            $this->info('No active topics found.');
            return 0;
        }

        $systemUser = User::where('role', 'admin')->first();
        if (!$systemUser) {
            $this->error('No admin user found to assign posts to.');
            return 1;
        }

        foreach ($topics as $topic) {
            if (!$this->option('force') && !$topic->isDueToday()) {
                $this->line("Skipping [{$topic->keyword}] — not due yet.");
                continue;
            }

            $this->info("Processing topic: {$topic->keyword}");
            $context = $this->fetchRssContext($topic->rss_source, $topic->keyword);

            $languages = match($topic->language) {
                'en'   => ['en'],
                'ne'   => ['ne'],
                default => ['en', 'ne'],
            };

            foreach ($languages as $lang) {
                $this->generatePost($topic, $context, $lang, $systemUser->id);
            }

            $topic->update(['last_run_at' => now()]);
        }

        $this->info('Done.');
        return 0;
    }

    private function generatePost(AiPostTopic $topic, string $context, string $lang, int $authorId): void
    {
        $isNepali = $lang === 'ne';
        $langLabel = $isNepali ? 'Nepali (Devanagari script)' : 'English';

        $prompt = $isNepali
            ? "तपाईं एक नेपाली पत्रकार हुनुहुन्छ। निम्न विषयमा नेपाली भाषामा (देवनागरी लिपिमा) एउटा जानकारीमूलक लेख लेख्नुहोस्:\n\nविषय: {$topic->keyword}\n\nसन्दर्भ जानकारी:\n{$context}\n\nकृपया यो ढाँचामा जवाफ दिनुहोस्:\nSHIRSHAK: [लेखको शीर्षक]\nSARAMSH: [2-3 वाक्यको सारांश]\nLEKH:\n[पूरा लेख, कम्तीमा 400 शब्द, अनुच्छेदमा विभाजित]\nTAGS: [3-5 ट्याग, अल्पविरामले छुट्याइएका]"
            : "You are a professional journalist. Write an informative article in English about the following topic:\n\nTopic: {$topic->keyword}\n\nContext from recent news:\n{$context}\n\nRespond in this exact format:\nTITLE: [Article title]\nEXCERPT: [2-3 sentence summary]\nBODY:\n[Full article, minimum 400 words, divided into paragraphs]\nTAGS: [3-5 tags, comma separated]";

        $generated = $this->callGemini($prompt);
        if (!$generated) {
            $this->warn("  Failed to generate {$langLabel} post for [{$topic->keyword}]");
            return;
        }

        $parsed = $this->parseResponse($generated, $isNepali);
        if (!$parsed['title']) {
            $this->warn("  Could not parse response for [{$topic->keyword}] ({$langLabel})");
            return;
        }

        $categoryId = $topic->category_id ?? Category::first()?->id;
        $slug       = Str::slug($parsed['title']) . '-' . Str::random(5);

        // Body stored as block array (matches existing post format)
        $body = [['type' => 'paragraph', 'content' => $parsed['body']]];

        Post::create([
            'author_id'    => $authorId,
            'category_id'  => $categoryId,
            'slug'         => $slug,
            'title'        => $parsed['title'],
            'excerpt'      => $parsed['excerpt'],
            'body'         => $body,
            'status'       => 'draft',
            'post_format'  => 'article',
            'sources'      => $topic->rss_source ? "AI generated from: {$topic->rss_source}" : 'AI generated',
            'published_at' => null,
        ]);

        $this->info("  Created {$langLabel} draft: {$parsed['title']}");
    }

    private function callGemini(string $prompt): ?string
    {
        $url     = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent?key={$this->geminiKey}";
        $payload = json_encode([
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 2048],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) return null;

        $data = json_decode($response, true);
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    private function fetchRssContext(?string $rssUrl, string $keyword): string
    {
        if (!$rssUrl) return "Write based on your knowledge of: {$keyword}";

        $ch = curl_init($rssUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; nkhoj-bot/1.0)',
        ]);
        $xml = curl_exec($ch);
        curl_close($ch);

        if (!$xml) return "Write based on your knowledge of: {$keyword}";

        // Parse RSS items (suppress XML errors for malformed feeds)
        libxml_use_internal_errors(true);
        $feed  = simplexml_load_string($xml);
        libxml_clear_errors();

        if (!$feed) return "Write based on your knowledge of: {$keyword}";

        $items   = $feed->channel->item ?? $feed->entry ?? [];
        $context = '';
        $count   = 0;

        foreach ($items as $item) {
            if ($count >= 5) break;
            $title       = (string)($item->title ?? '');
            $description = strip_tags((string)($item->description ?? $item->summary ?? ''));
            if (!$title) continue;
            $context .= "- {$title}: " . Str::limit($description, 200) . "\n";
            $count++;
        }

        return $context ?: "Write based on your knowledge of: {$keyword}";
    }

    private function parseResponse(string $text, bool $isNepali): array
    {
        $titleKey   = $isNepali ? 'SHIRSHAK' : 'TITLE';
        $excerptKey = $isNepali ? 'SARAMSAH|SARAMSAH|SARAMSH' : 'EXCERPT';
        $bodyKey    = $isNepali ? 'LEKH' : 'BODY';

        preg_match("/{$titleKey}:\s*(.+)/u", $text, $titleMatch);
        preg_match("/{$excerptKey}:\s*(.+)/su", $text, $excerptMatch);

        // Body: everything between BODY/LEKH: and TAGS:
        preg_match("/{$bodyKey}:\s*(.+?)(?:TAGS:|$)/su", $text, $bodyMatch);
        preg_match('/TAGS:\s*(.+)/u', $text, $tagsMatch);

        return [
            'title'   => trim($titleMatch[1] ?? ''),
            'excerpt' => trim($excerptMatch[1] ?? ''),
            'body'    => trim($bodyMatch[1] ?? $text),
            'tags'    => array_map('trim', explode(',', $tagsMatch[1] ?? '')),
        ];
    }

    private function getSettings(): array
    {
        $path = storage_path('app/site_settings.json');
        return file_exists($path) ? (json_decode(file_get_contents($path), true) ?? []) : [];
    }
}
