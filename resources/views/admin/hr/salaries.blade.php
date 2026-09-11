@extends('layouts.app')

@section('title', 'Salaries & Payroll - Bakery Management System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/users.css') }}">
@endpush

@section('content')
<!-- Header Bar -->
<div class="users-header-bar">
    <div class="users-title-group">
        <h2>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <span data-lang-key="salaries_management">Staff Salaries & Payroll</span>
        </h2>
        <p data-lang-key="salaries_subtitle">Manage monthly salary slips, base compensation, deductions, and payouts.</p>
    </div>

    <button type="button" class="btn-user-primary" id="btn_open_salary_modal">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span data-lang-key="generate_salary">Generate Salary Slip</span>
    </button>
</div>

<!-- Navigation Tabs -->
<div class="hr-nav-tabs">
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.users.index') }}" class="hr-nav-tab">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Staff Directory</span>
        </a>
    @endif
    <a href="{{ route('admin.hr.attendance') }}" class="hr-nav-tab">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span>Attendance</span>
    </a>
    <a href="{{ route('admin.hr.schedules') }}" class="hr-nav-tab">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span>Work Schedules</span>
    </a>
    <a href="{{ route('admin.hr.salaries') }}" class="hr-nav-tab active">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        <span>Salaries & Payroll</span>
    </a>
</div>

<!-- Flash Notifications -->
@if(session('success'))
    <div style="background: rgba(22, 163, 74, 0.12); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.3); border-radius: 12px; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div style="background: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; font-weight: 700;">
        <ul style="margin: 0; padding-left: 1.2rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- KPI Cards -->
<div class="user-stats-grid">
    <div class="user-stat-card">
        <div class="stat-icon-wrapper total">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Payroll</div>
            <div class="stat-number">${{ number_format($stats['total_payroll'], 2) }}</div>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-icon-wrapper active">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Disbursed</div>
            <div class="stat-number">${{ number_format($stats['total_paid'], 2) }}</div>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-icon-wrapper suspended">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Pending Payouts</div>
            <div class="stat-number">{{ $stats['pending_count'] }}</div>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-icon-wrapper active">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Completed Slips</div>
            <div class="stat-number">{{ $stats['paid_count'] }}</div>
        </div>
    </div>
</div>

<!-- Filters Toolbar -->
<div class="users-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.hr.salaries') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 160px;">
            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--user-muted); margin-bottom: 0.35rem;">Salary Month</label>
            <input type="month" name="period" value="{{ $period }}" class="user-search-input" style="width: 100%;">
        </div>

        <div style="flex: 1; min-width: 180px;">
            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--user-muted); margin-bottom: 0.35rem;">Staff Member</label>
            <select name="user_id" class="user-filter-select" style="width: 100%;">
                <option value="">All Staff</option>
                @foreach($staffMembers as $member)
                    <option value="{{ $member->id }}" {{ $staffId == $member->id ? 'selected' : '' }}>{{ $member->name }} ({{ ucfirst($member->role) }})</option>
                @endforeach
            </select>
        </div>

        <div style="flex: 1; min-width: 160px;">
            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--user-muted); margin-bottom: 0.35rem;">Payment Status</label>
            <select name="payment_status" class="user-filter-select" style="width: 100%;">
                <option value="">All Statuses</option>
                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn-user-primary" style="padding: 0.65rem 1.25rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <span>Filter</span>
            </button>
            <a href="{{ route('admin.hr.salaries') }}" class="btn-user-secondary" style="margin-left: 0.5rem; text-decoration: none; padding: 0.65rem 1rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; border: 1px solid var(--user-border); display: inline-flex; align-items: center;">Reset</a>
        </div>
    </form>
</div>

