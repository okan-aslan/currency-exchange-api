<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Rules\ValidCurrency;
use App\Services\ExchangeRateService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use ApiResponses;

    protected $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    public function convertCurrency(Request $request)
    {
        try {
            $request->validate([
                'base_currency' => ['required', 'string', 'size:3', new ValidCurrency($this->exchangeRateService)],
                'target_currency' => ['required', 'string', 'size:3', new ValidCurrency($this->exchangeRateService)],
                'amount' => ['required', 'numeric', 'min:0.01'],
            ]);

            $baseCurrency = $request->input('base_currency');
            $targetCurrency = $request->input('target_currency');
            $amount = $request->input('amount');

            $convertedValue = $this->exchangeRateService->convertCurrency($baseCurrency, $targetCurrency, $amount);

            if ($convertedValue === null) {
                return $this->error(null, "Döviz kuru hesaplanılamadı", 400);
            }

            $conversionRate = round($amount / $convertedValue, 3);

            return $this->success([
                'from_currency' => $baseCurrency,
                'to_currency' => $targetCurrency,
                'amount' => $amount,
                "conversion_rate" => $conversionRate,
                'converted_value' => round($convertedValue, 3),
                'converted_value_currency' => $targetCurrency,
            ]);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function createAccount(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'currency' => ['required', 'string', 'size:3', new ValidCurrency($this->exchangeRateService)],
            ]);

            $user = $request->user();
            $currency = $request->input('currency');

            if ($user->accounts()->where('currency', $currency)->exists()) {
                return $this->error(null, "Bu para biriminde zaten bir hesabınız bulunmaktadır.", 400);
            }

            $account = $user->accounts()->create([
                'currency' => $currency,
            ]);

            return $this->success(new AccountResource($account), "Hesap başarıyla oluşturuldu", 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function getAllAccounts(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $accounts = $user->accounts()->get();

            return $this->success(AccountResource::collection($accounts));
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function showAccount(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'account_no' => ['required', 'string', 'exists:accounts,account_no']
            ]);

            $account = $request->user()->accounts()->where('account_no', $request->input('account_no'))->firstOrFail();

            return $this->success(new AccountResource($account));
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function getAccountValue(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'account_no' => ['required', 'string', 'exists:accounts,account_no'],
                'target_currency' => ['required', 'string', 'max:3', new ValidCurrency($this->exchangeRateService)]
            ]);

            $account = $request->user()->accounts()->where('account_no', $request->input('account_no'))->firstOrFail();

            if ($account->balance == 0) {
                return $this->error(null, "Hesap bakiyesi sıfır olduğundan döviz kuru hesaplanamıyor.", 400);
            }

            $convertedValue = $this->exchangeRateService->convertCurrency(
                $account->currency,
                $request->input('target_currency'),
                $account->balance
            );

            if ($convertedValue === null) {
                return $this->error(null, "Döviz kuru hesaplanılamadı", 400);
            }

            $conversionRate = round($account->balance / $convertedValue, 3);

            return $this->success([
                'account_currency' => $account->currency,
                'to_currency' => $request->input('target_currency'),
                'balance' => $account->balance,
                "conversion_rate" => $conversionRate,
                'converted_value' => round($convertedValue, 3),
                'converted_value_currency' => $request->input('target_currency'),
            ]);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function deposit(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'account_no' => ['required', 'string', 'exists:accounts,account_no'],
                "amount" => ["required", "numeric", "min:0.01"]
            ]);

            $account = $request->user()->accounts()->where("account_no", $request->input(['account_no']))->firstOrFail();

            $account->balance += $request->input('amount');
            $account->save();

            return $this->success(
                ["account" => new AccountResource($account)],
                "Para başarıyla yatırıldı.",
                200
            );
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function withdraw(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'account_no' => ['required', 'string', 'exists:accounts,account_no'],
                "amount" => ["required", "numeric", "min:0.01"]
            ]);

            $account = $request->user()->accounts()->where("account_no", $request->input(['account_no']))->firstOrFail();

            if ($account->balance < $request->input('amount')) {
                return response()->json(['message' => 'Yetersiz bakiye.'], 400);
            }

            $account->balance -= $request->input('amount');
            $account->save();

            return $this->success(
                ["account" => new AccountResource($account)],
                "Para başarıyla çekildi.",
                200
            );
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        try {
            $request->validate(['required', 'string', 'exists:accounts,account_no']);

            $account = $request->user()->accounts()->where('account_no', $request->input(['account_no']))->firstOrFail();

            $account->delete();

            return $this->success(null, "Hesap başarıyla silindi.", 200);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
