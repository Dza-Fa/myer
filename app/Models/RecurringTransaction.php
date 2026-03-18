<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'type',
        'amount',
        'frequency',
        'day_of_month',
        'next_run_date',
        'total_runs',
        'run_count',
        'is_active',
        'last_executed_at',
    ];

    protected function casts(): array
    {
        return [
            'next_run_date' => 'date',
            'last_executed_at' => 'datetime',
            'is_active' => 'boolean',
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scheduledJobs(): HasMany
    {
        return $this->hasMany(ScheduledJob::class);
    }
}
