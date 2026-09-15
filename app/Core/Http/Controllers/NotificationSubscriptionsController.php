<?php

namespace App\Core\Http\Controllers;

use App\Models\Notifications\NotificationActivityLog;
use App\Models\UserEngagement\User;
use File;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class NotificationSubscriptionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get notification settings and user's current subscriptions
     */
    public function index(User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $response = $this->getConfig();

        // Filter by permissions
        $response['subscriptions'] = collect($response['subscriptions'])
            ->map(function ($group) use ($user) {
                $group['subscriptions'] = collect($group['subscriptions'])
                    ->filter(function ($subscription) use ($user) {
                        if (!isset($subscription['permissions'])) {
                            return true;
                        }
                        return collect($subscription['permissions'])->every(
                            fn($perm) => $user->hasPermission($perm)
                        );
                    })
                    ->values()
                    ->toArray();
                return $group;
            })
            ->filter(fn($g) => count($g['subscriptions']))
            ->values()
            ->toArray();

        // Get user's current subscriptions
        $response['user_selections'] = $user->notificationSubscriptions()
            ->select('id', 'notif_id', 'channels')
            ->get()
            ->keyBy('notif_id')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    /**
     * Update user's notification subscriptions
     */
    public function update(User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $data = $this->validate(request(), [
            'selections' => 'array|required',
            'selections.*.notif_id' => 'required|string',
            'selections.*.channels' => 'required|array',
        ]);

        $allConfig = collect($this->getConfig()['subscriptions'])
            ->flatMap(fn($g) => $g['subscriptions']);

        foreach ($data['selections'] as $selection) {
            // Validate user has permissions for this notification
            $config = $allConfig->firstWhere('notif_id', $selection['notif_id']);
            if (isset($config['permissions'])) {
                $hasAllPermissions = collect($config['permissions'])->every(
                    fn($perm) => $user->hasPermission($perm)
                );
                if (!$hasAllPermissions) {
                    return response()->json([
                        'success' => false,
                        'message' => "You lack permission to modify '{$selection['notif_id']}' notification settings",
                    ], 403);
                }
            }

            // Update or create subscription
            $subscription = $user->notificationSubscriptions()
                ->where('notif_id', $selection['notif_id'])
                ->first();

            if (!$subscription) {
                $subscription = $user->notificationSubscriptions()->create([
                    'notif_id' => $selection['notif_id'],
                    'channels' => [],
                ]);
            }

            // Merge channels
            $channels = $subscription->channels ?? [];
            foreach ($selection['channels'] as $channel => $enabled) {
                $channels[$channel] = (bool)$enabled;
            }

            $subscription->update(['channels' => $channels]);
        }

        // Log activity
        NotificationActivityLog::log(
            $user,
            'subscription',
            'updated',
            'system',
            true,
            null,
            [
                'selections_count' => count($data['selections']),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully',
        ]);
    }

    /**
     * Reset user's subscriptions to defaults
     */
    public function reset(User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $config = $this->getConfig();

        $defaultChannels = json_encode(
            collect($config['available_channels'] ?? ['browser', 'email'])
                ->mapWithKeys(fn($ch) => [$ch => true])
        );

        $user->notificationSubscriptions()->update(['channels' => $defaultChannels]);

        NotificationActivityLog::log(
            $user,
            'subscription',
            'reset_to_defaults',
            'system',
            true
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences reset to defaults',
        ]);
    }

    /**
     * Get notification configuration
     */
    private function getConfig()
    {
        return File::getRequire(
            resource_path('defaults/notification-settings.php')
        );
    }
}
