<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the Notification Center page.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Throttled sync of live condition alerts
        if ($user) {
            $this->notificationService->syncThrottled($user);
        }

        $query = Notification::forUser($user->id);

        // Filter: Read / Unread
        $statusFilter = $request->get('status', 'all');
        if ($statusFilter === 'unread') {
            $query->unread();
        } elseif ($statusFilter === 'read') {
            $query->read();
        }

        // Filter: Category / Type
        $typeFilter = $request->get('type', 'all');
        if ($typeFilter !== 'all' && in_array($typeFilter, ['inventory', 'expiring', 'order', 'production', 'purchase_order', 'custom_order', 'system', 'promotion'])) {
            $query->where('type', $typeFilter);
        }

        // Filter: Severity
        if ($request->filled('severity') && in_array($request->severity, ['critical', 'danger', 'warning', 'info', 'success'])) {
            if (in_array($request->severity, ['critical', 'danger'])) {
                $query->whereIn('severity', ['critical', 'danger']);
            } else {
                $query->where('severity', $request->severity);
            }
        }

        // Filter: Search Term
        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('message', 'like', $term);
            });
        }

        $notifications = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        // User statistics
        $stats = [
            'total'     => Notification::forUser($user->id)->count(),
            'unread'    => Notification::forUser($user->id)->unread()->count(),
            'critical'  => Notification::forUser($user->id)->unread()->whereIn('severity', ['critical', 'danger'])->count(),
            'inventory' => Notification::forUser($user->id)->unread()->whereIn('type', ['inventory', 'expiring'])->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'stats', 'statusFilter', 'typeFilter'));
    }

    /**
     * Get current unread notification count for authenticated user (AJAX).
     */
    public function unreadCount(): JsonResponse
    {
        $count = Notification::forUser(Auth::id())->unread()->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications feed for header dropdown (AJAX).
     */
    public function feed(): JsonResponse
    {
        $user = Auth::user();
        if ($user) {
            $this->notificationService->syncThrottled($user);
        }

        $notifications = Notification::forUser($user->id)
            ->orderByDesc('created_at')
            ->take(15)
            ->get()
            ->map(function ($notif) {
                return [
                    'id'          => $notif->id,
                    'type'        => $notif->type,
                    'title'       => $notif->title,
                    'message'     => $notif->message,
                    'severity'    => $notif->severity,
                    'action_url'  => $notif->action_url,
                    'is_read'     => $notif->isRead(),
                    'time_ago'    => $notif->created_at->diffForHumans(),
                ];
            });

        $unreadCount = Notification::forUser($user->id)->unread()->count();

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        // Strict Authorization: User must own the notification
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $notification->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'unread_count' => Notification::forUser(Auth::id())->unread()->count(),
            ]);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark a single notification as unread.
     */
    public function markAsUnread(Request $request, Notification $notification)
    {
        // Strict Authorization: User must own the notification
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $notification->markAsUnread();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'unread_count' => Notification::forUser(Auth::id())->unread()->count(),
            ]);
        }

        return redirect()->back()->with('success', 'Notification marked as unread.');
    }

    /**
     * Mark all unread notifications of authenticated user as read.
     */
    public function markAllAsRead(Request $request)
    {
        Notification::forUser(Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'unread_count' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, Notification $notification)
    {
        // Strict Authorization: User must own the notification
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $notification->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'unread_count' => Notification::forUser(Auth::id())->unread()->count(),
            ]);
        }

        return redirect()->back()->with('success', 'Notification removed.');
    }

    /**
     * Bulk mark notifications as read.
     */
    public function bulkRead(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (!empty($ids) && is_array($ids)) {
            Notification::forUser(Auth::id())
                ->whereIn('id', $ids)
                ->unread()
                ->update(['read_at' => now()]);
        }

        return response()->json([
            'success'      => true,
            'unread_count' => Notification::forUser(Auth::id())->unread()->count(),
        ]);
    }

    /**
     * Bulk mark notifications as unread.
     */
    public function bulkUnread(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (!empty($ids) && is_array($ids)) {
            Notification::forUser(Auth::id())
                ->whereIn('id', $ids)
                ->update(['read_at' => null]);
        }

        return response()->json([
            'success'      => true,
            'unread_count' => Notification::forUser(Auth::id())->unread()->count(),
        ]);
    }

    /**
     * Bulk delete notifications.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (!empty($ids) && is_array($ids)) {
            Notification::forUser(Auth::id())
                ->whereIn('id', $ids)
                ->delete();
        }

        return response()->json([
            'success'      => true,
            'unread_count' => Notification::forUser(Auth::id())->unread()->count(),
        ]);
    }

    /**
     * Send / broadcast a manual promotion or system announcement (Admin only).
     */
    public function sendPromotion(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->hasPermission('notifications.promote'))) {
            abort(403, 'Unauthorized action. Only administrators can broadcast promotions.');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:150',
            'message'     => 'required|string|max:1000',
            'target_role' => 'nullable|string|in:all,admin,manager,cashier,baker',
            'severity'    => 'nullable|string|in:info,warning,success,danger,critical',
        ]);

        $targetRole = $validated['target_role'] ?? 'all';
        $severity   = $validated['severity'] ?? 'info';

        $count = $this->notificationService->broadcastPromotion(
            $validated['title'],
            $validated['message'],
            $targetRole,
            $severity
        );

        AuditLogService::log(
            'notification.promotion_sent',
            "Broadcast announcement '{$validated['title']}' sent to {$count} users ({$targetRole})",
            null,
            [
                'title'           => $validated['title'],
                'target_role'     => $targetRole,
                'severity'        => $severity,
                'recipient_count' => $count,
            ]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Promotion successfully broadcast to {$count} users.",
                'count'   => $count,
            ]);
        }

        return redirect()->route('admin.notifications')->with('success', "Promotion successfully broadcast to {$count} users.");
    }
}
