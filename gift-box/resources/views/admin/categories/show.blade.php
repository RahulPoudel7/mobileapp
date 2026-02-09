@extends('layouts.app')

@section('title', 'Category Details')

@section('content')
<div class="cards" style="display:block;">
    
    <!-- Back Button -->
    <div style="margin-bottom:1rem;">
        <a href="{{ route('admin.categories.index') }}" style="display:inline-block; padding:0.5rem 1rem; background-color:#3b82f6; color:white; text-decoration:none; border-radius:0.5rem; font-size:0.9rem;">
            ← Back to Categories
        </a>
    </div>

    <!-- Page Header -->
    <div class="card" style="margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <div class="card-title">📂 {{ $category->name }}</div>
            <p class="card-text">Category Details</p>
        </div>
        <a href="{{ route('admin.categories.edit', $category) }}" style="padding:0.5rem 1rem; background:#10b981; color:#fff; text-decoration:none; border-radius:0.5rem; font-weight:600;">
            ✏️ Edit Category
        </a>
    </div>

    <!-- Category Information -->
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-title">📋 Category Information</div>
        
        <div style="display:grid; gap:1rem; margin-top:1rem;">
            <div style="display:grid; grid-template-columns:150px 1fr; gap:1rem;">
                <strong style="color:#9ca3af;">ID:</strong>
                <span style="color:#e5e7eb;">CAT-{{ str_pad($category->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            
            <div style="display:grid; grid-template-columns:150px 1fr; gap:1rem;">
                <strong style="color:#9ca3af;">Name:</strong>
                <span style="color:#e5e7eb; font-weight:600;">{{ $category->name }}</span>
            </div>
            
            <div style="display:grid; grid-template-columns:150px 1fr; gap:1rem;">
                <strong style="color:#9ca3af;">Slug:</strong>
                <code style="background:#1f2937; padding:0.25rem 0.5rem; border-radius:0.25rem; color:#60a5fa;">{{ $category->slug }}</code>
            </div>
            
            <div style="display:grid; grid-template-columns:150px 1fr; gap:1rem;">
                <strong style="color:#9ca3af;">Description:</strong>
                <span style="color:#e5e7eb;">{{ $category->description ?? 'No description provided' }}</span>
            </div>
            
            <div style="display:grid; grid-template-columns:150px 1fr; gap:1rem;">
                <strong style="color:#9ca3af;">Created:</strong>
                <span style="color:#e5e7eb;">{{ $category->created_at->format('M d, Y h:i A') }}</span>
            </div>
            
            <div style="display:grid; grid-template-columns:150px 1fr; gap:1rem;">
                <strong style="color:#9ca3af;">Last Updated:</strong>
                <span style="color:#e5e7eb;">{{ $category->updated_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>
    </div>

    <!-- Category Image -->
    @if($category->image_url)
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-title">🖼️ Category Image</div>
        <div style="margin-top:1rem; text-align:center;">
            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" style="max-width:100%; max-height:400px; border-radius:0.5rem; border:1px solid #374151;">
        </div>
        <p style="margin-top:0.5rem; font-size:0.85rem; color:#9ca3af;">
            <strong>URL:</strong> <a href="{{ $category->image_url }}" target="_blank" style="color:#60a5fa; text-decoration:none;">{{ $category->image_url }}</a>
        </p>
    </div>
    @endif

    <!-- Gifts in this Category -->
    <div class="card">
        <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
            <span>🎁 Gifts in this Category ({{ $category->gifts->count() }})</span>
            @if($category->gifts->count() > 0)
                <span style="padding:0.25rem 0.75rem; background:rgba(52, 211, 153, 0.15); color:#34d399; border-radius:999px; font-size:0.85rem;">
                    {{ $category->gifts->count() }} {{ $category->gifts->count() == 1 ? 'Gift' : 'Gifts' }}
                </span>
            @endif
        </div>

        @if($category->gifts->count() > 0)
            <table style="width:100%; border-collapse:collapse; font-size:0.9rem; margin-top:1rem;">
                <thead>
                    <tr style="border-bottom:1px solid #374151;">
                        <th style="text-align:left; padding:0.75rem; color:#9ca3af; font-weight:500;">Image</th>
                        <th style="text-align:left; padding:0.75rem; color:#9ca3af; font-weight:500;">Name</th>
                        <th style="text-align:left; padding:0.75rem; color:#9ca3af; font-weight:500;">Price</th>
                        <th style="text-align:left; padding:0.75rem; color:#9ca3af; font-weight:500;">Status</th>
                        <th style="text-align:left; padding:0.75rem; color:#9ca3af; font-weight:500;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($category->gifts as $gift)
                    <tr style="border-bottom:1px solid #1f2937;">
                        <td style="padding:0.75rem;">
                            @if($gift->image_url)
                                <img src="{{ $gift->image_url }}" alt="{{ $gift->name }}" style="width:50px; height:50px; object-fit:cover; border-radius:0.5rem; border:1px solid #374151;">
                            @else
                                <div style="width:50px; height:50px; background:#1f2937; border-radius:0.5rem; display:flex; align-items:center; justify-content:center; color:#6b7280;">
                                    🎁
                                </div>
                            @endif
                        </td>
                        <td style="padding:0.75rem; color:#f9fafb;">{{ $gift->name }}</td>
                        <td style="padding:0.75rem; color:#34d399; font-weight:600;">₹{{ number_format($gift->price, 2) }}</td>
                        <td style="padding:0.75rem;">
                            @if($gift->is_active)
                                <span style="padding:0.25rem 0.5rem; background:rgba(52, 211, 153, 0.15); color:#34d399; border-radius:0.25rem; font-size:0.75rem;">
                                    ✓ Active
                                </span>
                            @else
                                <span style="padding:0.25rem 0.5rem; background:rgba(248, 113, 113, 0.15); color:#f87171; border-radius:0.25rem; font-size:0.75rem;">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td style="padding:0.75rem;">
                            <a href="{{ route('admin.gifts.show', $gift) }}" class="card-link" style="margin-right:0.5rem;">View</a>
                            <a href="{{ route('admin.gifts.edit', $gift) }}" class="card-link">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding:2rem; text-align:center; color:#6b7280;">
                <p style="font-size:1.5rem; margin-bottom:0.5rem;">📦</p>
                <p>No gifts in this category yet.</p>
                <a href="{{ route('admin.gifts.create') }}" style="display:inline-block; margin-top:1rem; padding:0.5rem 1rem; background:#3b82f6; color:#fff; text-decoration:none; border-radius:0.5rem;">
                    + Add Gift
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
