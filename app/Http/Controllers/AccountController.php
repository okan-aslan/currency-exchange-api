<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\GetAccountValueRequest;
use App\Http\Resources\AccountResource;
use App\Services\ExchangeRateService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use ApiResponses;

    protected $exchangeRateService;

    /**
     * AccountController constructor.
     *
     * @param ExchangeRateService $exchangeRateService
     */
    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    /**
     * Create a new account for the authenticated user.
     *
     * @param CreateAccountRequest $request
     * @return JsonResponse
     */
    public function createAccount(CreateAccountRequest $request): JsonResponse
    {
        try {
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

    /**
     * Get all accounts of the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Show a specific account of the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Convert the balance of an account to a different currency.
     *
     * @param GetAccountValueRequest $request
     * @return JsonResponse
     */
    public function getAccountValue(GetAccountValueRequest $request): JsonResponse
    {
        try {
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

    /**
     * Delete a specific account of the authenticated user.
     *
     * @param DeleteAccountRequest $request
     * @return JsonResponse
     */
    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        try {
            $accountNo = $request->input('account_no');

            $account = $request->user()->accounts()->where('account_no', $accountNo)->firstOrFail();

            $account->delete();

            return $this->success(null, "Hesap başarıyla silindi.", 200);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
