<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'salary_period',
        'base_salary',
        'allowance',
        'deduction',
        'net_salary',
        'payment_status',
        'payment_date',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return strtolower($this->payment_status) === 'paid';
    }

    public function getFormattedPeriodAttribute(): string
    {
        try {
            return Carbon::parse($this->salary_period . '-01')->format('F Y');
        } catch (\Exception $e) {
            return $this->salary_period;
        }
    }
}
