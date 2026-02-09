<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Start query
        $query = Order::with(['user', 'items.gift']);
        
        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by order status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $orders = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function edit(Order $order)
    {
        $statuses = ['pending', 'confirmed', 'processing', 'in_transit', 'delivered', 'cancelled'];
        $paymentStatuses = ['unpaid', 'paid'];
        
        // Check if payment method is eSewa
        $canEditPaymentStatus = $order->payment_method !== 'esewa';

        return view('admin.orders.edit', compact('order', 'statuses', 'paymentStatuses', 'canEditPaymentStatus'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,processing,in_transit,delivered,cancelled'],
            'payment_status' => ['required', 'in:unpaid,paid'],
        ]);

        // Prevent changing payment status for eSewa payments
        if ($order->payment_method === 'esewa' && $order->payment_status !== $data['payment_status']) {
            return redirect()
                ->back()
                ->withErrors(['payment_status' => 'Cannot modify payment status for eSewa payments.'])
                ->withInput();
        }

        $order->status = $data['status'];
        $order->payment_status = $data['payment_status'];
        
        // If marking as delivered, set delivered_at timestamp
        if ($data['status'] === 'delivered' && !$order->delivered_at) {
            $order->delivered_at = now();
        }
        
        $order->save();

        // Preserve only payment method filter
        $queryParams = [];
        if ($request->input('payment_method')) {
            $queryParams['payment_method'] = $request->input('payment_method');
        }

        return redirect()
            ->route('admin.orders.index', $queryParams)
            ->with('success', 'Order updated successfully.');
    }
    public function show(Order $order)
    {
        $order->load('user', 'items.gift');

        return view('admin.orders.show', compact('order'));
    }

    }
