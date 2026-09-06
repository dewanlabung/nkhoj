<?php

namespace App\Services\AI;

class SurveyNarrator extends BaseAgent
{
    /**
     * @param array $context {
     *   survey: array  — full survey object with questions, options, response_counts,
     *                    summary stats, geography, timeline
     * }
     * @return string  Markdown-formatted analytics report
     */
    public function run(array $context): mixed
    {
        $surveyJson = json_encode($context['survey'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $this->call(
            promptKey:    'survey_narrator',
            userMessages: [['role' => 'user', 'content' => $surveyJson]],
            maxTokens:    1500,
        );
    }
}
