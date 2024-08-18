<?php

namespace App\Rules;

use App\Services\ExchangeRateService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCurrency implements ValidationRule
{
    protected $supportedCurrencies;

    /**
     * ValidCurrency constructor.
     *
     * @param ExchangeRateService $exchangeRateService
     */
    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->supportedCurrencies = $exchangeRateService->getSupportedCurrencies();
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array(strtoupper($value), $this->supportedCurrencies)) {
            $fail('The selected currency is not supported.');
        }
    }
}