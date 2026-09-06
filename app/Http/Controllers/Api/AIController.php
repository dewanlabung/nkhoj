<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Services\AI\OrchestratorAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AIController extends Controller
{
    public function __construct(private OrchestratorAgent $ai) {}

    // POST /api/v1/ai/author-assist
    public function authorAssist(Request $request): JsonResponse
    {
        $request->validate([
            'action'      => 'required|in:outline,seo,improve,tags,translate',
            'topic'       => 'required_if:action,outline|string|max:300',
            'content'     => 'required_unless:action,outline|string|max:10000',
            'category'    => 'sometimes|string',
            'target_lang' => 'required_if:action,translate|in:ne,en',
        ]);

        $result = $this->ai->handle('author_assist', $request->validated());

        return response()->json(['data' => $result]);
    }

    // POST /api/v1/ai/moderate
    public function moderate(Request $request): JsonResponse
    {
        $request->validate([
            'content'     => 'required|string|max:5000',
            'type'        => 'required|in:comment,post,bio',
            'author_tier' => 'sometimes|in:new,trusted,verified',
        ]);

        $result = $this->ai->handle('moderation', $request->validated());

        return response()->json(['data' => $result]);
    }

    // POST /api/v1/ai/search-enhance
    public function searchEnhance(Request $request): JsonResponse
    {
        $request->validate([
            'query'           => 'required|string|max:200',
            'category_slugs'  => 'sometimes|array',
        ]);

        // Cache enhanced queries — same query gets same answer for 10 minutes
        $cacheKey = 'search_enhance_' . md5($request->input('query'));

        $result = Cache::remember($cacheKey, 600, fn() =>
            $this->ai->handle('search_enhance', $request->validated())
        );

        return response()->json(['data' => $result]);
    }

    // GET /api/v1/surveys/{survey}/insight
    public function surveyInsight(Survey $survey): JsonResponse
    {
        $this->authorize('view-analytics', $survey);

        // Cache per-survey insight for 6 hours
        $insight = Cache::remember(
            "survey_insight_{$survey->id}",
            now()->addHours(6),
            fn() => $this->ai->handle('survey_insight', [
                'survey' => $survey->toAnalyticsArray(),
            ])
        );

        return response()->json(['data' => $insight]);
    }

    // POST /api/v1/newsletter/generate
    public function generateNewsletter(Request $request): JsonResponse
    {
        $this->authorize('generate-newsletter');

        $request->validate([
            'articles'      => 'required|array|min:3|max:7',
            'category_name' => 'required|string|max:100',
            'week_range'    => 'required|string|max:50',
        ]);

        $result = $this->ai->handle('newsletter', $request->validated());

        return response()->json(['data' => $result]);
    }
}
