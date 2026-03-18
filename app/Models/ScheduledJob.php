<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'recurring_transaction_id',
        'user_id',
        'transaction_id',
        'scheduled_at',
        'status',
        'error_message',
        'attempts',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
