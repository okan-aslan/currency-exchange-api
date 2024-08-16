<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConvertCurrencyRequest;
use App\Services\ExchangeRateService;
use App\Traits\ApiResponses;

class ConvertCurrencyController extends Controller
{
    use ApiResponses;

    protected $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    public function convertCurrency(ConvertCurrencyRequest $request)
    {
        try {
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
}
