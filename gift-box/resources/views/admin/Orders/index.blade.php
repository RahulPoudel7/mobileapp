@extends('layouts.app')

@section('title', 'Orders')

@section('content')
    <div class="cards" style="display:block;">
        <div class="card" style="margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div class="card-title">Orders</div>
                <p class="card-text">
                    View customer orders and update their status.
                </p>
            </div>
            {{-- Optional: no "New order" button, since orders come from app --}}
        </div>

        <!-- Filters -->
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-title">🔍 Filter Orders</div>
            <form method="GET" action="{{ route('admin.orders.index') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-top:1rem;">
                
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.85rem; color:#9ca3af;">Payment Method</label>
                    <select name="payment_method" style="width:100%; padding:0.5rem; border-radius:0.5rem; background:#111827; border:1px solid #374151; color:#e5e7eb;">
                        <option value="">All Methods</option>
                        <option value="esewa" {{ request('payment_method') == 'esewa' ? 'selected' : '' }}>eSewa</option>
                        <option value="cod" {{ request('payment_method') == 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
                    </select>
                </div>

                <div style="display:flex; gap:0.5rem; align-items:flex-end;">
                    <button type="submit" style="padding:0.5rem 1.5rem; background:#3b82f6; color:#fff; border:none; border-radius:0.5rem; cursor:pointer; font-weight:500;">
                        Apply Filter
                    </button>
                    <a href="{{ route('admin.orders.index') }}" style="padding:0.5rem 1rem; background:#374151; color:#e5e7eb; border-radius:0.5rem; text-decoration:none; display:inline-block; text-align:center;">
                        Clear
                    </a>
                </div>
            </form>

            @if(request('payment_method'))
                <div style="margin-top:1rem; padding:0.75rem; background:#111827; border-radius:0.5rem; font-size:0.85rem;">
                    <strong>Active Filter:</strong>
                    <span style="display:inline-block; padding:0.25rem 0.5rem; background:#1e40af; border-radius:0.25rem; margin:0.25rem;">
                        {{ request('payment_method') == 'esewa' ? 'eSewa' : 'Cash on Delivery' }}
                    </span>
                </div>
            @endif
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                <thead>
                    <tr style="background:#020617;">
                        <th style="text-align:left; padding:0.75rem 1rem;">Order</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Customer</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Gift</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Personal Note</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Delivery Date</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Amount</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Payment Method</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Payment Status</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Order Status</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                <strong style="color:#60a5fa;">ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong>
                                <br><small style="color:#6b7280;">{{ $order->created_at->format('M d, Y') }}</small>
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                {{ $order->recipient_name }}<br>
                                <small style="color:#6b7280;">{{ $order->recipient_phone }}</small>
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                @if($order->items && $order->items->count())
                                    @foreach($order->items as $item)
                                        <div style="margin-bottom:0.25rem;">
                                            <small>{{ optional($item->gift)->name ?? '—' }} x{{ $item->quantity }}</small>
                                        </div>
                                    @endforeach
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                @if($order->has_personal_note && $order->personal_note_text)
                                    <span style="padding:0.25rem 0.5rem; background:rgba(139, 92, 246, 0.15); color:#a78bfa; border-radius:0.25rem; font-size:0.75rem; font-weight:600; cursor:help;" title="{{ $order->personal_note_text }}">
                                        📝 {{ Str::limit($order->personal_note_text, 20) }}
                                    </span>
                                @elseif($order->has_personal_note)
                                    <span style="padding:0.25rem 0.5rem; background:rgba(139, 92, 246, 0.15); color:#a78bfa; border-radius:0.25rem; font-size:0.75rem; font-weight:600;">
                                        📝 Yes (no text)
                                    </span>
                                @else
                                    <span style="color:#6b7280; font-size:0.75rem;">—</span>
                                @endif
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                @if($order->delivery_date)
                                    <span style="color:#e5e7eb;">{{ \Carbon\Carbon::parse($order->delivery_date)->format('M d, Y') }}</span>
                                @else
                                    <span style="color:#6b7280;">Not selected</span>
                                @endif
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                <strong style="color:#34d399;">₹{{ number_format($order->total_amount, 2) }}</strong>
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                @if($order->payment_method == 'esewa')
                                    <span style="padding:0.25rem 0.5rem; background:rgba(96, 165, 250, 0.15); color:#60a5fa; border-radius:0.25rem; font-size:0.75rem; font-weight:600;">
                                        eSewa
                                    </span>
                                @elseif($order->payment_method == 'cod')
                                    <span style="padding:0.25rem 0.5rem; background:rgba(251, 191, 36, 0.15); color:#fbbf24; border-radius:0.25rem; font-size:0.75rem; font-weight:600;">
                                        COD
                                    </span>
                                @else
                                    <span style="color:#6b7280;">N/A</span>
                                @endif
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                @if($order->payment_status == 'paid')
                                    <span style="padding:0.25rem 0.5rem; background:rgba(52, 211, 153, 0.15); color:#34d399; border-radius:0.25rem; font-size:0.75rem; font-weight:600;">
                                        ✓ Paid
                                    </span>
                                @else
                                    <span style="padding:0.25rem 0.5rem; background:rgba(248, 113, 113, 0.15); color:#f87171; border-radius:0.25rem; font-size:0.75rem; font-weight:600;">
                                        Unpaid
                                    </span>
                                @endif
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                <span style="padding:0.25rem 0.5rem; border-radius:0.25rem; font-size:0.75rem; font-weight:600;
                                    @if($order->status == 'pending') background:rgba(251, 191, 36, 0.15); color:#fbbf24;
                                    @elseif($order->status == 'confirmed') background:rgba(96, 165, 250, 0.15); color:#60a5fa;
                                    @elseif($order->status == 'processing') background:rgba(167, 139, 250, 0.15); color:#a78bfa;
                                    @elseif($order->status == 'in_transit') background:rgba(244, 114, 182, 0.15); color:#f472b6;
                                    @elseif($order->status == 'delivered') background:rgba(52, 211, 153, 0.15); color:#34d399;
                                    @else background:rgba(248, 113, 113, 0.15); color:#f87171; @endif">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                <a href="{{ route('admin.orders.show', $order) }}?{{ http_build_query(request()->query()) }}" class="card-link" style="margin-right:0.5rem;">
                                    View
                                </a>
                                <a href="{{ route('admin.orders.edit', $order) }}?{{ http_build_query(request()->query()) }}" class="card-link">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="padding:2rem 1rem; text-align:center; color:#6b7280;">
                                @if(request()->hasAny(['payment_method', 'payment_status', 'status']))
                                    No orders found matching the selected filters.
                                @else
                                    No orders found.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($orders->hasPages())
                <div style="padding:1rem; border-top:1px solid #111827; display:flex; justify-content:space-between; align-items:center;">
                    <div style="font-size:0.85rem; color:#9ca3af;">
                        Showing <strong>{{ $orders->firstItem() }}</strong> to <strong>{{ $orders->lastItem() }}</strong> of <strong>{{ $orders->total() }}</strong> orders
                    </div>
                    <div style="display:flex; gap:0.5rem; align-items:center;">
                        @if($orders->onFirstPage())
                            <span style="padding:0.5rem 0.75rem; background:#1f2937; color:#6b7280; border-radius:0.25rem; font-size:0.85rem; cursor:not-allowed;">
                                ← Previous
                            </span>
                        @else
                            <a href="{{ $orders->appends(request()->query())->previousPageUrl() }}" style="padding:0.5rem 0.75rem; background:#374151; color:#e5e7eb; border-radius:0.25rem; font-size:0.85rem; text-decoration:none; cursor:pointer; hover:background:#4b5563;">
                                ← Previous
                            </a>
                        @endif
                        
                        <div style="display:flex; gap:0.25rem;">
                            @foreach($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                                @if($page == $orders->currentPage())
                                    <span style="padding:0.5rem 0.75rem; background:#3b82f6; color:#fff; border-radius:0.25rem; font-size:0.85rem; font-weight:600;">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}{{ strpos($url, '?') ? '&' : '?' }}{{ http_build_query(request()->query()) }}" style="padding:0.5rem 0.75rem; background:#374151; color:#e5e7eb; border-radius:0.25rem; font-size:0.85rem; text-decoration:none; cursor:pointer;">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                        
                        @if($orders->hasMorePages())
                            <a href="{{ $orders->appends(request()->query())->nextPageUrl() }}" style="padding:0.5rem 0.75rem; background:#374151; color:#e5e7eb; border-radius:0.25rem; font-size:0.85rem; text-decoration:none; cursor:pointer;">
                                Next →
                            </a>
                        @else
                            <span style="padding:0.5rem 0.75rem; background:#1f2937; color:#6b7280; border-radius:0.25rem; font-size:0.85rem; cursor:not-allowed;">
                                Next →
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
