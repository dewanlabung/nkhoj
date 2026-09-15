<?php

namespace App\Core\Http\Controllers;

use App\Core\Models\NotificationActivityLog;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Routing\Controller;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get paginated notifications for current user
     */
    public function index(): JsonResponse
    {
        $pagination = Auth::user()
            ->notifications()
            ->latest()
            ->simplePaginate(request('perPage', 15));

        return response()->json([
            'success' => true,
            'data' => $pagination,
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(): JsonResponse
    {
        $count = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }

    /**
     * Mark notifications as read
     */
    public function markAsRead(): JsonResponse
    {
        $data = $this->validate(request(), [
            'ids' => 'array|required_without:markAllAsRead',
            'markAllAsRead' => 'boolean|required_without:ids',
        ]);

        $query = Auth::user()->unreadNotifications();

        if (isset($data['ids'])) {
            $query->whereIn('id', $data['ids']);
        }

        $query->update(['read_at' => now()]);

        $unreadCount = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'message' => 'Notifications marked as read',
        ]);
    }

    /**
     * Delete notifications
     */
    public function destroy(string $ids): JsonResponse
    {
        $ids = explode(',', $ids);

        Auth::user()
            ->notifications()
            ->whereIn('id', $ids)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notifications deleted successfully',
        ]);
    }

    /**
     * Delete all notifications
     */
    public function deleteAll(): JsonResponse
    {
        Auth::user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted successfully',
        ]);
    }

    /**
     * Get notification activity logs for current user
     */
    public function activityLogs(): JsonResponse
    {
        $logs = NotificationActivityLog::forUser(Auth::user(), 50);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Get activity logs for specific notification type
     */
    public function activityLogsByType(string $notifType): JsonResponse
    {
        $logs = NotificationActivityLog::byNotificationType($notifType, 50);

        return response()->json([
            'success' => true,
            'notification_type' => $notifType,
            'data' => $logs,
        ]);
    }
}
