<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Services\ExchangeRateService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use ApiResponses;

    protected $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    public function createAccount(Request $request)
    {
        $user = $request->user();
        $currency = $request->input('currency');

        if ($user->accounts()->where('currency', $currency)->exists()) {
            return $this->error(null, "Bu para biriminde zaten bir hesabınız bulunmaktadır.", 400);
        }

        $account = $user->accounts()->create([
            'currency' => $currency,
        ]);

        return $this->success(new AccountResource($account), "Hesap başarıyla oluşturuldu", 201);
    }

    public function getAllAccounts(Request $request)
    {
        $user = $request->user();

        $accounts = $user->accounts()->get();

        return $this->success(AccountResource::collection($accounts));
    }

    public function showAccount(Request $request, $accountNo)
    {
        $account = $request->user()->accounts()->where('account_no', $accountNo)->firstOrFail();

        return $this->success(new AccountResource($account));
    }

    public function getAccountValue(Request $request, $accountNo, $targetCurrency)
    {
        $account = $request->user()->accounts()->where('account_no', $accountNo)->firstOrFail();

        $convertedValue = $this->exchangeRateService->convertCurrency($account->currency, $targetCurrency, $account->balance);

        // dd($convertedValue);

        if ($convertedValue === null) {
            return $this->error(null, "Döviz kuru hesaplanılamadı", 400);
        }

        return response()->json([
            'account_currency' => $account->currency,
            'to_currency' => $targetCurrency,
            'balance' => $account->balance,
            "conversion_rate" => ($account->balance / $convertedValue),
            'converted_value' => $convertedValue,
        ]);
    }

    public function deposit(Request $request, $accountNo)
    {
        $request->validate([
            "amount" => ["required", "numeric", "min:0.01"]
        ]);

        $account = $request->user()->accounts()->where("account_no", $accountNo)->firstOrFail();

        $account->balance += $request->input('amount');
        $account->save();

        return response()->json([
            "message" => "Para başarıyla yatırıldı.",
            "account" => new AccountResource($account),
        ], 200);
    }

    public function withdraw(Request $request, $accountNo)
    {
        $request->validate([
            "amount" => ["required", "numeric", "min:0.01"]
        ]);

        $account = $request->user()->accounts()->where("account_no", $accountNo)->firstOrFail();

        if ($account->balance < $request->input('amount')) {
            return response()->json(['message' => 'Yetersiz bakiye.'], 400);
        }

        $account->balance -= $request->input('amount');
        $account->save();

        return response()->json([
            "message" => "Para başarıyla çekildi.",
            "account" => new AccountResource($account),
        ], 200);
    }

    public function deleteAccount(Request $request, $accountNo)
    {
        $account = $request->user()->accounts()->where('account_no', $accountNo)->firstOrFail();

        $account->delete();

        return response()->json(['message' => 'Hesap başarıyla silindi.'], 200);
    }
}
