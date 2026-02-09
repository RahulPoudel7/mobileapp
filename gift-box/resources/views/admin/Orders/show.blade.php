@extends('layouts.app')

@section('title', 'Order '.$order->id)

@section('content')
    <div class="cards" style="display:block;">
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
                Placed on {{ $order->created_at->format('Y-m-d H:i') }} · Status:
                <span class="badge">
                    {{ ucfirst(str_replace('_',' ',$order->status)) }}
                </span>
            </p>
        </div>

        <!-- Customer & Delivery -->
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-title">Customer & Delivery</div>
            <p class="card-text">
                <strong>Name:</strong> {{ $order->recipient_name }}<br>
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
                {{ $order->has_personal_note ? 'Yes' : 'No' }}
                @if($order->has_personal_note && $order->personal_note_text)
                    <br><span style="margin-left:1rem; padding:0.5rem; background:#f3f4f6; border-radius:0.25rem; display:inline-block; margin-top:0.5rem; font-style:italic; color:#374151;">{{ $order->personal_note_text }}</span>
                @endif
                <br>
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

        <!-- Payment & Status -->
        <div class="card">
            <div class="card-title">Payment & Status</div>
            <p class="card-text">
                <strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}<br>
                <strong>Order Status:</strong> <span class="badge">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span>
            </p>
        </div>
    </div>
@endsection
