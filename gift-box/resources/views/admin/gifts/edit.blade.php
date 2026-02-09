@extends('layouts.app')

@section('title', 'Edit gift')

@section('content')
<div class="cards" style="display:block;">
    <div class="card" style="max-width:640px;">
        <div class="card-title" style="margin-bottom:0.25rem;">
            Edit gift
        </div>
        <p class="card-text" style="margin-bottom:1.5rem;">
            Update this gift’s details and availability in the app.
        </p>

        <form method="POST" action="{{ route('admin.gifts.update', $gift) }}" style="display:flex; flex-direction:column; gap:1rem;">
            @csrf
            @method('PUT')

            <div class="field">
                <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">Name</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $gift->name) }}"
                    required
                    style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem; border:1px solid #374151; background:#020617; color:#e5e7eb;"
                >
                @error('name')
                    <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">Description</label>
                <textarea
                    name="description"
                    rows="3"
                    style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem; border:1px solid #374151; background:#020617; color:#e5e7eb; resize:vertical;"
                >{{ old('description', $gift->description) }}</textarea>
                @error('description')
                    <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div class="field" style="display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem;">
                <div>
                    <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">Price (NPR)</label>
                    <input
                        type="number"
                        step="0.01"
                        name="price"
                        value="{{ old('price', $gift->price) }}"
                        required
                        style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem; border:1px solid #374151; background:#020617; color:#e5e7eb;"
                    >
                    @error('price')
                        <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">Status</label>
                    <select
                        name="is_active"
                        style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem; border:1px solid #374151; background:#020617; color:#e5e7eb;"
                    >
                        <option value="1" @selected(old('is_active', $gift->is_active) == 1)>Active</option>
                        <option value="0" @selected(old('is_active', $gift->is_active) == 0)>Inactive</option>
                    </select>
                    @error('is_active')
                        <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="field">
                <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">Image URL</label>
                <input
                    type="url"
                    name="image_url"
                    value="{{ old('image_url', $gift->image_url) }}"
                    placeholder="https://example.com/image.jpg"
                    style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem; border:1px solid #374151; background:#020617; color:#e5e7eb;"
                >
                @error('image_url')
                    <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label style="display:block; font-size:0.85rem; color:#9ca3af; margin-bottom:0.25rem;">Category</label>
                <select
                    name="category_id"
                    style="width:100%; padding:0.55rem 0.7rem; border-radius:0.5rem; border:1px solid #374151; background:#020617; color:#e5e7eb;"
                >
                    <option value="">None</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            @selected(old('category_id', $gift->category_id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p style="color:#fca5a5; font-size:0.8rem; margin-top:0.25rem;">{{ $message }}</p>
                @enderror
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.5rem;">
                <a href="{{ route('admin.gifts.index') }}" style="font-size:0.85rem; color:#9ca3af; text-decoration:none;">
                    ← Back to gifts
                </a>

                <button type="submit"
                    style="padding:0.6rem 1.1rem; border-radius:0.6rem; border:none; background:#6366f1; color:#f9fafb; font-weight:500; cursor:pointer;">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
