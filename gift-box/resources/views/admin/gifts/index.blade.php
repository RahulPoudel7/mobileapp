@extends('layouts.app')

@section('title', 'Gifts')

@section('content')
    <div class="cards" style="display:block;">
        <div class="card" style="margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div class="card-title">Gifts</div>
                <p class="card-text">Manage all gifts in the system.</p>
            </div>
            <a href="{{ route('admin.gifts.create') }}" class="card-link" style="padding:0.5rem 0.9rem; background:#4f46e5; border-radius:0.5rem;">
                + New gift
            </a>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                <thead>
                    <tr style="background:#020617;">
                        <th style="text-align:left; padding:0.75rem 1rem;">Name</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Category</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Price</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Status</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gifts as $gift)
                        <tr>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                {{ $gift->name }}
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                {{ optional($gift->category)->name ?? '—' }}
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                {{ $gift->price ?? 'N/A' }}
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                <span class="badge">
                                    {{ $gift->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                <a href="{{ route('admin.gifts.edit', $gift) }}" class="card-link">Edit</a>

                                <form action="{{ route('admin.gifts.destroy', $gift) }}" method="POST" style="display:inline-block; margin-left:0.5rem;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none; border:none; color:#fca5a5; cursor:pointer; font-size:0.85rem;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:0.75rem 1rem;">No gifts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
