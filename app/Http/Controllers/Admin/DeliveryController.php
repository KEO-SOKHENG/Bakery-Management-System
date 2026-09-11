<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeliveryController extends Controller
{
    /**
     * Display a paginated list of deliveries with filters, KPI stats, and search.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Delivery::with(['order.customer', 'deliveryStaff']);

        // If delivery staff, only see their assigned deliveries
        if ($user && $user->isDeliveryStaff()) {
            $query->where('user_id', $user->id);
        }

        // Search by order number, tracking number, recipient, phone, or address
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%")
                  ->orWhere('delivery_address', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($oq) use ($search) {
                      $oq->where('order_number', 'like', "%{$search}%")
                         ->orWhere('customer_name', 'like', "%{$search}%");
                  });
            });
        }

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('delivery_status', strtolower($request->status));
        }

        // Staff Filter
        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }

        // Date Filter
        if ($request->filled('date')) {
            $date = Carbon::parse($request->date)->toDateString();
            $query->whereDate('scheduled_at', $date);
        }

        // Tab Filter
        $tab = $request->input('tab', 'all');
        if ($tab === 'pending') {
            $query->whereIn('delivery_status', ['pending', 'assigned']);
        } elseif ($tab === 'in_transit') {
            $query->where('delivery_status', 'out_for_delivery');
        } elseif ($tab === 'completed') {
            $query->where('delivery_status', 'delivered');
        }

        $deliveries = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Summary KPI Metrics
        $baseKpiQuery = Delivery::query();
        if ($user && $user->isDeliveryStaff()) {
            $baseKpiQuery->where('user_id', $user->id);
        }

        $totalDeliveries = (clone $baseKpiQuery)->count();
        $pendingCount = (clone $baseKpiQuery)->whereIn('delivery_status', ['pending', 'assigned'])->count();
        $outForDeliveryCount = (clone $baseKpiQuery)->where('delivery_status', 'out_for_delivery')->count();
        $deliveredCount = (clone $baseKpiQuery)->where('delivery_status', 'delivered')->count();

        // Delivery Staff list for assignment dropdown
        $deliveryStaff = User::whereIn('role', ['delivery_staff', 'manager', 'cashier'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Orders eligible for new delivery scheduling
        $eligibleOrders = Order::whereDoesntHave('delivery')
            ->whereIn('order_status', ['pending', 'preparing', 'ready_for_pickup'])
            ->orderBy('id', 'desc')
            ->take(30)
            ->get();

        $currency = Setting::get('currency', '$');

        return view('admin.deliveries.index', compact(
            'deliveries',
            'totalDeliveries',
            'pendingCount',
            'outForDeliveryCount',
            'deliveredCount',
            'deliveryStaff',
            'eligibleOrders',
            'currency',
            'tab'
        ));
    }

    /**
     * Show form / modal for scheduling a delivery.
     */
    public function create(Request $request)
    {
        $order = null;
        if ($request->filled('order_id')) {
            $order = Order::with('customer')->findOrFail($request->order_id);
        }

        $eligibleOrders = Order::whereDoesntHave('delivery')
            ->whereIn('order_status', ['pending', 'preparing', 'ready_for_pickup'])
            ->orderBy('id', 'desc')
            ->get();

        $deliveryStaff = User::whereIn('role', ['delivery_staff', 'manager'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $currency = Setting::get('currency', '$');

        return view('admin.deliveries.create', compact('order', 'eligibleOrders', 'deliveryStaff', 'currency'));
    }

    /**
     * Store a newly scheduled delivery.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'         => 'required|exists:orders,id',
            'user_id'          => 'nullable|exists:users,id',
            'recipient_name'   => 'nullable|string|max:255',
            'recipient_phone'  => 'nullable|string|max:50',
            'delivery_address' => 'required|string|max:1000',
            'scheduled_at'     => 'nullable|date',
            'delivery_fee'     => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Check if delivery already exists for this order
        if ($order->delivery()->exists()) {
            return redirect()->back()->with('error', "Delivery already exists for Order #{$order->order_number}.");
        }

        $recipientName = $validated['recipient_name'] ?: ($order->customer_name ?: 'Valued Customer');
        $recipientPhone = $validated['recipient_phone'] ?: ($order->customer->phone ?? null);

        $initialStatus = !empty($validated['user_id']) ? Delivery::STATUS_ASSIGNED : Delivery::STATUS_PENDING;

        $delivery = Delivery::create([
            'order_id'         => $order->id,
            'user_id'          => $validated['user_id'] ?? null,
            'recipient_name'   => $recipientName,
            'recipient_phone'  => $recipientPhone,
            'delivery_address' => $validated['delivery_address'],
            'delivery_status'  => $initialStatus,
            'scheduled_at'     => !empty($validated['scheduled_at']) ? Carbon::parse($validated['scheduled_at']) : now()->addHours(2),
            'delivery_fee'     => $validated['delivery_fee'] ?? 0.00,
            'notes'            => $validated['notes'] ?? null,
            'tracking_number'  => 'DEL-' . strtoupper(Str::random(8)),
        ]);

        // Dispatch notification
        try {
            if ($delivery->user_id) {
                app(NotificationService::class)->createNotification(
                    $delivery->user_id,
                    'delivery',
                    "New Delivery Assigned: #{$delivery->tracking_number}",
                    "You have been assigned to deliver Order #{$order->order_number} to {$delivery->delivery_address}.",
                    'info',
                    route('admin.deliveries.show', $delivery),
                    "delivery_assigned:{$delivery->id}"
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to notify delivery assignment: " . $e->getMessage());
        }

        return redirect()->route('admin.deliveries.show', $delivery)
            ->with('success', "Delivery #{$delivery->tracking_number} scheduled successfully.");
    }

    /**
     * Display the specified delivery record.
     */
    public function show(Delivery $delivery)
    {
        $user = Auth::user();

        // If delivery staff, ensure they are assigned
        if ($user && $user->isDeliveryStaff() && $delivery->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this delivery record.');
        }

        $delivery->load(['order.items.product', 'order.customer', 'deliveryStaff']);

        $deliveryStaff = User::whereIn('role', ['delivery_staff', 'manager', 'cashier'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $currency = Setting::get('currency', '$');

        return view('admin.deliveries.show', compact('delivery', 'deliveryStaff', 'currency'));
    }

    /**
     * Update delivery details.
     */
    public function update(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'user_id'          => 'nullable|exists:users,id',
            'recipient_name'   => 'nullable|string|max:255',
            'recipient_phone'  => 'nullable|string|max:50',
            'delivery_address' => 'required|string|max:1000',
            'scheduled_at'     => 'nullable|date',
            'delivery_fee'     => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $delivery->update([
            'user_id'          => $validated['user_id'] ?? $delivery->user_id,
            'recipient_name'   => $validated['recipient_name'] ?? $delivery->recipient_name,
            'recipient_phone'  => $validated['recipient_phone'] ?? $delivery->recipient_phone,
            'delivery_address' => $validated['delivery_address'],
            'scheduled_at'     => !empty($validated['scheduled_at']) ? Carbon::parse($validated['scheduled_at']) : $delivery->scheduled_at,
            'delivery_fee'     => $validated['delivery_fee'] ?? $delivery->delivery_fee,
            'notes'            => $validated['notes'] ?? $delivery->notes,
        ]);

        return redirect()->route('admin.deliveries.show', $delivery)
            ->with('success', "Delivery #{$delivery->tracking_number} updated successfully.");
    }

    /**
     * Update delivery status and synchronize with Order state machine.
     */
    public function updateStatus(Request $request, Delivery $delivery)
    {
        $user = Auth::user();

        // Delivery staff can only update deliveries assigned to them
        if ($user && $user->isDeliveryStaff() && $delivery->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'delivery_status' => 'required|string|in:pending,assigned,out_for_delivery,delivered,failed,cancelled',
        ]);

        $targetStatus = strtolower(trim($validated['delivery_status']));

        try {
            DB::transaction(function () use ($delivery, $targetStatus) {
                $lockedDelivery = Delivery::lockForUpdate()->findOrFail($delivery->id);
                $lockedOrder = Order::lockForUpdate()->find($lockedDelivery->order_id);

                $lockedDelivery->delivery_status = $targetStatus;

                if ($targetStatus === Delivery::STATUS_DELIVERED) {
                    $lockedDelivery->delivered_at = now();

                    // If order is in an active state that can be completed
                    if ($lockedOrder && in_array($lockedOrder->order_status, [
                        Order::STATUS_OUT_FOR_DELIVERY,
                        Order::STATUS_READY_FOR_PICKUP,
                        'ready'
                    ])) {
                        $lockedOrder->order_status = Order::STATUS_COMPLETED;
                        $lockedOrder->payment_status = 'paid';

                        // Ensure Sale record exists
                        if (!$lockedOrder->sale) {
                            Sale::create([
                                'order_id'       => $lockedOrder->id,
                                'user_id'        => Auth::id() ?? $lockedOrder->user_id,
                                'total'          => $lockedOrder->total,
                                'payment_method' => $lockedOrder->payment_method,
                                'payment_status' => 'paid',
                                'sold_at'        => now(),
                            ]);
                        }

                        // Ensure Payment record exists
                        if ($lockedOrder->payments()->count() === 0) {
                            Payment::create([
                                'order_id'          => $lockedOrder->id,
                                'user_id'           => Auth::id() ?? $lockedOrder->user_id,
                                'payment_method'    => $lockedOrder->payment_method,
                                'amount'            => $lockedOrder->total,
                                'payment_status'    => 'completed',
                                'payment_reference' => 'DEL-PAY-' . strtoupper(uniqid()),
                                'payment_date'      => now(),
                                'notes'             => "Completed via Delivery #" . $lockedDelivery->tracking_number,
                            ]);
                        }

                        // Award loyalty points
                        if ($lockedOrder->customer) {
                            $points = (int) floor($lockedOrder->total);
                            $lockedOrder->customer->addLoyaltyPoints($points);
                        }

                        $lockedOrder->save();
                    }
                } elseif ($targetStatus === Delivery::STATUS_OUT_FOR_DELIVERY) {
                    if ($lockedOrder && in_array($lockedOrder->order_status, [
                        Order::STATUS_READY_FOR_PICKUP,
                        'ready'
                    ])) {
                        $lockedOrder->order_status = Order::STATUS_OUT_FOR_DELIVERY;
                        $lockedOrder->save();
                    }
                }

                $lockedDelivery->save();
            });

            // Dispatch notification
            try {
                $statusHuman = ucwords(str_replace('_', ' ', $targetStatus));
                $title = "Delivery Status Updated: {$statusHuman}";
                $message = "Delivery #{$delivery->tracking_number} is now marked as {$statusHuman}.";

                app(NotificationService::class)->notifyRoles(
                    ['admin', 'manager', 'cashier'],
                    'delivery',
                    $title,
                    $message,
                    $targetStatus === 'delivered' ? 'success' : 'info',
                    route('admin.deliveries.show', $delivery),
                    "delivery_status:{$delivery->id}:{$targetStatus}"
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to dispatch delivery status notification: " . $e->getMessage());
            }

            return redirect()->back()
                ->with('success', "Delivery #{$delivery->tracking_number} status updated to " . ucwords(str_replace('_', ' ', $targetStatus)) . ".");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Assign delivery staff to this delivery.
     */
    public function assign(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $staff = User::findOrFail($validated['user_id']);

        $delivery->user_id = $staff->id;
        if ($delivery->delivery_status === Delivery::STATUS_PENDING) {
            $delivery->delivery_status = Delivery::STATUS_ASSIGNED;
        }
        $delivery->save();

        // Notify assigned staff
        try {
            app(NotificationService::class)->createNotification(
                $staff->id,
                'delivery',
                "Delivery Assigned: #{$delivery->tracking_number}",
                "You have been assigned to deliver Order #" . ($delivery->order->order_number ?? 'Order') . " to {$delivery->delivery_address}.",
                'info',
                route('admin.deliveries.show', $delivery),
                "delivery_assigned:{$delivery->id}"
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to dispatch staff assignment notification: " . $e->getMessage());
        }

        return redirect()->back()->with('success', "Delivery assigned to {$staff->name}.");
    }
}
