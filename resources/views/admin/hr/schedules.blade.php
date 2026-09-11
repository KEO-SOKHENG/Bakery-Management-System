@extends('layouts.app')

@section('title', 'Work Schedules - Bakery Management System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/users.css') }}">
@endpush

@section('content')
<!-- Header Bar -->
<div class="users-header-bar">
    <div class="users-title-group">
        <h2>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span data-lang-key="work_schedules">Staff Work Schedules</span>
        </h2>
        <p data-lang-key="schedules_subtitle">Plan shifts, duty rosters, and bakery operational hours.</p>
    </div>

    <button type="button" class="btn-user-primary" id="btn_open_schedule_modal">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span data-lang-key="assign_shift">Assign Work Shift</span>
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
    <a href="{{ route('admin.hr.schedules') }}" class="hr-nav-tab active">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span>Work Schedules</span>
    </a>
    <a href="{{ route('admin.hr.salaries') }}" class="hr-nav-tab">
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
        <div class="stat-icon-wrapper active">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Scheduled Shifts In Window</div>
            <div class="stat-number">{{ $stats['total_shifts'] }}</div>
        </div>
    </div>

    <div class="user-stat-card">
        <div class="stat-icon-wrapper total">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Shifts Scheduled Today</div>
            <div class="stat-number">{{ $stats['scheduled_today'] }}</div>
        </div>
    </div>
</div>

<!-- Filters Toolbar -->
<div class="users-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.hr.schedules') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 160px;">
            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--user-muted); margin-bottom: 0.35rem;">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="user-search-input" style="width: 100%;">
        </div>

        <div style="flex: 1; min-width: 160px;">
            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--user-muted); margin-bottom: 0.35rem;">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="user-search-input" style="width: 100%;">
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

        <div>
            <button type="submit" class="btn-user-primary" style="padding: 0.65rem 1.25rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <span>Filter</span>
            </button>
            <a href="{{ route('admin.hr.schedules') }}" class="btn-user-secondary" style="margin-left: 0.5rem; text-decoration: none; padding: 0.65rem 1rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; border: 1px solid var(--user-border); display: inline-flex; align-items: center;">Reset</a>
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
                    <th>Date</th>
                    <th>Shift</th>
                    <th>Working Hours</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $shift)
                    <tr>
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar" style="background: linear-gradient(135deg, #10b981, #047857); color: #fff;">
                                    {{ strtoupper(substr($shift->user->name ?? 'U', 0, 2)) }}
                                </div>
                                <div class="user-meta">
                                    <div class="user-name">{{ $shift->user->name ?? 'Unknown' }}</div>
                                    <div class="user-email">{{ ucfirst($shift->user->role ?? '') }}</div>
                                </div>
                            </div>
                        </td>
                        <td><strong>{{ $shift->schedule_date ? $shift->schedule_date->format('M d, Y') : '-' }}</strong></td>
                        <td><span style="font-weight: 700; color: var(--user-primary);">{{ $shift->shift_name }}</span></td>
                        <td>{{ $shift->formatted_time_range }}</td>
                        <td>
                            @if($shift->status === 'completed')
                                <span class="user-badge active">Completed</span>
                            @elseif($shift->status === 'cancelled')
                                <span class="user-badge inactive">Cancelled</span>
                            @else
                                <span class="user-badge suspended">Scheduled</span>
                            @endif
                        </td>
                        <td style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $shift->notes ?? '-' }}
                        </td>
                        <td style="text-align: right;">
                            <form action="{{ route('admin.hr.schedules.destroy', $shift) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Remove this work schedule shift?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon-action delete" title="Delete shift">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--user-muted);">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 0.5rem;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <p style="margin: 0; font-size: 0.95rem; font-weight: 600;">No shifts scheduled for this date range.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($schedules->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--user-border);">
            {{ $schedules->links() }}
        </div>
    @endif
</div>

<!-- Modal: Assign Work Shift -->
<div class="user-modal-overlay" id="modal_assign_schedule">
    <div class="user-modal-card">
        <div class="modal-header">
            <h3 class="modal-title">Assign Staff Work Shift</h3>
            <button type="button" class="btn-close-modal" id="btn_close_schedule_modal">&times;</button>
        </div>
        <form action="{{ route('admin.hr.schedules.store') }}" method="POST">
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
                    <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Shift Date <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="schedule_date" value="{{ Carbon\Carbon::today()->toDateString() }}" required class="form-input" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);">
                </div>

                <div class="form-group" style="margin-bottom: 1.15rem;">
                    <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Shift Name / Title <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="shift_name" value="Morning Baking Shift" required class="form-input" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.15rem;">
                    <div class="form-group">
                        <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Start Time <span style="color: #ef4444;">*</span></label>
                        <input type="time" name="start_time" value="06:00" required class="form-input" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">End Time <span style="color: #ef4444;">*</span></label>
                        <input type="time" name="end_time" value="14:00" required class="form-input" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.15rem;">
                    <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Status</label>
                    <select name="status" class="form-input" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);">
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Notes / Special Tasks</label>
                    <textarea name="notes" rows="3" placeholder="e.g. Sourdough prep, Inventory counting..." class="form-input" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 10px; border: 1px solid var(--user-border); background: var(--user-card-bg); color: var(--user-text);"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.25rem; border-top: 1px solid var(--user-border);">
                <button type="button" class="btn-user-secondary" id="btn_cancel_schedule_modal" style="padding: 0.65rem 1.25rem; border-radius: 9999px; font-weight: 600; border: 1px solid var(--user-border); cursor: pointer;">Cancel</button>
                <button type="submit" class="btn-user-primary">Assign Shift</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modal_assign_schedule');
    const btnOpen = document.getElementById('btn_open_schedule_modal');
    const btnClose = document.getElementById('btn_close_schedule_modal');
    const btnCancel = document.getElementById('btn_cancel_schedule_modal');

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
