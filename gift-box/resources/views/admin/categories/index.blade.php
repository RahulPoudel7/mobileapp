@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="cards" style="display:block;">
    <div class="card" style="margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <div class="card-title">📂 Categories</div>
            <p class="card-text">Manage categories and their details.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="card-link"
           style="padding:0.5rem 0.9rem; background:#4f46e5; border-radius:0.5rem;">
            + New category
        </a>
    </div>

    @if (session('success'))
        <div style="background:rgba(52, 211, 153, 0.1); border:1px solid #34d399; border-radius:0.5rem; padding:1rem; margin-bottom:1rem; color:#34d399;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div style="background:rgba(248, 113, 113, 0.1); border:1px solid #f87171; border-radius:0.5rem; padding:1rem; margin-bottom:1rem; color:#f87171;">
            ⚠️ {{ session('error') }}
        </div>
    @endif
    
    <div class="card" style="padding:0; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
            <thead>
                <tr style="background:#020617;">
                    <th style="text-align:left; padding:0.75rem 1rem;">ID</th>
                    <th style="text-align:left; padding:0.75rem 1rem;">Image</th>
                    <th style="text-align:left; padding:0.75rem 1rem;">Name</th>
                    <th style="text-align:left; padding:0.75rem 1rem;">Slug</th>
                    <th style="text-align:left; padding:0.75rem 1rem;">Description</th>
                    <th style="text-align:center; padding:0.75rem 1rem;">Gifts</th>
                    <th style="text-align:left; padding:0.75rem 1rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                            <strong style="color:#60a5fa;">CAT-{{ str_pad($category->id, 5, '0', STR_PAD_LEFT) }}</strong>
                        </td>
                        <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                            @if($category->image_url)
                                <img src="{{ $category->image_url }}" alt="{{ $category->name }}" style="width:50px; height:50px; object-fit:cover; border-radius:0.5rem; border:1px solid #374151;">
                            @else
                                <div style="width:50px; height:50px; background:#1f2937; border-radius:0.5rem; display:flex; align-items:center; justify-content:center; color:#6b7280;">
                                    📂
                                </div>
                            @endif
                        </td>
                        <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                            <strong style="color:#f9fafb;">{{ $category->name }}</strong>
                        </td>
                        <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                            <code style="background:#1f2937; padding:0.25rem 0.5rem; border-radius:0.25rem; color:#9ca3af; font-size:0.85rem;">{{ $category->slug }}</code>
                        </td>
                        <td style="padding:0.6rem 1rem; border-top:1px solid #111827; color:#9ca3af;">
                            {{ Str::limit($category->description, 50) ?? 'N/A' }}
                        </td>
                        <td style="padding:0.6rem 1rem; border-top:1px solid #111827; text-align:center;">
                            <span style="padding:0.25rem 0.5rem; background:rgba(167, 139, 250, 0.15); color:#a78bfa; border-radius:0.25rem; font-size:0.85rem; font-weight:600;">
                                {{ $category->gifts_count ?? 0 }}
                            </span>
                        </td>
                        <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                            <a href="{{ route('admin.categories.show', $category) }}" class="card-link" style="margin-right:0.5rem;">
                                View
                            </a>
                            <a href="{{ route('admin.categories.edit', $category) }}" class="card-link" style="margin-right:0.5rem;">
                                Edit
                            </a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:none; border:none; color:#f87171; cursor:pointer; text-decoration:underline; font-size:0.9rem; padding:0;" @if($category->gifts_count > 0) disabled title="Cannot delete category with gifts" @endif>
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:2rem 1rem; text-align:center; color:#6b7280;">
                            No categories found. <a href="{{ route('admin.categories.create') }}" class="card-link">Create one now</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
