<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Models\PageCategory;
use App\Models\PageVerificationRequest;
use App\Models\SocialPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SocialPageAdminController extends BaseAdminController
{
    public function adminSocialPages(Request $request)
    {
        $this->requireAdmin();

        $query = SocialPage::with('owner')->withCount(['admins', 'reports']);

        if ($search = $request->input('q')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($category = $request->input('category')) {
            $query->whereJsonContains('categories', $category);
        }
        if ($verified = $request->input('verified')) {
            $query->where('is_verified', $verified === 'yes');
        }

        $pages = $query->latest()->paginate(25)->withQueryString();

        $pendingVerifications = PageVerificationRequest::with('page')
            ->where('status', 'pending')->latest()->get();

        $stats = [
            'total'    => SocialPage::count(),
            'active'   => SocialPage::where('status', 'active')->count(),
            'disabled' => SocialPage::where('status', 'disabled')->count(),
            'verified' => SocialPage::where('is_verified', true)->count(),
            'pending'  => $pendingVerifications->count(),
        ];

        $categories = PageCategory::active()->get();

        return view('admin.social-pages', compact('pages', 'pendingVerifications', 'stats', 'categories'));
    }

    public function adminSocialPageAction(Request $request, int $id)
    {
        $this->requireAdmin();

        $page   = SocialPage::findOrFail($id);
        $action = $request->input('action');

        switch ($action) {
            case 'verify':
                $page->update(['is_verified' => true]);
                PageVerificationRequest::where('social_page_id', $page->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'approved', 'reviewed_at' => now(), 'reviewed_by' => auth()->id()]);
                return back()->with('success', "\"{$page->name}\" has been verified.");

            case 'unverify':
                $page->update(['is_verified' => false]);
                return back()->with('success', "Verification removed from \"{$page->name}\".");

            case 'enable':
                $page->update(['status' => 'active', 'disabled_reason' => null]);
                return back()->with('success', "\"{$page->name}\" is now active.");

            case 'disable':
                $page->update([
                    'status'          => 'disabled',
                    'disabled_reason' => $request->input('reason', 'Disabled by admin.'),
                ]);
                return back()->with('success', "\"{$page->name}\" has been disabled.");

            case 'suspend':
                $page->update([
                    'status'          => 'suspended',
                    'disabled_reason' => $request->input('reason', 'Suspended by admin.'),
                ]);
                return back()->with('success', "\"{$page->name}\" has been suspended.");

            case 'delete':
                $name = $page->name;
                $page->delete();
                return back()->with('success', "\"{$name}\" has been permanently deleted.");

            case 'approve_verification':
                $vr = PageVerificationRequest::where('id', $request->input('vr_id'))
                    ->where('status', 'pending')->firstOrFail();
                $vr->update(['status' => 'approved', 'reviewed_at' => now(), 'reviewed_by' => auth()->id()]);
                $vr->page()->update(['is_verified' => true]);
                return back()->with('success', 'Verification request approved.');

            case 'reject_verification':
                $vr = PageVerificationRequest::where('id', $request->input('vr_id'))
                    ->where('status', 'pending')->firstOrFail();
                $vr->update([
                    'status'      => 'rejected',
                    'reviewed_at' => now(),
                    'reviewed_by' => auth()->id(),
                    'admin_notes' => $request->input('reason', ''),
                ]);
                return back()->with('success', 'Verification request rejected.');
        }

        return back()->with('error', 'Unknown action.');
    }

    public function adminPageCategories()
    {
        $this->requireAdmin();
        $categories = PageCategory::orderBy('sort_order')->get();
        return view('admin.page-categories', compact('categories'));
    }

    public function adminPageCategoryStore(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:page_categories,name',
            'icon' => 'nullable|string|max:10',
        ]);
        $max = PageCategory::max('sort_order') ?? 0;
        PageCategory::create([
            'name'       => $data['name'],
            'slug'       => Str::slug($data['name']),
            'icon'       => $data['icon'] ?? null,
            'sort_order' => $max + 1,
            'is_active'  => true,
        ]);
        return back()->with('success', 'Category added.');
    }

    public function adminPageCategoryToggle(int $id)
    {
        $this->requireAdmin();
        $cat = PageCategory::findOrFail($id);
        $cat->update(['is_active' => !$cat->is_active]);
        return back()->with('success', $cat->is_active ? 'Category enabled.' : 'Category disabled.');
    }

    public function adminPageCategoryDelete(int $id)
    {
        $this->requireAdmin();
        PageCategory::findOrFail($id)->delete();
        return back()->with('success', 'Category deleted.');
    }
}
