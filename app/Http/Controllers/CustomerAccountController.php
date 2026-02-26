<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    /**
     * Overview landing page for signed-in customers.
     */
    public function dashboard(Request $request): View
    {
        $ordersQuery = $this->ordersFor($request);
        $recentOrders = (clone $ordersQuery)
            ->with('items')
            ->take(3)
            ->get();

        $stats = [
            'total_orders' => (clone $ordersQuery)->count(),
            'open_orders' => (clone $ordersQuery)->whereIn('status', ['pending', 'paid'])->count(),
            'lifetime_value' => (clone $ordersQuery)->where('status', 'paid')->sum('amount_total'),
            'last_order_at' => optional($recentOrders->first())->created_at,
        ];

        return view('account.dashboard', [
            'user' => $request->user(),
            'current' => 'dashboard',
            'eyebrow' => 'Welcome back',
            'title' => 'Account overview',
            'intro' => 'Keep tabs on your bookings, receipts, and contact preferences all in one place.',
            'recentOrders' => $recentOrders,
            'stats' => $stats,
        ]);
    }

    /**
     * Paginated list of all orders tied to this customer.
     */
    public function orders(Request $request): View
    {
        $orders = $this->ordersFor($request)
            ->with(['items' => function ($query) {
                $query->orderBy('id');
            }])
            ->paginate(10)
            ->withQueryString();

        return view('account.orders.index', [
            'user' => $request->user(),
            'current' => 'orders',
            'eyebrow' => 'Bookings & receipts',
            'title' => 'All orders',
            'intro' => 'Every therapy, class, or event you have booked through AtEase is listed here.',
            'orders' => $orders,
        ]);
    }

    /**
     * Detailed view for a single order.
     */
    public function showOrder(Request $request, int $orderId): View
    {
        $order = $this->ordersFor($request)
            ->with(['items', 'customerProfile', 'shippingDetail'])
            ->where('id', $orderId)
            ->firstOrFail();

        return view('account.orders.show', [
            'user' => $request->user(),
            'current' => 'orders',
            'eyebrow' => 'Receipt',
            'title' => 'Order #'.$order->id,
            'intro' => 'Placed '.optional($order->created_at)->format('j M Y · H:i'),
            'order' => $order,
        ]);
    }

    /**
     * Base query for fetching orders owned by the signed-in customer.
     */
    protected function ordersFor(Request $request): Builder
    {
        $user = $request->user();

        return Order::query()
            ->forCustomer($user)
            ->latest('created_at');
    }
}
