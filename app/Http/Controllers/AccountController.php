<?php

namespace App\Http\Controllers;

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
            return response()->json(['message' => 'Bu para biriminde zaten bir hesabınız var.'], 400);
        }

        $account = $user->accounts()->create([
            'currency' => $currency,
        ]);

        return response()->json($account, 201);
    }

    public function getAllAccounts(Request $request)
    {
        $user = $request->user();

        $accounts = $user->accounts();

        return response()->json([$accounts], 200);
    }

    public function showAccount(Request $request, $accountNo)
    {
        $account = $request->user()->accounts()->where('account_no', $accountNo)->firstOrFail();

        return response()->json($account, 200);
    }

    public function getAccountValue(Request $request, $accountNo, $targetCurrency)
    {
        $account = $request->user()->accounts()->where('account_no', $accountNo)->firstOrFail();

        $convertedValue = $this->exchangeRateService->convertCurrency($account->currency, $targetCurrency, $account->balance);

        // dd($convertedValue);

        if ($convertedValue === null) {
            return response()->json(['message' => 'Döviz kuru hesaplanamadı.'], 400);
        }

        return response()->json([
            'account_currency' => $account->currency,
            'balance' => $account->balance,
            'converted_value' => $convertedValue,
            'to_currency' => $targetCurrency,
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
            "account" => $account,
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
            "account" => $account,
        ], 200);
    }

    public function deleteAccount(Request $request, $accountNo)
    {
        $account = $request->user()->accounts()->where('account_no', $accountNo)->firstOrFail();

        $account->delete();

        return response()->json(['message' => 'Hesap başarıyla silindi.'], 200);
    }
}