<!-- Table Container -->
<div class="users-card">
    <div class="users-table-container">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th>Period</th>
                    <th>Base Salary</th>
                    <th>Allowances</th>
                    <th>Deductions</th>
                    <th>Net Salary</th>
                    <th>Status</th>
                    <th>Payout Info</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salaries as $sal)
                    <tr>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar" style="background: linear-gradient(135deg, #d97706, #b45309); color: #fff;">
                                    {{ strtoupper(substr($sal->user->name ?? 'U', 0, 2)) }}
                                </div>
                                <div class="user-meta">
                                    <div class="user-name">{{ $sal->user->name ?? 'Unknown' }}</div>
                                    <div class="user-email">{{ ucfirst($sal->user->role ?? '') }}</div>
                                </div>
                            </div>
                        </td>
                        <td><strong>{{ $sal->formatted_period }}</strong></td>
                        <td>${{ number_format($sal->base_salary, 2) }}</td>
                        <td style="color: #16a34a;">+${{ number_format($sal->allowance, 2) }}</td>
                        <td style="color: #ef4444;">-${{ number_format($sal->deduction, 2) }}</td>
                        <td><strong style="font-size: 1rem; color: var(--user-primary);">${{ number_format($sal->net_salary, 2) }}</strong></td>
                        <td>
                            @if($sal->isPaid())
                                <span class="user-badge active">Paid</span>
                            @else
                                <span class="user-badge suspended">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if($sal->isPaid())
                                <div style="font-size: 0.8rem;">
                                    <div>{{ $sal->payment_date ? $sal->payment_date->format('M d, Y') : 'Paid' }}</div>
                                    <div style="color: var(--user-muted); text-transform: capitalize;">{{ str_replace('_', ' ', $sal->payment_method ?? 'Direct') }}</div>
                                </div>
                            @else
                                <span style="color: var(--user-muted); font-size: 0.85rem;">Unpaid</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            @if(!$sal->isPaid())
                                <form action="{{ route('admin.hr.salaries.markPaid', $sal) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    <button type="submit" class="btn-icon-action view" title="Mark as Paid" style="color: #16a34a; border-color: rgba(22, 163, 74, 0.3);" onclick="return confirm('Mark this salary as disbursed/paid?');">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('admin.hr.salaries.destroy', $sal) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Delete this salary slip?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon-action delete" title="Delete record">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2.5rem; color: var(--user-muted);">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 0.5rem;"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            <p style="margin: 0; font-size: 0.95rem; font-weight: 600;">No salary records found for this period.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($salaries->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--user-border);">
            {{ $salaries->links() }}
        </div>
    @endif
</div>

<!-- Modal: Generate Salary Slip -->
<div class="user-modal-overlay" id="modal_generate_salary">
    <div class="user-modal-card">
        <div class="modal-header">
            <h3 class="modal-title">Generate Staff Salary Slip</h3>
            <button type="button" class="btn-close-modal" id="btn_close_salary_modal">&times;</button>
        </div>
        <form action="{{ route('admin.hr.salaries.store') }}" method="POST" id="form_salary">
            @csrf
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 1.15rem;">
                    <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Staff Member <span style="color: #ef4444;">*</span></label>
                    <select name="user_id" class="form-input" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);">
                        @foreach($staffMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->name }} ({{ ucfirst($member->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1.15rem;">
                    <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Salary Month / Period <span style="color: #ef4444;">*</span></label>
                    <input type="month" name="salary_period" value="{{ Carbon\Carbon::now()->format('Y-m') }}" required class="form-input" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.15rem;">
                    <div class="form-group">
                        <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Base Salary ($) <span style="color: #ef4444;">*</span></label>
                        <input type="number" step="0.01" min="0" name="base_salary" id="salary_base" value="500.00" required class="form-input" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Allowances ($)</label>
                        <input type="number" step="0.01" min="0" name="allowance" id="salary_allowance" value="0.00" class="form-input" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Deductions ($)</label>
                        <input type="number" step="0.01" min="0" name="deduction" id="salary_deduction" value="0.00" class="form-input" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);">
                    </div>
                </div>

                <div style="background: rgba(86, 48, 32, 0.08); border-radius: 10px; padding: 0.85rem 1.25rem; margin-bottom: 1.15rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700; color: var(--user-text);">Estimated Net Salary:</span>
                    <span id="salary_net_display" style="font-size: 1.2rem; font-weight: 800; color: var(--user-primary);">$500.00</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.15rem;">
                    <div class="form-group">
                        <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Payment Status <span style="color: #ef4444;">*</span></label>
                        <select name="payment_status" id="salary_status" class="form-input" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Payment Method</label>
                        <select name="payment_method" class="form-input" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="check">Cheque</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Notes / Remarks</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Overtime bonus, unpaid leaves deduction..." class="form-input" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.25rem; border-top: 1px solid var(--user-border);">
                <button type="button" class="btn-user-secondary" id="btn_cancel_salary_modal" style="padding: 0.65rem 1.25rem; border-radius: 9999px; font-weight: 600; border: 1px solid var(--user-border); cursor: pointer;">Cancel</button>
                <button type="submit" class="btn-user-primary">Create Salary Slip</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modal_generate_salary');
    const btnOpen = document.getElementById('btn_open_salary_modal');
    const btnClose = document.getElementById('btn_close_salary_modal');
    const btnCancel = document.getElementById('btn_cancel_salary_modal');

    const baseInput = document.getElementById('salary_base');
    const allowInput = document.getElementById('salary_allowance');
    const dedInput = document.getElementById('salary_deduction');
    const netDisplay = document.getElementById('salary_net_display');

    function calcNet() {
        const base = parseFloat(baseInput?.value || 0);
        const allow = parseFloat(allowInput?.value || 0);
        const ded = parseFloat(dedInput?.value || 0);
        const net = Math.max(0, base + allow - ded);
        if (netDisplay) {
            netDisplay.textContent = '$' + net.toFixed(2);
        }
    }

    [baseInput, allowInput, dedInput].forEach(el => {
        if (el) el.addEventListener('input', calcNet);
    });

    if (btnOpen) {
        btnOpen.addEventListener('click', () => { modal.style.display = 'flex'; });
    }
    if (btnClose) {
        btnClose.addEventListener('click', () => { modal.style.display = 'none'; });
    }
    if (btnCancel) {
        btnCancel.addEventListener('click', () => { modal.style.display = 'none'; });
    }
    window.addEventListener('click', (e) => {
        if (e.target === modal) modal.style.display = 'none';
    });
});
</script>
@endsection
