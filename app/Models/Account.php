<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['currency'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($account) {
            $account->account_no = self::generateUniqueAccountNo();
        });
    }

    private static function generateUniqueAccountNo()
    {
        do {
            $accountNo = Str::upper(Str::random(12));
        } while (self::where('account_no', $accountNo)->exists());

        return $accountNo;
    }

    /**
     * Get the user associated with the Account
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * Get all of the transactions for the Account
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all of the comments for the Account
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function incomingTransactions()
    {
        return $this->hasMany(Transaction::class, 'target_account_id');
    }

    public function deposit(float $amount): void
    {
        $this->balance += $amount;
        $this->save();
    }

    public function withdraw(float $amount): void
    {
        if ($this->balance < $amount) {
            throw new \Exception("Yetersiz bakiye.");
        }

        $this->balance -= $amount;
        $this->save();
    }

    public function transferTo(Account $targetAccount, float $amount): void
    {
        if ($this->balance < $amount) {
            throw new \Exception("Yetersiz bakiye.");
        }

        if ($this->currency !== $targetAccount->currency) {
            throw new \Exception("Para birimleri uyumsuz.");
        }

        if ($this->user_id === $targetAccount->user_id) {
            throw new \Exception("Kendinize transfer yapamazsınız.");
        }

        DB::transaction(function () use ($targetAccount, $amount) {
            $this->withdraw($amount);
            $targetAccount->deposit($amount);
        });
    }
}
