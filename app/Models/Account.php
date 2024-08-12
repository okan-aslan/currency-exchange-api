<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
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
}
