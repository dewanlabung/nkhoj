<?php

namespace App\Services\AI;

use InvalidArgumentException;

/**
 * Routes AI tasks to the appropriate specialist agent.
 * All agents call OmniRoute (localhost:20128), which handles
 * provider fallback automatically when Claude quota runs out.
 */
class OrchestratorAgent
{
    private string $gateway;
    private string $primaryModel;
    private string $fastModel;

    public function __construct()
    {
        $this->gateway      = config('services.omniroute.endpoint', 'http://localhost:20128/v1');
        $this->primaryModel = config('services.omniroute.primary_model', 'claude-opus-5');
        $this->fastModel    = config('services.omniroute.fast_model', 'gemini-2.5-flash');
    }

    /**
     * Dispatch a task to the appropriate agent.
     *
     * @param string $task  One of: author_assist, survey_insight, moderation,
     *                               search_enhance, newsletter
     * @param array  $context  Task-specific payload (see each agent's run() signature)
     */
    public function handle(string $task, array $context): mixed
    {
        return match ($task) {
            'author_assist'  => $this->makeAgent(AuthorAgent::class, $this->primaryModel)->run($context),
            'survey_insight' => $this->makeAgent(SurveyNarrator::class, $this->primaryModel)->run($context),
            'moderation'     => $this->makeAgent(ModerationGuard::class, $this->fastModel)->run($context),
            'search_enhance' => $this->makeAgent(SearchEnhancer::class, $this->fastModel)->run($context),
            'newsletter'     => $this->makeAgent(NewsletterGenerator::class, $this->primaryModel)->run($context),
            default          => throw new InvalidArgumentException("Unknown AI task: {$task}"),
        };
    }

    private function makeAgent(string $class, string $model): BaseAgent
    {
        return new $class($this->gateway, $model);
    }
}
