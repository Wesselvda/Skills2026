<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'amount',
        'order_id',
        'callback_url',
        'status',
        'error_code',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    const string STATUS_PENDING = 'pending';

    const string STATUS_FAILED = 'failed';

    const string STATUS_SUCCESSFUL = 'successful';

    public function markAsSuccessful(): void
    {
        $this->update([
            'status' => self::STATUS_SUCCESSFUL,
            'paid_at' => now(),
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->markAsFailed('CANCELLED');
    }

    public function markAsFailed(string $errorCode): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_code' => $errorCode,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->expires_at->isPast();
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESSFUL;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED || $this->expires_at->isPast();
    }

    public function error(): string|null
    {
        return $this->error_code ?? ($this->expires_at->isPast() ? "EXPIRED" : null);
    }
}
