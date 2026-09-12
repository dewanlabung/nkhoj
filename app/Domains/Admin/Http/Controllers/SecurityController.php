<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SecurityController extends BaseAdminController
{
    // ── IP Ban Management ────────────────────────────────────────

    public function addBannedIp(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'ip' => ['required', 'string', 'max:50', function ($attr, $value, $fail) {
                $ip = trim($value);
                $isCidr = str_contains($ip, '/');
                if ($isCidr) {
                    [$subnet, $bits] = explode('/', $ip, 2);
                    if (!filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || !is_numeric($bits) || $bits < 0 || $bits > 32) {
                        $fail('Invalid CIDR range.');
                    }
                } elseif (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    $fail('Invalid IP address.');
                }
            }],
            'reason' => 'nullable|string|max:255',
        ]);

        $s = $this->settings->get();
        $banned = $s['security']['banned_ips'] ?? [];
        $ip     = trim($data['ip']);

        if (!in_array($ip, $banned, true)) {
            $banned[] = $ip;
        }

        $s['security']['banned_ips'] = array_values($banned);
        $this->settings->save($s);

        return back()->with('success', "IP {$ip} has been banned.");
    }

    public function removeBannedIp(Request $request)
    {
        $this->requireAdmin();
        $ip = trim($request->input('ip', ''));
        if (!$ip) return back()->with('error', 'No IP provided.');

        $s = $this->settings->get();
        $banned = $s['security']['banned_ips'] ?? [];
        $banned = array_values(array_filter($banned, fn($b) => trim($b) !== $ip));

        $s['security']['banned_ips'] = $banned;
        $this->settings->save($s);

        return back()->with('success', "IP {$ip} has been removed from ban list.");
    }

    // ── Security Analysis ────────────────────────────────────────

    public function analysis()
    {
        $this->requireAdmin();

        $recentThreats = LoginHistory::where('successful', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['ip_address', 'user_agent', 'created_at', 'user_id']);

        $topAttackerIps = LoginHistory::where('successful', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('ip_address, COUNT(*) as attempts')
            ->groupBy('ip_address')
            ->orderByDesc('attempts')
            ->limit(10)
            ->get();

        $s = $this->settings->get();
        $bannedIps = $s['security']['banned_ips'] ?? [];

        return view('admin.security-analysis', compact('recentThreats', 'topAttackerIps', 'bannedIps', 's'));
    }

    // ── Rate Limiting ────────────────────────────────────────────

    public function updateRateLimits(Request $request)
    {
        $this->requireAdmin();
        $data = $request->validate([
            'rate_comments_per_minute'  => 'nullable|integer|min:1|max:1000',
            'rate_posts_per_minute'     => 'nullable|integer|min:1|max:1000',
            'rate_api_per_minute'       => 'nullable|integer|min:1|max:10000',
            'rate_login_per_minute'     => 'nullable|integer|min:1|max:100',
        ]);

        $s = $this->settings->get();
        $s['security']['rate_limits'] = [
            'comments_per_minute' => (int) ($data['rate_comments_per_minute'] ?? 10),
            'posts_per_minute'    => (int) ($data['rate_posts_per_minute'] ?? 5),
            'api_per_minute'      => (int) ($data['rate_api_per_minute'] ?? 60),
            'login_per_minute'    => (int) ($data['rate_login_per_minute'] ?? 5),
        ];
        $this->settings->save($s);

        return back()->with('success', 'Rate limits updated.');
    }

    // ── Security Headers ─────────────────────────────────────────

    public function updateSecurityHeaders(Request $request)
    {
        $this->requireAdmin();
        $enabled = $request->boolean('security_headers_enabled');

        $s = $this->settings->get();
        $s['security']['security_headers_enabled'] = $enabled;
        $this->settings->save($s);

        return back()->with('success', 'Security headers ' . ($enabled ? 'enabled' : 'disabled') . '.');
    }
}
