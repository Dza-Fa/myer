<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'idempotency_key',
        'client_request_id',
        'account_id',
        'transfer_account_id',
        'category_id',
        'type',
        'amount',
        'transaction_date',
        'notes',
        'status',
        'posting_date',
        'version',
        'reversal_of_id',
        'is_reversal',
        'transaction_group_id',
        'cleared',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'posting_date' => 'datetime',
            'is_reversal' => 'boolean',
            'cleared' => 'boolean',
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

    public function transferAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'transfer_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'reversal_of_id');
    }

    public function reversals(): HasMany
    {
        return $this->hasMany(Transaction::class, 'reversal_of_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'transaction_tags');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TransactionLog::class);
    }
}
