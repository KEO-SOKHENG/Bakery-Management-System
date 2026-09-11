<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Salary;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HrController extends Controller
{
    // ==========================================
    // ATTENDANCE MANAGEMENT
    // ==========================================

    public function attendance(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $staffId = $request->input('user_id');
        $status = $request->input('status');

        $query = Attendance::with('user')
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($selectedDate) {
            $query->whereDate('date', $selectedDate);
        }

        if ($staffId) {
            $query->where('user_id', $staffId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $attendances = $query->paginate(20)->appends($request->all());

        // Summary stats for today / selected date
        $statsDate = $selectedDate ?: Carbon::today()->toDateString();
        $dateRecords = Attendance::whereDate('date', $statsDate)->get();

        $stats = [
            'present' => $dateRecords->where('status', 'present')->count(),
            'late'    => $dateRecords->where('status', 'late')->count(),
            'leave'   => $dateRecords->whereIn('status', ['on_leave', 'absent'])->count(),
            'total'   => $dateRecords->count(),
        ];

        $staffMembers = User::where('status', 'active')->orderBy('name')->get();

        return view('admin.hr.attendance', compact('attendances', 'stats', 'staffMembers', 'selectedDate', 'staffId', 'status'));
    }

    public function storeAttendance(Request $request)
    {
        $validated = $request->validate([
            'user_id'   => 'required|exists:users,id',
            'date'      => 'required|date',
            'check_in'  => 'nullable|string',
            'check_out' => 'nullable|string',
            'status'    => 'required|string|in:present,late,absent,on_leave',
            'notes'     => 'nullable|string|max:500',
        ]);

        Attendance::updateOrCreate(
            ['user_id' => $validated['user_id'], 'date' => $validated['date']],
            [
                'check_in'  => $validated['check_in'] ?: null,
                'check_out' => $validated['check_out'] ?: null,
                'status'    => $validated['status'],
                'notes'     => $validated['notes'] ?? null,
            ]
        );

        return back()->with('success', __('messages.attendance_recorded_success', ['default' => 'Attendance recorded successfully.']));
    }

    public function updateAttendance(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'check_in'  => 'nullable|string',
            'check_out' => 'nullable|string',
            'status'    => 'required|string|in:present,late,absent,on_leave',
            'notes'     => 'nullable|string|max:500',
        ]);

        $attendance->update([
            'check_in'  => $validated['check_in'] ?: null,
            'check_out' => $validated['check_out'] ?: null,
            'status'    => $validated['status'],
            'notes'     => $validated['notes'] ?? null,
        ]);

        return back()->with('success', __('messages.attendance_updated_success', ['default' => 'Attendance updated successfully.']));
    }

    public function destroyAttendance(Attendance $attendance)
    {
        $attendance->delete();
        return back()->with('success', __('messages.attendance_deleted_success', ['default' => 'Attendance record removed.']));
    }

    // ==========================================
    // WORK SCHEDULES MANAGEMENT
    // ==========================================

    public function schedules(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->addDays(6)->toDateString());
        $staffId = $request->input('user_id');

        $query = WorkSchedule::with('user')
            ->orderBy('schedule_date', 'asc')
            ->orderBy('start_time', 'asc');

        if ($startDate && $endDate) {
            $query->whereBetween('schedule_date', [$startDate, $endDate]);
        }

        if ($staffId) {
            $query->where('user_id', $staffId);
        }

        $schedules = $query->paginate(25)->appends($request->all());

        $staffMembers = User::where('status', 'active')->orderBy('name')->get();

        $stats = [
            'total_shifts'    => WorkSchedule::whereBetween('schedule_date', [$startDate, $endDate])->count(),
            'scheduled_today' => WorkSchedule::whereDate('schedule_date', Carbon::today())->count(),
        ];

        return view('admin.hr.schedules', compact('schedules', 'stats', 'staffMembers', 'startDate', 'endDate', 'staffId'));
    }

    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'schedule_date' => 'required|date',
            'start_time'    => 'required|string',
            'end_time'      => 'required|string',
            'shift_name'    => 'required|string|max:100',
            'status'        => 'nullable|string|in:scheduled,completed,cancelled',
            'notes'         => 'nullable|string|max:500',
        ]);

        WorkSchedule::create([
            'user_id'       => $validated['user_id'],
            'schedule_date' => $validated['schedule_date'],
            'start_time'    => $validated['start_time'],
            'end_time'      => $validated['end_time'],
            'shift_name'    => $validated['shift_name'],
            'status'        => $validated['status'] ?? 'scheduled',
            'notes'         => $validated['notes'] ?? null,
        ]);

        return back()->with('success', __('messages.schedule_created_success', ['default' => 'Work schedule assigned successfully.']));
    }

    public function updateSchedule(Request $request, WorkSchedule $workSchedule)
    {
        $validated = $request->validate([
            'schedule_date' => 'required|date',
            'start_time'    => 'required|string',
            'end_time'      => 'required|string',
            'shift_name'    => 'required|string|max:100',
            'status'        => 'required|string|in:scheduled,completed,cancelled',
            'notes'         => 'nullable|string|max:500',
        ]);

        $workSchedule->update($validated);

        return back()->with('success', __('messages.schedule_updated_success', ['default' => 'Work schedule updated.']));
    }

    public function destroySchedule(WorkSchedule $workSchedule)
    {
        $workSchedule->delete();
        return back()->with('success', __('messages.schedule_deleted_success', ['default' => 'Schedule removed.']));
    }

    // ==========================================
    // PAYROLL & SALARIES MANAGEMENT
    // ==========================================

    public function salaries(Request $request)
    {
        $period = $request->input('period', Carbon::now()->format('Y-m'));
        $status = $request->input('payment_status');
        $staffId = $request->input('user_id');

        $query = Salary::with('user')->orderBy('salary_period', 'desc')->orderBy('id', 'desc');

        if ($period) {
            $query->where('salary_period', $period);
        }

        if ($status) {
            $query->where('payment_status', $status);
        }

        if ($staffId) {
            $query->where('user_id', $staffId);
        }

        $salaries = $query->paginate(20)->appends($request->all());

        // Stats for current period
        $periodRecords = Salary::where('salary_period', $period)->get();
        $stats = [
            'total_payroll' => $periodRecords->sum('net_salary'),
            'total_paid'    => $periodRecords->where('payment_status', 'paid')->sum('net_salary'),
            'pending_count' => $periodRecords->where('payment_status', 'pending')->count(),
            'paid_count'    => $periodRecords->where('payment_status', 'paid')->count(),
        ];

        $staffMembers = User::where('status', 'active')->orderBy('name')->get();

        return view('admin.hr.salaries', compact('salaries', 'stats', 'staffMembers', 'period', 'status', 'staffId'));
    }

    public function storeSalary(Request $request)
    {
        $validated = $request->validate([
            'user_id'        => 'required|exists:users,id',
            'salary_period'  => 'required|string|max:20', // e.g. "2026-09"
            'base_salary'    => 'required|numeric|min:0',
            'allowance'      => 'nullable|numeric|min:0',
            'deduction'      => 'nullable|numeric|min:0',
            'payment_status' => 'required|string|in:pending,paid',
            'payment_date'   => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'notes'          => 'nullable|string|max:500',
        ]);

        $base = (float) $validated['base_salary'];
        $allowance = (float) ($validated['allowance'] ?? 0);
        $deduction = (float) ($validated['deduction'] ?? 0);
        $netSalary = max(0, $base + $allowance - $deduction);

        Salary::create([
            'user_id'        => $validated['user_id'],
            'salary_period'  => $validated['salary_period'],
            'base_salary'    => $base,
            'allowance'      => $allowance,
            'deduction'      => $deduction,
            'net_salary'     => $netSalary,
            'payment_status' => $validated['payment_status'],
            'payment_date'   => $validated['payment_status'] === 'paid' ? ($validated['payment_date'] ?? Carbon::today()->toDateString()) : null,
            'payment_method' => $validated['payment_method'] ?? null,
            'notes'          => $validated['notes'] ?? null,
        ]);

        return back()->with('success', __('messages.salary_created_success', ['default' => 'Salary record generated successfully.']));
    }

    public function markSalaryPaid(Request $request, Salary $salary)
    {
        $validated = $request->validate([
            'payment_date'   => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $salary->update([
            'payment_status' => 'paid',
            'payment_date'   => $validated['payment_date'] ?? Carbon::today()->toDateString(),
            'payment_method' => $validated['payment_method'] ?? 'bank_transfer',
        ]);

        return back()->with('success', __('messages.salary_marked_paid_success', ['default' => 'Salary marked as paid.']));
    }

    public function destroySalary(Salary $salary)
    {
        $salary->delete();
        return back()->with('success', __('messages.salary_deleted_success', ['default' => 'Salary record deleted.']));
    }
}
