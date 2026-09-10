@extends('layouts.app')

@section('title', 'Create New User - Bakery Management System')

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
            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #27272a; margin-bottom: 0.4rem;">Full Name</label>
            <input type="text" name="name" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #d4d4d8;" value="{{ old('name') }}" placeholder="e.g. Sarah Connor" required>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #27272a; margin-bottom: 0.4rem;">Username</label>
            <input type="text" name="username" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #d4d4d8;" value="{{ old('username') }}" placeholder="e.g. sarahc" required>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #27272a; margin-bottom: 0.4rem;">Email Address</label>
            <input type="email" name="email" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #d4d4d8;" value="{{ old('email') }}" placeholder="e.g. sarah@bakery.com" required>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #27272a; margin-bottom: 0.4rem;">Password</label>
            <input type="password" name="password" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #d4d4d8;" placeholder="Minimum 6 characters" required>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #27272a; margin-bottom: 0.4rem;">Assigned Role</label>
            <select name="role" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #d4d4d8; background: #fff;" required>
                <option value="cashier" {{ old('role') === 'cashier' ? 'selected' : '' }}>Cashier (Dashboard, Products, Orders, Sales)</option>
                <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager (Operations, Products, Categories, Ingredients, Recipes, Suppliers, Production, Reports)</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Full Access & User Management)</option>
            </select>
        </div>

        <div style="margin-bottom: 1.75rem;">
            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #27272a; margin-bottom: 0.4rem;">Status</label>
            <select name="status" class="form-control" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid #d4d4d8; background: #fff;" required>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <a href="{{ route('admin.users.index') }}" style="padding: 0.6rem 1.2rem; border-radius: 9999px; border: 1px solid #d4d4d8; text-decoration: none; color: #52525b; font-weight: 700;">Cancel</a>
            <button type="submit" style="padding: 0.6rem 1.5rem; background: #5D4037; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer;">Save Account</button>
        </div>
    </form>
</div>
@endsection
