<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Models\AiPostTopic;
use App\Models\Category;
use App\Models\Post;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AiController extends BaseAdminController
{
    public function __construct(private SiteSettingsService $settings) {}

    public function aiContent()
    {
        $this->requireAdmin();
        $topics     = AiPostTopic::with('category')->latest()->get();
        $categories = Category::orderBy('sort_order')->get();
        $drafts     = Post::with(['author', 'category'])
                        ->where('status', 'draft')
                        ->where('sources', 'like', 'AI generated%')
                        ->latest()
                        ->paginate(20);
        $s           = $this->settings->get();
        $geminiKey   = $s['gemini_api_key'] ?? '';
        $geminiModel = $s['gemini_model'] ?? 'gemini-1.5-flash';

        return view('admin.ai-content', compact('topics', 'categories', 'drafts', 'geminiKey', 'geminiModel'));
    }

    public function storeAiTopic(Request $request)
    {
        $this->requireAdmin();
        AiPostTopic::create([
            'keyword'     => $request->keyword,
            'language'    => $request->language ?? 'both',
            'category_id' => $request->category_id ?: null,
            'rss_source'  => $request->rss_source ?: null,
            'frequency'   => $request->frequency ?? 'daily',
            'is_active'   => true,
        ]);
        return redirect('/admin/ai-content')->with('success', 'Topic added.');
    }

    public function toggleAiTopic(AiPostTopic $topic)
    {
        $this->requireAdmin();
        $topic->update(['is_active' => !$topic->is_active]);
        return back();
    }

    public function deleteAiTopic(AiPostTopic $topic)
    {
        $this->requireAdmin();
        $topic->delete();
        return redirect('/admin/ai-content')->with('success', 'Topic deleted.');
    }

    public function runAiTopic(AiPostTopic $topic)
    {
        $this->requireAdmin();
        $exitCode = Artisan::call('ai:generate-posts', ['--topic' => $topic->id, '--force' => true]);
        $output   = trim(Artisan::output());

        if ($exitCode !== 0) {
            $error = $output ?: 'Generation failed. Check your Gemini API key in AI Content settings.';
            return redirect('/admin/ai-content')->with('error', $error);
        }

        $topic->update(['last_run_at' => now()]);
        $msg = $output ?: 'Posts generated for: ' . $topic->keyword;
        return redirect('/admin/ai-content')->with('success', $msg);
    }

    public function runAllAiTopics()
    {
        $this->requireAdmin();
        Artisan::call('ai:generate-posts', ['--force' => true]);
        return redirect('/admin/ai-content')->with('success', 'Generation triggered for all active topics.');
    }

    public function updateGeminiSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['gemini_api_key'] = $request->gemini_api_key;
        $s['gemini_model']   = $request->gemini_model ?? 'gemini-1.5-flash';
        $this->settings->save($s);
        return redirect('/admin/ai-content')->with('success', 'AI settings saved.');
    }

    public function publishAiDraft(Post $post)
    {
        $this->requireAdmin();
        $post->update(['status' => 'published', 'published_at' => now()]);
        return back()->with('success', 'Post published.');
    }

    public function deleteAiDraft(Post $post)
    {
        $this->requireAdmin();
        $post->delete();
        return back()->with('success', 'Draft deleted.');
    }
}
