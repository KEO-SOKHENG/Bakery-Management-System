<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedCheckInAttribute(): ?string
    {
        if (!$this->check_in) {
            return null;
        }
        return Carbon::parse($this->check_in)->format('h:i A');
    }

    public function getFormattedCheckOutAttribute(): ?string
    {
        if (!$this->check_out) {
            return null;
        }
        return Carbon::parse($this->check_out)->format('h:i A');
    }

    public function getWorkDurationAttribute(): ?string
    {
        if (!$this->check_in || !$this->check_out) {
            return null;
        }
        $start = Carbon::parse($this->check_in);
        $end = Carbon::parse($this->check_out);
        $diffMinutes = $start->diffInMinutes($end);
        $hours = floor($diffMinutes / 60);
        $mins = $diffMinutes % 60;
        return "{$hours}h {$mins}m";
    }
}
