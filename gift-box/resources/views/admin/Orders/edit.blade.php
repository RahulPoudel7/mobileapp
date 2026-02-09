@extends('layouts.app')

@section('title', 'Edit Order')

@section('content')
<div class="cards" style="display:block;">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Back Button -->
    <div style="margin-bottom:1rem;">
        <a href="{{ route('admin.orders.index') }}?{{ http_build_query(request()->query()) }}" style="display:inline-block; padding:0.5rem 1rem; background-color:#007bff; color:white; text-decoration:none; border-radius:0.25rem; font-size:0.9rem;">
            ← Back to Orders
        </a>
    </div>

    <!-- Order Header -->
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-title">
            Order #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
        </div>
        <p class="card-text">
            Placed on {{ $order->created_at->format('Y-m-d H:i') }}
        </p>
    </div>

    <!-- Customer & Delivery -->
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-title">Customer & Delivery</div>
        <p class="card-text">
            <strong>Customer Name:</strong> {{ $order->recipient_name }}<br>
            <strong>User:</strong> {{ $order->user->name ?? 'N/A' }}<br>
            <strong>Phone:</strong> {{ $order->recipient_phone }}<br>
            <strong>Address:</strong> {{ $order->delivery_address }}<br>
            <strong>Distance:</strong> {{ number_format($order->distance_km, 2) }} km<br>
            <strong>Delivery Date:</strong> 
            @if($order->delivery_date)
                {{ \Carbon\Carbon::parse($order->delivery_date)->format('M d, Y') }}
            @else
                Not selected
            @endif
        </p>
        <p class="card-text" style="margin-top:0.75rem;">
            <strong>Personal note:</strong>
            {{ $order->has_personal_note ? 'Yes' : 'No' }}<br>
            <strong>Gift wrapping:</strong>
            {{ $order->has_gift_wrapping ? 'Yes' : 'No' }}
        </p>
    </div>

    <!-- Gifts -->
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-title">Gifts:</div>
        <p class="card-text">
            @if($order->items && $order->items->count())
                @foreach($order->items as $item)
                    <div>
                        <strong>{{ optional($item->gift)->name ?? '—' }}</strong> <small>x{{ $item->quantity }}</small>
                        <span style="float:right;">Rs. {{ number_format($item->price * $item->quantity, 2) }}</span>
                        <br>
                    </div>
                @endforeach
            @else
                —
            @endif
        </p>
    </div>

    <!-- Pricing Breakdown -->
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-title">Pricing Breakdown</div>
        <p class="card-text">
            <strong>Subtotal:</strong> <span style="float:right;">Rs. {{ number_format($order->subtotal, 2) }}</span><br>
            <strong>Delivery Charge:</strong> <span style="float:right;">Rs. {{ number_format($order->delivery_charge, 2) }}</span><br>
            @if($order->personal_note_fee > 0)
                <strong>Personal Note Fee:</strong> <span style="float:right;">Rs. {{ number_format($order->personal_note_fee, 2) }}</span><br>
            @endif
            @if($order->gift_wrapping_fee > 0)
                <strong>Gift Wrapping Fee:</strong> <span style="float:right;">Rs. {{ number_format($order->gift_wrapping_fee, 2) }}</span><br>
            @endif
            <hr style="margin:0.5rem 0;">
            <strong style="font-size:1.1rem;">Total Amount:</strong> <span style="float:right; font-size:1.1rem; font-weight:bold;">Rs. {{ number_format($order->total_amount, 2) }}</span>
        </p>
    </div>

    <!-- Payment & Status Update -->
    <div class="card">
        <div class="card-title">Payment & Status</div>
        <p class="card-text">
            <strong>Payment Method:</strong> 
            @if($order->payment_method == 'esewa')
                <span style="padding:0.25rem 0.5rem; background:rgba(96, 165, 250, 0.15); color:#60a5fa; border-radius:0.25rem; font-size:0.85rem;">eSewa</span>
            @elseif($order->payment_method == 'cod')
                <span style="padding:0.25rem 0.5rem; background:rgba(251, 191, 36, 0.15); color:#fbbf24; border-radius:0.25rem; font-size:0.85rem;">Cash on Delivery</span>
            @else
                {{ ucfirst($order->payment_method) }}
            @endif
            <br>
            <strong>Transaction UUID:</strong> {{ $order->transaction_uuid ?? 'N/A' }}<br>
        </p>

        <form method="POST" action="{{ route('admin.orders.update', $order) }}" style="margin-top:1rem;">
            @csrf
            @method('PUT')

            <!-- Hidden input to preserve payment method filter -->
            @if(request('payment_method'))
                <input type="hidden" name="payment_method" value="{{ request('payment_method') }}">
            @endif

            <div style="margin-bottom:1rem;">
                <label for="status" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#f9fafb;">Order Status:</label>
                <select name="status" id="status" style="width:100%; padding:0.6rem; border-radius:0.5rem; background:#111827; border:1px solid #374151; color:#e5e7eb; font-size:0.95rem;">
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($order->status === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <div style="color:#f87171; font-size:0.85rem; margin-top:0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:1.5rem;">
                <label for="payment_status" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#f9fafb;">Payment Status:</label>
                <select name="payment_status" id="payment_status" style="width:100%; padding:0.6rem; border-radius:0.5rem; background:#111827; border:1px solid #374151; color:#e5e7eb; font-size:0.95rem;" {{ !$canEditPaymentStatus ? 'disabled' : '' }}>
                    @foreach ($paymentStatuses as $paymentStatus)
                        <option value="{{ $paymentStatus }}" @selected($order->payment_status === $paymentStatus)>
                            {{ ucfirst($paymentStatus) }}
                            @if($paymentStatus == 'paid')
                                ✓
                            @endif
                        </option>
                    @endforeach
                </select>
                
                @if(!$canEditPaymentStatus)
                    <input type="hidden" name="payment_status" value="{{ $order->payment_status }}">
                @endif
                
                @error('payment_status')
                    <div style="color:#f87171; font-size:0.85rem; margin-top:0.25rem;">{{ $message }}</div>
                @enderror
                
                @if($order->payment_method == 'esewa')
                    <p style="margin-top:0.5rem; font-size:0.85rem; color:#60a5fa; background:rgba(96, 165, 250, 0.1); padding:0.5rem; border-radius:0.25rem;">
                        🔒 <strong>eSewa Payment:</strong> Payment status cannot be modified as it's controlled by the eSewa gateway.
                    </p>
                @elseif($order->payment_method == 'cod')
                    <p style="margin-top:0.5rem; font-size:0.85rem; color:#fbbf24; background:rgba(251, 191, 36, 0.1); padding:0.5rem; border-radius:0.25rem;">
                        💡 <strong>Cash on Delivery:</strong> Mark as "Paid" after receiving payment on delivery.
                    </p>
                @endif
            </div>

            <button type="submit" style="padding:0.6rem 1.5rem; background:linear-gradient(135deg, #10b981, #059669); color:#fff; border:none; border-radius:0.5rem; cursor:pointer; font-size:0.95rem; font-weight:600; box-shadow:0 4px 6px rgba(0,0,0,0.3);">
                💾 Update Order
            </button>
        </form>
    </div>
</div>
@endsection
