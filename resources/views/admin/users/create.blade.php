@extends('layouts.app')

@section('title', 'Create New User - Bakery Management System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/users.css') }}">
@endpush

@section('content')
<div class="card-header-flex" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: #5D4037;">Create New User Account</h1>
        <p style="font-size: 0.85rem; color: #6b7280;">Add a new internal staff member and assign their role permission level.</p>
    </div>
    <a href="{{ route('admin.users.index') }}" style="text-decoration: none; color: #52525b; font-weight: 700; font-size: 0.85rem;">
        &larr; Back to Users List
    </a>
</div>

<div class="bakery-card" style="max-width: 600px; margin: 0 auto; padding: 2rem; background: #fff; border-radius: 12px; border: 1px solid #e4e4e7; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    @if ($errors->any())
        <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 0.85rem 1.15rem; border-radius: 8px; margin-bottom: 1.25rem;">
            <ul style="margin-left: 1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #27272a; margin-bottom: 0.4rem;">Full Name *</label>
            <input type="text" name="name" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #d4d4d8;" value="{{ old('name') }}" placeholder="e.g. Sarah Connor" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #27272a; margin-bottom: 0.4rem;">Username *</label>
                <input type="text" name="username" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #d4d4d8;" value="{{ old('username') }}" placeholder="e.g. sarahc" required>
            </div>
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #27272a; margin-bottom: 0.4rem;">Phone Number</label>
                <input type="text" name="phone" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #d4d4d8;" value="{{ old('phone') }}" placeholder="e.g. 012 345 678">
            </div>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #27272a; margin-bottom: 0.4rem;">Email Address *</label>
            <input type="email" name="email" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #d4d4d8;" value="{{ old('email') }}" placeholder="e.g. sarah@bakery.com" required>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #27272a; margin-bottom: 0.4rem;">Password * (min 6 characters)</label>
            <input type="password" name="password" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #d4d4d8;" placeholder="Minimum 6 characters" required minlength="6">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #27272a; margin-bottom: 0.4rem;">Assigned Role *</label>
                <select name="role" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #d4d4d8; background: #fff;" required>
                    <option value="cashier" {{ old('role') === 'cashier' ? 'selected' : '' }}>Cashier</option>
                    <option value="baker" {{ old('role') === 'baker' ? 'selected' : '' }}>Baker</option>
                    <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #27272a; margin-bottom: 0.4rem;">Status *</label>
                <select name="status" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #d4d4d8; background: #fff;" required>
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 1.5rem; background: rgba(86, 48, 32, 0.05); padding: 0.75rem; border-radius: 8px; border: 1px dashed #d4d4d8;">
            <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; font-size: 0.85rem; font-weight: 700; color: #27272a;">
                <input type="checkbox" name="must_change_password" value="1" {{ old('must_change_password', '1') ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #563020;">
                <span>Force password change on next login</span>
            </label>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <a href="{{ route('admin.users.index') }}" style="padding: 0.6rem 1.2rem; border-radius: 9999px; border: 1px solid #d4d4d8; text-decoration: none; color: #52525b; font-weight: 700;">Cancel</a>
            <button type="submit" style="padding: 0.6rem 1.5rem; background: #563020; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer;">Save Account</button>
        </div>
    </form>
</div>
@endsection
