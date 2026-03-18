<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlySummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'total_income',
        'total_expense',
        'net_savings',
        'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'last_updated_at' => 'datetime',
            'total_income' => 'decimal:2',
            'total_expense' => 'decimal:2',
            'net_savings' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
