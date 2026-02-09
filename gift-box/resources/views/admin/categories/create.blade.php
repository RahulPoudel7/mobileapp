@extends('layouts.app')

@section('title', 'Add Category')

@section('content')
<div class="cards" style="display:block;">
    <!-- Back Button -->
    <div style="margin-bottom:1rem;">
        <a href="{{ route('admin.categories.index') }}" style="display:inline-block; padding:0.5rem 1rem; background-color:#3b82f6; color:white; text-decoration:none; border-radius:0.5rem; font-size:0.9rem;">
            ← Back to Categories
        </a>
    </div>

    <!-- Page Header -->
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-title">➕ Add New Category</div>
        <p class="card-text">Create a new category for organizing gifts</p>
    </div>

    <!-- Create Form -->
    <div class="card">
        @if ($errors->any())
            <div style="background:rgba(248, 113, 113, 0.1); border:1px solid #f87171; border-radius:0.5rem; padding:1rem; margin-bottom:1rem;">
                <strong style="color:#f87171;">⚠️ Please fix the following errors:</strong>
                <ul style="margin:0.5rem 0 0 1.5rem; color:#f87171;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf

            <!-- Category Name -->
            <div style="margin-bottom:1.5rem;">
                <label for="name" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#f9fafb;">
                    Category Name <span style="color:#f87171;">*</span>
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name') }}" 
                    required
                    style="width:100%; padding:0.75rem; border-radius:0.5rem; background:#111827; border:1px solid #374151; color:#e5e7eb; font-size:0.95rem;"
                    placeholder="e.g., Birthday Gifts, Anniversary Gifts"
                >
                @error('name')
                    <div style="color:#f87171; font-size:0.85rem; margin-top:0.25rem;">{{ $message }}</div>
                @enderror
                <small style="color:#9ca3af; font-size:0.85rem; display:block; margin-top:0.25rem;">
                    A unique slug will be generated automatically.
                </small>
            </div>

            <!-- Description -->
            <div style="margin-bottom:1.5rem;">
                <label for="description" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#f9fafb;">
                    Description
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="4"
                    style="width:100%; padding:0.75rem; border-radius:0.5rem; background:#111827; border:1px solid #374151; color:#e5e7eb; font-size:0.95rem; resize:vertical;"
                    placeholder="Enter a brief description of this category"
                >{{ old('description') }}</textarea>
                @error('description')
                    <div style="color:#f87171; font-size:0.85rem; margin-top:0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Image URL -->
            <div style="margin-bottom:1.5rem;">
                <label for="image_url" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#f9fafb;">
                    Image URL
                </label>
                <input 
                    type="url" 
                    id="image_url" 
                    name="image_url" 
                    value="{{ old('image_url') }}"
                    style="width:100%; padding:0.75rem; border-radius:0.5rem; background:#111827; border:1px solid #374151; color:#e5e7eb; font-size:0.95rem;"
                    placeholder="https://example.com/image.jpg"
                >
                @error('image_url')
                    <div style="color:#f87171; font-size:0.85rem; margin-top:0.25rem;">{{ $message }}</div>
                @enderror

                @if(old('image_url'))
                    <div style="margin-top:1rem; padding:1rem; background:#111827; border-radius:0.5rem; border:1px solid #374151;">
                        <p style="font-size:0.85rem; color:#9ca3af; margin-bottom:0.5rem;">Preview:</p>
                        <img src="{{ old('image_url') }}" alt="Preview" style="max-width:200px; max-height:200px; border-radius:0.5rem; border:1px solid #374151;">
                    </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div style="display:flex; gap:1rem; align-items:center;">
                <button 
                    type="submit" 
                    style="padding:0.75rem 2rem; background:linear-gradient(135deg, #10b981, #059669); color:#fff; border:none; border-radius:0.5rem; cursor:pointer; font-size:0.95rem; font-weight:600; box-shadow:0 4px 6px rgba(0,0,0,0.3);"
                >
                    ✅ Create Category
                </button>
                
                <a 
                    href="{{ route('admin.categories.index') }}" 
                    style="padding:0.75rem 1.5rem; background:#374151; color:#e5e7eb; border-radius:0.5rem; text-decoration:none; display:inline-block; text-align:center; font-size:0.95rem;"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Tips Card -->
    <div class="card" style="margin-top:1.5rem; border-color:#1d4ed8;">
        <div class="card-title" style="color:#60a5fa;">💡 Tips for Better Categories</div>
        <ul style="margin:0.75rem 0 0 1.25rem; color:#9ca3af; line-height:1.6;">
            <li>Use short and clear names (e.g., "Birthday", "Anniversary").</li>
            <li>Add a descriptive image to improve visual appeal.</li>
            <li>Keep descriptions concise and helpful for admins.</li>
        </ul>
    </div>
</div>
@endsection
