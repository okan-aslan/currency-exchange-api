<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = ['account_id', 'user_id', 'target_account_id', 'amount', 'type'];

    /**
     * Get the user that performs the transaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account that owns the Transaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user that owns the Transaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function targetAccount(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a transaction in the database.
     *
     * @param int $userId The ID of the user who is making the transaction.
     * @param int $accountId The ID of the account from which the transaction is made.
     * @param string $type The type of the transaction (e.g., 'deposit', 'withdraw', 'transfer').
     * @param float $amount The amount involved in the transaction.
     * @param int|null $targetAccountId The ID of the target account for transfers, or null for other transaction types.
     * @return void
     */
    public static function recordTransaction(int $userId, int $accountId, string $type, float $amount, int $targetAccountId = null): void
    {
        self::create([
            'user_id' => $userId,
            'account_id' => $accountId,
            'type' => $type,
            'amount' => $amount,
            'target_account_id' => $targetAccountId,
        ]);
    }
}
