@extends('layouts.app')

@section('title', 'Create user')

@section('content')
<div class="cards" style="display:block;">
    <div class="card" style="max-width:640px;">
        <div class="card-title" style="margin-bottom:0.25rem;">
            New user
        </div>
        <p class="card-text" style="margin-bottom:1.5rem;">
            Create a new account and assign a role.
        </p>

        @if ($errors->any())
            <div class="error" style="margin-bottom:1rem;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}"
              style="display:flex; flex-direction:column; gap:1rem;">
            @csrf

            <div class="field">
                <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">
                    Name
                </label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
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
                    value="{{ old('email') }}"
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
                    value="{{ old('phone') }}"
                    style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem;
                           border:1px solid #374151; background:#020617; color:#e5e7eb;"
                >
                @error('phone')
                    <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div class="field" style="display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem;">
                <div>
                    <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">
                        Password
                    </label>
                    <input
                        type="password"
                        name="password"
                        required
                        style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem;
                               border:1px solid #374151; background:#020617; color:#e5e7eb;"
                    >
                    @error('password')
                        <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">
                        Role
                    </label>
                    <select
                        name="is_admin"
                        style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem;
                               border:1px solid #374151; background:#020617; color:#e5e7eb;"
                    >
                        <option value="0" @selected(old('is_admin') == '0')>User</option>
                        <option value="1" @selected(old('is_admin') == '1')>Admin</option>
                    </select>
                    @error('is_admin')
                        <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.5rem;">
                <a href="{{ route('admin.users.index') }}"
                   style="font-size:0.85rem; color:#9ca3af; text-decoration:none;">
                    ← Back to users
                </a>

                <button type="submit"
                        style="padding:0.6rem 1.1rem; border-radius:0.6rem; border:none;
                               background:#6366f1; color:#f9fafb; font-weight:500; cursor:pointer;">
                    Save user
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
