<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    protected $baseUrl = "https://v6.exchangerate-api.com/v6/";
    protected $apiKey;

    /**
     * ExchangeRateService constructor.
     *
     * @param ExchangeRateService $exchangeRateService
     */
    public function __construct()
    {
        $this->apiKey = config('services.exchange_rate.api_key');
    }

    /**
     * Get exchange rates for the specified base currency.
     *
     * @param string $baseCurrency
     * @return array|null
     */
    public function getRates(string $baseCurrency = "USD"): ?array
    {
        $url = $this->baseUrl . $this->apiKey . '/latest' . $baseCurrency;

        $response = Http::get($url);

        if ($response->successful()) {
            return $response->json();
        };

        return null;
    }

    /**
     * Convert an amount from the base currency to the target currency.
     *
     * @param string $baseCurrency
     * @param string $targetCurrency
     * @param float $amount
     * @return float|null
     */
    public function convertCurrency(string $baseCurrency, string $targetCurrency, float $amount): ?float
    {
        $url = $this->baseUrl . $this->apiKey . '/pair/' . $baseCurrency . '/' . $targetCurrency;

        $response = Http::get($url);

        if ($response->successful()) {
            $rate = $response->json()['conversion_rate'];
            return $amount * $rate;
        }

        return null;
    }

    /**
     * Get a list of supported currencies.
     *
     * @return string[]
     */
    public function getSupportedCurrencies(): array
    {
        $response = Http::get($this->baseUrl . $this->apiKey . '/codes');

        if ($response->successful()) {

            $data = $response->json();
            if ($data['result'] === 'success') {
                return array_column($data['supported_codes'], 0);
            }
        }

        return [];
    }
}
