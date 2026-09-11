<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Salary;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $baker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'admin_hr_test'],
            [
                'name' => 'Admin HR',
                'email' => 'admin_hr@test.com',
                'password' => Hash::make('pass123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        $this->baker = User::firstOrCreate(
            ['username' => 'baker_hr_test'],
            [
                'name' => 'Baker HR',
                'email' => 'baker_hr@test.com',
                'password' => Hash::make('pass123'),
                'role' => 'baker',
                'status' => 'active',
            ]
        );
    }

    public function test_admin_can_view_and_record_attendance()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.hr.attendance'));
        $response->assertStatus(200);
        $response->assertSee('Staff Attendance');

        $today = Carbon::today()->toDateString();

        $storeResponse = $this->actingAs($this->admin)->post(route('admin.hr.attendance.store'), [
            'user_id' => $this->baker->id,
            'date' => $today,
            'check_in' => '07:30',
            'check_out' => '16:00',
            'status' => 'present',
            'notes' => 'On time morning shift',
        ]);

        $storeResponse->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->baker->id,
            'status' => 'present',
            'notes' => 'On time morning shift',
        ]);

        $attendance = Attendance::where('user_id', $this->baker->id)->first();
        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->work_duration);
    }

    public function test_admin_can_assign_work_schedule()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.hr.schedules'));
        $response->assertStatus(200);
        $response->assertSee('Staff Work Schedules');

        $scheduleDate = Carbon::tomorrow()->toDateString();

        $storeResponse = $this->actingAs($this->admin)->post(route('admin.hr.schedules.store'), [
            'user_id' => $this->baker->id,
            'schedule_date' => $scheduleDate,
            'start_time' => '06:00',
            'end_time' => '14:00',
            'shift_name' => 'Early Dawn Bake',
            'status' => 'scheduled',
            'notes' => 'Croissant rolling duty',
        ]);

        $storeResponse->assertRedirect();

        $this->assertDatabaseHas('work_schedules', [
            'user_id' => $this->baker->id,
            'shift_name' => 'Early Dawn Bake',
            'status' => 'scheduled',
        ]);

        $shift = WorkSchedule::where('user_id', $this->baker->id)->first();
        $this->assertNotNull($shift);
        $this->assertStringContainsString('06:00 AM', $shift->formatted_time_range);
    }

    public function test_admin_can_generate_salary_slip_and_mark_as_paid()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.hr.salaries'));
        $response->assertStatus(200);
        $response->assertSee('Salaries');

        $period = Carbon::now()->format('Y-m');

        $storeResponse = $this->actingAs($this->admin)->post(route('admin.hr.salaries.store'), [
            'user_id' => $this->baker->id,
            'salary_period' => $period,
            'base_salary' => 600.00,
            'allowance' => 50.00,
            'deduction' => 20.00,
            'payment_status' => 'pending',
            'notes' => 'Monthly baker compensation',
        ]);

        $storeResponse->assertRedirect();

        $salary = Salary::where('user_id', $this->baker->id)->where('salary_period', $period)->first();
        $this->assertNotNull($salary);
        $this->assertEquals(630.00, (float) $salary->net_salary);
        $this->assertFalse($salary->isPaid());

        // Mark as paid
        $payResponse = $this->actingAs($this->admin)->post(route('admin.hr.salaries.markPaid', $salary), [
            'payment_date' => Carbon::today()->toDateString(),
            'payment_method' => 'bank_transfer',
        ]);

        $payResponse->assertRedirect();

        $salary->refresh();
        $this->assertTrue($salary->isPaid());
        $this->assertEquals('bank_transfer', $salary->payment_method);
    }
}
