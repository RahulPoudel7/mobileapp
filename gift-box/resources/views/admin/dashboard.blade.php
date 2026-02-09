@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- OVERVIEW STATS -->
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #f9fafb;">📊 Business Overview</h2>
        <div class="cards">
            <div class="card" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); border-color: #3b82f6;">
                <div class="card-title" style="color: #fff;">💰 Total Revenue</div>
                <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0; color: #fff;">₹{{ number_format($totalRevenue, 2) }}</p>
                <p style="font-size: 0.85rem; color: #bfdbfe;">All-time earnings from paid orders</p>
            </div>

            <div class="card" style="background: linear-gradient(135deg, #065f46 0%, #047857 100%); border-color: #10b981;">
                <div class="card-title" style="color: #fff;">📦 Total Orders</div>
                <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0; color: #fff;">{{ $totalOrders }}</p>
                <p style="font-size: 0.85rem; color: #a7f3d0;">
                    <span style="color: #34d399;">{{ $paidOrders }} Paid</span> • 
                    <span style="color: #fbbf24;">{{ $unpaidOrders }} Unpaid</span>
                </p>
            </div>

            <div class="card" style="background: linear-gradient(135deg, #7c2d12 0%, #9a3412 100%); border-color: #f97316;">
                <div class="card-title" style="color: #fff;">👥 Total Customers</div>
                <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0; color: #fff;">{{ $totalUsers }}</p>
                <p style="font-size: 0.85rem; color: #fed7aa;">
                    <span style="color: #fdba74;">+{{ $newUsersWeek }}</span> this week
                </p>
            </div>

            <div class="card" style="background: linear-gradient(135deg, #581c87 0%, #6b21a8 100%); border-color: #a855f7;">
                <div class="card-title" style="color: #fff;">🎁 Total Gifts</div>
                <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0; color: #fff;">{{ $totalGifts }}</p>
                <p style="font-size: 0.85rem; color: #e9d5ff;">Available in catalog</p>
            </div>
        </div>
    </div>

    <!-- REVENUE ANALYTICS -->
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #f9fafb;">💵 Revenue Analytics</h2>
        <div class="cards">
            <div class="card">
                <div class="card-title">Today's Revenue</div>
                <p style="font-size: 1.75rem; font-weight: 700; margin: 0.5rem 0; color: #34d399;">₹{{ number_format($todayRevenue, 2) }}</p>
            </div>

            <div class="card">
                <div class="card-title">This Week</div>
                <p style="font-size: 1.75rem; font-weight: 700; margin: 0.5rem 0; color: #60a5fa;">₹{{ number_format($weekRevenue, 2) }}</p>
            </div>

            <div class="card">
                <div class="card-title">This Month</div>
                <p style="font-size: 1.75rem; font-weight: 700; margin: 0.5rem 0; color: #a78bfa;">₹{{ number_format($monthRevenue, 2) }}</p>
            </div>

            <div class="card">
                <div class="card-title">Avg Order Value</div>
                <p style="font-size: 1.75rem; font-weight: 700; margin: 0.5rem 0; color: #fbbf24;">₹{{ number_format($avgOrderValue, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- ORDER STATUS BREAKDOWN -->
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #f9fafb;">📋 Order Status</h2>
        <div class="cards">
            <div class="card">
                <div class="card-title">⏳ Pending</div>
                <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0; color: #fbbf24;">{{ $pendingOrders }}</p>
                <a href="{{ route('admin.orders.index') }}?status=pending" class="card-link">View pending orders →</a>
            </div>

            <div class="card">
                <div class="card-title">✅ Confirmed</div>
                <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0; color: #60a5fa;">{{ $confirmedOrders }}</p>
                <a href="{{ route('admin.orders.index') }}?status=confirmed" class="card-link">View confirmed →</a>
            </div>

            <div class="card">
                <div class="card-title">🚚 Delivered</div>
                <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0; color: #34d399;">{{ $deliveredOrders }}</p>
                <a href="{{ route('admin.orders.index') }}?status=delivered" class="card-link">View delivered →</a>
            </div>

            <div class="card">
                <div class="card-title">❌ Cancelled</div>
                <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0; color: #f87171;">{{ $cancelledOrders }}</p>
                <a href="{{ route('admin.orders.index') }}?status=cancelled" class="card-link">View cancelled →</a>
            </div>
        </div>
    </div>

    <!-- PAYMENT METHOD ANALYTICS -->
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #f9fafb;">💳 Payment Methods</h2>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem;">
            <div class="card">
                <div class="card-title">eSewa Payments</div>
                <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0; color: #34d399;">{{ $esewaOrders }}</p>
                <div style="margin-top: 0.5rem; font-size: 0.85rem; color: #9ca3af;">
                    {{ $totalOrders > 0 ? round(($esewaOrders / $totalOrders) * 100, 1) : 0 }}% of total orders
                </div>
            </div>

            <div class="card">
                <div class="card-title">Cash on Delivery</div>
                <p style="font-size: 2rem; font-weight: 700; margin: 0.5rem 0; color: #fbbf24;">{{ $codOrders }}</p>
                <div style="margin-top: 0.5rem; font-size: 0.85rem; color: #9ca3af;">
                    {{ $totalOrders > 0 ? round(($codOrders / $totalOrders) * 100, 1) : 0 }}% of total orders
                </div>
            </div>
        </div>
    </div>

    <!-- REVENUE BREAKDOWN -->
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #f9fafb;">💸 Revenue Breakdown</h2>
        <div class="cards">
            <div class="card">
                <div class="card-title">Delivery Charges</div>
                <p style="font-size: 1.5rem; font-weight: 700; margin: 0.5rem 0; color: #60a5fa;">₹{{ number_format($totalDeliveryCharges, 2) }}</p>
            </div>

            <div class="card">
                <div class="card-title">Gift Wrapping</div>
                <p style="font-size: 1.5rem; font-weight: 700; margin: 0.5rem 0; color: #a78bfa;">₹{{ number_format($totalGiftWrapping, 2) }}</p>
            </div>

            <div class="card">
                <div class="card-title">Personal Notes</div>
                <p style="font-size: 1.5rem; font-weight: 700; margin: 0.5rem 0; color: #f472b6;">₹{{ number_format($totalPersonalNotes, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- TOP SELLING GIFTS -->
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #f9fafb;">🏆 Top Selling Gifts</h2>
        <div class="card">
            @if($topGifts->count() > 0)
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #1f2937;">
                            <th style="text-align: left; padding: 0.75rem; color: #9ca3af; font-weight: 500;">Rank</th>
                            <th style="text-align: left; padding: 0.75rem; color: #9ca3af; font-weight: 500;">Gift Name</th>
                            <th style="text-align: right; padding: 0.75rem; color: #9ca3af; font-weight: 500;">Units Sold</th>
                            <th style="text-align: right; padding: 0.75rem; color: #9ca3af; font-weight: 500;">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topGifts as $index => $gift)
                        <tr style="border-bottom: 1px solid #111827;">
                            <td style="padding: 0.75rem;">
                                <span style="display: inline-block; width: 24px; height: 24px; background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: 50%; text-align: center; line-height: 24px; font-weight: 700; color: #000;">{{ $index + 1 }}</span>
                            </td>
                            <td style="padding: 0.75rem; color: #f9fafb;">{{ $gift->name }}</td>
                            <td style="padding: 0.75rem; text-align: right; color: #34d399; font-weight: 600;">{{ $gift->total_sold }}</td>
                            <td style="padding: 0.75rem; text-align: right; color: #60a5fa; font-weight: 600;">₹{{ number_format($gift->total_revenue, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #9ca3af; text-align: center; padding: 2rem;">No sales data available yet.</p>
            @endif
        </div>
    </div>

    <!-- TOP CUSTOMERS -->
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #f9fafb;">👑 Top Customers</h2>
        <div class="card">
            @if($topCustomers->count() > 0)
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #1f2937;">
                            <th style="text-align: left; padding: 0.75rem; color: #9ca3af; font-weight: 500;">Customer</th>
                            <th style="text-align: right; padding: 0.75rem; color: #9ca3af; font-weight: 500;">Orders</th>
                            <th style="text-align: right; padding: 0.75rem; color: #9ca3af; font-weight: 500;">Total Spent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCustomers as $customer)
                        <tr style="border-bottom: 1px solid #111827;">
                            <td style="padding: 0.75rem; color: #f9fafb;">
                                {{ $customer->user ? $customer->user->name : 'Unknown' }}
                                <div style="font-size: 0.75rem; color: #6b7280;">{{ $customer->user ? $customer->user->email : '' }}</div>
                            </td>
                            <td style="padding: 0.75rem; text-align: right; color: #a78bfa; font-weight: 600;">{{ $customer->order_count }}</td>
                            <td style="padding: 0.75rem; text-align: right; color: #34d399; font-weight: 600;">₹{{ number_format($customer->total_spent, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #9ca3af; text-align: center; padding: 2rem;">No customer data available yet.</p>
            @endif
        </div>
    </div>

    <!-- MONTHLY REVENUE TREND -->
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #f9fafb;">📈 Monthly Revenue Trend (Last 6 Months)</h2>
        <div class="card">
            @if($monthlyRevenue->count() > 0)
                <div style="display: flex; align-items: flex-end; gap: 1rem; height: 200px; padding: 1rem 0;">
                    @php
                        $maxRevenue = $monthlyRevenue->max('revenue');
                    @endphp
                    @foreach($monthlyRevenue as $month)
                        @php
                            $height = $maxRevenue > 0 ? ($month->revenue / $maxRevenue) * 100 : 0;
                        @endphp
                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                            <div style="font-size: 0.75rem; color: #9ca3af; margin-bottom: 0.5rem;">
                                ₹{{ number_format($month->revenue, 0) }}
                            </div>
                            <div style="width: 100%; background: linear-gradient(to top, #3b82f6, #60a5fa); border-radius: 0.25rem; height: {{ $height }}%; min-height: 10px;"></div>
                            <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">
                                {{ date('M', strtotime($month->month . '-01')) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: #9ca3af; text-align: center; padding: 2rem;">No revenue data available yet.</p>
            @endif
        </div>
    </div>

    <!-- RECENT ORDERS -->
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #f9fafb;">🕐 Recent Orders</h2>
        <div class="card">
            @if($recentOrders->count() > 0)
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #1f2937;">
                            <th style="text-align: left; padding: 0.75rem; color: #9ca3af; font-weight: 500;">Order ID</th>
                            <th style="text-align: left; padding: 0.75rem; color: #9ca3af; font-weight: 500;">Customer</th>
                            <th style="text-align: left; padding: 0.75rem; color: #9ca3af; font-weight: 500;">Status</th>
                            <th style="text-align: left; padding: 0.75rem; color: #9ca3af; font-weight: 500;">Payment</th>
                            <th style="text-align: right; padding: 0.75rem; color: #9ca3af; font-weight: 500;">Amount</th>
                            <th style="text-align: right; padding: 0.75rem; color: #9ca3af; font-weight: 500;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        <tr style="border-bottom: 1px solid #111827;">
                            <td style="padding: 0.75rem; color: #60a5fa; font-weight: 600;">#{{ $order->id }}</td>
                            <td style="padding: 0.75rem; color: #f9fafb;">{{ $order->user ? $order->user->name : 'Guest' }}</td>
                            <td style="padding: 0.75rem;">
                                <span style="padding: 0.2rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; 
                                    @if($order->status == 'pending') background: rgba(251, 191, 36, 0.15); color: #fbbf24;
                                    @elseif($order->status == 'confirmed') background: rgba(96, 165, 250, 0.15); color: #60a5fa;
                                    @elseif($order->status == 'delivered') background: rgba(52, 211, 153, 0.15); color: #34d399;
                                    @else background: rgba(248, 113, 113, 0.15); color: #f87171; @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td style="padding: 0.75rem;">
                                <span style="padding: 0.2rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; 
                                    @if($order->payment_status == 'paid') background: rgba(52, 211, 153, 0.15); color: #34d399;
                                    @else background: rgba(248, 113, 113, 0.15); color: #f87171; @endif">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                            <td style="padding: 0.75rem; text-align: right; color: #34d399; font-weight: 600;">₹{{ number_format($order->total_amount, 2) }}</td>
                            <td style="padding: 0.75rem; text-align: right; color: #9ca3af; font-size: 0.85rem;">{{ $order->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="margin-top: 1rem; text-align: center;">
                    <a href="{{ route('admin.orders.index') }}" class="card-link">View all orders →</a>
                </div>
            @else
                <p style="color: #9ca3af; text-align: center; padding: 2rem;">No orders yet.</p>
            @endif
        </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #f9fafb;">⚡ Quick Actions</h2>
        <div class="cards">
            <div class="card">
                <div class="card-title">
                    Gifts <span class="badge">Catalog</span>
                </div>
                <p class="card-text">
                    Create, edit, or remove gifts available in the app.
                </p>
                <a href="{{ route('admin.gifts.index') }}" class="card-link">
                    Manage gifts →
                </a>
            </div>

            <div class="card">
                <div class="card-title">
                    Users <span class="badge">Customers</span>
                </div>
                <p class="card-text">
                    View user accounts and handle important actions.
                </p>
                <a href="{{ route('admin.users.index') }}" class="card-link">
                    Manage users →
                </a>
            </div>

            <div class="card">
                <div class="card-title">
                    Orders <span class="badge">Operations</span>
                </div>
                <p class="card-text">
                    View and update customer orders and delivery status.
                </p>
                <a href="{{ route('admin.orders.index') }}" class="card-link">
                    Manage orders →
                </a>
            </div>
        </div>
    </div>
@endsection
