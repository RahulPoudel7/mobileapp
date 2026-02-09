@extends('layouts.app')

@section('title', 'Edit user')

@section('content')
<div class="cards" style="display:block;">
    <div class="card" style="max-width:640px;">
        <div class="card-title" style="margin-bottom:0.25rem;">
            Edit user
        </div>
        <p class="card-text" style="margin-bottom:1.5rem;">
            Update this user’s profile, password, and role.
        </p>

        @if ($errors->any())
            <div class="error" style="margin-bottom:1rem;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}"
              style="display:flex; flex-direction:column; gap:1rem;">
            @csrf
            @method('PUT')

            <div class="field">
                <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">
                    Name
                </label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                    style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem;
                           border:1px solid #374151; background:#020617; color:#e5e7eb;"
                >
                @error('name')
                    <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">
                    Email
                </label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                    style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem;
                           border:1px solid #374151; background:#020617; color:#e5e7eb;"
                >
                @error('email')
                    <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">
                    Phone
                </label>
                <input
                    type="text"
                    name="phone"
                    value="{{ old('phone', $user->phone) }}"
                    style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem;
                           border:1px solid #374151; background:#020617; color:#e5e7eb;"
                >
                @error('phone')
                    <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">
                    New password
                    <span style="color:#6b7280; font-size:0.78rem;">(leave blank to keep current)</span>
                </label>
                <input
                    type="password"
                    name="password"
                    style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem;
                           border:1px solid #374151; background:#020617; color:#e5e7eb;"
                >
                @error('password')
                    <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">
                    Default Delivery Address
                </label>
                <div style="padding:0.75rem; background:#111827; border-radius:0.5rem; border:1px solid #374151;">
                    @if($user->default_delivery_address)
                        <p style="color:#e5e7eb; margin:0; font-size:0.9rem;">
                            📍 {{ $user->default_delivery_address }}
                        </p>
                        <p style="color:#9ca3af; margin:0.25rem 0 0 0; font-size:0.75rem;">
                            Lat: {{ $user->default_delivery_lat }}, Lng: {{ $user->default_delivery_lng }}
                        </p>
                    @else
                        <p style="color:#6b7280; margin:0; font-size:0.9rem;">No default address set</p>
                    @endif
                </div>
            </div>

            <div class="field">
                <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">
                    Role
                </label>
                <select
                    name="is_admin"
                    style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem;
                           border:1px solid #374151; background:#020617; color:#e5e7eb;"
                >
                    <option value="0" @selected(old('is_admin', $user->is_admin) == 0)>User</option>
                    <option value="1" @selected(old('is_admin', $user->is_admin) == 1)>Admin</option>
                </select>
                @error('is_admin')
                    <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.5rem;">
                <a href="{{ route('admin.users.index') }}"
                   style="font-size:0.85rem; color:#9ca3af; text-decoration:none;">
                    ← Back to users
                </a>

                <button type="submit"
                        style="padding:0.6rem 1.1rem; border-radius:0.6rem; border:none;
                               background:#6366f1; color:#f9fafb; font-weight:500; cursor:pointer;">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
