@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="cards" style="display:block;">
        <div class="card" style="margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div class="card-title">Users</div>
                <p class="card-text">Manage users and their roles.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="card-link"
               style="padding:0.5rem 0.9rem; background:#4f46e5; border-radius:0.5rem;">
                + New user
            </a>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                <thead>
                    <tr style="background:#020617;">
                        <th style="text-align:left; padding:0.75rem 1rem;">Name</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Email</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Phone</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Default Address</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Role</th>
                        <th style="text-align:left; padding:0.75rem 1rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                {{ $user->name }}
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                {{ $user->email }}
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                {{ $user->phone ?? '—' }}
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                @if($user->default_delivery_address)
                                    <span style="padding:0.25rem 0.5rem; background:rgba(96, 165, 250, 0.15); color:#60a5fa; border-radius:0.25rem; font-size:0.75rem; cursor:help;" title="{{ $user->default_delivery_address }}">
                                        📍 {{ Str::limit($user->default_delivery_address, 30) }}
                                    </span>
                                @else
                                    <span style="color:#6b7280;">—</span>
                                @endif
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                <span class="badge">
                                    {{ $user->is_admin ? 'Admin' : 'User' }}
                                </span>
                            </td>
                            <td style="padding:0.6rem 1rem; border-top:1px solid #111827;">
                                <a href="{{ route('admin.users.edit', $user) }}" class="card-link">Edit</a>

                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                      style="display:inline-block; margin-left:0.5rem;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            style="background:none; border:none; color:#fca5a5; cursor:pointer; font-size:0.85rem;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:0.75rem 1rem;">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
